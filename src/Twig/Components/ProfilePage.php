<?php

namespace App\Twig\Components;

use App\Entity\User;
use App\Enum\BuiltinAttribute;
use App\Repository\AttributeRepository;
use App\Repository\AttributeValueRepository;
use App\Service\CandidateService;
use App\Service\UploadService;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
class ProfilePage
{
    use DefaultActionTrait;

    #[LiveProp(writable: true, onUpdated: 'updated')]
    public string $firstName = '';

    #[LiveProp(writable: true, onUpdated: 'updated')]
    public string $lastName = '';

    #[LiveProp(writable: true, onUpdated: 'updated')]
    public string $location = '';

    #[LiveProp(writable: true)]
    public string $image = '';

    #[LiveProp]
    public bool $isUnsaved = false;

    public function __construct(
        private AttributeRepository $repo,
        private CandidateService $candidateService,
        private AttributeValueRepository $attributeValueRepository,
        private UploadService $uploadService,
        private Security $security)
    {
    }

    public function getUser(): ?User
    {
        /** @var User|null $user */
        return $this->security->getUser();
    }

    #[LiveAction]
    public function uploadImage(Request $request): void {
        /** @var UploadedFile|null $image */
        $image = $request->files->get('image');
        if (!$image) return;
        $imageUrl = $this->uploadService->uploadImage($image);
        $this->image = $imageUrl;
        $this->save();
    }

    // TODO: automatically call this function every 5-10 seconds during editing
    #[LiveAction]
    public function save(): void {
        $this->candidateService->saveBuiltinValues($this->getUser(), [
            BuiltinAttribute::FIRST_NAME->value => $this->firstName,
            BuiltinAttribute::LAST_NAME->value => $this->lastName,
            BuiltinAttribute::LOCATION->value => $this->location,
            BuiltinAttribute::IMAGE_URL->value => $this->image,
        ]);
        $this->isUnsaved = false;
    }

    public function updated(): void {
        $this->isUnsaved = true;
    }
}