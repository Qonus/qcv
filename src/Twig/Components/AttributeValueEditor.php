<?php

namespace App\Twig\Components;

use App\Entity\Attribute;
use App\Entity\AttributeValue;
use App\Entity\User;
use App\Enum\AttributeDataType;
use App\Repository\AttributeRepository;
use App\Repository\AttributeValueRepository;
use App\Service\AttributeValueHydrator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
class AttributeValueEditor
{
    use DefaultActionTrait;

    #[LiveProp]
    public AttributeValue $attributeValue;

    #[LiveProp]
    public bool $required = true;

    #[LiveProp(writable: true, onUpdated: 'updated')]
    public ?string $value = '';

    #[LiveProp(writable: ['start', 'end'], onUpdated: ['start'=>'updated', 'end'=>'updated'])]
    public ?array $periodValue = ['start' => '', 'end' => ''];

    #[LiveProp]
    public bool $isSaved = true;

    public function __construct(
        private AttributeRepository $attributeRepository,
        private AttributeValueRepository $attributeValueRepository,
        private AttributeValueHydrator $attributeValueHydrator,
        private EntityManagerInterface $em,
        private Security $security)
    {
    }

    public function mount(AttributeValue $attributeValue): void
    {
        $this->attributeValue = $attributeValue;
        $value = $this->attributeValueHydrator->getStringValue($attributeValue);
        if ($this->isPeriod()) {
            $this->periodValue = $value;
        } else {
            $this->value = $value;
        }
    }

    public function getUser(): ?User {
        /** @var User|null $user */
        return $this->security->getUser();
    }

    #[LiveAction]
    public function save() {
        // dd(['value' => $this->value, 'period' => $this->periodValue]);

        if (!$this->isValidValue()) return;
        if ($this->isPeriod()) {
            $this->attributeValueHydrator->setValueFromString($this->attributeValue, $this->periodValue);
        } else {
            $this->attributeValueHydrator->setValueFromString($this->attributeValue, $this->value);
        }
        $this->em->flush();
        $this->isSaved = true;
    }

    public function isValidValue(): bool {
        if ($this->isPeriod() &&
        $this->periodValue['start'] == '' &&
        $this->periodValue['end'] == '') return false;
        if (!$this->isPeriod() && 
            $this->getDataType() != AttributeDataType::BOOLEAN &&
            $this->value === '') return false;
        return true;
    }

    public function updated() {
        $this->isSaved = false;
    }

    private function isPeriod(): bool {
        return ($this->getDataType() == AttributeDataType::PERIOD);
    }

    private function getDataType(): AttributeDataType {
        return $this->attributeValue->getAttribute()->getDataType();
    }
}