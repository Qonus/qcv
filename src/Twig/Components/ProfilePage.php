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
class ProfilePage
{
    use DefaultActionTrait;

    #[LiveProp(writable: true)]
    public string $firstName = '';

    #[LiveProp(writable: true)]
    public string $lastName = '';

    #[LiveProp(writable: true)]
    public string $location = '';

    #[LiveProp(writable: true)]
    public string $image = '';

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

    // TODO: automatically call this function every 5-10 seconds during editing
    public function save(): void {
        // TODO: save the values

        // TODO: handle the case where saving fails due to the old version
    }
}