<?php

namespace App\Controller;

use App\Enum\BuiltinAttribute;
use App\Repository\AttributeValueRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted("ROLE_RECRUITER")]
class CandidateController extends AbstractController {
    public function __construct(
        private UserRepository $userRepository,
        private AttributeValueRepository $attributeValueRepository
    ) {}

    #[Route(path: '/candidate/{email}', name: "app_candidate_show")]
    public function show(string $email): Response {
        $candidate = $this->userRepository->findByEmail($email, 'ROLE_CANDIDATE');
        if (!$candidate) throw $this->createNotFoundException("Candidate doesn't exist");
        $attributeValues = $this->attributeValueRepository->findByUser($candidate, false);
        $valuesByName = [];
        foreach ($this->attributeValueRepository->findByUser($candidate, true) as $value) {
            $valuesByName[$value->getAttribute()->getName()] = $value;
        }
        return $this->render('candidate/show.html.twig', [
            'candidate' => $candidate,
            'firstName' => ($valuesByName[BuiltinAttribute::FIRST_NAME->value] ?? null)?->getValue() ?? '',
            'lastName'  => ($valuesByName[BuiltinAttribute::LAST_NAME->value]  ?? null)?->getValue() ?? '',
            'location'  => ($valuesByName[BuiltinAttribute::LOCATION->value]   ?? null)?->getValue() ?? '',
            'image'     => ($valuesByName[BuiltinAttribute::IMAGE_URL->value]  ?? null)?->getValue() ?? '',
            'attributeValues' => $attributeValues
        ]);
    }
}