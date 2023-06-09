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
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Annotations as OA;

#[Route('/api/students')]
class StudentsController extends MasterService
{
    /**
     * @OA\Response(
     *      response=200,
     *      description="Returns all students of a user or only one if the id is given",
     *      @OA\JsonContent(
     *          type="array",
     *          @OA\Items(ref=@Model(type=Students::class))
     *      )
     * )
     * 
     * @OA\Tag(name="Students")
     * 
     * @param Request $request
     * @param string $id
     * @param StudentsRepository $StudentsRepository
     * @return JsonResponse
     */
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

    /**
     * @OA\Response(
     *      description="Create a new students for the classrooms whose ID has been gived",
     *      response=201,
     *      @OA\JsonContent(
     *          type="array",
     *          @OA\Items(ref=@Model(type=Students::class))
     *      )
     * )
     * 
     * @OA\Tag(name="Students")
     * 
     * @param Request $request
     * @param EntityManagerInterface $em
     * @return JsonResponse
     */
    #[Route(name: 'add_students', methods:['POST'])]
    public function addStudents(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $studentsJson = $request->getContent();
        $students = $this->_serializer->deserialize($studentsJson, 'array<App\Entity\Students>', 'json');

        $validate = $this->_validator->validator($students);
        if($validate !== true)
            return new JsonResponse($validate, Response::HTTP_BAD_REQUEST, [], true);
        
        foreach($students as $student)
            $em->persist($student);

        $em->flush();
        return new JsonResponse(null, Response::HTTP_CREATED, [], false);
    }

    /**
     * @OA\Response(
     *      description="Edit an students by is id",
     *      response=204,
     *      @OA\JsonContent(
     *          type="array",
     *          @OA\Items(ref=@Model(type=Students::class))
     *      )
     * )
     * 
     * @OA\Tag(name="Students")
     * 
     * @param Request $request
     * @param string $id
     * @param StudentsRepository $StudentsRepository
     * @param EntityManagerInterface $em
     * @return JsonResponse
     */
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

    /**
     * @OA\Response(
     *      description="Delete a students by is id",
     *      response=204,
     *      @OA\JsonContent(
     *          type="array",
     *          @OA\Items(ref=@Model(type=Students::class))
     *      )
     * )
     * 
     * @OA\Tag(name="Students")
     * 
     * @param Request $request
     * @param string $id
     * @param StudentsRepository $studentsRepository
     * @param EntityManagerInterface $em
     * @return JsonResponse
     */
    #[Route('/{id<\d+>}', name: 'delete_student', methods:['DELETE'])]
    public function deleteStudent(Request $request, string $id, StudentsRepository $studentsRepository, EntityManagerInterface $em): JsonResponse
    {
        $user = $this->getUser();

        $student = $studentsRepository->findOneBy(['FK_user'=>$user, 'id'=>$id]);

        if(!$student)
            return new JsonResponse(null, Response::HTTP_NOT_FOUND, [], false);
        
        $em->remove($student);
        $em->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT, [], false);
    }
}