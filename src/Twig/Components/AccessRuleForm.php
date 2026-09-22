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
    // TODO: FIX, PLACEHOLDER
    use DefaultActionTrait;

    #[LiveProp]
    public Position $position;
        
    #[LiveProp]
    public ?int $index;

    #[LiveProp(onUpdated: 'updated')]
    public ?Attribute $attribute = null;

    #[LiveProp(writable: true)]
    public string $query = '';

    #[LiveProp(writable: true)]
    public string $matchType = '';

    #[LiveProp(writable: true)]
    public string $attributeDimension = '';

    #[LiveProp(writable: true)]
    public string $operation = '';

    #[LiveProp(writable: true)]
    public string $filterValue = '';

    #[LiveProp()]
    public string $filterValueType = '';


    public function __construct(private AttributeRepository $attributeRepository){}

    public function getAttributes() {
        return $this->attributeRepository->findBy(['isBuiltin' => false]);
    }

    #[LiveAction]
    public function updated() {
        
    }

    #[LiveAction]
    public function selectAttribute(#[LiveArg]int|string|null $id) {
        $this->attribute = $this->attributeRepository->find($id);
    }
}