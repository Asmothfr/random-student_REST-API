<?php

namespace App\Tests;

use App\Entity\Users;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UsersTest extends KernelTestCase
{    
    public function testSuccessData(): void
    {
        self::bootKernel();
        $container = static::getContainer();
        $user = new Users;
        $user->setEmail('test@test.com')
            ->setName('testMan0')
            ->setPassword('$2y$13$eNJNBqngZOGKb1JrliwyAOS6ZB7xuu7yzxPbUp7Krbt2DM8VrpWQi')
            ->setRoles(['USER']);

        $errors = $container->get('validator')->validate($user);

        $this->assertCount(0, $errors);
    }

    public function testBlankMail(): void
    {
        self::bootKernel();
        $container = static::getContainer();

        $user = new Users;
        $user->setEmail('')
            ->setName('testMan0')
            ->setPassword('$2y$13$eNJNBqngZOGKb1JrliwyAOS6ZB7xuu7yzxPbUp7Krbt2DM8VrpWQi')
            ->setRoles(['USER']);

        $errors = $container->get('validator')->validate($user);

        $this->assertCount(1, $errors);
    }

    public function testWrongMail(): void
    {
        self::bootKernel();
        $container = static::getContainer();

        $user = new Users;
        $user->setEmail('toto.com')
            ->setName('testMan0')
            ->setPassword('$2y$13$eNJNBqngZOGKb1JrliwyAOS6ZB7xuu7yzxPbUp7Krbt2DM8VrpWQi')
            ->setRoles(['USER']);

        $errors = $container->get('validator')->validate($user);

        $this->assertCount(1, $errors);
    }

    public function testBlankName(): void
    {
        self::bootKernel();
        $container = static::getContainer();

        $user = new Users;
        $user->setEmail('test@test.com')
            ->setName('')
            ->setPassword('$2y$13$eNJNBqngZOGKb1JrliwyAOS6ZB7xuu7yzxPbUp7Krbt2DM8VrpWQi')
            ->setRoles(['USER']);

        $errors = $container->get('validator')->validate($user);

        $this->assertCount(2, $errors);
    }

    public function testShortName(): void
    {
        self::bootKernel();
        $container = static::getContainer();

        $user = new Users;
        $user->setEmail('test@test.com')
            ->setName('toto')
            ->setPassword('$2y$13$eNJNBqngZOGKb1JrliwyAOS6ZB7xuu7yzxPbUp7Krbt2DM8VrpWQi')
            ->setRoles(['USER']);

        $errors = $container->get('validator')->validate($user);

        $this->assertCount(1, $errors);
    }

    public function testBlankPassword(): void
    {
        self::bootKernel();
        $container = static::getContainer();

        $user = new Users;
        $user->setEmail('test@test.com')
            ->setName('testMan0')
            ->setPassword('')
            ->setRoles(['USER']);

        $errors = $container->get('validator')->validate($user);

        $this->assertCount(2, $errors);
    }

    public function testWeekPassword(): void
    {
        self::bootKernel();
        $container = static::getContainer();

        $user = new Users;
        $user->setEmail('test@test.com')
            ->setName('testMan0')
            ->setPassword('0000000000000000')
            ->setRoles(['USER']);

        $errors = $container->get('validator')->validate($user);

        $this->assertCount(1, $errors);
    }

    public function testRoleEmptyString(): void
    {
        self::bootKernel();
        $container = static::getContainer();

        $user = new Users;
        $user->setEmail('test@test.com')
            ->setName('testMan0')
            ->setPassword('$2y$13$eNJNBqngZOGKb1JrliwyAOS6ZB7xuu7yzxPbUp7Krbt2DM8VrpWQi')
            ->setRoles(['']);

        $errors = $container->get('validator')->validate($user);

        $this->assertCount(1, $errors);
    }
}
