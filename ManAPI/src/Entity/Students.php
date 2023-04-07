<?php

namespace App\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use App\Repository\StudentsRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: StudentsRepository::class)]
class Students
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'students')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Classrooms $FK_classroom = null;

    #[ORM\Column(length: 31, nullable: true)]
    #[Assert\Regex('/[-a-zA-Z]/')]
    private ?string $lastname = null;

    #[ORM\Column(length: 31)]
    #[Assert\Regex('/[-a-zA-Z]/')]
    #[Assert\NotBlank(message:'First name is required')]
    private ?string $firstname = null;

    #[ORM\Column]
    private ?int $score = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFKClassroomId(): ?classrooms
    {
        return $this->FK_classroom;
    }

    public function setFKClassroomId(?classrooms $FK_classroom): self
    {
        $this->FK_classroom = $FK_classroom;

        return $this;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(?string $lastname): self
    {
        $this->lastname = $lastname;

        return $this;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname): self
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getScore(): ?int
    {
        return $this->score;
    }

    public function setScore(int $score): self
    {
        $this->score = $score;

        return $this;
    }
}
