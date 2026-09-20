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
    // WARNING: UNFINISHED
    use DefaultActionTrait;

    #[LiveProp]
    public AttributeValue $attributeValue;

    #[LiveProp(writable: true)]
    public ?string $value = '';

    #[LiveProp(writable: ['start', 'end'])]
    public ?array $periodValue = ['start' => '', 'end' => ''];

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
        if ($this->isPeriod()) {
            $this->attributeValueHydrator->setValueFromString($this->attributeValue, $this->periodValue);
        } else {
            $this->attributeValueHydrator->setValueFromString($this->attributeValue, $this->value);
        }
        $this->em->flush();
    }

    private function isPeriod(): bool {
        return ($this->attributeValue->getAttribute()->getDataType() == AttributeDataType::PERIOD);
    }
}