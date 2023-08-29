<?php

namespace App\DataFixtures;

use Faker\Factory;
use App\Entity\Users;
use App\Entity\Classrooms;
use App\Entity\Establishments;
use App\Entity\Students;
use App\Repository\UsersRepository;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\FixturesBundle\Fixture;
use App\Repository\EstablishmentsRepository;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $userPasswordHasherInterface)
    {
        $this->passwordHasher = $userPasswordHasherInterface;
    }


    public function load(ObjectManager $manager): void
    {
        $this->loadUsers($manager);
        $this->loadEstablishments($manager);
        $this->loadClassrooms($manager);
        $this->loadStudents($manager);

        $manager->flush();
    }

    public function loadUsers(ObjectManager $manager): void
    {
        $faker = Factory::create('fr-FR');

        for ($i=0; $i < 10; $i++) { 
            $user = new Users;
            $user->setName($faker->name())
                ->setEmail($faker->email())
                ->setRoles(['USER'])
                ->setPassword($this->passwordHasher->hashPassword($user,'passworddevtest0'));
            $manager->persist($user);
        }
    }

    /**
     * ! A Modifier !
     * Créer x nb d'établissement pour chaque utilisateurs.
     */
    public function loadEstablishments(ObjectManager $manager): void
    {
        $faker = Factory::create('fr-FR');

        $users = $this->getAllUsers();

        foreach ($users as $user) {
            for ($i=0; $i < 5; $i++) { 
                $establishment= new Establishments;
                $establishment->setFKUser($user)
                    ->setName($faker->company());

                $manager->persist($establishment);
            }
        }
    }

    public function loadClassrooms(ObjectManager $manager): void
    {
        $faker = Factory::create('fr-FR');

        $users = $this->getAllUsers();

        foreach ($users as $user) {
            $establishments = $user->getEstablishments();
            foreach ($establishments as $establishment) {
                for ($i=0; $i < 5; $i++) { 
                    $classroom = new Classrooms;
                    $classroom->setFKUser($user)
                        ->setFKEstablishmentId($establishment)
                        ->setName($faker->company());

                    $manager->persist($classroom);
                }
            }
        }
    }

    public function loadStudents(ObjectManager $manager)
    {
        $faker = Factory::create('fr-FR');

        $users = $this->getAllUsers();

        foreach ($users as  $user) {
            $classrooms = $user->getClassrooms();
            foreach ($classrooms as $classroom) {
                for ($i=0; $i < 20; $i++) { 
                    $student = new Students;
                    $student->setFKUser($user)
                        ->setFKClassroomId($classroom)
                        ->setLastname($faker->lastName())
                        ->setFirstname($faker->firstName())
                        ->setScore(0);
                    $manager->persist($student);
                }
            }
        }
    }

    public function getAllUsers(): array
    {
        $managerRegistry = new ManagerRegistry();
        $usersRepository = new UsersRepository($managerRegistry);
        return $users = $usersRepository->findAll();
    }
}
