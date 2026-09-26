<?php

namespace App\Twig\Components;

use App\Entity\AttributeValue;
use App\Entity\User;
use App\Enum\BuiltinAttribute;
use App\Repository\AttributeRepository;
use App\Repository\AttributeValueRepository;
use App\Service\CandidateService;
use App\Service\UploadService;
use Doctrine\ORM\OptimisticLockException;
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

    #[LiveProp(writable: ['value'], onUpdated: ['value' => 'updated'])]
    public array $firstName = ['value'=> '', 'version'=>''];

    #[LiveProp(writable: ['value'], onUpdated: ['value' => 'updated'])]
    public array $lastName = ['value'=> '', 'version'=>''];

    #[LiveProp(writable: ['value'], onUpdated: ['value' => 'updated'])]
    public array $location = ['value'=> '', 'version'=>''];

    #[LiveProp(writable: ['value'])]
    public array $image = ['value'=> '', 'version'=>''];

    #[LiveProp]
    public bool $isUnsaved = false;

    #[LiveProp]
    public bool $conflict = false;

    public function __construct(
        private AttributeRepository $repo,
        private CandidateService $candidateService,
        private AttributeValueRepository $attributeValueRepository,
        private UploadService $uploadService,
        private Security $security)
    {
    }

    public function mount(
        AttributeValue $firstName,
        AttributeValue $lastName,
        AttributeValue $location,
        AttributeValue $image,
    ) {
        $this->firstName = [
            'value' => $firstName->getValue(),
            'version' => $firstName->getVersion(),
        ];
        $this->lastName = [
            'value' => $lastName->getValue(),
            'version' => $lastName->getVersion(),
        ];
        $this->location = [
            'value' => $location->getValue(),
            'version' => $location->getVersion(),
        ];
        $this->image = [
            'value' => $image->getValue(),
            'version' => $image->getVersion(),
        ];

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
        $this->image['value'] = $imageUrl;
        $this->save();
    }

    // TODO: automatically call this function every 5-10 seconds during editing
    #[LiveAction]
    public function save(): void {
        try {
            $this->candidateService->saveBuiltinValues($this->getUser(), [
                BuiltinAttribute::FIRST_NAME->value => $this->firstName,
                BuiltinAttribute::LAST_NAME->value => $this->lastName,
                BuiltinAttribute::LOCATION->value => $this->location,
                BuiltinAttribute::IMAGE_URL->value => $this->image,
            ]);
            $this->isUnsaved = false;
        } catch (OptimisticLockException) {
            $this->conflict = true;
        }
    }

    public function updated(): void {
        $this->isUnsaved = true;
    }
}