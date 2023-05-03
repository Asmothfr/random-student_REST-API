<?php

namespace App\Entity;

use App\Repository\EstablishmentsRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Type;
use JMS\Serializer\Annotation\Groups;
use Hateoas\Configuration\Annotation as Hateoas;
use Symfony\Component\Validator\Constraints as Assert;


#[ORM\Entity(repositoryClass: EstablishmentsRepository::class)]
class Establishments
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 63)]
    #[Assert\NotBlank(message:'Name is required.')]
    #[Assert\Regex('/[-a-zA-Z0-9]/')]
    private ?string $name = null;

    #[ORM\OneToMany(mappedBy: 'FK_establishment', targetEntity: Classrooms::class, orphanRemoval: true)]
    private Collection $classrooms;

    #[ORM\ManyToOne(inversedBy: 'establishments')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Users $FK_user = null;



    public function __construct()
    {
        $this->classrooms = new ArrayCollection();
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
     * @return Collection<int, Classrooms>
     */
    public function getClassrooms(): Collection
    {
        return $this->classrooms;
    }

    public function addClassroom(Classrooms $classroom): self
    {
        if (!$this->classrooms->contains($classroom)) {
            $this->classrooms->add($classroom);
            $classroom->setFKEstablishmentId($this);
        }

        return $this;
    }

    public function removeClassroom(Classrooms $classroom): self
    {
        if ($this->classrooms->removeElement($classroom)) {
            // set the owning side to null (unless already changed)
            if ($classroom->getFKEstablishmentId() === $this) {
                $classroom->setFKEstablishmentId(null);
            }
        }

        return $this;
    }

    public function getFKUser(): ?Users
    {
        return $this->FK_user;
    }

    public function setFKUser(?Users $FK_user): self
    {
        $this->FK_user = $FK_user;

        return $this;
    }
}
