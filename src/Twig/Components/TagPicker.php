<?php

namespace App\Twig\Components;

use App\Entity\Tag;
use App\Entity\TaggableEntity;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
class TagPicker
{
    use DefaultActionTrait;

    #[LiveProp]
    public string $entityClass;

    #[LiveProp]
    public int $entityId;

    #[LiveProp(writable: true)]
    public string $query = '';

    public function __construct(private readonly EntityManagerInterface $em) {}

    private function getEntity(): TaggableEntity
    {
        $entity = $this->em->getRepository($this->entityClass)->find($this->entityId);
        if (!$entity instanceof TaggableEntity) {
            throw new \LogicException(sprintf('%s must implement TaggableEntity', $this->entityClass));
        }

        return $entity;
    }

    public function getTags(): array
    {
        return $this->getEntity()->getTags()->toArray();
    }

    public function getSuggestions(): array
    {
        if ($this->query === '') {
            return [];
        }

        $existingNames = array_map(static fn (Tag $t) => $t->getName(), $this->getTags());

        $qb = $this->em->getRepository(Tag::class)->createQueryBuilder('t')
            ->andWhere('t.name LIKE :q')->setParameter('q', $this->query . '%')
            ->orderBy('t.name', 'ASC')
            ->setMaxResults(8);

        if ($existingNames) {
            $qb->andWhere('t.name NOT IN (:existing)')->setParameter('existing', $existingNames);
        }

        return $qb->getQuery()->getResult();
    }

    public function getExactMatchExists(): bool
    {
        foreach ($this->getSuggestions() as $tag) {
            if (0 === strcasecmp($tag->getName(), $this->query)) {
                return true;
            }
        }

        return false;
    }

    #[LiveAction]
    public function addTag(#[LiveArg] ?int $id = null, #[LiveArg] ?string $name = null): void
    {
        $tag = null;

        if (null !== $id) {
            $tag = $this->em->getRepository(Tag::class)->find($id);
        } elseif (null !== $name && '' !== trim($name)) {
            $name = trim($name);
            $tag = $this->em->getRepository(Tag::class)->findOneBy(['name' => $name]);
            if (!$tag) {
                $tag = new Tag();
                $tag->setName($name);
                $this->em->persist($tag);
            }
        }

        if ($tag) {
            $this->getEntity()->addTag($tag);
        }

        $this->em->flush();
        $this->query = '';
    }

    #[LiveAction]
    public function removeTag(#[LiveArg] int $id): void
    {
        $tag = $this->em->getRepository(Tag::class)->find($id);
        if ($tag) {
            $this->getEntity()->removeTag($tag);
            $this->em->flush();
        }
    }

    #[LiveAction]
    public function addOnEnter(): void
    {
        $suggestions = $this->getSuggestions();
        if ($suggestions) {
            $this->addTag(id: $suggestions[0]->getId());
        } else {
            $this->addTag(name: $this->query);
        }
    }
}