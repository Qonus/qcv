<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\ProjectRepository;
use App\Service\CandidateService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('IS_AUTHENTICATED_FULLY')]
#[Route('/profile')]
class ProfileController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private ProjectRepository $projectRepository,
        private CandidateService $candidateService,
    ) {}

    #[Route('', name: 'app_profile', methods: ['GET'])]
    public function index(#[CurrentUser] User $user): Response
    {
        return $this->render('profile/index.html.twig', [
            ...($this->candidateService->getCandidateAttributes($user, true, false)),
        ]);
    }

    #[Route('/attributes', name: 'app_candidate_attributes')]
    public function attributes(#[CurrentUser] User $user): Response {
        // The logic is in the live component
        return $this->render('profile/attributes.html.twig', [
        ]);
    }

    #[Route('/projects', name: 'app_candidate_projects')]
    public function projects(#[CurrentUser] User $user, Request $request): Response {
        $projects = $this->projectRepository->findByUser($user, $request->query->get('q'));
        return $this->render('profile/projects.html.twig', [
            'projects' => $projects
        ]);
    }

    #[Route('/cvs', name: 'app_candidate_cvs')]
    public function cvs(#[CurrentUser] User $user): Response {
        return $this->render('profile/cvs.html.twig', [
        ]);
    }
}