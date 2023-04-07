<?php

namespace App\Controller\DevController;

use Faker\Factory;
use App\Entity\Establishments;
use App\Repository\UsersRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\EstablishmentsRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use JMS\Serializer\SerializerInterface;

class DevEstablishmentsController extends AbstractController
{
    private SerializerInterface $serializer;

    public function __construct(SerializerInterface $serializerInterface)
    {
        $this->serializer = $serializerInterface;
    }

    #[Route('api/dev/establishments', name: 'dev_est_get-all-establishments', methods:['GET'])]
    public function getAllEstablishments(EstablishmentsRepository $establishmentsRepository): JsonResponse
    {
        $establishments = $establishmentsRepository->findAll();

        if ($establishments)
        {
            $data = [];

            foreach($establishments as $establishment)
            {
                $establishmentId = $establishment->getId();
                $establishmentFk = $establishment->getFKUserId()->getId();
                $establishmentsName = $establishment->getName();
                array_push($data,[
                    'id'=>$establishmentId,
                    'fk_user_id' => $establishmentFk,
                    'name'=>$establishmentsName
                ]);
            }

            $jsonData = $this->serializer->serialize($data, 'json');

            return new JsonResponse($jsonData, Response::HTTP_OK, [], true);
        }
        return new JsonResponse(null, Response::HTTP_NOT_FOUND, [] ,false);
    }
    #[Route('api/dev/establishments/{number<\d>?3}', name: 'dev_est_add-establishments-all-users', methods: ['POST'])]
    public function addEstablishmentsToAllUsers(Request $request, int $number, UsersRepository $usersRepository, EntityManagerInterface $manager): JsonResponse
    {
        $users = $usersRepository->findAll();
        
        if ($users) {
            $faker = Factory::create();

            foreach ($users as $user)
            {
                for($i = 0; $i<$number; $i++)
                {

                    $establishments = new Establishments;
                    
                    $establishments->setFKUserId($user)
                    ->setName($faker->company());
                    
                    $manager->persist($establishments);
                }
            }
            $manager->flush();

            return new JsonResponse ($number, Response::HTTP_OK, [], true);
        } else {
            return new JsonResponse(null, Response::HTTP_NOT_FOUND, [], false);
        }
    }
}
