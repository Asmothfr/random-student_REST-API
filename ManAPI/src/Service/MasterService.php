<?php

namespace App\Service;

use JMS\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

abstract class MasterService extends AbstractController
{
    protected CacheService $_cache;
    protected ValidatorService $_validator;
    protected UserService $_user;
    protected SerializerInterface $_serializer;

    public function __construct(CacheService $cacheService, ValidatorService $validatorService, UserService $userService, SerializerInterface $serializerInterface)
    {
        $this->_cache = $cacheService;
        $this->_serializer = $serializerInterface;
        $this->_user = $userService;
        $this->_validator = $validatorService;
    }
}