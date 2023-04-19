<?php
namespace App\Controller\DevController;

use Faker\Factory;
use App\Entity\Users;
use App\Repository\UsersRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

class DevUsersController extends AbstractController
{
    private UserPasswordHasherInterface $usersPasswordHasher;
    private Serializer $serializer;
    private array $encoders;
    private array $normalizers;

    public function __construct(UserPasswordHasherInterface $usersPasswordHasher)
    {
        $this->usersPasswordHasher = $usersPasswordHasher;
        $this->encoders[] = new JsonEncoder();
        $this->normalizers[] = new ObjectNormalizer();
        $this->serializer = new Serializer($this->normalizers,$this->encoders);
    }

    #[Route('/api/dev/users', name: 'dev_users', methods: ['GET'])]
    public function getUsers(UsersRepository $usersRepository): JsonResponse
    {
        $users = $usersRepository->findAll();
        
        $JsonContent = $this->serializer->serialize($users, 'json');
        return $this->json([
            Response::HTTP_OK, [], true,
            $JsonContent
        ]);
    }

    #[Route('api/dev/users/create/{number<\d+>?10}', name: 'dev_users_create', methods: ['POST'])]
    public function createUsers(Request $request, int $number, EntityManagerInterface $manager): JsonResponse
    {
        $faker = Factory::create('fr_FR');
        $hasher = $this->usersPasswordHasher;
        for ($i = 0; $i < $number; $i++)
        {   
            $user = new Users;
            $user->setName($faker->userName());
            $user->setEmail($faker->email());
            $user->setPassword($hasher->hashPassword($user, "password"));
            
            $manager->persist($user);
        };
        
        $manager->flush();

        return $this->json([
            Response::HTTP_OK, [], true,
            'content' => "$number users was created."
        ]);
    }

    #[Route('api/dev/users/user/{id<\d+>}', name:'edit-user', methods:['PUT'])]
    public function editUser(Request $request, int $id, UsersRepository $usersRepository, EntityManagerInterface $manager): JsonResponse
    {
        $currentUser = $usersRepository->find($id);
        if($currentUser)
        {
            $jsonData = $request->getContent();
            $updatedUser = $this->serializer->deserialize($jsonData, Users::class, 'json',
                            [AbstractNormalizer::OBJECT_TO_POPULATE=>$currentUser]);

            $manager->persist($updatedUser);
            $manager->flush();
            return $this->json([
                Response::HTTP_NO_CONTENT,
                null
            ]);
        }
        else
        {
            return $this->json([
                Response::HTTP_NOT_FOUND,
                'content' => 'User not found.'
            ]);
        }
    }

    #[Route('api/dev/users/user/{id<\d+>}/delete', name:'dev-users-delete-one-user', methods:['DELETE'])]
    public function deleteUser(Request $request, string $id, UsersRepository $usersRepository, EntityManagerInterface $entityManagerInterface): JsonResponse
    {
        $user = $usersRepository->find($id);

        if ($user)
        {
            $usersRepository->remove($user);
            $entityManagerInterface->flush();

            return$this->json([
                Response::HTTP_OK,
                'content' => "User $id and all is data was deleted"
            ]);
        }
        return $this->json([
            Response::HTTP_NOT_FOUND,
            'content' => 'User not found'
        ]);
    }
    #[Route('api/dev/users/delete', name:'dev-users-delete-all-users', methods:['DELETE'])]
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
