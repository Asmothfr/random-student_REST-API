<?php

namespace App\Tests;

use App\Entity\Users;
use App\Entity\Classrooms;
use App\Entity\Establishments;
use App\Entity\Schedules;
use DateTime;
use DateTimeInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class SchedulesTest extends KernelTestCase
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

        $startTime = new DateTime('14:00:00');
        $endTime = new DateTime('16:00:00');
        $day = "monday";

        $schedule = new Schedules;
        $schedule->setFKUser($this->user())
            ->setFKClassroomId($this->classroom())
            ->setday($day)
            ->setStartTime($startTime)
            ->setEndTime($endTime);
        
        $validatorResult = $validator->validate($schedule);
        $this->assertCount(0,$validatorResult);
    }

    public function testFailDay(): void
    {
        self::bootKernel();
        $container = static::getContainer();
        $validator = $container->get('validator');

        $startTime = new DateTime('14:00:00');
        $endTime = new DateTime('16:00:00');
        $day = "monday friday";

        $schedule = new Schedules;
        $schedule->setFKUser($this->user())
            ->setFKClassroomId($this->classroom())
            ->setday($day)
            ->setStartTime($startTime)
            ->setEndTime($endTime);
        
        $validatorResult = $validator->validate($schedule);
        $this->assertCount(1,$validatorResult);
    }

    public function testEmptyDay(): void
    {
        self::bootKernel();
        $container = static::getContainer();
        $validator = $container->get('validator');

        $startTime = new DateTime('14:00:00');
        $endTime = new DateTime('16:00:00');
        $day = "";

        $schedule = new Schedules;
        $schedule->setFKUser($this->user())
            ->setFKClassroomId($this->classroom())
            ->setday($day)
            ->setStartTime($startTime)
            ->setEndTime($endTime);
        
        $validatorResult = $validator->validate($schedule);
        $this->assertCount(1,$validatorResult);
    }

    public function testFailStartTimeNull(): void
    {
        self::bootKernel();
        $container = static::getContainer();
        $validator = $container->get('validator');

        $endTime = new DateTime('2024-01-01 06:00:00');
        $day = "monday";

        $schedule = new Schedules;
        $schedule->setFKUser($this->user())
            ->setFKClassroomId($this->classroom())
            ->setDay($day)
            ->setEndTime($endTime);
        
        $validatorResult = $validator->validate($schedule);
        $this->assertCount(1,$validatorResult);
    }

    public function testFailFKUser(): void
    {
        self::bootKernel();
        $container = static::getContainer();
        $validator = $container->get('validator');

        $startTime = new DateTime('2024-01-01 00:00:00');
        $endTime = new DateTime('2024-01-01 06:00:00');
        $day = "monday";

        $schedule = new Schedules;
        $schedule->setFKClassroomId($this->classroom())
            ->setDay($day)
            ->setStartTime($startTime)
            ->setEndTime($endTime);
        
        $validatorResult = $validator->validate($schedule);
        $this->assertCount(1,$validatorResult);
    }

    public function testFailFKClassrooms(): void
    {
        self::bootKernel();
        $container = static::getContainer();
        $validator = $container->get('validator');

        $startTime = new DateTime('2024-01-01 00:00:00');
        $endTime = new DateTime('2024-01-01 06:00:00');
        $day = "monday";

        $schedule = new Schedules;
        $schedule->setFKUser($this->user())
            ->setDay($day)
            ->setStartTime($startTime)
            ->setEndTime($endTime);
        
        $validatorResult = $validator->validate($schedule);
        $this->assertCount(1,$validatorResult);
    }
}
