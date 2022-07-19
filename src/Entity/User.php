<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserRepository::class)]
class User
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column()]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Delivery::class, orphanRemoval: true)]
    private Collection $deliveries;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Rideshare::class, orphanRemoval: true)]
    private Collection $rideshares;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Rent::class, orphanRemoval: true)]
    private Collection $rents;

    public function __construct()
    {
        $this->deliveries = new ArrayCollection();
        $this->rideshares = new ArrayCollection();
        $this->rents = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Collection<int, Delivery>
     */
    public function getDeliveries(): Collection
    {
        return $this->deliveries;
    }

    public function addDelivery(Delivery $delivery): self
    {
        if (!$this->deliveries->contains($delivery)) {
            $this->deliveries[] = $delivery;
            $delivery->setUser($this);
        }

        return $this;
    }

    public function removeDelivery(Delivery $delivery): self
    {
        if ($this->deliveries->removeElement($delivery)) {
            // set the owning side to null (unless already changed)
            if ($delivery->getUser() === $this) {
                $delivery->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Rideshare>
     */
    public function getRideshares(): Collection
    {
        return $this->rideshares;
    }

    public function addRideshare(Rideshare $rideshare): self
    {
        if (!$this->rideshares->contains($rideshare)) {
            $this->rideshares[] = $rideshare;
            $rideshare->setUser($this);
        }

        return $this;
    }

    public function removeRideshare(Rideshare $rideshare): self
    {
        if ($this->rideshares->removeElement($rideshare)) {
            // set the owning side to null (unless already changed)
            if ($rideshare->getUser() === $this) {
                $rideshare->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Rent>
     */
    public function getRents(): Collection
    {
        return $this->rents;
    }

    public function addRent(Rent $rent): self
    {
        if (!$this->rents->contains($rent)) {
            $this->rents[] = $rent;
            $rent->setUser($this);
        }

        return $this;
    }

    public function removeRent(Rent $rent): self
    {
        if ($this->rents->removeElement($rent)) {
            // set the owning side to null (unless already changed)
            if ($rent->getUser() === $this) {
                $rent->setUser(null);
            }
        }

        return $this;
    }
}

