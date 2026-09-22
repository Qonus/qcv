<?php

namespace App\Controller;

use App\Entity\Attribute;
use App\Entity\AttributeValue;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class AttributeValueController extends AbstractController {
    public function __construct(private EntityManagerInterface $em) {}
    #[Route(path: "/attribute_value/create/{id}", name: "app_attribute_value_create")]
    public function create(#[CurrentUser]User $user, Attribute $attribute, Request $request) {
        if (!$attribute) throw $this->createNotFoundException("Attribute doesn't exist");
        $attributeValue = new AttributeValue();
        $attributeValue->setCandidate($user);
        $attributeValue->setAttribute($attribute);
        $this->em->persist($attributeValue);
        $this->em->flush();
        $referer = $request->headers->get('referer');
        $targetUrl = $referer ?: $this->generateUrl('app_candidate_index');
        return $this->redirect($targetUrl);
    }
}