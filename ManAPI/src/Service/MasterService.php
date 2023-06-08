<?php

namespace App\Service;

use JMS\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class MasterService extends AbstractController
{
    protected ValidatorService $_validator;
    protected SerializerInterface $_serializer;

    public function __construct(ValidatorService $validatorService, SerializerInterface $serializerInterface)
    {
        $this->_serializer = $serializerInterface;
        $this->_validator = $validatorService;
    }
}