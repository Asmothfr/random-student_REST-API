<?php

namespace App\Controller;

use App\Entity\Users;
use App\Entity\Classrooms;
use App\Service\CacheService;
use App\Service\ValidatorService;
use JMS\Serializer\SerializerInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\EstablishmentsRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('api/classrooms')]
class ClassroomsController extends AbstractController
{
    private SerializerInterface $_serializer;
    private CacheService $_cache;
    private ValidatorService $_validator;

    public function __construct(SerializerInterface $serializerInterface, CacheService $cacheService, ValidatorService $validatorService )
    {
        $this->_serializer = $serializerInterface;
        $this->_cache = $cacheService;
        $this->_validator = $validatorService;
    }

    #[Route('/{id<\d+>}', name:'add_classroom', methods:['POST'])]
    public function addClassroom(Request $request, string $id, EstablishmentsRepository $establishmentsRepository, EntityManagerInterface $em)
    {        
        $token = $request->server->get('HTTP_AUTHORIZATION');
        $userJson = $this->_cache->getUserCache($token);
        $user = $this->_serializer->deserialize($userJson, Users::class, 'json');
        $userId = $user->getId();

        $establishment = $establishmentsRepository->findOneBy(['FK_user'=>$userId, 'id'=>$id]);
        if(!$establishment)
            return new JsonResponse(null, Response::HTTP_BAD_REQUEST, [], false);

        $classroom = $this->_serializer->deserialize($request->getContent(), Classrooms::class, 'json');

        $toValidate = $this->_validator->validator($classroom);
        if(!$toValidate)
            return new JsonResponse($toValidate, Response::HTTP_BAD_REQUEST, [], true);

        $classroom->setFKUser($user)
                    ->setFKEstablishmentId($establishment);

        $em->persist($classroom);
        $em->flush();

        return new JsonResponse(null, Response::HTTP_CREATED, [], false);
    }
}