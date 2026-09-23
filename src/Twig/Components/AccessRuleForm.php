<?php

namespace App\Twig\Components;

use App\Entity\Attribute;
use App\Entity\Position;
use App\Repository\AttributeRepository;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;


#[AsLiveComponent]
class AccessRuleForm {
    use DefaultActionTrait;

    #[LiveProp]
    public Position $position;
        
    #[LiveProp]
    public ?int $index;
    #[LiveProp]
    public bool $required = true;

    #[LiveProp(writable: true)]
    public ?int $attributeId = null;

    #[LiveProp(writable: true)]
    public string $query = '';

    #[LiveProp(writable: true)]
    public string $matchType = '';

    #[LiveProp(writable: true)]
    public string $attributeDimension = '';

    #[LiveProp(writable: true)]
    public string $operation = '';

    #[LiveProp(writable: true)]
    public ?string $filterValue = '';

    #[LiveProp()]
    public string $filterValueType = '';


    public function __construct(private AttributeRepository $attributeRepository){}

    public function mount() {
        // dd();
    }

    public function getAttributes() {
        return $this->attributeRepository->findBy(['isBuiltin' => false]);
    }

    public function getAttribute(): ?Attribute {
        if (!$this->attributeId) return null;
        return $this->attributeRepository->find($this->attributeId);
    }

    
    #[LiveAction]
    public function selectAttribute(#[LiveArg]int|string|null $id) {
        $this->attributeId = $id;
    }
}