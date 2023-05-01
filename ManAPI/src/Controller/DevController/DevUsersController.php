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
    private UserPasswordHasherInterface $_usersPasswordHasher;
    private SerializerInterface $_serializer;
    private CacheService $_cache;

    public function __construct(UserPasswordHasherInterface $usersPasswordHasher, SerializerInterface $serializerInterface, CacheService $cacheClass)
    {
        $this->_usersPasswordHasher = $usersPasswordHasher;
        $this->_serializer = $serializerInterface;
        $this->_cache = $cacheClass;
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
     * @param Request $request
     * @param string $id
     * @param UsersRepository $usersRespository
     * @return JsonResponse
     */
    #[Route('/api/dev/users/{id<\d+>?null}', name: 'dev_users_get', methods: ['GET'])]
    public function getUsers(Request $request, string $id, UsersRepository $usersRepository): JsonResponse
    {        
        $token = $request->server->get('HTTP_AUTHORIZATION');
        $trimed = trim($token, "bearer");
        dump($trimed);
        $tokenDecode = base64_decode($trimed);
        dump($tokenDecode);
        $tokenDeserialize = json_decode($tokenDecode);
        dd($tokenDeserialize);

        if($id == "null" || $id == null)
        {
            $jsonContent = $this->_cache->getCache('allUsers', $usersRepository, 'findAll');
        }
        else
        {
            $jsonContent = $this->_cache->getCache("oneUser"."$id", $usersRepository, "find", $id);
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
     * @param Request $request
     * @param string $number
     * @param EntityManagerInterface $manager
     * @return JsonResponse
     */
    #[Route('api/dev/users/{number<\d+>?10}', name: 'dev_users_create', methods: ['POST'])]
    public function createUsers(Request $request, string $number, EntityManagerInterface $manager): JsonResponse
    {
        $faker = Factory::create('fr_FR');
        $hasher = $this->_usersPasswordHasher;
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
     * @param Request $request
     * @param string $id
     * @param UsersRepository $usersRepository
     * @param EntityManagerInterface $manager
     * @return JsonResponse
     */
    #[Route('api/dev/users/{id<\d+>}', name:'dev_users_edit-one', methods:['PUT'])]
    public function editUser(Request $request, string $id, UsersRepository $usersRepository, EntityManagerInterface $manager): JsonResponse
    {
        $currentUser = $usersRepository->find($id);
        $jsonData = $request->getContent();

        $newUser = $this->_serializer->deserialize($jsonData, Users::class, 'json');

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
     *      description = "Delete all users in database. Delete one user if the id is given.",
     *      @OA\JsonContent(
     *          type="array",
     *      @OA\Items(ref=@Model(type=Users::class))
     *      )
     * )
     * @OA\Tag(name="Dev-Users")
     * @param Request $request
     * @param string $id
     * @param UsersRepository $usersRepository
     * @param EstablishmentsRepository $establishmentsRepository
     * @param EntityManagerInterface $entityManagerInterface
     * @return JsonResponse
     */
    #[Route('api/dev/users/{id<\d+>?null}', name:'dev-users-delete', methods:['DELETE'])]
    public function deleteAllUsers(Request $request, string $id, UsersRepository $usersRepository, EstablishmentsRepository $establishmentsRepository, EntityManagerInterface $entityManagerInterface): JsonResponse
    {
        if ($id == null || $id = "null")
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
        }
        else
        {
            $user = $usersRepository->find($id);
            $establishments = $establishmentsRepository->findBy(['FK_user'=>$id]);

            foreach ($establishments as $establishment)
            {
                $establishmentsRepository->remove($establishment);
            }

            $usersRepository->remove($user);
        }
        $entityManagerInterface->flush();
        $this->_cache->clearAllCache();
        return new JsonResponse(null, Response::HTTP_NO_CONTENT, [], false);
    }
}
