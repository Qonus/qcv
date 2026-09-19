<?php

namespace App\Controller;

use App\Entity\Project;
use App\Repository\ProjectRepository;
use App\Service\AutosaveService;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class ProjectController extends AbstractController {
    private const EDITABLE_FIELDS = ['name', 'description', 'startDateFromString', 'endDateFromString'];
    public function __construct(
        private ProjectRepository $projectRepository,
        private AutosaveService $autosaveService) {

    }

    #[Route(path: "/project/show/{id}", name: "app_project_show")]
    public function show(int $id) {
        $project = $this->projectRepository->find($id);
        if (!$project) {
            throw $this->createNotFoundException('The position does not exist');
        }
        return $this->render("project/show.html.twig", [
            "project"=> $project
        ]);
    }

    #[Route(path: "/project/create", name: "app_project_create")]
    public function create(EntityManagerInterface $em, #[CurrentUser] $user) {
        $project = new Project();
        $project->setCandidate($user);
        $project->setName('Untitled project');

        $em->persist($project);
        $em->flush();
        return $this->redirectToRoute('app_project_edit', ['id' => $project->getId()]);
    }

    #[Route(path:"/project/edit/{id}", name: "app_project_edit")]
    public function edit(int $id, Request $request, EntityManagerInterface $em) {
        /** @var Project */
        $project = $this->projectRepository->find($id);
        if (!$project) {
            throw $this->createNotFoundException('The project does not exist');
        }

        if ($request->isMethod('POST')) {
            // CSRF Validation
            if (!$this->isCsrfTokenValid('project_form', $request->request->get('_token'))) {
                throw $this->createAccessDeniedException('Invalid CSRF token.');
            }

            $project->setName($request->request->get('name'));
            $project->setStartDate(new \DateTime($request->request->get('startDate')));
            $project->setEndDate(new \DateTime($request->request->get('endDate')));
            $project->setDescription($request->request->get('description'));

            $em->flush();

            $this->addFlash('success', 'Project updated successfully.');
            return $this->redirectToRoute('app_project_show', ['id' => $project->getId()]);
        }

        $response = $this->render('project/edit.html.twig', [
            'project' => $project
        ]);
        $response->headers->set('Cache-Control', 'no-store');
        return $response;
    }

    #[Route('/project/save/{id}', name: 'app_project_save')]
    public function save(Project $project, Request $request): JsonResponse
    {
        return $this->autosaveService->patchEntityFromRequest($project, self::EDITABLE_FIELDS, $request);
    }
}