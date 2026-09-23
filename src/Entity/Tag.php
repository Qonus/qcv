<?php

namespace App\Entity;

use App\Repository\TagRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\UniqueConstraint;

#[ORM\Entity(repositoryClass: TagRepository::class)]
#[ORM\UniqueConstraint(name:"UNIQ_TAG_NAME", fields: ["name"])]
class Tag
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    /**
     * @var Collection<int, Position>
     */
    #[ORM\ManyToMany(targetEntity: Position::class, mappedBy: 'tags')]
    private Collection $positions;

    /**
     * @var Collection<int, Project>
     */
    #[ORM\ManyToMany(targetEntity: Project::class, mappedBy: 'tags')]
    private Collection $projects;

    public function __construct() {
        $this->positions = new ArrayCollection();
        $this->projects = new ArrayCollection();
    }

    public function __tostring(): string {
        return $this->name;
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

    /**
     * @var Collection<int, Position>
     */
    public function getPositions(): Collection
    {
        return $this->positions;
    }

    /**
     * @var Collection<int, Project>
     */
    public function getProjects(): Collection
    {
        return $this->projects;
    }
}
