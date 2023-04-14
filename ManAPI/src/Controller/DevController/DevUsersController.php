<?php
namespace App\Controller\DevController;

use Faker\Factory;
use App\Entity\Users;
use App\Repository\UsersRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\Entity;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class DevUsersController extends AbstractController
{
    private $usersPasswordHasher;

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
            Response::HTTP_OK, [], true,
            $JsonContent
        ]);
    }

    #[Route('api/dev/users/create/{number<\d+>?10}', name: 'dev_users_create', methods: ['POST'])]
    public function createUsers(Request $request, int $number, EntityManagerInterface $manager): JsonResponse
    {
        $faker = Factory::create('fr_FR');

        for ($i = 0; $i < $number; $i++) {

            $users = new Users;
            $users->setName($faker->userName());
            $users->setEmail($faker->email());
            $users->setPassword($this->usersPasswordHasher->hashPassword($users, "password"));

            $manager->persist($users);
        }

        $manager->flush();

        return $this->json([
            Response::HTTP_OK, [], true,
            'content' => "$number users was created."
        ]);
    }

    #[Route('api/dev/users/user/{id<\d+>}/edit/{username<[a-zA-Z0-9]{8,32}>}', name:'dev_user_update-one-user-name', methods:['PUT'])]
    public function editUers(Request $request, string $id , string $username, UsersRepository $usersRepository, EntityManagerInterface $manager): JsonResponse
    {
        $user = $usersRepository->find($id);
        if($user)
        {
            $oldUsername = $user->getName();
            $user->setName($username);
            $manager->persist($user);
            $manager->flush();
            return $this->json([
                Response::HTTP_OK, [], true,
                'content' => "Username $oldUsername was changed with $username."
            ]);
        }
        else
        {
            return $this->json([
                Response::HTTP_NOT_FOUND,
                'content' => 'Utilisateur introuvable'
            ]);
        }
    }

    #[Route('api/dev/users/delete', name:'delete_all_users', methods:['DELETE'])]
    public function deleteAllUsers(UsersRepository $usersRepository, EntityManagerInterface $entityManagerInterface): JsonResponse
    {
        $users = $usersRepository->findAll();

        foreach ($users as $user)
        {
            $usersRepository->remove($user);
        }
        $entityManagerInterface->flush();

        return $this->json([
            Response::HTTP_OK,
            'content' => 'All Users was deleted.'
        ]);
    }
}
