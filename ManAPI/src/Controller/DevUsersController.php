<?php

namespace App\Controller;

use Faker\Factory;
use App\Entity\Users;
use App\Repository\UsersRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class DevUsersController extends AbstractController
{
    private $usersPasswordHasher;
    private $users;

    public function __construct(UserPasswordHasherInterface $usersPasswordHasher)
    {
        $this->usersPasswordHasher = $usersPasswordHasher;
    }

    #[Route('/api/dev/users', name: 'dev_users', methods: ['GET'])]
    public function getUsers(UsersRepository $usersRepository): JsonResponse
    {

        $encoders = [new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];
        
        $serializer = new Serializer($normalizers,$encoders);

        $users = $usersRepository->findAll();


        $JsonContent = $serializer->serialize($users, 'json');
        return $this->json([
            $JsonContent
        ]);
    }

    #[Route('api/dev/users/create/{number<\d+>?10}', name: 'dev_users_create', methods: ['GET'])]
    public function createUsers(Request $request, $number, EntityManagerInterface $manager): JsonResponse
    {
        $faker = Factory::create('fr_FR');

        for ($i = 0; $i < $number; $i++) {

            $users = new Users;
            $users->setName($faker->userName());
            $users->setEmail($faker->email());
            $users->setPassword($this->usersPasswordHasher->hashPassword($users, "mescouillessurtonfront"));

            $manager->persist($users);
        }

        $manager->flush();

        return $this->json([
            'message' => "$number users was create."
        ]);
    }
}
