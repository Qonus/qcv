<?php

namespace App\Twig\Components;

use App\Entity\Position;
use App\Service\AccessRuleService;
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

    public function __construct(private AccessRuleService $accessRuleService) {}

    public function mount(Position $position): void
    {
        $this->position = $position;
        if (empty($this->position->getAccessRules())) {
            $this->addRule();
        } else {
            $this->rules = array_map(
                fn($r)=>$this->accessRuleService->getStringAccessRule($r),
                $this->position->getAccessRules()->toArray()
            );
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
            'filterValue' => '',
        ];
    }

    #[LiveListener('removeRule')]
    public function removeRule(#[LiveArg()] int $index): void
    {
        if (isset($this->rules[$index])) {
            array_splice($this->rules, $index, 1);
        }
    }
}