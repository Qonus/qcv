<?php

namespace App\Controller;

use App\Entity\Position;
use App\Entity\User;
use App\Enum\Level;
use App\Repository\PositionRepository;
use App\Service\AutosaveService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class PositionController extends AbstractController {
    private const EDITABLE_FIELDS = ['name', 'description', 'company', 'level', 'maxProjects'];

    public function __construct(
        private PositionRepository $positionRepository,
        private AutosaveService $autosaveService) {
    }

    #[Route(path: "/position", name: "app_positions")]
    public function index() {
        $positions = $this->positionRepository->findAll();
        return $this->render("position/index.html.twig", [
            "positions"=> $positions
        ]);
    }

    #[Route(path: "/position/show/{id}", name: "app_position_show")]
    public function show(int $id) {
        $position = $this->positionRepository->find($id);
        if (!$position) {
            throw $this->createNotFoundException('The position does not exist');
        }
        return $this->render("position/show.html.twig", [
            "position"=> $position
        ]);
    }

    #[Route(path: "/position/create", name: "app_position_create")]
    public function create(EntityManagerInterface $em) {
        $position = new Position();
        $position->setName('Untitled Position');
        // $position->setIsPublic(false); // stays hidden from candidates until the recruiter actually configures it

        $em->persist($position);
        $em->flush();
        return $this->redirectToRoute('app_position_edit', ['id' => $position->getId()]);
    }

    #[Route(path: "/position/edit/{id}", name: "app_position_edit")]
    public function edit(int $id, Request $request, EntityManagerInterface $em) {
        /** @var Position */
        $position = $this->positionRepository->find($id);
        if (!$position) {
            throw $this->createNotFoundException('The position does not exist');
        }

        if ($request->isMethod('POST')) {
            // CSRF Validation
            if (!$this->isCsrfTokenValid('position_form', $request->request->get('_token'))) {
                throw $this->createAccessDeniedException('Invalid CSRF token.');
            }

            $position->setCompany($request->request->get('company'));
            $position->setName($request->request->get('title'));

            if ($request->request->get('level') != '') $position->setLevel(Level::from($request->request->get('level')));
            $position->setDescription($request->request->get('description'));
            // TODO: Add tags, attributes and attribute filters

            $em->flush();

            $this->addFlash('success', 'Position updated successfully.');
            return $this->redirectToRoute('app_position_show', ['id' => $position->getId()]);
        }

        return $this->render("position/edit.html.twig", [
            "position" => $position
        ]);
    }

    #[Route('/position/save/{id}', name: 'app_position_save', methods: ['PATCH'])]
    public function save(Position $position, Request $request, EntityManagerInterface $em): JsonResponse
    {
        return $this->autosaveService->patchEntityFromRequest($position, self::EDITABLE_FIELDS, $request);
    }

    #[Route('/position/duplicate/{id}', name: 'app_position_duplicate', methods: ['POST'])]
    public function duplicate(Position $position, EntityManagerInterface $em, #[CurrentUser] User $user): Response
    {
        $copy = new Position();
        $copy->setName($position->getName() . ' (copy)');
        $copy->setDescription($position->getDescription());
        $copy->setCompany($position->getCompany());
        $copy->setLevel($position->getLevel());
        $copy->setMaxProjects($position->getMaxProjects());
        // $copy->setIsPublic(false);
        // $copy->setCreatedBy($user);
        $em->persist($copy);
 
        foreach ($position->getAttributes() as $attribute) {
            $copy->addAttribute($attribute);
        }
        foreach ($position->getTags() as $tag) {
            $copy->addTag($tag);
        }
 
        $em->flush();
 
        return $this->redirectToRoute('app_position_edit', ['id' => $copy->getId()]);
    }
}