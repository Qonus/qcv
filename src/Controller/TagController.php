<?php

namespace App\Controller;

use App\Entity\Position;
use App\Entity\Tag;
use App\Entity\User;
use App\Repository\AttributeRepository;
use App\Repository\TagRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class TagController extends AbstractController {
    // Currently Unused
    public function __construct(
        private EntityManagerInterface $em,
        private TagRepository $tagRepository
        ){}

    #[Route('/tag/create', name: 'app_tag_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $name = trim($data['name'] ?? '');
        if (empty($name)) {
            return new JsonResponse(['error' => 'Name cannot be empty'], 400);
        }
        $existingTag = $this->tagRepository->findOneBy(['name' => $name]);
        if ($existingTag) {
            return new JsonResponse([
                'id' => $existingTag->getId(),
                'name' => $existingTag->getName()
            ]);
        }
        $tag = new Tag();
        $tag->setName($name);
        $this->em->persist($tag);
        $this->em->flush();
        return new JsonResponse([
            'id' => $tag->getId(),
            'name' => $tag->getName()
        ]);
    }

    #[Route(path:"/tag/search", name: "app_tag_search")]
    public function search(Request $request) {
        $query = $request->query->get('query');
        $tags = $this->tagRepository->search($query);
        $data = array_map(fn($t)=> ['id'=>$t->getId(),'name'=>$t->getName()], $tags);
        return $this->json($data);
    }
}