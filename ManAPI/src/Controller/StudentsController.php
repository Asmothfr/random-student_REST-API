<?php

namespace App\Controller;

use App\Entity\Students;
use App\Service\UserService;
use App\Service\CacheService;
use App\Service\ValidatorService;
use JMS\Serializer\SerializerInterface;
use App\Repository\ClassroomsRepository;
use App\Service\MasterService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Validator\Constraints\Length;

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
        $tudentsJson2 = $request->toArray();
        dd($tudentsJson2);
        $student = $this->_serializer->deserialize($studentsJson, Students::class, 'json');

        $validate = $this->_validator->validator($student);
        if($validate !== true)
            return new JsonResponse($validate, Response::HTTP_BAD_REQUEST, [], true);

        $token = $request->headers->get('authorization');
        $userId = $this->_user->getUserId($token);
        
        return new JsonResponse(null, Response::HTTP_OK, [], false);
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