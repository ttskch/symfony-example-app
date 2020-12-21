<?php

declare(strict_types=1);

namespace App\Entity\Customer;

use App\Entity\Customer;
use App\EntityListener\Customer\PersonListener;
use App\Repository\Customer\PersonRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=PersonRepository::class)
 * @ORM\EntityListeners({PersonListener::class})
 */
class Person
{
    use TimestampableEntity;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Customer::class, inversedBy="people")
     * @ORM\JoinColumn(nullable=false)
     */
    public ?Customer $customer = null;

    /**
     * @ORM\Column(type="string", length=255)
     *
     * @Assert\NotBlank()
     */
    public ?string $fullName = null;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     *
     * @Assert\Email()
     */
    public ?string $email = null;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    public ?string $tel = null;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    public ?string $note = null;

    public function getId(): ?int
    {
        return $this->id;
    }
}
