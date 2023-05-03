<?php

namespace App\Service;

use Psr\Cache\CacheItemPoolInterface;
use JMS\Serializer\SerializerInterface;
use JMS\Serializer\SerializationContext;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class CacheService extends AbstractController
{
    private CacheItemPoolInterface $_cache;
    private SerializerInterface $_serializer;

    public function __construct(CacheItemPoolInterface $cacheItemPoolInterface, SerializerInterface $serializerInterface)
    {
        $this->_cache = $cacheItemPoolInterface;
        $this->_serializer = $serializerInterface;
    }

    /**
     * Function that retrieves a cached user.
     * @param string $userToken Use to create the name of item in cache. If it doesn't exist, it gets created.
     * @return string
     */
    public function getUserCache(string $userToken): string
    {
        $cacheItemKey = 'user'.$userToken;
        $cacheItem = $this->_cache->getItem($cacheItemKey);
        $cacheItemValue = $cacheItem->get('value');

        if($cacheItemValue != null)
        {   
            echo('cache');
            return $cacheItemValue;
        }

        $user = $this->getUser();
        $context = SerializationContext::create()->setGroups(['user_info']);
        $jsonContent = $this->_serializer->serialize($user, 'json', $context);
        $cacheItem->set($jsonContent);
        $this->_cache->save($cacheItem);
        return $jsonContent;
    }
    
    /**
     * Function that retrieves a cached object. If it doesn't exist, it returns data from db.
     * @param string $cacheItemKey Name of item in cache. If it doesn't exist, it gets created.
     * @param ServiceEntityRepository $repository Repository used to perform the query
     * @param string $repositoryMethod 
     * @param ?int $resourceId 
     * @param ?SerializationContext $context
     * @return string
     */
    public function getCache(string $cacheItemKey, ServiceEntityRepository $repository, string $repositoryMethod, ?int $resourceId = null, ?SerializationContext $context = null): ?string
    {
        $cacheItem = $this->_cache->getItem($cacheItemKey);
        $cacheItemValue = $cacheItem->get('value');

        if($cacheItemValue != null)
        {
            return $cacheItemValue;
        }

        $entity = $repository->$repositoryMethod($resourceId);
        if($entity != null)
        {
            $jsonContent = $this->_serializer->serialize($entity, 'json', $context);
            $cacheItem->set($jsonContent);
            $this->_cache->save($cacheItem);
            return $jsonContent;
        }
        
        return null;
    }

    public function clearAllCache():void
    {
        $this->_cache->clear();
    }

    public function clearCacheItem(string $cacheItemName, string $id)
    {
        $cacheItemKey = $cacheItemName.$id;
        $this->_cache->deleteItem($cacheItemKey);
    }

    public function clearUserCache(string $userToken)
    {
        $cacheItemKey = 'user'.$userToken;
        $this->_cache->deleteItem($cacheItemKey);
    }
}