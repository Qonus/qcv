<?php

namespace App\Twig\Components;

use App\Entity\Attribute;
use App\Entity\AttributeValue;
use App\Entity\User;
use App\Repository\AttributeRepository;
use App\Repository\AttributeValueRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
class CandidateAttributes
{
    // WARNING: FINISH THIS IS UNTESTED
    use DefaultActionTrait;

    #[LiveProp(writable: true)]
    public string $query = '';

    #[LiveProp(url: true)]
    public string $q = '';

    public function __construct(
        private AttributeRepository $attributeRepository,
        private AttributeValueRepository $attributeValueRepository,
        private EntityManagerInterface $em,
        private Security $security)
    {
    }

    public function getUser(): ?User {
        /** @var User|null $user */
        return $this->security->getUser();
    }

    #[LiveAction]
    public function addAttribute(#[LiveArg] int $id) {
        $newAttributeValue = new AttributeValue();
        $newAttributeValue->setAttribute($this->getAttribute($id));
        $newAttributeValue->setCandidate($this->getUser());
        $newAttributeValue->setValue(null);
        $this->em->persist($newAttributeValue);
        $this->em->flush();
    }

    public function getResults(): array {
        return $this->attributeRepository->searchNewForUser($this->getUser(), $this->query);
    }

    public function getAttributeValues(): array {
        return $this->attributeValueRepository->findByUser($this->getUser(), false, $this->q);
    }

    #[LiveAction]
    public function deleteAttributeValue(#[LiveArg]int $id) {
        $attributeValue = $this->attributeValueRepository->find($id);
        if (!$attributeValue) return;
        $this->em->remove($attributeValue);
        $this->em->flush();
    }
    private function getAttribute(int $id): Attribute {
        return $this->attributeRepository->find($id);
    }
}