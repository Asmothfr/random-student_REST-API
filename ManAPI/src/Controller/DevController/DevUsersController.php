<?php
namespace App\Controller\DevController;

use Faker\Factory;
use App\Entity\Users;
use App\Repository\UsersRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\EstablishmentsRepository;
use App\Service\CacheService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use JMS\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Annotations as OA;

class DevUsersController extends AbstractController
{
    private UserPasswordHasherInterface $usersPasswordHasher;
    private SerializerInterface $serializer;
    private CacheService $cache;

    public function __construct(UserPasswordHasherInterface $usersPasswordHasher, SerializerInterface $serializerInterface, CacheService $cacheClass)
    {
        $this->usersPasswordHasher = $usersPasswordHasher;
        $this->serializer = $serializerInterface;
        $this->cache = $cacheClass;
    }

    /**
     * @OA\Response(
     *      response=200,
     *      description = "Get all users in database. Get one user if the id is given.",
     *      @OA\JsonContent(
     *          type="array",
     *      @OA\Items(ref=@Model(type=Users::class))
     *      )
     * )
     * @OA\Tag(name="Dev-Users")
     * @param int $id
     * @param UsersRepository $usersRespository
     * @return JsonResponse
     */
    #[Route('/api/dev/users/{id<\d+>?null}', name: 'dev_users_get', methods: ['GET'])]
    public function getUsers(Request $request, int $id, UsersRepository $usersRepository): JsonResponse
    {
        if($id == "null" || $id == null)
        {
            $jsonContent = $this->cache->getCache('allUsers', $usersRepository, 'findAll');
        }
        else
        {
            $jsonContent = $this->cache->getCache("oneUser"."$id", $usersRepository, "find", $id);
        }

        return new JsonResponse($jsonContent,Response::HTTP_OK, [], true);
    }
    
    /**
     * @OA\Response(
     *      response=201,
     *      description = "Create x numbers of users in database.",
     *      @OA\JsonContent(
     *          type="array",
     *      @OA\Items(ref=@Model(type=Users::class))
     *      )
     * )
     * @OA\Tag(name="Dev-Users")
     * @param int $number
     * @param EntityManagerInterface $manager
     * @return JsonResponse
     */
    #[Route('api/dev/users/{number<\d+>?10}', name: 'dev_users_create', methods: ['POST'])]
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

        return new JsonResponse(null, Response::HTTP_CREATED, [], false);
    }

    /**
     * @OA\Response(
     *      response=204,
     *      description = "Edit one user in database.",
     *      @OA\JsonContent(
     *          type="array",
     *      @OA\Items(ref=@Model(type=Users::class))
     *      )
     * )
     * @OA\Tag(name="Dev-Users")
     * @param int $id
     * @param UsersRepository $usersRepository
     * @param EntityManagerInterface $manager
     * @return JsonResponse
     */
    #[Route('api/dev/users/{id<\d+>}', name:'dev_users_edit-one', methods:['PUT'])]
    public function editUser(Request $request, int $id, UsersRepository $usersRepository, EntityManagerInterface $manager): JsonResponse
    {
        $currentUser = $usersRepository->find($id);
        $jsonData = $request->getContent();

        $newUser = $this->serializer->deserialize($jsonData, Users::class, 'json');

        $updatedUser = $currentUser->setEmail($newUser->getTitle())
                                    ->setName($newUser->getName())
                                    ->setPassword($newUser->getPassword())
                                    ->setRoles($newUser->getRoles());

        $manager->persist($updatedUser);
        $manager->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT, [], false);
    }

    /**
     * @OA\Response(
     *      response=204,
     *      description = "Delete all users in database.",
     *      @OA\JsonContent(
     *          type="array",
     *      @OA\Items(ref=@Model(type=Users::class))
     *      )
     * )
     * @OA\Tag(name="Dev-Users")
     * @param UsersRepository $usersRepository
     * @param EstablishmentsRepository $establishmentsRepository
     * @param EntityManagerInterface $entityManagerInterface
     * @return JsonResponse
     */
    #[Route('api/dev/users', name:'dev-users-delete', methods:['DELETE'])]
    public function deleteAllUsers(UsersRepository $usersRepository, EstablishmentsRepository $establishmentsRepository, EntityManagerInterface $entityManagerInterface): JsonResponse
    {
        $users = $usersRepository->findAll();
        $establishments = $establishmentsRepository->findAll();

        foreach ($establishments as $establishment)
        {
            $establishmentsRepository->remove($establishment);
        }

        foreach ($users as $user)
        {
            $usersRepository->remove($user);
        }
        $entityManagerInterface->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT, [], false);
    }

    #[Route('api/dev/users/{id<\d+>}', name:'dev-users-delete-one', methods:['DELETE'])]
    public function deleteUser(Request $request, string $id, UsersRepository $usersRepository, EntityManagerInterface $entityManagerInterface): JsonResponse
    {
        $user = $usersRepository->find($id);

        $usersRepository->remove($user);
        $entityManagerInterface->flush();
        return new JsonResponse(null, Response::HTTP_NO_CONTENT, [], false);
    }
}
