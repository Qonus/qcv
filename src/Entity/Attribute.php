<?php

namespace App\Entity;

use App\Enum\AttributeDataType;
use App\Repository\AttributeCategoryRepository;
use App\Repository\AttributeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AttributeRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_ATTRIBUTE_NAME', fields: ['name'])]
class Attribute
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $name = null;

    #[ORM\ManyToOne(targetEntity: AttributeCategory::class, inversedBy: 'attributes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?AttributeCategory $category = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(enumType: AttributeDataType::class)]
    private ?AttributeDataType $dataType = null;

    #[ORM\Column]
    private ?bool $isBuiltin = false;

    /**
     * @var Collection<int, AttributeOption>
     */
    #[ORM\OneToMany(targetEntity: AttributeOption::class, mappedBy: 'attribute', orphanRemoval: true)]
    private Collection $options;

    public function __construct()
    {
        $this->options = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getCategory(): ?AttributeCategory
    {
        return $this->category;
    }

    public function setCategory(?AttributeCategory $category): static
    {
        $this->category = $category;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getDataType(): ?AttributeDataType
    {
        return $this->dataType;
    }

    public function setDataType(AttributeDataType $dataType): static
    {
        $this->dataType = $dataType;

        return $this;
    }

    public function isBuiltin(): ?bool
    {
        return $this->isBuiltin;
    }

    public function setIsBuiltin(bool $isBuiltin): static
    {
        $this->isBuiltin = $isBuiltin;

        return $this;
    }

    /**
     * @return Collection<int, AttributeOption>
     */
    public function getOptions(): Collection
    {
        return $this->options;
    }

    public function addOption(AttributeOption $option): static
    {
        if (!$this->options->contains($option)) {
            $this->options->add($option);
            $option->setAttribute($this);
        }

        return $this;
    }

    public function removeOption(AttributeOption $option): static
    {
        if ($this->options->removeElement($option)) {
            if ($option->getAttribute() === $this) {
                $option->setAttribute(null);
            }
        }

        return $this;
    }
}
