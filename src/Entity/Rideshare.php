<?php

namespace App\Entity;

use App\Repository\RideshareRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RideshareRepository::class)]
class Rideshare
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column()]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'rideshares')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\Column(length: 1)]
    private ?int $action_point_withdrew = null;

    #[ORM\Column(length: 1)]
    private ?int $booster_point_withdrew = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $created_at = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function setActionPointWithdrew(int $actionPointWithdrew): self
    {
        $this->action_point_withdrew = $actionPointWithdrew;

        return $this;
    }


    public function setBoosterPointWithdrew(int $boosterPointWithdrew): self
    {
        $this->booster_point_withdrew = $boosterPointWithdrew;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeImmutable $created_at): self
    {
        $this->created_at = $created_at;

        return $this;
    }
}

