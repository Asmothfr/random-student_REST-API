<?php

namespace App\Controller\DevController;

use Faker\Factory;
use App\Entity\Establishments;
use App\Repository\UsersRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\EstablishmentsRepository;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class DevEstablishmentsController extends AbstractController
{
    private Serializer $serializer;
    private array $encoders;
    private array $normalizers;

    public function __construct()
    {
        $this->encoders[] = new JsonEncoder();
        $this->normalizers[] = new ObjectNormalizer();
        $this->serializer = new Serializer($this->normalizers,$this->encoders);
    }

    #[Route('api/dev/establishments', name: 'dev_est_get-all-establishments')]
    public function getAllEstablishments(EstablishmentsRepository $establishmentsRepository): JsonResponse
    {
        $establishments = $establishmentsRepository->findAll();
        // dd($establishments);
        if ($establishments) {
            $data = $this->serializer->serialize($establishments, 'json');
            return $this->json([
                Response::HTTP_OK,
                'content' =>  "$data"
            ]);
        }
        return $this->json([
            Response::HTTP_NOT_FOUND,
            'content' => 'No establishments in db.'
        ]);
    }
    #[Route('api/dev/establishments/create/{number<\d>?3}', name: 'dev_est_add-establishments-all-users', methods: ['GET'])]
    public function addEstablishmentsToAllUsers(Request $request, int $number, UsersRepository $usersRepository, EntityManagerInterface $manager): JsonResponse
    {
        $users = $usersRepository->findAll();

        if ($users) {
            $faker = Factory::create();

            foreach ($users as $user) {

                dd ($user);
                $establishments = new Establishments;
                $establishments->setFKUserId($user)
                    ->setName($faker->company());
                $manager->persist($establishments);
            }

            $manager->flush();

            return $this->json([
                Response::HTTP_OK,
                'content' => "$number Establishments was added to each users."
            ]);
        } else {
            return $this->json([
                Response::HTTP_NOT_FOUND,
                'content' => 'no users in db, add establishments is impossible.'
            ]);
        }
    }
}
