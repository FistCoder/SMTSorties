<?php

namespace App\Entity;

use App\Repository\HangoutRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: HangoutRepository::class)]
class Hangout
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column]
    private ?\DateTime $startingDateTime = null;

    #[ORM\Column(type: Types::TIME_MUTABLE)]
    private ?\DateTime $length = null;

    #[ORM\Column]
    private ?\DateTime $lastSubmitDate = null;

    #[ORM\Column]
    private ?int $maxParticipant = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $detail = null;

    #[ORM\ManyToOne(inversedBy: 'hangoutLst')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Location $location = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?State $state = null;

    #[ORM\ManyToOne(inversedBy: 'hangoutLst')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Campus $campus = null;

    /**
     * @var Collection<int, User>
     */
    #[ORM\ManyToMany(targetEntity: User::class, inversedBy: 'subscribedHangoutLst')]
    private Collection $subscriberLst;

    #[ORM\ManyToOne(inversedBy: 'organizedHangoutLst')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $organizer = null;

    public function __construct()
    {
        $this->subscriberLst = new ArrayCollection();
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

    public function getStartingDateTime(): ?\DateTime
    {
        return $this->startingDateTime;
    }

    public function setStartingDateTime(\DateTime $startingDateTime): static
    {
        $this->startingDateTime = $startingDateTime;

        return $this;
    }

    public function getLength(): ?\DateTime
    {
        return $this->length;
    }

    public function setLength(\DateTime $length): static
    {
        $this->length = $length;

        return $this;
    }

    public function getLastSubmitDate(): ?\DateTime
    {
        return $this->lastSubmitDate;
    }

    public function setLastSubmitDate(\DateTime $lastSubmitDate): static
    {
        $this->lastSubmitDate = $lastSubmitDate;

        return $this;
    }

    public function getMaxParticipant(): ?int
    {
        return $this->maxParticipant;
    }

    public function setMaxParticipant(int $maxParticipant): static
    {
        $this->maxParticipant = $maxParticipant;

        return $this;
    }

    public function getDetail(): ?string
    {
        return $this->detail;
    }

    public function setDetail(string $detail): static
    {
        $this->detail = $detail;

        return $this;
    }

    public function getLocation(): ?Location
    {
        return $this->location;
    }

    public function setLocation(?Location $location): static
    {
        $this->location = $location;

        return $this;
    }

    public function getState(): ?State
    {
        return $this->state;
    }

    public function setState(?State $state): static
    {
        $this->state = $state;

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
     * @return Collection<int, User>
     */
    public function getSubscriberLst(): Collection
    {
        return $this->subscriberLst;
    }

    public function addSubscriberLst(User $subscriberLst): static
    {
        if (!$this->subscriberLst->contains($subscriberLst)) {
            $this->subscriberLst->add($subscriberLst);
        }

        return $this;
    }

    public function removeSubscriberLst(User $subscriberLst): static
    {
        $this->subscriberLst->removeElement($subscriberLst);

        return $this;
    }

    public function getOrganizer(): ?User
    {
        return $this->organizer;
    }

    public function setOrganizer(?User $organizer): static
    {
        $this->organizer = $organizer;

        return $this;
    }

}
