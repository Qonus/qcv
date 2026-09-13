<?php

namespace App\Twig\Components;

use App\Entity\User;
use App\Enum\BuiltinAttribute;
use App\Repository\AttributeRepository;
use App\Repository\AttributeValueRepository;
use App\Service\CandidateService;
use Symfony\Bundle\SecurityBundle\Security;
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
        private CandidateService $candidateService,
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
    // TODO: Upload the profile picture into cloudinary and update the image url.
    #[LiveAction]
    public function save(): void {
        $this->candidateService->saveBuiltinValues($this->getUser(), [
            BuiltinAttribute::FIRST_NAME->value => $this->firstName,
            BuiltinAttribute::LAST_NAME->value => $this->lastName,
            BuiltinAttribute::LOCATION->value => $this->location,
            BuiltinAttribute::IMAGE_URL->value => $this->image,
        ]);
    }
}