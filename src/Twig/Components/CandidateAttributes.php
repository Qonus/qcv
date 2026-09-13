<?php

namespace App\Twig\Components;

use App\Entity\User;
use App\Repository\AttributeRepository;
use App\Repository\AttributeValueRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentToolsTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
class CandidateAttributes
{
    use DefaultActionTrait;

    #[LiveProp(writable: true)]
    public string $query = '';

    public function __construct(
        private AttributeRepository $repo,
        private AttributeValueRepository $attributeValueRepository,
        private Security $security)
    {
    }

    public function getUser(): ?User
    {
        /** @var User|null $user */
        return $this->security->getUser();
    }

    #[LiveAction]
    public function selectOption(#[LiveArg] int $id) {
        // TODO: Add the attribute with $id to the user's attribute values.
        
    }

    public function getResults(): array
    {
        return $this->repo->search($this->query);
    }

    public function getAttributeValues(): array {
        return $this->attributeValueRepository->findByUser($this->getUser(), false);
    }
}