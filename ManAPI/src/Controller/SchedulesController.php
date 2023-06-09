<?php

namespace App\Controller;

use App\Entity\Schedules;
use App\Service\MasterService;
use App\Repository\SchedulesRepository;
use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\SerializationContext;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Annotations as OA;

#[Route('/api/schedules')]
class SchedulesController extends MasterService
{
    /**
     * @OA\Response(
     *      response=200,
     *      description="Returns all schedules of a user or only one if the id is given",
     *      @OA\JsonContent(
     *          type="array",
     *          @OA\Items(ref=@Model(type=Schedules::class))
     *      )
     * )
     * 
     * @OA\Tag(name="Schedules")
     * 
     * @param Request $request
     * @param string $id
     * @param StudentsRepository $StudentsRepository
     * @return JsonResponse
     */
    #[Route('{/{id<\d+>?null}', name:'get_schedules', methods:['GET'])]
    public function getSchedules(Request $request, string $id, SchedulesRepository $schedulesRepository): JsonResponse
    {
        $user = $this->getUser();

        if($id === null || $id === 'null')
        {
            $schedules = $schedulesRepository->findBy(['FK_user'=>$user]);
        }
        else
        {
            $schedules = $schedulesRepository->findOneBy(['FK_user'=>$user, 'id'=>$id]);
        }

        if(!$schedules)
            return new JsonResponse(null, Response::HTTP_NOT_FOUND, [], false);

        $context = SerializationContext::create()->setGroups('schedules_info');
        $jsonContent = $this->_serializer->serialize($schedules, 'json', $context);

        return new JsonResponse($jsonContent, Response::HTTP_OK, [], true);
    }

    /**
     * @OA\Response(
     *      description="Create a new schedules for the classrooms whose ID has been gived",
     *      response=201,
     *      @OA\JsonContent(
     *          type="array",
     *          @OA\Items(ref=@Model(type=Schedules::class))
     *      )
     * )
     * 
     * @OA\Tag(name="Schedules")
     * 
     * @param Request $request
     * @param EntityManagerInterface $em
     * @return JsonResponse
     */
    #[Route(name:'add_schedules', methods:['POST'])]
    public function createSchedules(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $jsonData = $request->getContent();
        $schedules = $this->_serializer->deserialize($jsonData, Schedules::class, 'json');

        $validate = $this->_validator->validator($schedules);
        if($validate !== true)
            return new JsonResponse($validate, Response::HTTP_BAD_REQUEST, [], true);
        
        $em->persist($schedules);
        $em->flush();

        return new JsonResponse(null, Response::HTTP_CREATED, [], false);
    }

    /**
     * @OA\Response(
     *      description="Edit an schedules by is id",
     *      response=204,
     *      @OA\JsonContent(
     *          type="array",
     *          @OA\Items(ref=@Model(type=Schedules::class))
     *      )
     * )
     * 
     * @OA\Tag(name="Schedules")
     * 
     * @param Request $request
     * @param string $id
     * @param SchedulesRepository $schedulesRepository
     * @param EntityManagerInterface $em
     * @return JsonResponse
     */
    #[Route('/{id<\d+>}', name: 'edit_schedules', methods: ['PUT'])]
    public function updateSchedules(Request $request, string $id, SchedulesRepository $schedulesRepository, EntityManagerInterface $em): JsonResponse
    {
        $jsonData = $request->getContent();
        $newSchedules = $this->_serializer->deserialize($jsonData, Schedules::class, 'json');

        $validate = $this->_validator->validator($newSchedules);
        if($validate !== true)
            return new JsonResponse($validate, Response::HTTP_BAD_REQUEST, [], true);

        $user = $this->getUser();
        $currentSchedules = $schedulesRepository->findOneBy(['FK_user'=>$user, 'id'=>$id]);

        $currentSchedules->setFKUser($newSchedules->getFKUser())
                         ->setFKClassroomId($newSchedules->getFKClassroomId())
                         ->setStartTime($newSchedules->getStartTime())
                         ->setEndTime($newSchedules->getEndTime());
        
        $em->persist($currentSchedules);
        $em->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT, [], false);
    }

    /**
     * @OA\Response(
     *      description="Delete a schedules by is id",
     *      response=204,
     *      @OA\JsonContent(
     *          type="array",
     *          @OA\Items(ref=@Model(type=Schedules::class))
     *      )
     * )
     * 
     * @OA\Tag(name="Schedules")
     * 
     * @param Request $request
     * @param string $id
     * @param SchedulesRepository $schedulesRepository
     * @param EntityManagerInterface $em
     * @return JsonResponse
     */
    #[Route('/{id<\d+>}', name:'delete_schedules', methods:['DELETE'])]
    public function deleteSchedules(Request $request, string $id, SchedulesRepository $schedulesRepository, EntityManagerInterface $em): JsonResponse
    {
        $user = $this->getUser();

        $schedule = $schedulesRepository->findOneBy(['FK_user'=>$user, 'id'=>$id]);

        if(!$schedule)
            return new JsonResponse(null, Response::HTTP_NOT_FOUND, [], false);

        $em->persist($schedule);
        $em->flush();
        
        return new JsonResponse(null, Response::HTTP_NO_CONTENT, [], false);
    }
}