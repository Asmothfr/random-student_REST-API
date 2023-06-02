<?php

namespace App\Controller;

use App\Entity\Classrooms;
use App\Entity\Users;
use App\Service\UserService;
use App\Service\CacheService;
use App\Entity\Establishments;
use App\Repository\ClassroomsRepository;
use OpenApi\Annotations as OA;
use App\Service\ValidatorService;
use App\Repository\UsersRepository;
use JMS\Serializer\SerializerInterface;
use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\SerializationContext;
use Nelmio\ApiDocBundle\Annotation\Model;
use App\Repository\EstablishmentsRepository;
use Doctrine\ORM\EntityManager;
use Nelmio\ApiDocBundle\Annotation\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\Json;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('api/establishments')]
class EstablishmentsController extends AbstractController
{
    private CacheService $_cache;
    private ValidatorService $_validator;
    private SerializerInterface $_serializer;
    private PropertyAccessor $_accessor;
    private UserService $_userService;

    public function __construct(CacheService $cacheService, ValidatorService $validatorService, SerializerInterface $serializerInterface, UserService $_userService)
    {
        $this->_cache = $cacheService;
        $this->_validator = $validatorService;
        $this->_serializer = $serializerInterface;
        $this->_accessor = PropertyAccess::createPropertyAccessor();
        $this->_userService = $_userService;
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
     * @param string $id
     * @param EstablishmentsRepository $establishmentsRepository
     * @return JsonResponse
     */
    #[Route('/{id<\d+>?null}', name: 'get_establisments', methods:['GET'])]
    public function getEstablishments(Request $request, string $id, EstablishmentsRepository $establishmentsRepository): JsonResponse
    {
        $token = $request->server->get('HTTP_AUTHORIZATION');
        $userId = $this->_userService->getUserId($token);

        $context = SerializationContext::create()->setGroups('establishments_info');
        if($id == null || $id == 'null')
        {
            $jsonEstablishments = $this->_cache->getCache('establishments'.$token, $establishmentsRepository, 'findBy', ['FK_user'=>$userId], $context);
            return new JsonResponse($jsonEstablishments, Response::HTTP_OK,[], true);
        }
        
        $jsonEstablishments = $this->_cache->getCache('establishments'.$token.$id,$establishmentsRepository, 'findOneBy', ['FK_user' => $userId, 'id'=>$id], $context);

        if(!$jsonEstablishments)
            return new JsonResponse(null, Response::HTTP_NOT_FOUND, [], false);

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
    #[Route(name: 'create_establishment', methods:['POST'])]
    public function createEstablishment(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $establishment = $this->_serializer->deserialize($request->getContent(), Establishments::class, 'json');
        
        $isValidate = $this->_validator->validator($establishment);
        if(!$isValidate)
            return new JsonResponse(null, Response::HTTP_BAD_REQUEST, [], false);

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
     *      description="Edit an establishment by is id",
     *      response=204,
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
    #[Route('/{id<\d+>}', name:'edit_establishments', methods:['PUT'])]
    public function editEstablishment(Request $request, string $id, EstablishmentsRepository $establishments, EntityManagerInterface $em): JsonResponse
    {
        $jsonData = $request->getContent();
        if(!$jsonData)
            return new JsonResponse(null, Response::HTTP_BAD_REQUEST, [], false);

        $newEstablishment = $this->_serializer->deserialize($jsonData, Establishments::class, 'json');
        
        $toValidate = $this->_validator->validator($newEstablishment);
        if($toValidate !== true)
            return new JsonResponse($toValidate, Response::HTTP_BAD_REQUEST, [], true);

        $token = $request->server->get('HTTP_AUTHORIZATION');
        $userId = $this->_userService->getUserId($token);

        
        $currentEstablishment = $establishments->findOneBy(['FK_user'=>$userId, 'id'=>$id]);
        
        if(!$currentEstablishment)
            return new JsonResponse(null, Response::HTTP_NOT_FOUND, [], false);

        $currentEstablishment->setName($newEstablishment->getName());

        $em->persist($currentEstablishment);
        $em->flush();

        $this->_cache->clearCacheItem('establishments',$id.$token);

        return new JsonResponse(null, Response::HTTP_NO_CONTENT, [], false);
    }

    /**
     * @OA\Response(
     *      description="Delete an establishment by is id",
     *      response=204,
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
    #[Route('/{id<\d+>}', name:'delete_establishment', methods:['DELETE'])]
    public function deleteEstablishment(Request $request, string $id, EstablishmentsRepository $establishments, EntityManagerInterface $em): JsonResponse
    {
        $token = $request->server->get('HTTP_AUTHORIZATION');
        $userId = $this->_userService->getUserId($token);

        $currentEstablishment = $establishments->findOneBy(['FK_user'=>$userId, 'id'=>$id]);

        if(!$currentEstablishment)
            return new JsonResponse(null, Response::HTTP_NOT_FOUND, [], false);

        $establishments->remove($currentEstablishment);
            
        $em->flush();

        $this->_cache->clearCacheItem('establishments',$id.$token);

        return new JsonResponse(null, Response::HTTP_NO_CONTENT, [], false);
    }

    /**
     * @OA\Response(
     *      response=200,
     *      description="Returns all classrooms of a establishment or only one if the id is given",
     *      @OA\JsonContent(
     *          type="array",
     *          @OA\Items(ref=@Model(type=Establishments::class))
     *      )
     * )
     * 
     * @OA\Tag(name="Establishments")
     * 
     * @param Request $request
     * @param string $estId
     * @param ?string $clsId
     * @param EstablishmentsRepository $establishmentsRepository
     * @param ClassroomsRepository $classroomsRepository
     * @return JsonResponse
     */
    #[Route('/{estId<\d+>}/classrooms/{clsId<\d+>?null}',name:'get-classrooms-from-establishment', methods: ['GET'])]
    public function getClassroomsByEstablishment(Request $request, string $estId, string $clsId, EstablishmentsRepository $establishmentsRepository, ClassroomsRepository $classroomsRepository): JsonResponse
    {
        $token = $request->server->get('HTTP_AUTHORIZATION');
        $userId = $this->_userService->getUserId($token);
        $establishmentJson = $this->_cache->getCache('establishment'.$token.$estId, $establishmentsRepository, 'findOneBy', ['FK_user'=>$userId, 'id'=>$estId]);
        if(!$establishmentJson)
            return new JsonResponse(null, Response::HTTP_NOT_FOUND, [], false);
        
        $establishment = $this->_serializer->deserialize($establishmentJson, Establishments::class, 'json');
        if($clsId === 'null' || $clsId === null)
        {
            $context = SerializationContext::create()->setGroups('classrooms_from_establishment');
            $classrooms = $this->_cache->getSubCollections("classrooms_from_establishment".$token.$estId, $establishment, 'getClassrooms', $context);
            // dd($classrooms);
        }
        else
        {
            $context = SerializationContext::create()->setGroups('classrooms_info');
            $classrooms = $this->_cache->getCache('classrooms'.$token.$clsId, $classroomsRepository, 'findOneBy', ['FK_User' => $userId, 'FK_establishment' => $estId, 'id'=>$clsId], $context);
            // dd($classrooms);
        }
        
        if(!$classrooms)
            return new JsonResponse(null, Response::HTTP_NOT_FOUND, [], false);
        
        return new JsonResponse($classrooms, Response::HTTP_OK, [], true);
    }
}