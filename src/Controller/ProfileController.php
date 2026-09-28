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
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_CANDIDATE')]
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

    #[Route('/delete', name: 'app_profile_delete', methods: ['GET'])]
    public function delete(#[CurrentUser] User $user, Request $request, TokenStorageInterface $tokenStorage): Response {
        $tokenStorage->setToken(null);
        $request->getSession()->invalidate();
        $this->em->remove($user);
        $this->em->flush();
        return $this->redirectToRoute('app_home');
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