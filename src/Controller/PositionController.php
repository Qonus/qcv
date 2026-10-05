<?php

namespace App\Controller;

use App\Entity\CV;
use App\Entity\Position;
use App\Entity\User;
use App\Enum\Level;
use App\Repository\AttributeRepository;
use App\Repository\CVRepository;
use App\Repository\PositionRepository;
use App\Repository\TagRepository;
use App\Service\AccessRuleService;
use App\Service\AutosaveService;
use App\Service\PositionAggregator;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\OptimisticLockException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

class PositionController extends AbstractController {
    private const EDITABLE_FIELDS = ['name', 'description', 'company', 'level', 'maxProjects'];

    public function __construct(
        private PositionRepository $positionRepository,
        private AttributeRepository $attributeRepository,
        private TagRepository $tagRepository,
        private AutosaveService $autosaveService,
        private AccessRuleService $accessRuleService,
        private TranslatorInterface $translator,
        private CVRepository $cvRepository,
        private EntityManagerInterface $em) {
    }

    #[IsGranted('edit', 'position')]
    #[Route('/position/api-token/{id}', name: 'app_position_api_token_generate', methods: ['POST'])]
    public function generate(Position $position, Request $request, EntityManagerInterface $em): Response
    {
        if (!$this->isCsrfTokenValid('position_api_token'.$position->getId(), $request->request->get('_token'))) {
            throw $this->createAccessDeniedException();
        }
        $token = bin2hex(random_bytes(32));
        $position->setApiTokenHash($token);
        $em->flush();
        // TODO: translate the message
        $this->addFlash('success', $this->translator->trans('success.generated'));
        return $this->redirectToRoute('app_position_show', ['id' => $position->getId()]);
    }

    #[Route('/api/v1/position-results', methods: ['GET'])]
    public function results(Request $request, PositionRepository $repo, PositionAggregator $agg): JsonResponse
    {
        $h = $request->headers->get('Authorization', '');
        if (!str_starts_with($h, 'Bearer ')) return $this->json(['error' => 'missing token'], 401);
        $position = $repo->findOneBy(['apiTokenHash' => substr($h,7)]);
        if (!$position) return $this->json(['error' => 'invalid token'], 401);
        return $this->json($agg->aggregate($position));
    }

    #[Route(path: "/position", name: "app_positions")]
    // TODO: display searched tag in the template
    public function index(Request $request) {
        $tag = $request->query->get("tag");
        $positions = $this->positionRepository->popular(query: $request->query->get('q'), tag: $tag);
        return $this->render("position/index.html.twig", [
            "positions"=> $positions
        ]);
    }

    #[Route(path: "/position/show/{id}", name: "app_position_show")]
    public function show(Position $position, #[CurrentUser]User $user) {
        if (!$position) {
            throw $this->createNotFoundException('The position does not exist');
        }
        $cv = $this->cvRepository->findOneBy(['position' => $position, 'candidate'=>$user]);
        return $this->render("position/show.html.twig", [
            "position"=> $position,
            'cv' => $cv
        ]);
    }

    #[IsGranted("ROLE_RECRUITER")]
    #[Route(path: "/position/cvs/{id}", name: "app_position_cvs")]
    public function cvs(Position $position, #[CurrentUser]User $recruiter) {
        if (!$position) {
            throw $this->createNotFoundException('The position does not exist');
        }
        $cvs = $this->cvRepository->findBy(['position' => $position, 'isPublic' => true]);
        return $this->render("position/cvs.html.twig", [
            'cvs' => $cvs,
            'liked_cvs' => array_map(fn($l) => $l->getCv(), $recruiter->getLikes()->toArray())
        ]);
    }

    #[Route(path: '/position/apply/{id}', name: 'app_position_apply')]
    public function apply(Position $position, #[CurrentUser]User $candidate) {
        $cv = $this->cvRepository->findOneBy(['position' => $position, 'candidate'=>$candidate]);
        if ($cv == null && $this->isGranted('apply', $position)) {
            $cv = new CV();
            $cv->setCandidate($candidate);
            $cv->setPosition($position);
            $this->em->persist($cv);
            $this->em->flush();
        }
        elseif ($cv == null) {
            $this->addFlash('danger', $this->translator->trans('errors.empty_fields'));
            return $this->redirectToRoute('app_profile');
        }
        return $this->redirectToRoute('app_cv_edit', ['id' => $cv->getId()]);
    }

    #[IsGranted('ROLE_RECRUITER')]
    #[Route(path: "/position/create", name: "app_position_create")]
    public function create() {
        $position = new Position();
        $position->setName('Untitled Position');
        $this->em->persist($position);
        $this->em->flush();
        $this->addFlash('success', $this->translator->trans('success.created'));
        return $this->redirectToRoute('app_position_edit', ['id' => $position->getId()]);
    }

    #[IsGranted('ROLE_RECRUITER')]
    #[Route(path: "/position/edit/{id}", name: "app_position_edit")]
    public function edit(Position $position, Request $request) {
        if (!$position) {
            throw $this->createNotFoundException($this->translator->trans('errors.404.no_position'));
        }

        if ($request->isMethod('POST')) {
            // dd($request->request);
            try {
                // CSRF Validation
                if (!$this->isCsrfTokenValid('position_form', $request->request->get('_token'))) {
                    throw $this->createAccessDeniedException('Invalid CSRF token.');
                }
                if ($position->getVersion() != $request->request->get("version")) {
                    $this->addFlash("error", $this->translator->trans("errors.version_conflict.message"));
                    return $this->render("position/edit.html.twig", [
                        "position" => $position
                    ]);
                }
                $position->setCompany($request->request->get('company'));
                $position->setName($request->request->get('title'));
                if ($request->request->get('level') != '') $position->setLevel(Level::from($request->request->get('level')));
                $position->setDescription($request->request->get('description'));
                $position->setMaxProjects($request->request->get('maxProjects'));
                
                $tagIds = $request->request->all('tags');
                $tags = $this->tagRepository->findBy(['id' => $tagIds]);
                $position->setTags($tags);

                $attributeIds = $request->request->all('attributes');
                $attributes = $this->attributeRepository->findBy(['id' => $attributeIds]);
                $position->setAttributes($attributes);

                $accessRules = $request->request->all('filters');
                foreach ($position->getAccessRules() as $existingRule) {
                    $position->getAccessRules()->removeElement($existingRule);
                    $this->em->remove($existingRule);
                }
                foreach ($accessRules as $accessRule) {
                    if ($accessRule['attributeId'] == null || $accessRule['attributeDimension'] == null || $accessRule['operation'] == null || $accessRule['filterValue'] == null) {
                        $this->addFlash('error', $this->translator->trans('errors.empty_fields'));
                        return $this->redirectToRoute('app_position_edit', ['id' => $position->getId()]);
                    }
                    $newAccessRule = $this->accessRuleService->createAccessRule(
                        $position,
                        $accessRule['matchType']??'and',
                        $this->attributeRepository->find($accessRule['attributeId']),
                        $accessRule['attributeDimension'],
                        $accessRule['operation'],
                        $accessRule['filterValue']
                    );
                    $this->em->persist($newAccessRule);
                }

                $this->em->flush();
            } catch (OptimisticLockException) {
                $this->addFlash("error", $this->translator->trans("errors.version_conflict.message"));
                return $this->render("position/edit.html.twig", [
                    "position" => $position
                ]);
            }

            $this->addFlash('success', $this->translator->trans('success.updated'));
            return $this->redirectToRoute('app_position_show', ['id' => $position->getId()]);
        }

        return $this->render("position/edit.html.twig", [
            "position" => $position
        ]);
    }
    // private function handlePositionEditError($position) {
    //     return $this->render("position/edit.html.twig", [
    //         "position" => $position
    //     ]);
    // }

    #[IsGranted('ROLE_RECRUITER')]
    #[Route('/position/save/{id}', name: 'app_position_save', methods: ['PATCH'])]
    public function save(Position $position, Request $request): JsonResponse
    {
        return $this->autosaveService->patchEntityFromRequest($position, self::EDITABLE_FIELDS, $request);
    }

    #[IsGranted('ROLE_RECRUITER')]
    #[Route('/position/duplicate/{id}', name: 'app_position_duplicate')]
    public function duplicate(Position $position, #[CurrentUser] User $user): Response
    {
        $copy = new Position();
        $copy->setName($position->getName() . ' (copy)');
        $copy->setDescription($position->getDescription());
        $copy->setCompany($position->getCompany());
        $copy->setLevel($position->getLevel());
        $copy->setMaxProjects($position->getMaxProjects());
        // $copy->setIsPublic(false);
        // $copy->setCreatedBy($user);
        $this->em->persist($copy);
 
        foreach ($position->getPositionAttributes() as $attribute) {
            $copy->addPositionAttribute($attribute);
        }
        foreach ($position->getTags() as $tag) {
            $copy->addTag($tag);
        }
 
        $this->em->flush();
        return $this->redirectToRoute('app_position_edit', ['id' => $copy->getId()]);
    }

    #[IsGranted('ROLE_RECRUITER')]
    #[Route('/position/delete/{id}', name: 'app_position_delete')]
    public function delete(Position $position, #[CurrentUser] User $user): Response
    {
        $this->em->remove($position);
        $this->em->flush();
        $this->addFlash('success', $this->translator->trans('success.deleted'));
        return $this->redirectToRoute('app_positions');
    }
}