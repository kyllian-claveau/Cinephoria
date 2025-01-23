<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Post;
use App\Controller\AuthController;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    operations: [
        new Post (
            name: 'user',
            uriTemplate: '/register',
            controller: AuthController::class
        )
    ]
)]
#[ORM\Entity]
#[ORM\Table(name: "user")]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private $id;

    #[ORM\Column(type: 'string', length: 64, nullable: true)]
    private ?string $confirmationToken = null;

    #[ORM\Column(type: 'boolean')]
    private bool $isActive = false;

    #[ORM\Column(type: 'boolean', nullable: true)]
    private ?bool $isTemporaryPassword = false;

    #[ORM\Column(type: "string", length: 180, unique: true)]
    #[Assert\Email]
    private $email;

    #[ORM\Column(type: "string", length: 255)]
    private $firstname;

    #[ORM\Column(type: "string", length: 255)]
    private $lastname;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    private array $roles;

    #[ORM\Column(type: "string")]
    private $password;

    // Getters et Setters...

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getConfirmationToken(): ?string
    {
        return $this->confirmationToken;
    }

    public function setConfirmationToken(?string $confirmationToken): self
    {
        $this->confirmationToken = $confirmationToken;
        return $this;
    }

    public function getIsTemporaryPassword(): ?bool
    {
        return $this->isTemporaryPassword;
    }

    public function setIsTemporaryPassword(?bool $isTemporaryPassword): self
    {
        $this->isTemporaryPassword = $isTemporaryPassword;
        return $this;
    }

    public function getIsActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;
        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname): self
    {
        $this->firstname = $firstname;
        return $this;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname): self
    {
        $this->lastname = $lastname;
        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;
        return $this;
    }

    /**
     * @see UserInterface
     *
     * @return list<string>
     */
    public function getRoles(): array
    {
        return $this->roles;
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    public function getUsername(): string
    {
        return $this->email;
    }

    public function eraseCredentials(): void
    {
    }
}