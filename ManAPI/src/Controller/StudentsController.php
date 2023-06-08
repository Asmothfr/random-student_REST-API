<?php

namespace App\Controller;

use App\Entity\Students;
use App\Service\MasterService;
use App\Repository\ClassroomsRepository;
use App\Repository\StudentsRepository;
use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\SerializationContext;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

#[Route('api/students')]
class StudentsController extends MasterService
{
    #[Route('/{id<\d+>?null}', name:'get_students', methods:['GET'])]
    public function getStudents(Request $request, string $id, StudentsRepository $studentsRepository): JsonResponse
    {
        $user = $this->getUser();

        if($id === null || $id === "null")
        {
            $students = $studentsRepository->findBy(['FK_user'=>$user]);
        }
        else
        {
            $students = $studentsRepository->findOneBy(['FK_user'=>$user, 'id'=>$id]);
        }

        if(!$students)
            return new JsonResponse(null, Response::HTTP_NOT_FOUND, [], false);
        
        $context = SerializationContext::create()->setGroups('students_info');
        $jsonContent = $this->_serializer->serialize($students, 'json', $context);
        return new JsonResponse($jsonContent, Response::HTTP_OK, [], true);

    }

    #[Route('/classrooms/{clsId<\d+>}', name: 'add_students', methods:['POST'])]
    public function addStudents(Request $request, string $clsId, ClassroomsRepository $classroomsRepository, EntityManagerInterface $em): JsonResponse
    {
        $studentsJson = $request->getContent();
        $students = $this->_serializer->deserialize($studentsJson, 'array<App\Entity\Students>', 'json');

        $validate = $this->_validator->validator($students);
        if($validate !== true)
            return new JsonResponse($validate, Response::HTTP_BAD_REQUEST, [], true);

        $user = $this->getUser();

        $classroom = $classroomsRepository->findOneBy(['FK_user'=>$user, 'id'=>$clsId]);
        if(!$classroom)
            return new JsonResponse(null, Response::HTTP_NOT_FOUND, [], false);
        
        foreach($students as $student)
        {
            $student->setFKUser($user)
            ->setFKClassroomId($classroom)
            ->setScore(0);

            $em->persist($student);
        }

        $em->flush();
        return new JsonResponse(null, Response::HTTP_CREATED, [], false);
    }

    #[Route('/{id<\d+>}',name: 'update_student', methods: ['PUT'])]
    public function updateStudent(Request $request, string $id, StudentsRepository $studentsRepository, EntityManagerInterface $em): JsonResponse
    {
        $jsonData = $request->getContent();
        $newStudent = $this->_serializer->deserialize($jsonData, Students::class, 'json');

        $validate = $this->_validator->validator($newStudent);
        if($validate !== true)
            return new JsonResponse($validate, Response::HTTP_BAD_REQUEST, [], true);

        $user = $this->getUser();
        $currentStudent = $studentsRepository->findOneBy(['FK_user'=>$user, 'id'=>$id]);
        
        $currentStudent->setFKClassroomId($newStudent->getFKClassroomId())
                        ->setLastname($newStudent->getLastname())
                        ->setFirstname($newStudent->getFirstname())
                        ->setScore($newStudent->getScore());
        $em->persist($currentStudent);
        $em->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT, [], false);
    }

    #[Route('/{id<\d+>}', name: 'delete_student', methods:['DELETE'])]
    public function deleteStudent(Request $request, string $id, StudentsRepository $studentsRepository, EntityManagerInterface $em): JsonResponse
    {
        $user = $this->getUser();

        $student = $studentsRepository->findOneBy(['FK_user'=>$user, 'id'=>$id]);

        if(!$student)
            return new JsonResponse(null, Response::HTTP_NOT_FOUND, [], false);
        
        $em->remove($student);
        $em->flush();

        return new JsonResponse(null, Response::HTTP_OK, [], false);
    }
}