<?php

namespace App\Controller;

use App\Entity\Users;
use App\Repository\UsersRepository;
use App\Service\MasterService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use OpenApi\Annotations as OA;
 
#[Route('/api/users')]
class UsersController extends MasterService
{
    private UserPasswordHasherInterface $_passwordHasher;

    public function __construct(UserPasswordHasherInterface $usersPasswordHasher)
    {
        $this->_passwordHasher = $usersPasswordHasher;
    }

    /**
     * @OA\Response(
     *      response=200,
     *      description="Return the current user information",
     *      @OA\JsonContent(
     *          type="array",
     *          @OA\Items(ref=@Model(type=Users::class))
     *      )
     * )
     * 
     * @OA\Tag(name="User")
     * 
     * @param Request $request
     * @return JsonResponse
     */
    #[Route(name: 'get_user', methods: ['GET'])]
    public function getCurrentUser(Request $request): JsonResponse
    {
        $token = $request->headers->get('authorization');
        $jsonContent = $this->_cache->getUserCache($token);
        
        if($jsonContent)
        {
            return new JsonResponse($jsonContent, Response::HTTP_OK, [], true,);
        }
        else
        {
            return new JsonResponse(null, Response::HTTP_NOT_FOUND, [], false);
        }
        
    }

    /**
     * @OA\Response(
     *      description="Create a new user",
     *      response=201,
     *      @OA\JsonContent(
     *          type="array",
     *          @OA\Items(ref=@Model(type=Users::class))
     *      )
     * )
     * 
     * @OA\Tag(name="User")
     * 
     * @param Request $request
     * @param EntityManagerInterface $em
     * @return JsonResponse
     */
    #[Route(name:"create_user", methods:['POST'])]
    public function createUser(Request $request, EntityManagerInterface $em) : JsonResponse
    {
        $userInfoJson = $request->getContent();
        $user = $this->_serializer->deserialize($userInfoJson, Users::class,'json');
        
        $isValidate = $this->_validator->validator($user);
        if($isValidate !== true)
            return new JsonResponse(null, Response::HTTP_BAD_REQUEST, [], false);
        
        $password = $user->getPassword();
        $hash = $this->_passwordHasher->hashPassword($user,$password);
        $user->setPassword($hash);

        $em->persist($user);
        $em->flush();

        return new JsonResponse(null, Response::HTTP_CREATED, [], false);
    }

    /**
     * @OA\Response(
     *      response=204,
     *      description="Edit the current user",
     *      @OA\JsonContent(
     *          type="array",
     *          @OA\Items(ref=@Model(type=Users::class))
     *      )
     * )
     * 
     * @OA\Tag(name="User")
     * 
     * @param Request $request
     * @param EntityManagerInterface $em
     * @return JsonResponse
     */
    #[Route( name:'edit_user', methods:['PUT'])]
    public function editUser(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $jsonData = $request->getContent();
        if(!$jsonData)
            return new JsonResponse(null, Response::HTTP_BAD_REQUEST, [], false);

        $newUser = $this->_serializer->deserialize($jsonData, Users::class, 'json');

        $toValidate = $this->_validator->validator($newUser);
        if($toValidate !== true)
            return new JsonResponse($toValidate, Response::HTTP_BAD_REQUEST, [], true);

        $token = $request->headers->get('authorization');
        $currentUser = $this->getUser();


        if($currentUser instanceof Users)
        {
            $password = $newUser->getPassword();
            $hash = $this->_passwordHasher->hashPassword($newUser,$password);

            $currentUser->setEmail($newUser->getEmail())
                        ->setName($newUser->getName())
                        ->setPassword($hash)
                        ->setRoles($newUser->getRoles());
        }

        $em->persist($currentUser);
        $em->flush();

        $this->_cache->clearUserCache($token);

        return new JsonResponse(null, Response::HTTP_NO_CONTENT, [], false);
    }

    /**
     * @OA\Response(
     *      response=204,
     *      description="Delete the current user",
     *      @OA\JsonContent(
     *          type="array",
     *          @OA\Items(ref=@Model(type=Users::class))
     *      )
     * )
     * 
     * @OA\Tag(name="User")
     * 
     * @param Request $request
     * @param UsersRepository $userRepository
     * @param EntityManagerInterface $em
     * @return JsonResponse
     */
    #[Route(name: 'delete_user', methods:['DELETE'])]
    public function deleteUser(Request $request, UsersRepository $usersRepository, EntityManagerInterface $em): JsonResponse
    {
        $token = $request->headers->get('authorization');
        $user = $this->getUser();

        if($user instanceof Users)
        {
            $usersRepository->remove($user);
            $em->flush();
            $this->_cache->clearUserCache($token);
        }
        return new JsonResponse(null, Response::HTTP_NO_CONTENT, [], false);
    }
}