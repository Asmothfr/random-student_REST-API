<?php

namespace App\Controller;

use App\Entity\Users;
use App\Entity\Classrooms;
use App\Entity\Establishments;
use App\Repository\ClassroomsRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\EstablishmentsRepository;
use App\Repository\SchedulesRepository;
use App\Repository\StudentsRepository;
use App\Service\MasterService;
use JMS\Serializer\SerializationContext;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Annotations as OA;

#[Route('api/classrooms')]
class ClassroomsController extends MasterService
{
    /**
     * @OA\Response(
     *      response=200,
     *      description="Returns all classrooms of a user or only one if the id is given",
     *      @OA\JsonContent(
     *          type="array",
     *          @OA\Items(ref=@Model(type=Establishments::class))
     *      )
     * )
     * 
     * @OA\Tag(name="Classrooms")
     * 
     * @param Request $request
     * @param string $id
     * @param ClassroomsRepository $ClassroomsRepository
     * @return JsonResponse
     */
    #[Route('/{id<\d+>?null}', name: 'get_classrooms', methods: ['GET'])]
    public function getClassrooms(Request $request, string $id, ClassroomsRepository $classroomsRepository): JsonResponse
    {
        $token = $request->headers->get('authorization');
        $userId = $this->_user->getUserId($token);
        $context = SerializationContext::create()->setGroups(['classrooms_info']);

        if($id === "null" || $id === null)
        {
             $jsonClassrooms = $this->_cache->getCache('classrooms'.$token, $classroomsRepository, 'findBy', ['FK_user'=>$userId], $context);
        }
        else
        {
            $jsonClassrooms = $this->_cache->getCache('classrooms'.$token.$id, $classroomsRepository, 'FindOneBy', ['FK_user'=>$userId, 'id'=>$id], $context);
        }
        if($jsonClassrooms)
            return new JsonResponse($jsonClassrooms, Response::HTTP_OK, [], true);
        
        return new JsonResponse(null, Response::HTTP_NOT_FOUND, [], false);
    }

    /**
     * @OA\Response(
     *      description="Create a new classrooom for the establishment whose ID has been gived",
     *      response=201,
     *      @OA\JsonContent(
     *          type="array",
     *          @OA\Items(ref=@Model(type=Users::class))
     *      )
     * )
     * 
     * @OA\Tag(name="Classrooms")
     * 
     * @param Request $request
     * @param string $id
     * @param EntityManagerInterface $em
     * @return JsonResponse
     */
    #[Route('/establishments/{estId<\d+>}', name:'add_classroom', methods:['POST'])]
    public function addClassroom(Request $request, string $estId, EstablishmentsRepository $establishmentsRepository, EntityManagerInterface $em): JsonResponse
    {   
        $classroom = $this->_serializer->deserialize($request->getContent(), Classrooms::class, 'json');

        $toValidate = $this->_validator->validator($classroom);
        if($toValidate !== true)
            return new JsonResponse($toValidate, Response::HTTP_BAD_REQUEST, [], true);

        $token = $request->headers->get('authorization');
        $userJson = $this->_cache->getUserCache($token);
        $user = $this->_serializer->deserialize($userJson, Users::class, 'json');
        $userId = $user->getId();

        $establishment = $establishmentsRepository->findOneBy(['FK_user'=>$userId, 'id'=>$estId]);
        if(!$establishment)
            return new JsonResponse(null, Response::HTTP_NOT_FOUND, [], false);

        $classroom->setFKUser($user)
                    ->setFKEstablishmentId($establishment);

        $em->persist($classroom);
        $em->flush();

        $this->_cache->clearCacheItem('classrooms',$token);

        return new JsonResponse(null, Response::HTTP_CREATED, [], false);
    }

    /**
     * @OA\Response(
     *      description="Edit an classrooms by is id",
     *      response=204,
     *      @OA\JsonContent(
     *          type="array",
     *          @OA\Items(ref=@Model(type=Users::class))
     *      )
     * )
     * 
     * @OA\Tag(name="Classrooms")
     * 
     * @param Request $request
     * @param string $clsId
     * @param string $estId
     * @param EstablishmentsRepository $establishments
     * @param ClassroomsRepository $classroomsRepository
     * @param EntityManagerInterface $em
     * @return JsonResponse
     */
    #[Route('/{clsId<\d+>}/establishments/{estId<\d+>}', name: 'edit_classroom', methods: ['PUT'])]
    public function editClassrooms(Request $request, string $clsId, string $estId, EstablishmentsRepository $establishmentsRepository, ClassroomsRepository $classroomsRepository, EntityManagerInterface $em ): JsonResponse
    {
        $jsonData = $request->getContent();
        if(!$jsonData)
            return new JsonResponse(null, Response::HTTP_BAD_REQUEST, [], false);

        $newClassroom = $this->_serializer->deserialize($jsonData, Classrooms::class, 'json');

        $validate = $this->_validator->validator($newClassroom);
        if($validate !== true)
            return new JsonResponse($validate, Response::HTTP_BAD_REQUEST, [], true);

        $token = $request->headers->get('authorization');
        $userId = $this->_user->getUserId($token);

        $establishmentJson = $this->_cache->getCache('establishments'.$token.$estId, $establishmentsRepository, 'findOneBy', ['FK_user'=>$userId, 'id'=>$estId]);
        $establishment = $this->_serializer->deserialize($establishmentJson, Establishments::class, 'json');

        if(!$establishment)
            return new JsonResponse(null, Response::HTTP_NOT_FOUND, [], false);

        $currentClassrooms = $classroomsRepository->findOneBy(['FK_user'=>$userId, 'id'=>$clsId]);

        if(!$currentClassrooms)
            return new JsonResponse(null, Response::HTTP_NOT_FOUND, [], false);

        $currentClassrooms->setName($newClassroom->getName())
                            ->setFKEstablishmentId($establishment);
        
        $em->persist($currentClassrooms);
        $em->flush();
        
        $this->_cache->clearCacheItem('classrooms',$token);
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
     * @OA\Tag(name="Classrooms")
     * 
     * @param Request $request
     * @param string $estId
     * @param ?string $clsId
     * @param EstablishmentsRepository $establishmentsRepository
     * @param ClassroomsRepository $classroomsRepository
     * @return JsonResponse
     */
    #[Route('/{id<\d+>}', name: 'delete_classroom', methods:['DELETE'])]
    public function DeleteClassroom(Request $request, string $id, ClassroomsRepository $classroomsRepository, EntityManagerInterface $em): JsonResponse
    {
        if($id === 'null' || $id === null)
            return new JsonResponse(null, Response::HTTP_BAD_REQUEST, [], false);

        $token = $request->headers->get('authorization');
        $userId = $this->_user->getUserId($token);

        $classroom = $classroomsRepository->findOneBy(['FK_user' => $userId,'id' => $id]);

        if(!$classroom)
            return new JsonResponse(null, Response::HTTP_NOT_FOUND, [], false);

        $classroomsRepository->remove($classroom);
        $em->flush();

        $this->_cache->clearCacheItem('classrooms',$token.$id);
        
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
     * @OA\Tag(name="Classrooms")
     * 
     * @param Request $request
     * @param string $clsId
     * @param ?string $stdId
     * @param ClassroomsRepository $classroomsRepository
     * @param StudentsRepository $StudentsRepository
     * @return JsonResponse
     */
    #[Route('/{clsId<\d+>}/students/{stdId<\d+>?null}', name:'get_students_from_classroom', methods:['GET'])]
    public function getStudentsfromClassroom(Request $request, string $clsId, string $stdId, ClassroomsRepository $classroomsRepository, StudentsRepository $studentsRepository): JsonResponse
    {
        $token = $request->headers->get('authorization');
        $userId = $this->_user->getUserId($token);
        $classroomJson = $this->_cache->getCache('classrooms'.$token.$clsId, $classroomsRepository, 'findOneBy', ['FK_user'=>$userId, 'id'=>$clsId]);
        $classroom = $this->_serializer->deserialize($classroomJson, Classrooms::class, 'json');

        if($stdId == 'null' || $stdId == null)
        {   
            $context = SerializationContext::create()->setGroups('students_from_classroom');
            $students = $this->_cache->getSubCollections('students_from_classroom'.$token.$clsId, $classroom, 'getStudents', $context);
        }
        else
        {
            $context = SerializationContext::create()->setGroups('students_info');
            $students = $this->_cache->getCache('students'.$token.$stdId, $studentsRepository, 'findOneBy', ['FK_user'=>$userId, 'FK_classrooms'=>$clsId, 'id'=>$stdId], $context);
        }

        if(!$students)
            return new JsonResponse(null, Response::HTTP_NOT_FOUND, [], false);

        return new JsonResponse($students, Response::HTTP_OK, [], true);
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
     * @OA\Tag(name="Classrooms")
     * 
     * @param Request $request
     * @param string $clsId
     * @param ?string $schId
     * @param ClassroomsRepository $classroomsRepository
     * @param SchedulesRepository $SchedulesRepository
     * @return JsonResponse
     */
    #[Route('/{clsId<\d+>}/schedules/{schId<\d+>?null}', name:'get_schedules_from_classroom', methods:['GET'])]
    public function getSchedulesfromClassroom(Request $request, string $clsId, string $schId, ClassroomsRepository $classroomsRepository, SchedulesRepository $schedulesRepository): JsonResponse
    {
        $token = $request->headers->get('authorization');
        $userId = $this->_user->getUserId($token);
        $classroomJson = $this->_cache->getCache('classrooms'.$token.$clsId, $classroomsRepository, 'findOneBy', ['FK_user'=>$userId, 'id'=>$clsId]);
        $classroom = $this->_serializer->deserialize($classroomJson, Classrooms::class, 'json');

        if($schId == 'null' || $schId == null)
        {   
            $context = SerializationContext::create()->setGroups('schedules_from_classroom');
            $schedules = $this->_cache->getSubCollections('schedules_from_classroom'.$token.$clsId, $classroom, 'getSchedules', $context);
        }
        else
        {
            $context = SerializationContext::create()->setGroups('schedules_info');
            $schedules = $this->_cache->getCache('students'.$token.$schId, $schedulesRepository, 'findOneBy', ['FK_user'=>$userId, 'id'=>$schId], $context);
        }

        return new JsonResponse($schedules, Response::HTTP_OK, [], true);
    }
}