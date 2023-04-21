<?php

namespace App\Controller;

use App\Entity\Users;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UsersController extends AbstractController
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
        $this->serializer = new Serializer($this->normalizers, $this->encoders);
    }


    public function login()
    {

    }

    #[Route('api/users/user', name:"users_create-user", methods:['POST'])]
    public function createUser(Request $request, EntityManagerInterface $em, ValidatorInterface $validator,) : JsonResponse
    {
        $userInfoJson = $request->getContent();
        $user = $this->serializer->deserialize($userInfoJson, Users::class,'json');

        $errors = $validator->validate($user);
        if($errors->count() > 0)
        {
            $errors = $this->serializer->serialize($errors, 'json');
            return new JsonResponse([
                Response::HTTP_BAD_REQUEST,
                [],
                true,
                'errors' => "$errors"
            ]);
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