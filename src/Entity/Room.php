<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Repository\RoomRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RoomRepository::class)]
#[ApiResource(
    operations: [
        'get' => new Get(),
        'post' => new Post(),
        'delete' => new Delete(),
        'patch' => new Patch(),
    ]
)]
class Room
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    public function __construct()
    {
        // Initialisation de reservedSeats à un tableau vide
        $this->reservedSeats = [];
    }

    #[ORM\Column(type: 'integer')]
    private ?int $number;

    #[ORM\Column(length: 180)]
    private ?string $quality = null;
    #[ORM\Column(type: 'integer')]
    private $rowsRoom;

    #[ORM\Column(type: 'integer')]
    private $columnsRoom;

    #[ORM\Column(type: 'integer')]
    private $totalSeats;

    #[ORM\Column(type: 'json')]
    private $accessibleSeats = [];

    #[ORM\Column(type: 'json')]
    private $stairs = [];

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNumber(): ?int
    {
        return $this->number;
    }

    public function setNumber(int $number): self
    {
        $this->number = $number;
        return $this;
    }

    public function getQuality(): ?string
    {
        return $this->quality;
    }

    public function setQuality(string $quality): self
    {
        $this->quality = $quality;
        return $this;
    }

    public function getRowsRoom(): ?int
    {
        return $this->rowsRoom;
    }

    public function setRowsRoom(int $rowsRoom): self
    {
        $this->rowsRoom = $rowsRoom;

        return $this;
    }

    public function getColumnsRoom(): ?int
    {
        return $this->columnsRoom;
    }

    public function setColumnsRoom(int $columnsRoom): self
    {
        $this->columnsRoom = $columnsRoom;

        return $this;
    }


    public function getAccessibleSeats(): array
    {
        return $this->accessibleSeats;
    }

    public function setAccessibleSeats(array $accessibleSeats): self
    {
        $this->accessibleSeats = $accessibleSeats;
        return $this;
    }

    public function getTotalSeats(): ?int
    {
        return $this->totalSeats;
    }

    public function setTotalSeats(int $totalSeats): self
    {
        $this->totalSeats = $totalSeats;

        return $this;
    }

    public function getStairs(): array
    {
        return $this->stairs;
    }

    public function setStairs(array $stairs): self
    {
        $this->stairs = $stairs;

        return $this;
    }

}