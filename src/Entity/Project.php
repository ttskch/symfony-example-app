<?php

declare(strict_types=1);

namespace App\Entity;

use App\EntityConstant\ProjectConstant;
use App\EntityListener\ProjectListener;
use App\Repository\ProjectRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=ProjectRepository::class)
 * @ORM\EntityListeners({ProjectListener::class})
 */
class Project
{
    use TimestampableEntity;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Customer::class, inversedBy="projects")
     * @ORM\JoinColumn(nullable=false)
     */
    public ?Customer $customer;

    /**
     * @ORM\Column(type="string", length=255)
     *
     * @Assert\NotBlank()
     * @Assert\Choice(callback={ProjectConstant::class, "getValidStates"})
     */
    public ?string $state = null;

    /**
     * @ORM\Column(type="string", length=255)
     *
     * @Assert\NotBlank()
     */
    public ?string $name = null;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    public ?string $note = null;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="projects")
     */
    public ?User $user = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function __toString(): string
    {
        return $this->name;
    }
}
