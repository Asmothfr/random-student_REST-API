<?php

namespace App\Service;

use JMS\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class MasterService extends AbstractController
{
    // protected CacheService $_cache;
    protected ValidatorService $_validator;
    protected SerializerInterface $_serializer;

    public function __construct(/*CacheService $cacheService,*/ ValidatorService $validatorService, SerializerInterface $serializerInterface)
    {
        // $this->_cache = $cacheService;
        $this->_serializer = $serializerInterface;
        $this->_validator = $validatorService;
    }
}