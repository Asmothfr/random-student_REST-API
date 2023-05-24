<?php

namespace App\Service;

use App\Entity\Users;
use App\Service\CacheService;
use JMS\Serializer\SerializerInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

class UserCheckerService
{
    private CacheService $_cache;
    private SerializerInterface $_serializer;

    public function __construct(CacheService $cacheService, SerializerInterface $serializerInterface)
    {
        $this->_cache = $cacheService;
        $this->_serializer = $serializerInterface;
    }

    /**
     * checks if the user is the owner of the resource.
     * @param string $userToken
     * @param object $entity
     * @return bool
     */
    public function userChecker(string $userToken, object $entity): bool
    {
        $userJson = $this->_cache->getUserCache($userToken);
        $user = $this->_serializer->deserialize($userJson, Users::class, 'json');
        $userId = $user->getId();

        $entityUserFk = $entity->getFKUser()->getId();

        if($userId === $entityUserFk)
        {
            return true;
        }
        return false;
    }
}