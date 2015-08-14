<?php

namespace Iphp\FileStoreBundle\Tests\Adapter\ORM;

use Iphp\FileStoreBundle\Tests\DummyEntity;
use Iphp\FileStoreBundle\Tests\DummyEntityProxyORM;
use Iphp\FileStoreBundle\DataStorage\OrmDataStorage;

/**
 * OrmDataStorageTes
 *
 * @author Vitiko <vitiko@mail.ru>
 */
class OrmDataStorageTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test the getObjectFromArgs method.
     */
    public function testGetObjectFromArgs()
    {
        if (!class_exists('Doctrine\ORM\Event\LifecycleEventArgs')) {
            $this->markTestSkipped('Doctrine\ORM\Event\LifecycleEventArgs does not exist.');
        } else {
            $entity = $this->getMock('Iphp\FileStoreBundle\Tests\DummyEntity');

            $args = $this->getMockBuilder('Doctrine\ORM\Event\LifecycleEventArgs')
                ->disableOriginalConstructor()
                ->getMock();
            $args
                ->expects($this->once())
                ->method('getEntity')
                ->will($this->returnValue($entity));

            $storage = new OrmDataStorage();

            $this->assertEquals($entity, $storage->getObjectFromArgs($args));
        }
    }

    /**
     * Tests the getReflectionClass method.
     */
    public function testGetReflectionClass()
    {
        if (!interface_exists('Doctrine\ORM\Proxy\Proxy')) {
            $this->markTestSkipped('Doctrine\ORM\Proxy\Proxy does not exist.');
        } else {
            $obj = new DummyEntity();
            $adapter = new OrmDataStorage();
            $class = $adapter->getReflectionClass($obj);

            $this->assertEquals($class->getName(), get_class($obj));
        }
    }

    /**
     * Tests the getReflectionClass method with a proxy.
     */
    public function testGetReflectionClassProxy()
    {
        if (!interface_exists('Doctrine\ORM\Proxy\Proxy')) {
            $this->markTestSkipped('Doctrine\ORM\Proxy\Proxy does not exist.');
        } else {
            $obj = new DummyEntityProxyORM();
            $adapter = new OrmDataStorage();
            $class = $adapter->getReflectionClass($obj);

            $this->assertEquals($class->getName(), get_parent_class($obj));
        }
    }


    public function testRecomputeChangeSet()
    {


        if (!class_exists('Doctrine\ORM\Event\LifecycleEventArgs')) {
            $this->markTestSkipped('Doctrine\ORM\Event\LifecycleEventArgs does not exist.');
        } else {
            $entity = $this->getMock('Iphp\FileStoreBundle\Tests\DummyEntity');

            $args = $this->getMockBuilder('Doctrine\ORM\Event\LifecycleEventArgs')
                ->disableOriginalConstructor()
                ->getMock();
            $args
                ->expects($this->once())
                ->method('getEntity')
                ->will($this->returnValue($entity));


            $em = $this->getMockBuilder('Doctrine\ORM\EntityManager')
                ->disableOriginalConstructor()
                ->getMock();


            $args->expects($this->once())
                ->method('getEntityManager')
                ->will($this->returnValue($em));


            $uow = $this->getMockBuilder('Doctrine\ORM\UnitOfWork')
                ->disableOriginalConstructor()
                ->getMock();


            $em->expects($this->once())
                ->method('getUnitOfWork')
                ->will($this->returnValue($uow));


            $metadata = $this->getMockBuilder('Doctrine\ORM\Mapping\ClassMetadata')
                ->disableOriginalConstructor()
                ->getMock();


            $em->expects($this->once())
                ->method('getClassMetadata')
                ->with(get_class($entity))
                ->will($this->returnValue($metadata));


            $storage = new OrmDataStorage();
            $storage->recomputeChangeSet($args);

        }

    }


    function testPostFlush()
    {
        if (!class_exists('Doctrine\ORM\Event\LifecycleEventArgs')) {
            $this->markTestSkipped('Doctrine\ORM\Event\LifecycleEventArgs does not exist.');
        } else {
            $entity = $this->getMock('Iphp\FileStoreBundle\Tests\DummyEntity');

            $args = $this->getMockBuilder('Doctrine\ORM\Event\LifecycleEventArgs')
                ->disableOriginalConstructor()
                ->getMock();


            $em = $this->getMockBuilder('Doctrine\ORM\EntityManager')
                ->disableOriginalConstructor()
                ->getMock();


            $args->expects($this->any())
                ->method('getEntityManager')
                ->will($this->returnValue($em));


            $em->expects($this->once())->method('persist')->with($entity);
            $em->expects($this->once())->method('flush');


            $storage = new OrmDataStorage();
            $storage->postFlush($entity, $args);
        }
    }


    public function testCurrentFieldData()
    {

        if (!class_exists('Doctrine\ORM\Event\LifecycleEventArgs')) {
            $this->markTestSkipped('Doctrine\ORM\Event\PreUpdateEventArgs does not exist.');
        } else {
            $entity = $this->getMock('Iphp\FileStoreBundle\Tests\DummyEntity');

            $args = $this->getMockBuilder('Doctrine\ORM\Event\PreUpdateEventArgs')
                ->disableOriginalConstructor()
                ->getMock();


            $args->expects($this->once())
                ->method('hasChangedField')
                ->with('file')
                ->will($this->returnValue(true));


            $args->expects($this->once())
                ->method('getOldValue')
                ->with('file')
                ->will($this->returnValue(array(1)));

            $storage = new OrmDataStorage();
            $this->assertSame($storage->previusFieldDataIfChanged('file', $args), array(1));
        }

    }

}
