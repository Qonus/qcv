<?php

namespace App\Twig\Components;

use App\Entity\Position;
use App\Entity\Post;
use App\Entity\User;
use App\Repository\PositionRepository;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
class Discussions
{
    use DefaultActionTrait;

    #[LiveProp()]
    public Position $position;

    #[LiveProp(writable: true)]
    public string $content = '';

    #[LiveProp(writable: true)]
    public ?int $editingPostId = null;

    public function __construct(
        private PositionRepository $repo,
        private PostRepository $postRepository,
        private EntityManagerInterface $em,
        private Security $security)
    {
    }

    public function getUser(): ?User
    {
        /** @var User|null $user */
        return $this->security->getUser();
    }

    public function getPosts(): array {
        $posts = $this->postRepository->findByPosition($this->position);
        return $posts;
    }

    #[LiveAction]
    public function createPost() {
        $user = $this->getUser();
        if ($user == null) return;
        $this->setEditingPost(id: -1);
        // dd($this->content);
    }
    #[LiveAction]
    public function editPost(#[LiveArg]int $id) {
        $post = $this->getMyPostFromId($id);
        if (!$post) return;
        $this->setEditingPost($post->getContent(), $id);
    }
    #[LiveAction]
    public function deletePost(#[LiveArg]int $id) {
        $post = $this->getMyPostFromId($id);
        if (!$post) return;
        $this->em->remove($post);
        $this->em->flush();
    }
    #[LiveAction]
    public function savePost() {
        if ($this->content == '') return;
        $post = $this->getMyPostFromId($this->editingPostId);
        if (!$post && $this->editingPostId == -1) {
            $this->createAndFlushPost($this->content);
            $this->setEditingPost();
            return;
        } else if (!$post) {
            return;
        }
        $post->setContent($this->content);
        $this->em->flush();
        $this->setEditingPost();
    }
    private function createAndFlushPost(string $content) {
        $post = new Post();
        $post->setAuthor($this->getUser());
        $post->setPosition($this->position);
        $post->setContent($content);
        $this->em->persist($post);
        $this->em->flush();
    }
    #[LiveAction]
    public function setEditingPost(string $content = '', ?int $id = null): void {
        $this->content = $content;
        $this->editingPostId = $id;
    }
    private function getMyPostFromId(?int $id): ?Post {
        if (!$id) return null;
        /** @var Post */
        $post = $this->postRepository->find($id);
        if (!$this->isMyPost($post)) return null;
        return $post;
    }
    private function isMyPost(?Post $post) {
        return $post && $post->getAuthor() == $this->getUser();
    }
}