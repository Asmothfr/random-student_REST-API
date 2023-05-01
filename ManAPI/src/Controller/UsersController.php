<?php

namespace App\Controller;

use App\Entity\Users;
use App\Repository\UsersRepository;
use App\Service\CacheService;
use JMS\Serializer\SerializerInterface;
use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\SerializationContext;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UsersController extends AbstractController
{
    private UserPasswordHasherInterface $_passwordHasher;
    private SerializerInterface $_serializer;
    private CacheService $_cache;

    public function __construct(UserPasswordHasherInterface $usersPasswordHasher, SerializerInterface $serializerInterface, CacheService $cacheService)
    {

        $this->_passwordHasher = $usersPasswordHasher;
        $this->_serializer = $serializerInterface;
        $this->_cache = $cacheService;
    }


    #[Route('api/users/{id<\d+>}', name:'users_get-current-user', methods:['GET'])]
    public function getUserIdentity(Request $request, string $id, UsersRepository $usersRepository): JsonResponse
    {
        $jsonContent = $this->_cache->getCache("user"."$id", $usersRepository, "find", $id);
        if($jsonContent)
        {
            return new JsonResponse($jsonContent, Response::HTTP_OK, [], true,);
        }
        return new JsonResponse([Response::HTTP_NOT_FOUND, [], false,]);
    }

    #[Route('api/users', name:"users_create-user", methods:['POST'])]
    public function createUser(Request $request, EntityManagerInterface $em, ValidatorInterface $validator) : JsonResponse
    {
        $userInfoJson = $request->getContent();
        $user = $this->_serializer->deserialize($userInfoJson, Users::class,'json');

        $errors = $validator->validate($user);
        if($errors->count() > 0)
        {
            $errors = $this->_serializer->serialize($errors, 'json');
            return new JsonResponse(
                $errors,
                Response::HTTP_BAD_REQUEST,
                [],
                false
            );
        }
        
        $password = $user->getPassword();
        $hash = $this->_passwordHasher->hashPassword($user,$password);
        $user->setPassword($hash);

        $em->persist($user);
        $em->flush();

        return new JsonResponse([Response::HTTP_CREATED, [], true]);
    }
}