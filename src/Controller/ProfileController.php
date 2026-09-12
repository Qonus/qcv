<?php

namespace App\Controller;

use App\Entity\Attribute;
use App\Entity\AttributeOption;
use App\Entity\AttributeValue;
use App\Entity\User;
use App\Enum\AttributeDataType;
use App\Repository\AttributeValueRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\OptimisticLockException;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_CANDIDATE')]
#[Route('/profile')]
class ProfileController extends AbstractController
{
    private const BUILTIN_FIRST_NAME = 'First Name';
    private const BUILTIN_LAST_NAME = 'Last Name';
    private const BUILTIN_LOCATION = 'Location';
    private const BUILTIN_PHOTO = 'Personal Photo';

    public function __construct(
        private readonly EntityManagerInterface $em,
        #[Autowire('%env(CLOUDINARY_CLOUD_NAME)%')] private readonly string $cloudinaryCloudName,
        #[Autowire('%env(CLOUDINARY_UPLOAD_PRESET)%')] private readonly string $cloudinaryUploadPreset,
    ) {}

    #[Route('', name: 'app_profile', methods: ['GET'])]
    public function index(#[CurrentUser] User $user): Response
    {
        /**
         * @var AttributeValueRepository
         */
        $valueRepo = $this->em->getRepository(AttributeValue::class);
        $valuesByName = [];
        foreach ($valueRepo->findByUser($user, true) as $value) {
            $valuesByName[$value->getAttribute()->getName()] = $value;
        }

        return $this->render('profile/index.html.twig', [
            'firstName' => ($valuesByName[self::BUILTIN_FIRST_NAME] ?? null)?->getValue() ?? '',
            'lastName'  => ($valuesByName[self::BUILTIN_LAST_NAME]  ?? null)?->getValue() ?? '',
            'location'  => ($valuesByName[self::BUILTIN_LOCATION]   ?? null)?->getValue() ?? '',
            'image'     => ($valuesByName[self::BUILTIN_PHOTO]      ?? null)?->getValue() ?? '',
        ]);
    }

    #[Route('/attributes', name: 'app_candidate_attributes')]
    public function attributes(#[CurrentUser] User $user): Response {
        return $this->render('profile/attributes.html.twig', [
        ]);
    }

    #[Route('/projects', name: 'app_candidate_projects')]
    public function projects(#[CurrentUser] User $user): Response {
        return $this->render('profile/projects.html.twig', [
        ]);
    }

    #[Route('/cvs', name: 'app_candidate_cvs')]
    public function cvs(#[CurrentUser] User $user): Response {
        return $this->render('profile/cvs.html.twig', [
        ]);
    }
}