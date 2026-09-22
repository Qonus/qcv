<?php

namespace App\Controller;

use App\Repository\AttributeValueRepository;
use App\Repository\UserRepository;
use App\Service\CandidateService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted("ROLE_RECRUITER")]
class CandidateController extends AbstractController {
    public function __construct(
        private UserRepository $userRepository,
        private CandidateService $candidateService,
        private AttributeValueRepository $attributeValueRepository
    ) {}

    #[Route(path: '/candidate/{email}', name: "app_candidate_show")]
    public function show(string $email): Response {
        $candidate = $this->userRepository->findByEmail($email, '');
        if (!$candidate) throw $this->createNotFoundException("Candidate doesn't exist");
        return $this->render('candidate/show.html.twig', [
            'candidate' => $candidate,
            'projects' => $candidate->getProjects(),
            ...($this->candidateService->getCandidateAttributes($candidate)),
        ]);
    }
}