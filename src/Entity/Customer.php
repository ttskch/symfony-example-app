<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Customer\Person;
use App\EntityConstant\CustomerConstant;
use App\EntityListener\CustomerListener;
use App\Repository\CustomerRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=CustomerRepository::class)
 * @ORM\EntityListeners({CustomerListener::class})
 */
class Customer
{
    use TimestampableEntity;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     *
     * @Assert\NotBlank()
     * @Assert\Choice(callback={CustomerConstant::class, "getValidStates"})
     */
    public ?string $state = null;

    /**
     * @ORM\Column(type="string", length=255)
     *
     * @Assert\NotBlank()
     */
    public ?string $name = null;

    /**
     * @var Collection|Person[]
     *
     * @ORM\OneToMany(targetEntity=Person::class, mappedBy="customer", cascade={"persist", "remove"}, orphanRemoval=true)
     *
     * @Assert\Valid()
     */
    public Collection $people;

    /**
     * @var Collection|Project[]
     *
     * @ORM\OneToMany(targetEntity=Project::class, mappedBy="customer", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    public Collection $projects;

    public function __construct()
    {
        $this->people = new ArrayCollection();
        $this->projects = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function addPerson(Person $person): self
    {
        if (!$this->people->contains($person)) {
            $this->people[] = $person;
            $person->customer = $this;
        }

        return $this;
    }

    public function removePerson(Person $person): self
    {
        if ($this->people->removeElement($person)) {
            if ($person->customer === $this) {
                $person->customer = null;
            }
        }

        return $this;
    }

    public function addProject(Project $project): self
    {
        if (!$this->projects->contains($project)) {
            $this->projects[] = $project;
            $project->customer = $this;
        }

        return $this;
    }

    public function removeProject(Project $project): self
    {
        if ($this->projects->removeElement($project)) {
            if ($project->customer === $this) {
                $project->customer = null;
            }
        }

        return $this;
    }

    public function __toString(): string
    {
        return $this->name;
    }
}
