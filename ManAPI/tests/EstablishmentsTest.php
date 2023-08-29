<?php

namespace App\Tests;

use App\Entity\Establishments;
use App\Entity\Users;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class EstablishmentsTest extends KernelTestCase
{
    // Name
    // FK_User

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

    public function testSuccessData(): void
    {
        self::bootKernel();
        $container = static::getContainer();
        $establishment = new Establishments;
        $establishment->setName('Collège Pistache')
            ->setFKUser($this->user());

        $validator = $container->get('validator');
        $validatorResult = $validator->validate($establishment);
        $this->assertCount(0, $validatorResult);
    }

    public function testEmptyName(): void
    {
        self::bootKernel();
        $container = static::getContainer();
        $establishment = new Establishments;
        $establishment->setName('')
            ->setFKUser($this->User());

        $validator = $container->get('validator');
        $ValidatorResult = $validator->validate($establishment);
        $this->assertCount(1, $ValidatorResult);
    }
    public function testNoFKUser(): void
    {
        self::bootKernel();
        $container = static::getContainer();
        $establishment = new Establishments;
        $establishment->setName('');

        $validator = $container->get('validator');
        $ValidatorResult = $validator->validate($establishment);
        $this->assertCount(2, $ValidatorResult);
    }
}
