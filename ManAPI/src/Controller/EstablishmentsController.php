<?php

namespace App\Controller;

use App\Entity\Users;
use App\Service\CacheService;
use App\Entity\Establishments;
use App\Repository\EstablishmentsRepository;
use App\Repository\UsersRepository;
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
use Symfony\Component\Validator\Constraints\Json;

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

    /**
     * @OA\Response(
     *      response=200,
     *      description="Returns all establishments of a user or only one if the id is given",
     *      @OA\JsonContent(
     *          type="array",
     *          @OA\Items(ref=@Model(type=Establishments::class))
     *      )
     * )
     * 
     * @OA\Tag(name="Establishments")
     * 
     * @param Request $request
     * @param strign $id
     * @param EstablishmentsRepository $establishmentsRepository
     * @return JsonResponse
     */
    #[Route('api/establishments/{id<\d+>?null}', name: 'get_establisments', methods:['GET'])]
    public function getEstablishments(Request $request, string $id, EstablishmentsRepository $establishmentsRepository): JsonResponse
    {
        $token = $request->server->get('HTTP_AUTHORIZATION');
        $jsonUser = $this->_cache->getUserCache($token);
        $user = $this->_serializer->deserialize($jsonUser, Users::class, 'json');
        $userId = $this->_accessor->getValue($user, 'id');

        if($id == null || $id == 'null')
        {
            $jsonEstablishments = $this->_cache->getCache('establishments'.$token, $establishmentsRepository, 'findBy', ['FK_user'=>$userId]);
            return new JsonResponse($jsonEstablishments, Response::HTTP_OK,[], true);
        }
        
        $jsonEstablishments = $this->_cache->getCache('establishments'.$id.$token,$establishmentsRepository, 'findBy', ['FK_user' => $userId, 'id'=>$id]);
        return new JsonResponse($jsonEstablishments, Response::HTTP_OK,[], true);
    }

    /**
     * @OA\Response(
     *      description="Create a new establishment for the user",
     *      response=201,
     *      @OA\JsonContent(
     *          type="array",
     *          @OA\Items(ref=@Model(type=Users::class))
     *      )
     * )
     * 
     * @OA\Tag(name="Establishments")
     * 
     * @param Request $request
     * @param EntityManagerInterface $em
     * @return JsonResponse
     */
    #[Route('api/establishments', name: 'create_establishment', methods:['POST'])]
    public function createEstablishment(Request $request, EntityManagerInterface $em): JsonResponse
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

     /**
     * @OA\Response(
     *      description="Edit an establishment",
     *      response=202,
     *      @OA\JsonContent(
     *          type="array",
     *          @OA\Items(ref=@Model(type=Users::class))
     *      )
     * )
     * 
     * @OA\Tag(name="Establishments")
     * 
     * @param Request $request
     * @param string $id
     * @param EstablishmentsRepository $establishments
     * @param EntityManagerInterface $em
     * @return JsonResponse
     */
    #[Route('api/establishments/{id<\d+>}', name:'edit_establishments', methods:['PUT'])]
    public function editEstablishment(Request $request, string $id, EstablishmentsRepository $establishments, EntityManagerInterface $em): JsonResponse
    {
        $jsonData = $request->getContent();
        if(!$jsonData)
        {
            return new JsonResponse(null, Response::HTTP_BAD_REQUEST, [], false);
        }
        
        $newEstablishment = $this->_serializer->deserialize($jsonData, Establishments::class, 'json');
        
        $toValidate = $this->_validator->validator($newEstablishment);
        if($toValidate !== true)
        {
            return new JsonResponse($toValidate, Response::HTTP_BAD_REQUEST, [], true);
        }

        $token = $request->server->get('HTTP_AUTHORIZATION');
        $userJson = $this->_cache->getUserCache($token);
        $user = $this->_serializer->deserialize($userJson, Users::class, 'json');
        $userId = $user->getId();

        $currentEstablishment = $establishments->find($id);
        $establishmentsUserFk = $currentEstablishment->getFKUser()->getId();

        if($userId != $establishmentsUserFk)
        {
            return new JsonResponse(null, Response::HTTP_FORBIDDEN, [], false);
        }

        $currentEstablishment->setName($newEstablishment->getName());
        $em->persist($currentEstablishment);
        $em->flush();

        $this->_cache->clearCacheItem('establishments',$id.$token);

        return new JsonResponse(null, Response::HTTP_NO_CONTENT, [], false);
    }

    public function deleteEstablishment(Request $request, string $id, EntityManagerInterface $em): JsonResponse
    {

        return new JsonResponse(null, Response::HTTP_ACCEPTED, [], false);
    }
}