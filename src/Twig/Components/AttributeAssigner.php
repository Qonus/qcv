<?php

namespace App\Twig\Components;

use App\Entity\Attribute;
use App\Entity\Position;
use App\Repository\AttributeRepository;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;


#[AsLiveComponent]
class AttributeAssigner {
    use DefaultActionTrait;

    #[LiveProp]
    public Position $position;
    

    // #[LiveProp(writable: true)]
    // public string $query = '';

    public function __construct(private AttributeRepository $attributeRepository){}

    public function getAttributes() {
        return $this->attributeRepository->findBy(['isBuiltin' => false]);
    }

    public function getSelectedAttributeIds(): array {
        return array_map(fn($a)=>$a->getId(), $this->position->getAttributes());
    }
}