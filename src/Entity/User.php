<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[UniqueEntity(fields: ['email'], message: 'There is already an account with this email')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
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
    private array $roles = [];

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
    #[ORM\OneToMany(targetEntity: OAuthAccount::class, mappedBy: 'userId', orphanRemoval: true)]
    private Collection $oauthAccounts;

    /**
     * @var Collection<int, AttributeValue>
     */
    #[ORM\OneToMany(targetEntity: AttributeValue::class, mappedBy: 'candidate', orphanRemoval: true)]
    private Collection $attributeValues;

    public function __construct()
    {
        $this->oauthAccounts = new ArrayCollection();
        $this->attributeValues = new ArrayCollection();
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
            $oauthAccount->setUserId($this);
        }

        return $this;
    }

    public function removeOAuthAccount(OAuthAccount $oauthAccount): static
    {
        if ($this->oauthAccounts->removeElement($oauthAccount)) {
            // set the owning side to null (unless already changed)
            if ($oauthAccount->getUserId() === $this) {
                $oauthAccount->setUserId(null);
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
}
