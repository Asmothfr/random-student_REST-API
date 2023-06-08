<?php

namespace App\Controller;

use App\Entity\Users;
use App\Entity\Establishments;
use App\Repository\ClassroomsRepository;
use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\SerializationContext;
use App\Repository\EstablishmentsRepository;
use App\Service\MasterService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Annotations as OA;

#[Route('api/establishments')]
class EstablishmentsController extends MasterService
{
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
        $user = $this->getUser();

        if($id == null || $id == 'null')
        {
            $establishments = $establishmentsRepository->findBy(['FK_user'=>$user]);
        }
        else
        {
            $establishments = $establishmentsRepository->findOneBy(['FK_user'=>$user, 'id'=>$id]);
        }
        
        if(!$establishments)
            return new JsonResponse(null, Response::HTTP_NOT_FOUND, [], false);

        $context = SerializationContext::create()->setGroups('establishments_info');
        $jsonContent = $this->_serializer->serialize($establishments, 'json', $context);
        return new JsonResponse($jsonContent, Response::HTTP_OK,[], true);
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
        if($isValidate !== true)
            return new JsonResponse(null, Response::HTTP_BAD_REQUEST, [], false);

        $user = $this->getUser();

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

        $user = $this->getUser();
        
        $currentEstablishment = $establishments->findOneBy(['FK_user'=>$user, 'id'=>$id]);
        if(!$currentEstablishment)
            return new JsonResponse(null, Response::HTTP_NOT_FOUND, [], false);

        $currentEstablishment->setName($newEstablishment->getName());

        $em->persist($currentEstablishment);
        $em->flush();

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
        $user = $this->getUser();

        $currentEstablishment = $establishments->findOneBy(['FK_user'=>$user, 'id'=>$id]);

        if(!$currentEstablishment)
            return new JsonResponse(null, Response::HTTP_NOT_FOUND, [], false);

        $establishments->remove($currentEstablishment);
            
        $em->flush();
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
        $user = $this->getUser();
        $establishment = $establishmentsRepository->findOneBy(['FK_user'=>$user, 'id'=>$estId]);
        if(!$establishment)
            return new JsonResponse(null, Response::HTTP_NOT_FOUND, [], false);
        
        if($clsId === 'null' || $clsId === null)
        {
            $context = SerializationContext::create()->setGroups('classrooms_from_establishment');
            $classrooms = $establishment->getClassrooms();
            $jsonContent = $this->_serializer->serialize($classrooms, 'json', $context);
        }
        else
        {
            $context = SerializationContext::create()->setGroups('classrooms_info');
            $classrooms = $classroomsRepository->findOneBy(['FK_User' => $user, 'FK_establishment' => $estId, 'id'=>$clsId]);
            $jsonContent = $this->_serializer->serialize($classrooms, 'json', $context);
        }
        
        if(!$classrooms)
            return new JsonResponse(null, Response::HTTP_NOT_FOUND, [], false);
        
        return new JsonResponse($jsonContent, Response::HTTP_OK, [], true);
    }
}