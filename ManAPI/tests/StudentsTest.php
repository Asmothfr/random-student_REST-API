<?php

namespace App\Tests;

use App\Entity\Users;
use App\Entity\Classrooms;
use App\Entity\Establishments;
use App\Entity\Students;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class StudentsTest extends KernelTestCase
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
    
    public function classroom(): object
    {
        $classroom = new Classrooms;
        $classroom->setId(1);
        $classroom->setFKEstablishmentId($this->establishment())
            ->setFKUser($this->user())
            ->setName('3m B');
        
            return $classroom;
    }

    public function testSuccessData(): void
    {
        self::bootKernel();
        $container = static::getContainer();
        $validator = $container->get('validator');

        $student = new Students;
        $student->setFKUser($this->user())
            ->setFKClassroomId($this->classroom())
            ->setLastname('Pistache')
            ->setFirstname('Verte');
        $student->setScore(0);
        
        $validatorResult = $validator->validate($student);
        $this->assertCount(0,$validatorResult);
    }

    public function testFailFKUser(): void
    {
        self::bootKernel();
        $container = static::getContainer();
        $validator = $container->get('validator');

        $student = new Students;
        $student->setFKClassroomId($this->classroom())
            ->setLastname('Pistache')
            ->setFirstname('Verte');
        $student->setScore(0);
        
        $validatorResult = $validator->validate($student);
        $this->assertCount(1,$validatorResult);
    }

    public function testFailFKClassrooms(): void
    {
        self::bootKernel();
        $container = static::getContainer();
        $validator = $container->get('validator');

        $student = new Students;
        $student->setFKUser($this->user())
            ->setLastname('Pistache')
            ->setFirstname('Verte');
        $student->setScore(0);
        
        $validatorResult = $validator->validate($student);
        $this->assertCount(1,$validatorResult);
    }

    public function testFailLastName(): void
    {
        self::bootKernel();
        $container = static::getContainer();
        $validator = $container->get('validator');

        $student = new Students;
        $student->setFKUser($this->user())
            ->setFKClassroomId($this->classroom())
            ->setLastname('Pistache')
            ->setFirstname('Verte');
        $student->setScore(0);
        
        $validatorResult = $validator->validate($student);
        $this->assertCount(0,$validatorResult);
    }
    
    public function testFailFirstName(): void
    {
        self::bootKernel();
        $container = static::getContainer();
        $validator = $container->get('validator');

        $student = new Students;
        $student->setFKUser($this->user())
            ->setFKClassroomId($this->classroom())
            ->setLastname('Pistache');
        $student->setScore(0);
        
        $validatorResult = $validator->validate($student);
        $this->assertCount(1,$validatorResult);
    }

    public function testEmptyScore(): void
    {
        self::bootKernel();
        $container = static::getContainer();
        $validator = $container->get('validator');

        $student = new Students;
        $student->setFKUser($this->user())
            ->setFKClassroomId($this->classroom())
            ->setLastname('Pistache')
            ->setFirstname('Verte');
        
        $validatorResult = $validator->validate($student);
        $this->assertCount(0,$validatorResult);
    }
}
