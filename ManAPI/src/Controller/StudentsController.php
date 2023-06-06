<?php

namespace App\Controller;

use App\Entity\Users;
use App\Entity\Classrooms;
use App\Service\MasterService;
use App\Repository\ClassroomsRepository;
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
    public function getStudents(): JsonResponse
    {
        dd('route ok');
    }

    #[Route('/classrooms/{clsId<\d+>}', name: 'add_students', methods:['POST'])]
    public function addStudents(Request $request, string $clsId, ClassroomsRepository $classroomsRepository, EntityManagerInterface $em): JsonResponse
    {
        $studentsJson = $request->getContent();
        $students = $this->_serializer->deserialize($studentsJson, 'array<App\Entity\Students>', 'json');
        // dd($students);

        $validate = $this->_validator->validator($students);
        if($validate !== true)
            return new JsonResponse($validate, Response::HTTP_BAD_REQUEST, [], true);

        $token = $request->headers->get('authorization');
        $userJson = $this->_cache->getUserCache($token);
        $user = $this->_serializer->deserialize($userJson, Users::class, 'json');
        $userId = $user->getId();

        $context = SerializationContext::create()->setGroups('classrooms_id');
        $classroomJson = $this->_cache->getCache('classrooms'.$token.$clsId, $classroomsRepository, 'findOneBy', ['FK_user'=>$userId, 'id'=>$clsId], $context);
        $classroom = $this->_serializer->deserialize($classroomJson, Classrooms::class, 'json');
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

    #[Route(name: 'update_student', methods: ['PUT'])]
    public function updateStudent(): JsonResponse
    {
        dd('route ok');
    }

    #[Route(name: 'delete_student', methods:['DELETE'])]
    public function deleteStudent(): JsonResponse
    {
        dd('route ok');
    }
}