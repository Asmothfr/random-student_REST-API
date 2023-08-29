<?php

namespace App\Tests;

use App\Entity\Classrooms;
use App\Entity\Establishments;
use App\Entity\Users;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ClassroomsTest extends KernelTestCase
{
    public function user(): object
    {
        $user = new Users;
        $user->setId(1);
        $user->setEmail('test@test.test')
            ->setName('ImTest01')
            ->setPassword('$2y$13$eNJNBqngZOGKb1JrliwyAOS6ZB7xuu7yzxPbUp7Krbt2DM8VrpWQi')
            ->setRoles(['TESTUSER']);
        return $user;
    }

    public function establishment(): object
    {
        $establishment = new Establishments;
        $establishment->setId(1);
        $establishment->setName('Collège Pistache')
            ->setFKUser($this->user());

        return $establishment;
    }

    public function testSuccessData(): void
    {
        self::bootKernel();
        $container = static::getContainer();
        $validator = $container->get('validator');

        $classroom = new Classrooms;
        $classroom->setFKEstablishmentId($this->establishment())
            ->setFKUser($this->user())
            ->setName('3m B');
        
        $validatorResult = $validator->validate($classroom);
        $this->assertCount(0,$validatorResult);
    }

    public function testBlankName(): void
    {
        self::bootKernel();
        $container = static::getContainer();
        $validator = $container->get('validator');

        $classroom = new Classrooms;
        $classroom->setFKEstablishmentId($this->establishment())
            ->setFKUser($this->user())
            ->setName('');
        
        $validatorResult = $validator->validate($classroom);
        $this->assertCount(1,$validatorResult);
    }

    public function testFailFKUser(): void
    {
        self::bootKernel();
        $container = static::getContainer();
        $validator = $container->get('validator');

        $classroom = new Classrooms;
        $classroom->setFKEstablishmentId($this->establishment())
            ->setName('3m B');
        
        $validatorResult = $validator->validate($classroom);
        $this->assertCount(1,$validatorResult);
    }

    public function testFailFKEstablishment(): void
    {
        self::bootKernel();
        $container = static::getContainer();
        $validator = $container->get('validator');

        $classroom = new Classrooms;
        $classroom->setFKUser($this->user())
            ->setName('3m B');
        
        $validatorResult = $validator->validate($classroom);
        $this->assertCount(1,$validatorResult);
    }
}
