<?php

namespace App\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use App\Repository\ClassroomsRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ClassroomsRepository::class)]
class Classrooms
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'classrooms')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Establishments $FK_establishment = null;

    #[ORM\Column(length: 127)]
    #[Assert\NotBlank(message:'Name is required.')]
    #[Assert\Regex('/[-a-zA-Z0-9]/')]
    private ?string $name = null;

    #[ORM\OneToMany(mappedBy: 'FK_classroom', targetEntity: Students::class, orphanRemoval: true)]
    private Collection $students;

    #[ORM\OneToMany(mappedBy: 'FK_classroom', targetEntity: Schedules::class, orphanRemoval: true)]
    private Collection $schedules;

    public function __construct()
    {
        $this->students = new ArrayCollection();
        $this->schedules = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFKEstablishmentId(): ?establishments
    {
        return $this->FK_establishment;
    }

    public function setFKEstablishmentId(?establishments $FK_establishment): self
    {
        $this->FK_establishment = $FK_establishment;

        return $this;
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
     * @return Collection<int, Students>
     */
    public function getStudents(): Collection
    {
        return $this->students;
    }

    public function addStudent(Students $student): self
    {
        if (!$this->students->contains($student)) {
            $this->students->add($student);
            $student->setFKClassroomId($this);
        }

        return $this;
    }

    public function removeStudent(Students $student): self
    {
        if ($this->students->removeElement($student)) {
            // set the owning side to null (unless already changed)
            if ($student->getFKClassroomId() === $this) {
                $student->setFKClassroomId(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Schedules>
     */
    public function getSchedules(): Collection
    {
        return $this->schedules;
    }

    public function addSchedule(Schedules $schedule): self
    {
        if (!$this->schedules->contains($schedule)) {
            $this->schedules->add($schedule);
            $schedule->setFKClassroomId($this);
        }

        return $this;
    }

    public function removeSchedule(Schedules $schedule): self
    {
        if ($this->schedules->removeElement($schedule)) {
            // set the owning side to null (unless already changed)
            if ($schedule->getFKClassroomId() === $this) {
                $schedule->setFKClassroomId(null);
            }
        }

        return $this;
    }
}
