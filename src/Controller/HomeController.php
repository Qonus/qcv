<?php
namespace App\Controller;

use App\Repository\CVRepository;
use App\Repository\PositionRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController {
    public function __construct(
        private PositionRepository $positionRepository,
        private UserRepository $userRepository,
        private CVRepository $cvRepository,
        ) {
    }

    #[Route(path: '/', name: 'app_home')]
    public function index(): Response {
        // TODO: Add Latest Positions (table showing the most recently created or updated positions)
        // TODO: Add Most Popular Positions (top 5 positions ranked by the number of submitted CVs)
        // TODO: Add Tag Cloud with technology tags (linked to CVs for Recruiters or positions for Candidates)

        // Statistics
        $positionCount = $this->positionRepository->count();
        $candidateCount = $this->userRepository->countByRole('ROLE_CANDIDATE');
        $recruiterCount = $this->userRepository->countByRole('ROLE_RECRUITER');
        $cvCount = $this->cvRepository->count();

        return $this->render('/index.html.twig', [
            'positionCount' => $positionCount,
            'candidateCount' => $candidateCount,
            'recruiterCount' => $recruiterCount,
            'cvCount' => $cvCount,
        ]);
    }
}