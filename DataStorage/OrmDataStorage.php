<?php

namespace Iphp\FileStoreBundle\DataStorage;

use Iphp\FileStoreBundle\DataStorage\DataStorageInterface;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\Common\EventArgs;

use Doctrine\ORM\Proxy\Proxy;

/**
 * Orm Data Storage
 *
 * @author Vitiko <vitiko@mail.ru>
 */
class OrmDataStorage implements DataStorageInterface
{
    /**
     * {@inheritDoc}
     */
    public function getObjectFromArgs(EventArgs $e)
    {
        return $e->getEntity();
    }

    /**
     * {@inheritDoc}
     */
    public function recomputeChangeSet(EventArgs $e)
    {
        $obj = $this->getObjectFromArgs($e);

        /**
         * var \Doctrine\ORM\EntityManager
         */
        $em = $e->getEntityManager();

        $uow = $em->getUnitOfWork();
        $metadata = $em->getClassMetadata(get_class($obj));
        $uow->recomputeSingleEntityChangeSet($metadata, $obj);
    }

    /**
     * {@inheritDoc}
     */
    public function getReflectionClass($obj)
    {
        if ($obj instanceof Proxy) {
            return new \ReflectionClass(get_parent_class($obj));
        }

        return new \ReflectionClass($obj);
    }


    /**
     * {@inheritDoc}
     */
    public function postFlush($obj, EventArgs $args)
    {
        $args->getEntityManager()->persist($obj);
        $args->getEntityManager()->flush();
    }


    public function previusFieldDataIfChanged($fieldName, EventArgs $args)
    {
        return $args->hasChangedField($fieldName) ? $args->getOldValue($fieldName) : null;
    }


}
