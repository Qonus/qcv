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
    // TODO: FIX, PLACEHOLDER
    use DefaultActionTrait;

    #[LiveProp]
    public Position $position;
    

    #[LiveProp(writable: true)]
    public string $query = '';

    public function __construct(private AttributeRepository $attributeRepository){}

    public function getAttributes() {
        return $this->attributeRepository->searchNewForPosition($this->position, $this->query);
    }
}