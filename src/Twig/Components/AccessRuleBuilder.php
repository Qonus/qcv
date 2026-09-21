<?php

namespace App\Twig\Components;

use App\Entity\Position;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveListener;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
class AccessRuleBuilder
{
    use DefaultActionTrait;

    #[LiveProp]
    public Position $position;

    #[LiveProp(writable: true)]
    public array $rules = [];

    public function mount(array $initialRules = []): void
    {
        if (empty($initialRules)) {
            $this->addRule();
        } else {
            $this->rules = $initialRules;
        }
    }

    #[LiveAction()]
    public function addRule(): void
    {
        $this->rules[] = [
            'id' => uniqid(),
            'matchType' => 'and',
            'attributeId' => null,
            'attributeDimension' => 'value',
            'operation' => 'equals',
            'filterValue' => null,
        ];
    }

    #[LiveListener('removeRule')]
    public function removeRule(#[LiveArg()] int $index): void
    {
        if (isset($this->rules[$index])) {
            array_splice($this->rules, $index, 1);
        }
    }

    #[LiveAction]
    public function saveRules(): void
    {
        // Add your logic to persist rules array
    }
}