<?php

namespace App\Controller;

use App\Entity\Project;
use App\Entity\User;
use App\Repository\ProjectRepository;
use App\Service\AutosaveService;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class ProjectController extends AbstractController {
    private const EDITABLE_FIELDS = ['name', 'description', 'startDateFromString', 'endDateFromString'];
    public function __construct(
        private ProjectRepository $projectRepository,
        private EntityManagerInterface $em,
        private AutosaveService $autosaveService) {

    }

    #[IsGranted('ROLE_RECRUITER')]
    #[Route('/projects/', name: 'app_projects')]
    // TODO: Add query search from search bar
    // TODO: Display requested Tag
    public function index(Request $request, #[CurrentUser] User $user): Response
    {
        $tag = $request->query->get("tag");
        $projects = $this->projectRepository->findByTag($tag);
        return $this->render('project/index.html.twig', [
            'projects' => $projects
        ]);
    }

    #[IsGranted('view', 'project')]
    #[Route(path: "/project/show/{id}", name: "app_project_show")]
    public function show(Project $project) {
        if (!$project) {
            throw $this->createNotFoundException('The position does not exist');
        }
        return $this->render("project/show.html.twig", [
            "project"=> $project
        ]);
    }

    #[IsGranted('edit', 'project')]
    #[Route(path: "/project/create", name: "app_project_create")]
    public function create(EntityManagerInterface $em, #[CurrentUser] $user) {
        $project = new Project();
        $project->setCandidate($user);
        $project->setName('Untitled project');

        $em->persist($project);
        $em->flush();
        return $this->redirectToRoute('app_project_edit', ['id' => $project->getId()]);
    }

    #[IsGranted('edit', 'project')]
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

    #[IsGranted('edit', 'project')]
    #[Route('/project/save/{id}', name: 'app_project_save')]
    public function save(Project $project, Request $request): JsonResponse
    {
        return $this->autosaveService->patchEntityFromRequest($project, self::EDITABLE_FIELDS, $request);
    }

    #[IsGranted('edit', 'project')]
    #[Route('/project/delete/{id}', name: 'app_project_delete')]
    public function delete(Project $project, Request $request, #[CurrentUser] User $user): Response
    {
        $this->em->remove($project);
        $this->em->flush();
        $referer = $request->headers->get('referer');
        return $this->redirect($referer?? $this->generateUrl('app_home'));
    }
}