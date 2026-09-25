<?php
namespace App\Controller;

use App\Repository\CVRepository;
use App\Repository\PositionRepository;
use App\Repository\TagRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class HomeController extends AbstractController {
    public function __construct(
        private PositionRepository $positionRepository,
        private TagRepository $tagRepository,
        private UserRepository $userRepository,
        private CVRepository $cvRepository,
        ) {
    }

    #[Route(path: '/', name: 'app_home')]
    public function index(): Response {
        $latestPositions = $this->positionRepository->latest(20);
        $popularPositions = $this->positionRepository->popular(20);
        $popularTags = $this->tagRepository->popular(20);
        // TODO: Use these results in the template

        // Statistics
        $positionCount = $this->positionRepository->count();
        $candidateCount = $this->userRepository->countByRole('ROLE_CANDIDATE');
        $recruiterCount = $this->userRepository->countByRole('ROLE_RECRUITER');
        $cvCount = $this->cvRepository->countCreatedInLast24Hours();

        return $this->render('/index.html.twig', [
            'latestPositions' => $latestPositions,
            'popularPositions' => $popularPositions,
            'popularTags' => $popularTags,
            'positionCount' => $positionCount,
            'candidateCount' => $candidateCount,
            'recruiterCount' => $recruiterCount,
            'cvCount' => $cvCount,
        ]);
    }
}