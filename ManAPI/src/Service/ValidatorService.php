<?php

namespace App\Service;

use JMS\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ValidatorService
{
    private ValidatorInterface $_validator;
    private SerializerInterface $_serializer;

    public function __construct(ValidatorInterface $validatorInterface, SerializerInterface $serializerInterface)
    {
        $this->_validator = $validatorInterface;
        $this->_serializer = $serializerInterface;
    }

    public function validator($entity): bool
    {
        $errors = $this->_validator->validate($entity);
        if($errors->count() > 0)
        {
            $errors = $this->_serializer->serialize($errors, 'json');
            return false;
        }
        return true;
    }
}