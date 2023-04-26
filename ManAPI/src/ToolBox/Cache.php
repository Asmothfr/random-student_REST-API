<?php

namespace App\ToolBox;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Serializer\SerializerInterface;

class Cache
{
    private CacheItemPoolInterface $_cache;
    private SerializerInterface $_serializer;

    public function __construct(CacheItemPoolInterface $cacheItemPoolInterface, SerializerInterface $serializerInterface)
    {
        $this->_cache = $cacheItemPoolInterface;
        $this->_serializer = $serializerInterface;
    }

    public function getCache(string $cacheItemKey, ServiceEntityRepository $repository, string $repositoryMethod, int $id = null): string
    {
        $cacheItem = $this->_cache->getItem($cacheItemKey);

        $cacheItemValue = $cacheItem->get('value');

        if($cacheItemValue != null)
        {
            $jsonContent = $cacheItemValue;
            return $jsonContent;
        }
        else
        {
            $entity = $repository->$repositoryMethod($id);
            $jsonContent = $this->_serializer->serialize($entity, 'json');
            $cacheItem->set($jsonContent);
            $this->_cache->save($cacheItem);
            return $jsonContent;
        }
    }
}