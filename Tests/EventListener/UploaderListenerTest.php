<?php

namespace Iphp\FileStoreBundle\Tests\EventListener;

use Iphp\FileStoreBundle\EventListener\UploaderListener;
use Iphp\FileStoreBundle\Tests\Mocks;
use Iphp\FileStoreBundle\Tests\DummyEntity;
use Iphp\FileStoreBundle\Driver\AnnotationDriver;
use Iphp\FileStoreBundle\Naming\NamerServiceInvoker;


use Iphp\FileStoreBundle\Mapping\PropertyMappingFactory;

/**
 * UploaderListenerTest.
 *
 * @author Vitiko <vitiko@mail.ru>
 */
class UploaderListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Iphp\FileStoreBundle\DataStorage\DataStorageInterface $dataStorage
     */
    protected $dataStorage;

    /**
     * @var \Iphp\FileStoreBundle\Driver\AnnotationDriver $driver
     */
    protected $driver;

    /**
     * @var \Iphp\FileStoreBundle\FileStorage\FileStorageInterface $fileStorage
     */
    protected $fileStorage;

    /**
     * @var \Iphp\FileStoreBundle\Mapping\PropertyMappingFactory  $propertyMappingfactory
     */
    protected $propertyMappingfactory;


    /**
     * @var \Iphp\FileStoreBundle\Naming\NamerServiceInvoker
     */
    protected $namerServiceInvoker;

    /**
     * Sets up the test
     */
    public function setUp()
    {
        $this->dataStorage = Mocks::getDataStorageMock($this);
        $this->driver = Mocks::getAnnotationDriverMock($this);
        $this->fileStorage = Mocks::getFileStorageMock($this);
        $this->namerServiceInvoker = Mocks::getNamerServiceInvokerMock($this);


        $this->propertyMappingfactory = Mocks::getPropertyMappingFactoryMock($this,
            $this->namerServiceInvoker, $this->driver /*, DummyEntity::getDefaultFileStoreConfig()*/);


        /*
        * $this->propertyMappingfactory = new PropertyMappingFactory(
            $this->namerServiceInvoker, $this->driver, DummyEntity::getDefaultFileStoreConfig());
        */

    }


    protected function getUploaderListener()
    {
        return new UploaderListener($this->dataStorage, $this->fileStorage, $this->propertyMappingfactory);
    }

    /**
     * Test the getSubscribedEvents method.
     */
    public function testGetSubscribedEvents()
    {
        $events = $this->getUploaderListener()->getSubscribedEvents();

        $this->assertTrue(in_array('prePersist', $events));
        $this->assertTrue(in_array('postFlush', $events));
        $this->assertTrue(in_array('preUpdate', $events));
        $this->assertTrue(in_array('postRemove', $events));
    }


    protected function setDataStorageObjectMapping($obj, $propertyMapping)
    {
        $class = new \ReflectionClass($obj);

        $this->dataStorage
            ->expects($this->once())
            ->method('getObjectFromArgs')
            ->will($this->returnValue($obj));

        $this->dataStorage
            ->expects($this->once())
            ->method('getReflectionClass')
            ->with($obj)
            ->will($this->returnValue($class));

        $this->propertyMappingfactory
            ->expects($this->once())
            ->method('getMappingsFromObject')
            ->with($obj, $class)
            ->will($this->returnValue(array($propertyMapping)));
    }


    protected function createFilledPropertyMapping(
        $filePropertyValue = null, $propertyName = null, $currentFieldData = null, $args = null)
    {
        $propertyMapping = Mocks::getPropertyMappingMock($this);

        $propertyMapping
            ->expects($this->once())
            ->method('getFileUploadPropertyValue')
            ->will($this->returnValue($filePropertyValue));


         if ($propertyName) $propertyMapping
            ->expects($this->any())
            ->method('getFileUploadPropertyName')
            ->will($this->returnValue($propertyName));


        if ($propertyName) $propertyMapping
            ->expects($this->any())
            ->method('getFileDataPropertyName')
            ->will($this->returnValue($propertyName));


        if ($propertyName) $propertyMapping
            ->expects($this->any())
            ->method('isUseOneProperty')
            ->will($this->returnValue(true));


        $this->dataStorage
            ->expects($this->any())
            ->method('previusFieldDataIfChanged')
            ->with($propertyName, $args)
            ->will($this->returnValue($currentFieldData));


        return $propertyMapping;
    }


    /**
     * Tests the prePersist method.
     */
    public function testPrePersistWithFile()
    {
        $obj = new DummyEntity();

        $propertyMapping = $this->createFilledPropertyMapping(Mocks::getFileMock($this));
        $this->setDataStorageObjectMapping($obj, $propertyMapping);


        $propertyMapping
            ->expects($this->once())
            ->method('setFileUploadPropertyValue')
            ->with(null);


        $listener = $this->getUploaderListener();

        $this->assertFalse($listener->hasDeferredObject($obj));
        $listener->prePersist(Mocks::getEventArgsMock($this));


        $this->assertTrue($listener->hasDeferredObject($obj));
        $this->assertTrue($listener->hasDeferredPropertyMapping($obj, $propertyMapping));

        $this->assertEquals($listener->getDeferredObjectNum(), 1);
    }


    /**
     * Tests the prePersist method.
     */
    public function testPrePersistWithNoFile()
    {
        $obj = new DummyEntity();
        $propertyMapping = $this->createFilledPropertyMapping(null);
        $this->setDataStorageObjectMapping($obj, $propertyMapping);

        $propertyMapping
            ->expects($this->once())
            ->method('setFileUploadPropertyValue')
            ->with(null);

        $listener = $this->getUploaderListener();
        $listener->prePersist(Mocks::getEventArgsMock($this));

        $this->assertFalse($listener->hasDeferredObject($obj));
        $this->assertFalse($listener->hasDeferredPropertyMapping($obj, $propertyMapping));

        $this->assertEquals($listener->getDeferredObjectNum(), 0);
    }


    /**
     * Test the preUpdate method - No new file, no current file
     */
    public function testPreUpdateNoFileNoCurrent()
    {
        $obj = new DummyEntity();

        $args = Mocks::getEventArgsMock($this);
        $propertyMapping = $this->createFilledPropertyMapping(null, 'file', null, $args);
        $this->setDataStorageObjectMapping($obj, $propertyMapping);

        $this->dataStorage->expects($this->once())->method('recomputeChangeSet')->with($args);

        $this->fileStorage->expects($this->never())->method('upload');
        $this->fileStorage->expects($this->never())->method('removeFile');


        $listener = $this->getUploaderListener();
        $listener->preUpdate($args);

        $this->assertEquals($listener->getDeferredObjectNum(), 0);
    }


    /**
     * Test the preUpdate method - With file, no current file
     */
    public function testPreUpdateNoFileWithCurrentFileExists()
    {
        $obj = new DummyEntity();
        $args = Mocks::getEventArgsMock($this);

        $propertyMapping = $this->createFilledPropertyMapping(null, 'file', array('fileName' => 'CURRENT_NAME'), $args);
        $this->setDataStorageObjectMapping($obj, $propertyMapping);


        $this->fileStorage
            ->expects($this->once())
            ->method('fileExists')
            ->with('/LOCATION/OF/CURRENT_NAME')
            ->will($this->returnValue(true));

        $propertyMapping
            ->expects($this->once())
            ->method('resolveFileName')
            ->with('CURRENT_NAME')
            ->will($this->returnValue('/LOCATION/OF/CURRENT_NAME'));


        $propertyMapping
            ->expects($this->once())
            ->method('setFileDataPropertyValue')
            ->with(array('fileName' => 'CURRENT_NAME'));


        $this->dataStorage->expects($this->once())->method('recomputeChangeSet')->with($args);
        $this->fileStorage->expects($this->never())->method('removeFile');

        $listener = $this->getUploaderListener();
        $listener->preUpdate($args);

        $this->assertEquals($listener->getDeferredObjectNum(), 0);
    }


    /**
     * Test the preUpdate method - With file, no current file
     */
    public function testPreUpdateWithFileNoCurrent()
    {
        $obj = new DummyEntity();
        $args = Mocks::getEventArgsMock($this);
        $file = Mocks::getFileMock($this);

        $propertyMapping = $this->createFilledPropertyMapping($file, 'file', null, $args);
        $this->setDataStorageObjectMapping($obj, $propertyMapping);


        $this->fileStorage
            ->expects($this->once())
            ->method('upload')
            ->with($propertyMapping, $file)
            ->will($this->returnValue(array('fileName' => '111')));

        $propertyMapping
            ->expects($this->once())
            ->method('setFileDataPropertyValue')
            ->with(array('fileName' => '111'));

        $this->dataStorage->expects($this->once())->method('recomputeChangeSet')->with($args);
        $this->fileStorage->expects($this->never())->method('removeFile');

        $listener = $this->getUploaderListener();
        $listener->preUpdate($args);

        $this->assertEquals($listener->getDeferredObjectNum(), 0);
    }


    /**
     * Test the preUpdate method - With file, with current file date, new file data != current file data
     */
    public function testPreUpdateWithFileWithCurrentNoSame()
    {
        $obj = new DummyEntity();
        $args = Mocks::getEventArgsMock($this);
        $file = Mocks::getFileMock($this);


        $propertyMapping = $this->createFilledPropertyMapping(
            $file, 'file', array('fileName' => 'CURRENT_NAME'), $args);

        $this->setDataStorageObjectMapping($obj, $propertyMapping);


        $this->fileStorage
            ->expects($this->once())
            ->method('upload')
            ->with($propertyMapping, $file)
            ->will($this->returnValue(array('fileName' => 'NEW_NAME')));

        $propertyMapping
            ->expects($this->once())
            ->method('resolveFileName')
            ->with('CURRENT_NAME')
            ->will($this->returnValue('/LOCATION/OF/CURRENT_NAME'));


        $this->fileStorage
            ->expects($this->once())
            ->method('isSameFile')
            ->with($file,   '/LOCATION/OF/CURRENT_NAME')
            ->will($this->returnValue(false));

        $this->fileStorage
            ->expects($this->once())
            ->method('removeFile')
            ->with('/LOCATION/OF/CURRENT_NAME');


        $propertyMapping
            ->expects($this->once())
            ->method('setFileDataPropertyValue')
            ->with(array('fileName' => 'NEW_NAME'));

        $this->dataStorage->expects($this->once())->method('recomputeChangeSet')->with($args);

        $listener = $this->getUploaderListener();
        $listener->preUpdate($args);

        $this->assertEquals($listener->getDeferredObjectNum(), 0);
    }


    /**
     * Test the preUpdate method - With file, with current file date, new file data == current file data
     */
    public function testPreUpdateWithFileWithCurrentIsSame()
    {
        $obj = new DummyEntity();
        $args = Mocks::getEventArgsMock($this);
        $file = Mocks::getFileMock($this);

        $propertyMapping = $this->createFilledPropertyMapping(
            $file, 'file', array('fileName' => 'CURRENT_NAME'), $args);

        $this->setDataStorageObjectMapping($obj, $propertyMapping);


        $propertyMapping
            ->expects($this->once())
            ->method('resolveFileName')
            ->with('CURRENT_NAME')
            ->will($this->returnValue('/LOCATION/OF/CURRENT_NAME'));

        $this->fileStorage
            ->expects($this->once())
            ->method('upload')
            ->with($propertyMapping, $file)
            ->will($this->returnValue(array('fileName' => 'CURRENT_NAME')));


        $this->fileStorage
            ->expects($this->once())
            ->method('isSameFile')
            ->with($file, '/LOCATION/OF/CURRENT_NAME')
            ->will($this->returnValue(true));

        $this->fileStorage
            ->expects($this->never())
            ->method('removeFile');


        $propertyMapping
            ->expects($this->once())
            ->method('setFileDataPropertyValue')
            ->with(array('fileName' => 'CURRENT_NAME'));

        $this->dataStorage->expects($this->once())->method('recomputeChangeSet')->with($args);

        $listener = $this->getUploaderListener();
        $listener->preUpdate($args);

        $this->assertEquals($listener->getDeferredObjectNum(), 0);
    }


    /**
     * Test the preUpdate method - With file, with current file date, new file data == current file data
     */
    public function testPreUpdateWithIphpDeletedFile()
    {
        $obj = new DummyEntity();
        $args = Mocks::getEventArgsMock($this);
        $file = Mocks::getIphpFileMock($this);
        $file->expects($this->once())->method('isDeleted')->will($this->returnValue(true));


        $propertyMapping = $this->createFilledPropertyMapping(
            $file, 'file', array('fileName' => 'CURRENT_NAME'), $args);

        $this->setDataStorageObjectMapping($obj, $propertyMapping);


        $propertyMapping
            ->expects($this->once())
            ->method('resolveFileName')
            ->with('CURRENT_NAME')
            ->will($this->returnValue('/LOCATION/OF/CURRENT_NAME'));



        $this->fileStorage
            ->expects($this->once())
            ->method('removeFile')
            ->with('/LOCATION/OF/CURRENT_NAME')
            ->will($this->returnValue(true));

        $propertyMapping
            ->expects($this->once())
            ->method('setFileDataPropertyValue')
            ->with(null);

        $this->fileStorage->expects($this->never())->method('upload');
        $this->dataStorage->expects($this->once())->method('recomputeChangeSet')->with($args);

        $listener = $this->getUploaderListener();
        $listener->preUpdate($args);

        $this->assertEquals($listener->getDeferredObjectNum(), 0);
    }


    /**
     * Test the postFlush method no deferred files
     */
    public function testPostFlushNoDeferred()
    {
        $args = Mocks::getEventArgsMock($this);

        $this->fileStorage->expects($this->never())->method('upload');
        $this->dataStorage->expects($this->never())->method('postFlush');

        $listener = $this->getUploaderListener();
        $this->assertEquals($listener->getDeferredObjectNum(), 0);
        $listener->postFlush($args);
        $this->assertEquals($listener->getDeferredObjectNum(), 0);
    }


    /**
     * Test the postFlush method has deferred files
     */
    public function testPostFlushHasDeferred()
    {
        $args = Mocks::getEventArgsMock($this);
        $obj = new DummyEntity();
        $file = Mocks::getFileMock($this);

        $propertyMapping = $this->createFilledPropertyMapping($file);
        $this->setDataStorageObjectMapping($obj, $propertyMapping);

        $listener = $this->getUploaderListener();
        $listener->prePersist(Mocks::getEventArgsMock($this));
        $this->assertEquals($listener->getDeferredObjectNum(), 1);


        $this->fileStorage->expects($this->once())
            ->method('upload')
            ->with($propertyMapping, $file)
            ->will($this->returnValue(array('fileName' => 'NEW_NAME')));

        $propertyMapping->expects($this->once())
            ->method('setFileDataPropertyValue')
            ->with(array('fileName' => 'NEW_NAME'));

        $this->dataStorage->expects($this->once())->method('postFlush');

        $listener->postFlush($args);
        $this->assertEquals($listener->getDeferredObjectNum(), 0);
    }


    public function testPostRemoveDelete()
    {
        $obj = new DummyEntity();
        $args = Mocks::getEventArgsMock($this);

        $propertyMapping = Mocks::getPropertyMappingMock($this);
        $this->setDataStorageObjectMapping($obj, $propertyMapping);

        $propertyMapping->expects($this->once())
            ->method('getDeleteOnRemove')
            ->will($this->returnValue(true));



        $propertyMapping
            ->expects($this->once())
            ->method('resolveFileName')

            ->will($this->returnValue('/LOCATION/OF/CURRENT_NAME'));




        $this->fileStorage->expects($this->once())
            ->method('removeFile')
            ->with('/LOCATION/OF/CURRENT_NAME');


        $listener = $this->getUploaderListener();
        $listener->postRemove($args);
    }


    public function testPostRemoveNoDelete()
    {
        $obj = new DummyEntity();
        $args = Mocks::getEventArgsMock($this);

        $propertyMapping = Mocks::getPropertyMappingMock($this);
        $this->setDataStorageObjectMapping($obj, $propertyMapping);

        $propertyMapping->expects($this->once())
            ->method('getDeleteOnRemove')
            ->will($this->returnValue(false));

        $this->fileStorage->expects($this->never())
            ->method('removeFile');

        $listener = $this->getUploaderListener();
        $listener->postRemove($args);
    }

}
