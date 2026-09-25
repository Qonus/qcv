<?php

namespace App\Entity;

use App\Enum\BuiltinAttribute;
use App\Enum\Theme;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[UniqueEntity(fields: ['email'], message: 'There is already an account with this email')]
class User implements UserInterface, PasswordAuthenticatedUserInterface, EquatableInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    private ?string $email = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    private array $roles = ["ROLE_CANDIDATE"];

    /**
     * @var string The hashed password
     */
    #[ORM\Column(nullable: true)]
    private ?string $password = null;

    #[ORM\Column]
    private bool $isVerified = false;
    #[ORM\Column]
    private bool $isBlocked = false;

    /**
     * @var Collection<int, OAuthAccount>
     */
    #[ORM\OneToMany(targetEntity: OAuthAccount::class, mappedBy: 'user', orphanRemoval: true)]
    private Collection $oauthAccounts;

    /**
     * @var Collection<int, AttributeValue>
     */
    #[ORM\OneToMany(targetEntity: AttributeValue::class, mappedBy: 'candidate', orphanRemoval: true)]
    private Collection $attributeValues;

    #[ORM\Column(enumType: Theme::class)]
    private ?Theme $theme = Theme::DARK;

    #[ORM\Column(length: 5, nullable: false)]
    private ?string $locale = "en";

    /**
     * @var Collection<int, Project>
     */
    #[ORM\OneToMany(targetEntity: Project::class, mappedBy: 'candidate', orphanRemoval: true)]
    private Collection $projects;

    /**
     * @var Collection<int, CV>
     */
    #[ORM\OneToMany(targetEntity: CV::class, mappedBy: 'candidate', orphanRemoval: true)]
    private Collection $cvs;

    /**
     * @var Collection<int, Post>
     */
    #[ORM\OneToMany(targetEntity: Post::class, mappedBy: 'author', cascade: ['remove'], orphanRemoval: true)]
    private Collection $posts;

    /**
     * @var Collection<int, Like>
     */
    #[ORM\OneToMany(targetEntity: Like::class, mappedBy: 'recruiter', orphanRemoval: true)]
    private Collection $likes;

    public function __construct()
    {
        $this->oauthAccounts = new ArrayCollection();
        $this->attributeValues = new ArrayCollection();
        $this->projects = new ArrayCollection();
        $this->cvs = new ArrayCollection();
        $this->posts = new ArrayCollection();
        $this->likes = new ArrayCollection();
    }

    public function isEqualTo(UserInterface $user): bool
    {
        if (!$user instanceof self) {
            return false;
        }
        if ($this->isBlocked !== $user->isBlocked()) {
            return false;
        }
        return true;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        if (empty($roles)) {
            $roles[] = 'ROLE_CANDIDATE';
        }

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    public function getRole(): string
    {
        if (empty($this->roles)) {
            return 'ROLE_CANDIDATE';
        }

        return $this->roles[0];
    }

    public function setRole(string $role): static
    {
        $this->roles = [$role];

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    #[\Deprecated]
    public function eraseCredentials(): void
    {
        // @deprecated, to be removed when upgrading to Symfony 8
    }

    public function isVerified(): bool
    {
        return $this->isVerified;
    }

    public function setIsVerified(bool $isVerified): static
    {
        $this->isVerified = $isVerified;

        return $this;
    }

    public function isBlocked(): bool
    {
        return $this->isBlocked;
    }

    public function setIsBlocked(bool $isBlocked): static
    {
        $this->isBlocked = $isBlocked;

        return $this;
    }

    public function getStatus(): string
    {
        if ($this->isBlocked) {
            return 'blocked';
        }

        return $this->isVerified ? 'verified' : 'unverified';
    }

    public function setStatus(string $status): static
    {
        match ($status) {
            'blocked' => [
                $this->isBlocked = true,
            ],
            'verified' => [
                $this->isBlocked = false,
                $this->isVerified = true,
            ],
            'unverified' => [
                $this->isBlocked = false,
                $this->isVerified = false,
            ],
        };

        return $this;
    }

    /**
     * @return Collection<int, OAuthAccount>
     */
    public function getOAuthAccounts(): Collection
    {
        return $this->oauthAccounts;
    }

    public function addOAuthAccount(OAuthAccount $oauthAccount): static
    {
        if (!$this->oauthAccounts->contains($oauthAccount)) {
            $this->oauthAccounts->add($oauthAccount);
            $oauthAccount->setUser($this);
        }

        return $this;
    }

    public function removeOAuthAccount(OAuthAccount $oauthAccount): static
    {
        if ($this->oauthAccounts->removeElement($oauthAccount)) {
            // set the owning side to null (unless already changed)
            if ($oauthAccount->getUser() === $this) {
                $oauthAccount->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, AttributeValue>
     */
    public function getAttributeValues(): Collection
    {
        return $this->attributeValues;
    }

    public function addAttributeValue(AttributeValue $attributeValue): static
    {
        if (!$this->attributeValues->contains($attributeValue)) {
            $this->attributeValues->add($attributeValue);
            $attributeValue->setCandidate($this);
        }

        return $this;
    }

    public function removeAttributeValue(AttributeValue $attributeValue): static
    {
        if ($this->attributeValues->removeElement($attributeValue)) {
            // set the owning side to null (unless already changed)
            if ($attributeValue->getCandidate() === $this) {
                $attributeValue->setCandidate(null);
            }
        }

        return $this;
    }

    public function getTheme(): ?Theme
    {
        return $this->theme;
    }

    public function setTheme(Theme $theme): static
    {
        $this->theme = $theme;

        return $this;
    }

    public function getLocale(): ?string
    {
        return $this->locale;
    }

    public function setLocale(?string $locale): static
    {
        $this->locale = $locale;

        return $this;
    }

    /**
     * @return Collection<int, Project>
     */
    public function getProjects(): Collection
    {
        return $this->projects;
    }

    public function addProject(Project $project): static
    {
        if (!$this->projects->contains($project)) {
            $this->projects->add($project);
            $project->setCandidate($this);
        }

        return $this;
    }

    public function removeProject(Project $project): static
    {
        if ($this->projects->removeElement($project)) {
            // set the owning side to null (unless already changed)
            if ($project->getCandidate() === $this) {
                $project->setCandidate(null);
            }
        }

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
            $cv->setCandidate($this);
        }

        return $this;
    }

    public function removeCv(CV $cv): static
    {
        if ($this->cvs->removeElement($cv)) {
            // set the owning side to null (unless already changed)
            if ($cv->getCandidate() === $this) {
                $cv->setCandidate(null);
            }
        }

        return $this;
    }

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
            $post->setAuthor($this);
        }

        return $this;
    }

    public function removePost(Post $post): static
    {
        if ($this->posts->removeElement($post)) {
            // set the owning side to null (unless already changed)
            if ($post->getAuthor() === $this) {
                $post->setAuthor(null);
            }
        }

        return $this;
    }

    public function getImage(): ?string {
        foreach ($this->attributeValues as $attributeValue) {
            if ($attributeValue->getAttribute()->getName() == BuiltinAttribute::IMAGE_URL->value) {
                return $attributeValue->getValue();
            }
        }
        return null;
    }

    /**
     * @return Collection<int, Like>
     */
    public function getLikes(): Collection
    {
        return $this->likes;
    }

    public function addLike(Like $like): static
    {
        if (!$this->likes->contains($like)) {
            $this->likes->add($like);
            $like->setRecruiter($this);
        }

        return $this;
    }

    public function removeLike(Like $like): static
    {
        if ($this->likes->removeElement($like)) {
            // set the owning side to null (unless already changed)
            if ($like->getRecruiter() === $this) {
                $like->setRecruiter(null);
            }
        }

        return $this;
    }
}
