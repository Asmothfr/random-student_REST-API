<?php

namespace App\Controller;

use App\Entity\Users;
use App\Repository\UsersRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Serializer\SerializerInterface;

class UsersController extends AbstractController
{
    private UserPasswordHasherInterface $usersPasswordHasher;
    private SerializerInterface $serializer;

    public function __construct(UserPasswordHasherInterface $usersPasswordHasher, SerializerInterface $serializerInterface)
    {

        $this->usersPasswordHasher = $usersPasswordHasher;
        $this->serializer = $serializerInterface;
    }


    #[Route('api/users/{id<\d+>}', name:'users_get-current-user', methods:['GET'])]
    public function getUserIdentity(Request $request, string $id, UsersRepository $usersRepository): JsonResponse
    {
        $currentUser = $usersRepository->find($id);
        if($currentUser)
        {
            $userJsonFormat = $this->serializer->serialize($currentUser, 'json', ['groups'=>'user_identity']);

            return new JsonResponse($userJsonFormat, Response::HTTP_OK, [], true,);
        }
        return new JsonResponse([
            Response::HTTP_NOT_FOUND,
            [],
            false,
        ]);
    }

    #[Route('api/users/', name:"users_create-user", methods:['POST'])]
    public function createUser(Request $request, EntityManagerInterface $em, ValidatorInterface $validator) : JsonResponse
    {
        $userInfoJson = $request->getContent();
        $user = $this->serializer->deserialize($userInfoJson, Users::class,'json');

        $errors = $validator->validate($user);
        if($errors->count() > 0)
        {
            $errors = $this->serializer->serialize($errors, 'json');
            return new JsonResponse(
                $errors,
                Response::HTTP_BAD_REQUEST,
                [],
                false
            );
        }
        
        $password = $user->getPassword();
        $hash = $this->usersPasswordHasher->hashPassword($user,$password);
        $user->setPassword($hash);

        $em->persist($user);
        $em->flush();

        return new JsonResponse([
            Response::HTTP_CREATED,
            [],
            true,
            'content' => 'New User was create'
        ]);
    }
}