<?php

namespace App\Entity;

use App\Enum\AttributeDimension;
use App\Enum\FilterValueType;
use App\Enum\MatchType;
use App\Enum\Operation;
use App\Repository\AccessRuleRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AccessRuleRepository::class)]
class AccessRule
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'accessRules')]
    #[ORM\JoinColumn(nullable: false)]
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
}
