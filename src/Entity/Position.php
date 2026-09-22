<?php

namespace App\Entity;

use App\Enum\Level;
use App\Repository\PositionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PositionRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Position implements TaggableEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = '';
    
    /**
     * @var Collection<int, Tag>
     */
    #[ORM\ManyToMany(targetEntity: Tag::class, cascade: ['persist'])]
    private Collection $tags;
    
    #[ORM\Column(nullable: true)]
    private ?int $maxProjects = 3;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $company = null;

    #[ORM\Column(nullable: true, enumType: Level::class)]
    private ?Level $level = null;

    /**
     * @var Collection<int, CV>
     */
    #[ORM\OneToMany(targetEntity: CV::class, mappedBy: 'position', orphanRemoval: true)]
    private Collection $cvs;

    #[ORM\Version]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $version = 0;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['default'=>'CURRENT_TIMESTAMP'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true, options: ['default'=>'CURRENT_TIMESTAMP'])]
    private ?\DateTimeInterface $updatedAt = null;

    /**
     * @var Collection<int, Post>
     */
    #[ORM\OneToMany(targetEntity: Post::class, mappedBy: 'position', orphanRemoval: true)]
    private Collection $posts;

    /**
     * @var Collection<int, PositionAttribute>
     */
    #[ORM\OneToMany(targetEntity: PositionAttribute::class, mappedBy: 'position', orphanRemoval: true)]
    private Collection $positionAttributes;

    /**
     * @var Collection<int, AccessRule>
     */
    #[ORM\OneToMany(targetEntity: AccessRule::class, mappedBy: 'position', orphanRemoval: true)]
    private Collection $accessRules;

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

    public function __construct()
    {
        $this->tags = new ArrayCollection();
        $this->cvs = new ArrayCollection();
        $this->posts = new ArrayCollection();
        $this->positionAttributes = new ArrayCollection();
        $this->accessRules = new ArrayCollection();
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return Collection<int, Tag>
     */
    public function getTags(): Collection
    {
        return $this->tags;
    }

    public function addTag(Tag $tag): static
    {
        if (!$this->tags->contains($tag)) {
            $this->tags->add($tag);
        }

        return $this;
    }

    public function removeTag(Tag $tag): static
    {
        $this->tags->removeElement($tag);

        return $this;
    }

    public function getMaxProjects(): ?int
    {
        return $this->maxProjects;
    }

    public function setMaxProjects(?int $maxProjects): static
    {
        $this->maxProjects = $maxProjects;

        return $this;
    }

    public function getCompany(): ?string
    {
        return $this->company;
    }

    public function setCompany(?string $company): static
    {
        $this->company = $company;

        return $this;
    }

    public function getLevel(): ?Level
    {
        return $this->level;
    }

    public function setLevel(?Level $level): static
    {
        $this->level = $level;

        return $this;
    }

    /**
     * @return Collection<int, CV>
     */
    public function getCvs(): Collection
    {
        return $this->cvs;
    }

    public function addCv(CV $cv): static
    {
        if (!$this->cvs->contains($cv)) {
            $this->cvs->add($cv);
            $cv->setPosition($this);
        }

        return $this;
    }

    public function removeCv(CV $cv): static
    {
        if ($this->cvs->removeElement($cv)) {
            // set the owning side to null (unless already changed)
            if ($cv->getPosition() === $this) {
                $cv->setPosition(null);
            }
        }

        return $this;
    }

    public function getVersion(): ?int
    {
        return $this->version;
    }

    public function getCreatedAt(): ?\DateTimeImmutable { return $this->createdAt; }
    public function getUpdatedAt(): ?\DateTimeInterface { return $this->updatedAt; }

    /**
     * @return Collection<int, Post>
     */
    public function getPosts(): Collection
    {
        return $this->posts;
    }

    public function addPost(Post $post): static
    {
        if (!$this->posts->contains($post)) {
            $this->posts->add($post);
            $post->setPosition($this);
        }

        return $this;
    }

    public function removePost(Post $post): static
    {
        if ($this->posts->removeElement($post)) {
            // set the owning side to null (unless already changed)
            if ($post->getPosition() === $this) {
                $post->setPosition(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, PositionAttribute>
     */
    public function getPositionAttributes(): Collection
    {
        return $this->positionAttributes;
    }

    /**
     * 
     * @return array<Attribute>
     */
    public function getAttributes(): array
    {
        return array_map(
            fn(PositionAttribute $positionAttribute) => $positionAttribute->getAttribute(),
            $this->positionAttributes->toArray()
        );
    }

    public function addPositionAttribute(PositionAttribute $positionAttribute): static
    {
        if (!$this->positionAttributes->contains($positionAttribute)) {
            $this->positionAttributes->add($positionAttribute);
            $positionAttribute->setPosition($this);
        }

        return $this;
    }

    public function removePositionAttribute(PositionAttribute $positionAttribute): static
    {
        if ($this->positionAttributes->removeElement($positionAttribute)) {
            // set the owning side to null (unless already changed)
            if ($positionAttribute->getPosition() === $this) {
                $positionAttribute->setPosition(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, AccessRule>
     */
    public function getAccessRules(): Collection
    {
        return $this->accessRules;
    }

    public function addAccessRule(AccessRule $accessRule): static
    {
        if (!$this->accessRules->contains($accessRule)) {
            $this->accessRules->add($accessRule);
            $accessRule->setPosition($this);
        }

        return $this;
    }

    public function removeAccessRule(AccessRule $accessRule): static
    {
        if ($this->accessRules->removeElement($accessRule)) {
            // set the owning side to null (unless already changed)
            if ($accessRule->getPosition() === $this) {
                $accessRule->setPosition(null);
            }
        }

        return $this;
    }
}
