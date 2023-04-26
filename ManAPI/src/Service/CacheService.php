<?php

namespace App\Service;

use Psr\Cache\CacheItemPoolInterface;
use JMS\Serializer\SerializerInterface;
use JMS\Serializer\SerializationContext;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

class CacheService
{
    private CacheItemPoolInterface $_cache;
    private SerializerInterface $_serializer;

    public function __construct(CacheItemPoolInterface $cacheItemPoolInterface, SerializerInterface $serializerInterface)
    {
        $this->_cache = $cacheItemPoolInterface;
        $this->_serializer = $serializerInterface;
    }

    /**
     * Function that retrieves a cached object. If it doesn't exist, it returns data from db.
     * @param string $cacheItemKey Name of item in cache. If it doesn't exist, it gets created.
     * @param ServiceEntityRepository $repository Repository used to perform the query
     * @param string $repositoryMethod 
     * @param ?int $resourceId 
     * @param ?SerializationContext $context
     */
    public function getCache(string $cacheItemKey, ServiceEntityRepository $repository, string $repositoryMethod, ?int $resourceId = null, ?SerializationContext $context = null): string
    {
        $cacheItem = $this->_cache->getItem($cacheItemKey);
        $cacheItemValue = $cacheItem->get('value');

        if($cacheItemValue != null)
        {
            return $cacheItemValue;
        }
        else
        {
            $entity = $repository->$repositoryMethod($resourceId);
            $jsonContent = $this->_serializer->serialize($entity, 'json', $context);
            $cacheItem->set($jsonContent);
            $this->_cache->save($cacheItem);
            return $jsonContent;
        }
    }
}