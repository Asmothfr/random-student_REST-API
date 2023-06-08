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
     *          @OA\Items(ref=@Model(type=Classrooms::class))
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
        $user = $this->getUser();

        if($id === "null" || $id === null)
        {
             $classrooms = $classroomsRepository->findBy(['FK_user'=>$user]);
        }
        else
        {
            $classrooms = $classroomsRepository->FindOneBy(['FK_user'=>$user, 'id'=>$id]);
        }
        
        if(!$classrooms)
            return new JsonResponse(null, Response::HTTP_NOT_FOUND, [], false);
        
        $context = SerializationContext::create()->setGroups(['classrooms_info']);
        $jsonContent = $this->_serializer->serialize($classrooms, 'json', $context);
        return new JsonResponse($jsonContent, Response::HTTP_OK, [], true);
        
    }

    /**
     * @OA\Response(
     *      description="Create a new classrooom for the establishment whose ID has been gived",
     *      response=201,
     *      @OA\JsonContent(
     *          type="array",
     *          @OA\Items(ref=@Model(type=Classrooms::class))
     *      )
     * )
     * 
     * @OA\Tag(name="Classrooms")
     * 
     * @param Request $request
     * @param string $id
     * @param EstablismentsRepository $establishmentsRepository
     * @param EntityManagerInterface $em
     * @return JsonResponse
     */
    #[Route(name:'add_classroom', methods:['POST'])]
    public function addClassroom(Request $request, EntityManagerInterface $em): JsonResponse
    {   
        $classroom = $this->_serializer->deserialize($request->getContent(), Classrooms::class, 'json');

        $toValidate = $this->_validator->validator($classroom);
        if($toValidate !== true)
            return new JsonResponse($toValidate, Response::HTTP_BAD_REQUEST, [], true);

        $em->persist($classroom);
        $em->flush();

        return new JsonResponse(null, Response::HTTP_CREATED, [], false);
    }

    /**
     * @OA\Response(
     *      description="Edit an classrooms by is id",
     *      response=204,
     *      @OA\JsonContent(
     *          type="array",
     *          @OA\Items(ref=@Model(type=Classrooms::class))
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
    #[Route('/{clsId<\d+>}', name: 'edit_classroom', methods: ['PUT'])]
    public function editClassrooms(Request $request, string $clsId, ClassroomsRepository $classroomsRepository, EntityManagerInterface $em ): JsonResponse
    {
        $jsonData = $request->getContent();
        if(!$jsonData)
            return new JsonResponse(null, Response::HTTP_BAD_REQUEST, [], false);

        $newClassroom = $this->_serializer->deserialize($jsonData, Classrooms::class, 'json');

        $validate = $this->_validator->validator($newClassroom);
        if($validate !== true)
            return new JsonResponse($validate, Response::HTTP_BAD_REQUEST, [], true);

        $user = $this->getUser();

        $currentClassrooms = $classroomsRepository->findOneBy(['FK_user'=>$user, 'id'=>$clsId]);
        if(!$currentClassrooms)
            return new JsonResponse(null, Response::HTTP_NOT_FOUND, [], false);

        $currentClassrooms->setName($newClassroom->getName())
                            ->setFKEstablishmentId($newClassroom->getFKEstablishmentId());
        
        $em->persist($currentClassrooms);
        $em->flush();
        
        return new JsonResponse(null, Response::HTTP_NO_CONTENT, [], false);
    }

    /**
     * @OA\Response(
     *      description="Delete a classrooms by is id",
     *      response=204,
     *      @OA\JsonContent(
     *          type="array",
     *          @OA\Items(ref=@Model(type=Classrooms::class))
     *      )
     * )
     * 
     * @OA\Tag(name="Classrooms")
     * 
     * @param Request $request
     * @param string $id
     * @param ClassroomsRepository $classroomsRepository
     * @param EntityManagerInterface $em
     * @return JsonResponse
     */
    #[Route('/{id<\d+>}', name: 'delete_classroom', methods:['DELETE'])]
    public function DeleteClassroom(Request $request, string $id, ClassroomsRepository $classroomsRepository, EntityManagerInterface $em): JsonResponse
    {
        if($id === 'null' || $id === null)
            return new JsonResponse(null, Response::HTTP_BAD_REQUEST, [], false);

        $user = $this->getUser();

        $classroom = $classroomsRepository->findOneBy(['FK_user' => $user,'id' => $id]);

        if(!$classroom)
            return new JsonResponse(null, Response::HTTP_NOT_FOUND, [], false);

        $classroomsRepository->remove($classroom);
        $em->flush();
        
        return new JsonResponse(null, Response::HTTP_NO_CONTENT, [], false);
    }

    /**
     * @OA\Response(
     *      response=200,
     *      description="Returns all students of a classroom or only one if the id is given",
     *      @OA\JsonContent(
     *          type="array",
     *          @OA\Items(ref=@Model(type=Classrooms::class))
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
        $user = $this->getUser();
        $classroom = $classroomsRepository->findOneBy(['FK_user'=>$user, 'id'=>$clsId]);

        if($stdId == 'null' || $stdId == null)
        {   
            $context = SerializationContext::create()->setGroups('students_from_classroom');
            $students = $classroom->getStudents();
            $jsonContent = $this->_serializer->serialize($students, 'json', $context);
        }
        else
        {
            $context = SerializationContext::create()->setGroups('students_info');
            $students = $studentsRepository->findOneBy(['FK_user'=>$user, 'FK_classrooms'=>$clsId, 'id'=>$stdId]);
            $jsonContent = $this->_serializer->serialize($students, 'json', $context);
        }

        if(!$students)
            return new JsonResponse(null, Response::HTTP_NOT_FOUND, [], false);

        return new JsonResponse($jsonContent, Response::HTTP_OK, [], true);
    }

    /**
     * @OA\Response(
     *      response=200,
     *      description="Returns all schedules of a classroom or only one if the id is given",
     *      @OA\JsonContent(
     *          type="array",
     *          @OA\Items(ref=@Model(type=Classrooms::class))
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
        $user = $this->getUser();
        $classroom= $classroomsRepository->findOneBy(['FK_user'=>$user, 'id'=>$clsId]);

        if($schId == 'null' || $schId == null)
        {   
            $context = SerializationContext::create()->setGroups('schedules_from_classroom');
            $schedules = $classroom->getSchedules();
            $jsonContent = $this->_serializer->serialize($schedules, 'json', $context);
        }
        else
        {
            $context = SerializationContext::create()->setGroups('schedules_info');
            $schedules = $schedulesRepository->findOneBy(['FK_user'=>$user, 'id'=>$schId], $context);
            $jsonContent = $this->_serializer->serialize($schedules, 'json', $context);
        }

        return new JsonResponse($jsonContent, Response::HTTP_OK, [], true);
    }
}