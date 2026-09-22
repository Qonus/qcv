<?php

namespace App\Entity;

use App\Enum\AttributeDataType;
use App\Repository\AttributeOptionRepository;
use App\Repository\AttributeValueRepository;
use DateTime;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AttributeValueRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_ATTRIBUTE_VALUE', fields: ['candidate', 'attribute'])]
class AttributeValue
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'attributeValues')]
    #[ORM\JoinColumn(nullable: false, onDelete:'CASCADE')]
    private ?User $candidate = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Attribute $attribute = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $valueString = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $valueText = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    private ?string $valueNumeric = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTime $valueDate = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTime $valueDateEnd = null;

    #[ORM\Column(nullable: true)]
    private ?bool $valueBoolean = null;

    #[ORM\ManyToOne]
    private ?AttributeOption $valueOption = null;
    
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $valueImageUrl = null;
    
    #[ORM\Version]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $version = 0;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['default'=>'CURRENT_TIMESTAMP'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true, options: ['default'=>'CURRENT_TIMESTAMP'])]
    private ?\DateTimeInterface $updatedAt = null;
    
    #[ORM\PrePersist]
    public function setCreatedAtValue(): void
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTime();
    }

    #[ORM\PreUpdate]
    public function setUpdatedAtValue(): void
    {
        $this->updatedAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCandidate(): ?User
    {
        return $this->candidate;
    }

    public function setCandidate(?User $candidate): static
    {
        $this->candidate = $candidate;

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

    public function getValue(): mixed
    {
        return match ($this->attribute?->getDataType()) {
            AttributeDataType::BOOLEAN => $this->isValueBoolean(),
            AttributeDataType::STRING => $this->getValueString(),
            AttributeDataType::TEXT    => $this->getValueText(),
            AttributeDataType::DATE    => $this->getValueDate(),
            AttributeDataType::PERIOD  => [
                'start' => $this->getValueDate(),
                'end' => $this->getValueDateEnd()],
            AttributeDataType::IMAGE => $this->getValueImageUrl(),
            AttributeDataType::NUMERIC => $this->getValueNumeric(),
            AttributeDataType::SELECT => $this->getValueOption(),
            default => null,
        };
    }

    public function getValueExists(): bool {
        if ($this->getValue() == null) return false;
        if ($this->getAttribute()->getDataType() != AttributeDataType::PERIOD) {
            return true;
        }
        return $this->getValue()['start'] != null && $this->getValue()['end'] != null;
    }

    public function setValue(mixed $value): void
    {
        match ($this->attribute?->getDataType()) {
            AttributeDataType::BOOLEAN => $this->setValueBoolean($value),
            AttributeDataType::STRING => $this->setValueString($value),
            AttributeDataType::TEXT    => $this->setValueText($value),
            AttributeDataType::DATE    => $this->setValueDate($value),
            AttributeDataType::PERIOD  => $this->setValuePeriod($value),
            AttributeDataType::IMAGE => $this->setValueImageUrl($value),
            AttributeDataType::NUMERIC => $this->setValueNumeric($value),
            AttributeDataType::SELECT => $this->setValueOption($value),
            default => null,
        };
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

    public function getValueText(): ?string
    {
        return $this->valueText;
    }

    public function setValueText(?string $valueText): static
    {
        $this->valueText = $valueText;

        return $this;
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

    public function getValueDateEnd(): ?\DateTime
    {
        return $this->valueDateEnd;
    }

    public function setValueDateEnd(?\DateTime $valueDateEnd): static
    {
        $this->valueDateEnd = $valueDateEnd;

        return $this;
    }

    public function setValuePeriod(mixed $value) {
        $this->setValueDate($value['start']??null);
        $this->setValueDateEnd($value['end']??null);
    }

    public function setValuePeriodFromString(mixed $value) {
        $this->setValueDate(new DateTime($value['start']??null));
        $this->setValueDateEnd(new DateTime($value['end']??null));
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

    public function getValueImageUrl(): ?string
    {
        return $this->valueImageUrl;
    }

    public function setValueImageUrl(?string $valueImageUrl): static
    {
        $this->valueImageUrl = $valueImageUrl;

        return $this;
    }

    public function getVersion(): ?int
    {
        return $this->version;
    }

    public function getCreatedAt(): ?\DateTimeImmutable { return $this->createdAt; }
    public function getUpdatedAt(): ?\DateTimeInterface { return $this->updatedAt; }
}
