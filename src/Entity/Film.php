<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Repository\FilmRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity(repositoryClass: FilmRepository::class)]
#[ApiResource(
    operations: [
        'get' => new Get(),
        'post' => new Post(),
        'delete' => new Delete(),
        'patch' => new Patch(),
    ]
)]
class Film
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    private ?string $title = null;

    #[ORM\Column(length: 180)]
    private ?string $filmFilename = null;
    private ?UploadedFile $filmFile = null;

    #[ORM\ManyToMany(targetEntity: Cinema::class, inversedBy: 'films')]
    private $cinemas;

    #[ORM\ManyToMany(targetEntity: Genre::class, inversedBy: 'films')]
    private $genres;

    #[ORM\Column(length: 180)]
    private ?string $description = null;

    #[ORM\Column(type: 'integer')]
    private ?int $ageMin;

    #[ORM\Column(type: 'integer')]
    private ?int $duration;

    #[ORM\Column]
    private ?bool $isFavorite = null;

    #[ORM\OneToMany(mappedBy: 'film', targetEntity: Review::class, orphanRemoval: true)]
    private Collection $reviews;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $createdAt = null;

    public function __construct()
    {
        $this->cinemas = new ArrayCollection();
        $this->genres = new ArrayCollection();
        $this->createdAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    public function getFilmFilename(): ?string
    {
        return $this->filmFilename;
    }

    public function setFilmFilename(?string $filmFilename): self
    {
        $this->filmFilename = $filmFilename;
        return $this;
    }

    public function getFilmFile(): ?UploadedFile
    {
        return $this->filmFile;
    }

    public function setFilmFile(?UploadedFile $filmFile): self
    {
        $this->filmFile = $filmFile;
        return $this;
    }

    public function getCinemas(): Collection
    {
        return $this->cinemas;
    }

    public function setCinemas(Collection $cinemas): self
    {
        $this->cinemas = $cinemas;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getGenres(): Collection
    {
        return $this->genres;
    }

    public function setGenres(Collection $genres): self
    {
        $this->genres = $genres;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getAgeMin(): ?int
    {
        return $this->ageMin;
    }

    public function setAgeMin(int $ageMin): self
    {
        $this->ageMin = $ageMin;
        return $this;
    }

    public function getDuration(): ?int
    {
        return $this->duration;
    }

    public function setDuration(int $duration): self
    {
        $this->duration = $duration;
        return $this;
    }

    public function getIsFavorite(): ?bool
    {
        return $this->isFavorite;
    }

    public function setIsFavorite(bool $isFavorite): self
    {
        $this->isFavorite = $isFavorite;
        return $this;
    }

    public function getReviews(): Collection
    {
        return $this->reviews;
    }

    // Ajouter un avis
    public function addReview(Review $review): self
    {
        if (!$this->reviews->contains($review)) {
            $this->reviews[] = $review;
            $review->setSession($this);
        }

        return $this;
    }

    // Supprimer un avis
    public function removeReview(Review $review): self
    {
        if ($this->reviews->removeElement($review)) {
            // Set the owning side to null (unless already changed)
            if ($review->getSession() === $this) {
                $review->setSession(null);
            }
        }

        return $this;
    }

    public function getGenresAsString(): string
    {
        return implode(', ', $this->genres->map(fn($genre) => $genre->getName())->toArray());
    }

    public function getCinemasAsString(): string
    {
        return implode(', ', $this->cinemas->map(fn($cinema) => $cinema->getName())->toArray());
    }

}
