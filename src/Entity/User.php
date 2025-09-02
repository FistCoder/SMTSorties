<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
class User
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $lastname = null;

    #[ORM\Column(length: 50)]
    private ?string $firstname = null;

    #[ORM\Column(length: 15, nullable: true)]
    private ?string $phone = null;

    #[ORM\Column(length: 255)]
    private ?string $email = null;

    #[ORM\Column(length: 255)]
    private ?string $password = null;

    #[ORM\Column]
    private ?bool $active = null;

    #[ORM\ManyToOne(inversedBy: 'studentLst')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Campus $campus = null;

    /**
     * @var Collection<int, Hangout>
     */
    #[ORM\ManyToMany(targetEntity: Hangout::class, mappedBy: 'subscriberLst')]
    private Collection $subscribedHangoutLst;

    /**
     * @var Collection<int, Hangout>
     */
    #[ORM\OneToMany(targetEntity: Hangout::class, mappedBy: 'organizer', orphanRemoval: true)]
    private Collection $organizedHangoutLst;

    public function __construct()
    {
        $this->subscribedHangoutLst = new ArrayCollection();
        $this->organizedHangoutLst = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname): static
    {
        $this->lastname = $lastname;

        return $this;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname): static
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): static
    {
        $this->phone = $phone;

        return $this;
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

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    public function isActive(): ?bool
    {
        return $this->active;
    }

    public function setActive(bool $active): static
    {
        $this->active = $active;

        return $this;
    }

    public function getCampus(): ?Campus
    {
        return $this->campus;
    }

    public function setCampus(?Campus $campus): static
    {
        $this->campus = $campus;

        return $this;
    }

    /**
     * @return Collection<int, Hangout>
     */
    public function getSubscribedHangoutLst(): Collection
    {
        return $this->subscribedHangoutLst;
    }

    public function addSubscribedHangoutLst(Hangout $subscribedHangoutLst): static
    {
        if (!$this->subscribedHangoutLst->contains($subscribedHangoutLst)) {
            $this->subscribedHangoutLst->add($subscribedHangoutLst);
            $subscribedHangoutLst->addSubscriberLst($this);
        }

        return $this;
    }

    public function removeSubscribedHangoutLst(Hangout $subscribedHangoutLst): static
    {
        if ($this->subscribedHangoutLst->removeElement($subscribedHangoutLst)) {
            $subscribedHangoutLst->removeSubscriberLst($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, Hangout>
     */
    public function getOrganizedHangoutLst(): Collection
    {
        return $this->organizedHangoutLst;
    }

    public function addOrganizedHangoutLst(Hangout $organizedHangoutLst): static
    {
        if (!$this->organizedHangoutLst->contains($organizedHangoutLst)) {
            $this->organizedHangoutLst->add($organizedHangoutLst);
            $organizedHangoutLst->setOrganizer($this);
        }

        return $this;
    }

    public function removeOrganizedHangoutLst(Hangout $organizedHangoutLst): static
    {
        if ($this->organizedHangoutLst->removeElement($organizedHangoutLst)) {
            // set the owning side to null (unless already changed)
            if ($organizedHangoutLst->getOrganizer() === $this) {
                $organizedHangoutLst->setOrganizer(null);
            }
        }

        return $this;
    }

}
