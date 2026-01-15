<?php

namespace App\Entity;

use App\Repository\PollOptionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PollOptionRepository::class)]
class PollOption
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $label = null;

    #[ORM\ManyToOne(inversedBy: 'options')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Poll $poll = null;

    #[ORM\ManyToMany(targetEntity: User::class)]
    private Collection $voters;

    public function __construct()
    {
        $this->voters = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(string $label): static
    {
        $this->label = $label;

        return $this;
    }

    public function getPoll(): ?Poll
    {
        return $this->poll;
    }

    public function setPoll(?Poll $poll): static
    {
        $this->poll = $poll;

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getVoters(): Collection
    {
        return $this->voters;
    }

    public function addVoter(User $voter): static
    {
        if (!$this->voters->contains($voter)) {
            $this->voters->add($voter);
        }

        return $this;
    }

    public function removeVoter(User $voter): static
    {
        $this->voters->removeElement($voter);

        return $this;
    }
}
