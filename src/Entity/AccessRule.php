<?php

namespace App\Entity;

use App\Enum\AttributeDimension;
use App\Enum\FilterValueType;
use App\Enum\MatchType;
use App\Enum\Operation;
use App\Repository\AccessRuleRepository;
use BcMath\Number;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AccessRuleRepository::class)]
class AccessRule
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'accessRules')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Position $position = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Attribute $attribute = null;

    #[ORM\Column(enumType: Operation::class)]
    private ?Operation $operation = Operation::EQUALS;

    #[ORM\Column(enumType: MatchType::class)]
    private ?MatchType $matchType = MatchType::AND;

    #[ORM\Column(enumType: AttributeDimension::class)]
    private ?AttributeDimension $attributeDimension = AttributeDimension::VALUE;

    #[ORM\Column(enumType: FilterValueType::class, nullable: true)]
    private ?FilterValueType $filterValueType = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    private ?string $valueNumeric = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTime $valueDate = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $valueString = null;

    #[ORM\Column(nullable: true)]
    private ?bool $valueBoolean = null;

    #[ORM\ManyToOne]
    private ?AttributeOption $valueOption = null;

    #[ORM\Column(nullable: true)]
    private ?int $valueDuration = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPosition(): ?Position
    {
        return $this->position;
    }

    public function setPosition(?Position $position): static
    {
        $this->position = $position;

        return $this;
    }

    public function getAttribute(): ?Attribute
    {
        return $this->attribute;
    }

    public function setAttribute(?Attribute $attribute): static
    {
        $this->attribute = $attribute;

        return $this;
    }

    public function getOperation(): ?string
    {
        return $this->operation;
    }

    public function setOperation(Operation|string $operation): static
    {
        if (is_string($operation)) $operation = Operation::from($operation);
        $this->operation = $operation;

        return $this;
    }

    public function getMatchType(): ?string
    {
        return $this->matchType;
    }

    public function setMatchType(MatchType|string $matchType): static
    {
        if (is_string($matchType)) $matchType = MatchType::from($matchType);
        $this->matchType = $matchType;

        return $this;
    }

    public function getAttributeDimension(): ?string
    {
        return $this->attributeDimension;
    }

    public function setAttributeDimension(AttributeDimension|string $attributeDimension): static
    {
        if (is_string($attributeDimension)) $attributeDimension = AttributeDimension::from($attributeDimension);
        $this->attributeDimension = $attributeDimension;

        return $this;
    }

    public function getFilterValueType(): ?FilterValueType
    {
        return $this->filterValueType;
    }

    public function setFilterValueType(FilterValueType|string $filterValueType): static
    {
        if (is_string($filterValueType)) {
            $filterValueType = FilterValueType::from($filterValueType);
        }
        $this->filterValueType = $filterValueType;

        return $this;
    }

    public function getValue(): mixed {
        return match ($this->getFilterValueType()) {
            FilterValueType::NUMBER   => $this->getValueNumeric(),
            FilterValueType::DATE     => $this->getValueDate(),
            FilterValueType::DURATION => $this->getValueDuration(),
            FilterValueType::STRING   => $this->getValueString(),
            FilterValueType::BOOLEAN  => $this->isValueBoolean(),
            FilterValueType::OPTION   => $this->getValueOption(),
            default => null,
        };
    }

    public function getValueExists(): bool {
        return $this->getValue() != null;
    }

    public function setValue(mixed $value): void
    {
        match ($this->getFilterValueType()) {
            FilterValueType::NUMBER   => $this->setValueNumeric($value),
            FilterValueType::DATE     => $this->setValueDate($value),
            FilterValueType::DURATION => $this->setValueDuration($value),
            FilterValueType::STRING   => $this->setValueString($value),
            FilterValueType::BOOLEAN  => $this->setValueBoolean($value),
            FilterValueType::OPTION   => $this->setValueOption($value),
            default => null,
        };
    }

    public function getValueNumeric(): ?string
    {
        return $this->valueNumeric;
    }

    public function setValueNumeric(?string $valueNumeric): static
    {
        $this->valueNumeric = $valueNumeric;

        return $this;
    }

    public function getValueDate(): ?\DateTime
    {
        return $this->valueDate;
    }

    public function setValueDate(?\DateTime $valueDate): static
    {
        $this->valueDate = $valueDate;

        return $this;
    }

    public function getValueString(): ?string
    {
        return $this->valueString;
    }

    public function setValueString(?string $valueString): static
    {
        $this->valueString = $valueString;

        return $this;
    }

    public function isValueBoolean(): ?bool
    {
        return $this->valueBoolean;
    }

    public function setValueBoolean(?bool $valueBoolean): static
    {
        $this->valueBoolean = $valueBoolean;

        return $this;
    }

    public function getValueOption(): ?AttributeOption
    {
        return $this->valueOption;
    }

    public function setValueOption(?AttributeOption $valueOption): static
    {
        $this->valueOption = $valueOption;

        return $this;
    }

    public function getValueDuration(): ?int
    {
        return $this->valueDuration;
    }

    public function setValueDuration(?int $valueDuration): static
    {
        $this->valueDuration = $valueDuration;

        return $this;
    }
}
