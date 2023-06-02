<?php

namespace App\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use App\Repository\SchedulesRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: SchedulesRepository::class)]
class Schedules
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['schedules_info', 'schedules_from_classroom'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'schedules',)]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['schedules_info'])]
    private ?Classrooms $FK_classroom = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Assert\DateTime()]
    #[Groups(['schedules_info', 'schedules_from_classroom'])]
    private ?\DateTimeInterface $start_time = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Assert\DateTime()]
    #[Groups(['schedules_info', 'schedules_from_classroom'])]
    private ?\DateTimeInterface $end_time = null;

    #[ORM\ManyToOne(inversedBy: 'schedules')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Users $FK_user = null;

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

    public function getStartTime(): ?\DateTimeInterface
    {
        return $this->start_time;
    }

    public function setStartTime(\DateTimeInterface $start_time): self
    {
        $this->start_time = $start_time;

        return $this;
    }

    public function getEndTime(): ?\DateTimeInterface
    {
        return $this->end_time;
    }

    public function setEndTime(\DateTimeInterface $end_time): self
    {
        $this->end_time = $end_time;

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
