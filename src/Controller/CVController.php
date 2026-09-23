<?php

namespace App\Controller;

use App\Entity\AttributeValue;
use App\Entity\CV;
use App\Entity\Like;
use App\Entity\User;
use App\Repository\CVRepository;
use App\Repository\LikeRepository;
use App\Service\CandidateService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class CVController extends AbstractController {
    public function __construct(
        private EntityManagerInterface $em,
        private LikeRepository $likeRepository,
        private CVRepository $cvRepository,
        private CandidateService $candidateService,
    ) {}

    #[Route(path:"/cv/show/{id}", name:"app_cv_show")]
    public function show(CV $cv) {
        if (!$cv) throw new NotFoundHttpException('CV not found');
        $candidate = $cv->getCandidate();
        return $this->render('cv/show.html.twig', [
            'cv' => $cv,
            'candidate' => $candidate,
            ...($this->candidateService->getCandidateAttributes($candidate)),
        ]);
    }

    #[IsGranted('ROLE_CANDIDATE')]
    #[Route(path:"/cv/edit/{id}", name: "app_cv_edit")]
    // TODO: Submition wipes unsaved data
    public function edit(CV $cv, Request $request) {
        if (!$cv) throw new NotFoundHttpException('CV not found');
        // For now all attributes are mandatory
        // $positionAttributes = $cv->getPosition()->getPositionAttributes()->toArray();
        // $requiredAttributes = array_map(fn($pa) => $pa->getAttribute(), array_filter($positionAttributes, fn($pa) => $pa->isRequired()));
        // $optionalAttributes = array_map(fn($pa) => $pa->getAttribute(), array_filter($positionAttributes, fn($pa) => !$pa->isRequired()));
        $attributeValues = $cv->getCandidate()->getAttributeValues();
        // $requiredAttributes = array_map(fn($a)=> ['attribute'=> $a, 'attributeValue'=>findattrv($a, $cv->getCandidate())],$cv->getPosition()->getAttributes());
        $results = $this->cvRepository->findPositionAttributesWithCandidateValues($cv->getPosition(), $cv->getCandidate());
        // dd($requiredAttributes);
        $requiredAttributes = array_map(function ($pair) {
            return [
                'attribute'      => $pair[0]->getAttribute(),
                'attributeValue' => $pair[1],
            ];
        }, array_chunk($results, 2));
        if ($request->isMethod('POST')) {
            if ($this->isValidCV($requiredAttributes)) {
                $cv->setIsPublic(true);
                $this->em->flush();
                return $this->redirectToRoute("app_candidate_cvs");
            } else {
                $this->addFlash("error", "All Required fields must be created, filled and saved!");
            }
        }
        return $this->render('cv/edit.html.twig', [
            'cv' => $cv,
            'requiredAttributes' => $requiredAttributes,
            // 'optionalAttributes' => $optionalAttributes,
            'attributeValues' => $attributeValues
        ]);
    }
    private function isValidCV($requiredAttributes) {
        foreach($requiredAttributes as $a) {
            /**
             * @var AttributeValue
             */
            $attributeValue = $a['attributeValue'];
            if ($attributeValue === null || !$attributeValue->getValueExists()) {
                // dd($attributeValue->getValueExists());
                return false;
            }
        }
        return true;
    }

    #[IsGranted('ROLE_RECRUITER')]
    #[Route('/cv/like/{id}', name: 'app_cv_like')]
    public function like(CV $cv, Request $request, #[CurrentUser] User $recruiter): Response
    {
        if (!$cv) throw new NotFoundHttpException('CV not found');
        $like = $this->likeRepository->findOneBy(['recruiter' => $recruiter, 'cv' => $cv]);
        if ($like) {
            $this->em->remove($like);
        } else {
            $like = new Like();
            $like->setCv($cv);
            $like->setRecruiter($recruiter);
            $this->em->persist($like);
        }
        $this->em->flush();
        $referer = $request->headers->get('referer');
        return $this->redirect($referer?? $this->generateUrl('app_home'));
    }

    #[IsGranted('delete', 'cv')]
    #[Route('/cv/delete/{id}', name: 'app_cv_delete')]
    public function delete(CV $cv, Request $request, #[CurrentUser] User $user): Response
    {
        $this->em->remove($cv);
        $this->em->flush();
        $referer = $request->headers->get('referer');
        return $this->redirect($referer?? $this->generateUrl('app_home'));
    }
}