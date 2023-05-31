<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\UsersRepository;
use JMS\Serializer\Annotation\Type;
use JMS\Serializer\Annotation\Groups;
use Hateoas\Configuration\Annotation as Hateoas;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;


#[ORM\Entity(repositoryClass: UsersRepository::class)]
class Users implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["user_info"])]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    #[Assert\Email(message:"The email is not valid.")]
    #[Assert\NotBlank(message:"Email is required.")]
    #[Groups(["user_info"])]
    private ?string $email = null;

    /**
     * @Type("array")
     */
    #[ORM\Column]
    #[Assert\NotBlank(message: "Roles is required.")]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    #[Assert\NotBlank(message: "Password is required.")]
    #[Assert\Regex('/[-a-zA-Z0-9]/')]
    #[Assert\Length(min: 16, max:255, minMessage: 'Password must be at least 16 characters long', maxMessage: 'Password cannot be longer than 255 characters',)]
    private ?string $password = null;

    #[ORM\Column(length: 31)]
    #[Assert\NotBlank(message: "Name is required.")]
    #[Assert\Regex('/[-a-zA-Z0-9]/')]
    #[Assert\Length(min: 8, max:32, minMessage: 'Name must be at least 8 characters long', maxMessage: 'Name cannot be longer than 32 characters',)]
    #[Groups(["user_info"])]
    private ?string $name = null;

    #[ORM\OneToMany(mappedBy: 'FK_user', targetEntity: Classrooms::class, orphanRemoval: true)]
    private Collection $classrooms;

    #[ORM\OneToMany(mappedBy: 'FK_user', targetEntity: Students::class, orphanRemoval: true)]
    private Collection $students;

    #[ORM\OneToMany(mappedBy: 'FK_user', targetEntity: Schedules::class, orphanRemoval: true)]
    private Collection $schedules;

    #[ORM\OneToMany(mappedBy: 'FK_user', targetEntity: Establishments::class, orphanRemoval: true)]
    private Collection $establishments;

    

    public function __construct()
    {
        $this->classrooms = new ArrayCollection();
        $this->students = new ArrayCollection();
        $this->schedules = new ArrayCollection();
        $this->establishments = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
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
            $classroom->setFKUser($this);
        }

        return $this;
    }

    public function removeClassroom(Classrooms $classroom): self
    {
        if ($this->classrooms->removeElement($classroom)) {
            // set the owning side to null (unless already changed)
            if ($classroom->getFKUser() === $this) {
                $classroom->setFKUser(null);
            }
        }

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
            $student->setFKUser($this);
        }

        return $this;
    }

    public function removeStudent(Students $student): self
    {
        if ($this->students->removeElement($student)) {
            // set the owning side to null (unless already changed)
            if ($student->getFKUser() === $this) {
                $student->setFKUser(null);
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
            $schedule->setFKUser($this);
        }

        return $this;
    }

    public function removeSchedule(Schedules $schedule): self
    {
        if ($this->schedules->removeElement($schedule)) {
            // set the owning side to null (unless already changed)
            if ($schedule->getFKUser() === $this) {
                $schedule->setFKUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Establishments>
     */
    public function getEstablishments(): Collection
    {
        return $this->establishments;
    }

    public function addEstablishment(Establishments $establishment): self
    {
        if (!$this->establishments->contains($establishment)) {
            $this->establishments->add($establishment);
            $establishment->setFKUser($this);
        }

        return $this;
    }

    public function removeEstablishment(Establishments $establishment): self
    {
        if ($this->establishments->removeElement($establishment)) {
            // set the owning side to null (unless already changed)
            if ($establishment->getFKUser() === $this) {
                $establishment->setFKUser(null);
            }
        }

        return $this;
    }

}
