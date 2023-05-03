<?php

namespace App\Controller;

use App\Entity\Users;
use App\Service\CacheService;
use App\Entity\Establishments;
use App\Service\ValidatorService;
use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use OpenApi\Annotations as OA;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;

class EstablishmentsController extends AbstractController
{
    private CacheService $_cache;
    private ValidatorService $_validator;
    private SerializerInterface $_serializer;
    private PropertyAccessor $_accessor;

    public function __construct(CacheService $cacheService, ValidatorService $validatorService, SerializerInterface $serializerInterface)
    {
        $this->_cache = $cacheService;
        $this->_validator = $validatorService;
        $this->_serializer = $serializerInterface;
        $this->_accessor = PropertyAccess::createPropertyAccessor();
    }


    #[Route('api/establishments', name: 'create_establishment', methods:['POST'])]
    public function createEstablishments(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $establishment = $this->_serializer->deserialize($request->getContent(), Establishments::class, 'json');
        
        $isValidate = $this->_validator->validator($establishment);
        if(!$isValidate)
        {
            return new JsonResponse(null, Response::HTTP_BAD_REQUEST, [], false);
        }

        $token = $request->server->get('HTTP_AUTHORIZATION');
        $userJson = $this->_cache->getUserCache($token);
        $user = $this->_serializer->deserialize($userJson, Users::class, 'json');

        $establishment->setFKUser($user);
        
        $em->persist($establishment);
        $em->flush();

        return new JsonResponse(null, Response::HTTP_CREATED,[],false);
    }
}