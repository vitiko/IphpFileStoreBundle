<?php
namespace Iphp\FileStoreBundle\Tests\Mapping;

use Iphp\FileStoreBundle\Tests\DummyEntity;
use Iphp\FileStoreBundle\Mapping\PropertyMapping;
use Iphp\FileStoreBundle\Tests\DummyEntitySeparateDataField;
use Iphp\FileStoreBundle\Tests\Mocks;

class PropertyMappingTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Iphp\FileStoreBundle\Mapping\PropertyMapping
     */
    protected $object;


    /**
     * @var \Iphp\FileStoreBundle\Naming\NamerServiceInvoker
     */
    protected $namerServiceInvoker;


    /**
     * Sets up the test.
     */
    public function setUp()
    {
        $this->namerServiceInvoker = Mocks::getNamerServiceInvokerMock($this);
        $this->fileStorage = Mocks::getFileStorageMock($this);
    }


    /**
     * @param $object
     * @param array $mergeConfig
     * @return PropertyMapping
     */
    public function getPropertyMapping($object, $mergeConfig = array())
    {
        $config = $object->defaultFileStoreConfig['dummy_file'];
        if ($mergeConfig) $config = array_merge($config, $mergeConfig);

        return new PropertyMapping($object, $config, $this->namerServiceInvoker);
    }

    public function testUseDirectoryNamerNoExists()
    {
        $propertyMapping = $this->getPropertyMapping(new DummyEntity());

        $this->assertFalse($propertyMapping->hasDirectoryNamer());
        $this->assertSame($propertyMapping->useDirectoryNamer('file name', 'file name'), '');
    }


    public function testUseDirectoryNamerExists()
    {
        $propertyMapping = $this->getPropertyMapping(new DummyEntity(),
            array('directory_namer' =>
            array('entityName' => array('service' => 'iphp.filestore.directory_namer.default'))));


        $this->assertTrue($propertyMapping->hasDirectoryNamer());

        $this->namerServiceInvoker
            ->expects($this->once())
            ->method('rename')
            ->with('iphp.filestore.directory_namer.default', 'entityName', $propertyMapping, 'file name', array())
            ->will($this->returnValue('DummyFile'));

        $this->assertSame($propertyMapping->useDirectoryNamer('file name', 'file name'), '/DummyFile');
    }


    public function testNeedResolveCollisionFileNoExists()
    {
        $propertyMapping = $this->getPropertyMapping(new DummyEntity());

        $this->fileStorage
            ->expects($this->once())
            ->method('fileExists')
            ->with( '/www/web/images/123')
            ->will($this->returnValue(false));






        //by default overwriteDuplicates = false
        $this->assertFalse($propertyMapping->needResolveCollision('123', $this->fileStorage));
    }


    public function testNeedResolveCollisionOverwriteDuplicates()
    {
        $propertyMapping = $this->getPropertyMapping(new DummyEntity(), array('overwrite_duplicates' => true));
        $this->fileStorage->expects($this->never())->method('fileExists');

        //by default overwriteDuplicates = false
        $this->assertFalse($propertyMapping->needResolveCollision('123', $this->fileStorage));
    }


    public function testPrepareFileName()
    {
        $propertyMapping = $this->getPropertyMapping(new DummyEntity());

        $this->namerServiceInvoker
            ->expects($this->once())
            ->method('rename')
            ->with('iphp.filestore.namer.default', 'translit', $propertyMapping, 'ab cd', array())
            ->will($this->returnValue('ab-cd'));


        $this->fileStorage
            ->expects($this->once())
            ->method('fileExists')
            ->with('/www/web/images/ab-cd')
            ->will($this->returnValue(false));


        //Default false
        $this->assertFalse($propertyMapping->isStoreFullDir());
        //From config
        $this->assertEquals($propertyMapping->getUploadPath(), '/images');

        $this->assertSame($propertyMapping->prepareFileName('ab cd', $this->fileStorage),
            array('/ab-cd', '/images/ab-cd'));
    }


    /**
     * Once resolved collision
     */
    public function testPrepareFileNameWithResolveCollision()
    {
        $propertyMapping = $this->getPropertyMapping(new DummyEntity());


        //service iphp.filestore.namer.default, method translit  will invoke once,
        //"ab cd" will be translited to "ab-cd"
        $this->namerServiceInvoker
            ->expects($this->once())
            ->method('rename')
            ->with('iphp.filestore.namer.default', 'translit', $propertyMapping, 'ab cd', array())
            ->will($this->returnValue('ab-cd'));


        $this->namerServiceInvoker
            ->expects($this->once())
            ->method('resolveCollision')
            ->with('iphp.filestore.namer.default', 'ab-cd', 1)
            ->will($this->returnValue('ab-cd_1'));


        $fileExistsCalls = 0;

        $this->fileStorage
            ->expects($this->any())
            ->method('fileExists')

            ->will($this->returnCallback(function() use (&$fileExistsCalls)
        {
            $args = func_get_args();
            $fileExistsCalls++;
            return $args[0] == '/www/web/images/ab-cd' ? true : false;
        }));


        $this->assertSame($propertyMapping->prepareFileName('ab cd', $this->fileStorage),
            array('/ab-cd_1', '/images/ab-cd_1'));

        $this->assertSame($fileExistsCalls, 2);

    }


    /**
     * Not resolved collision
     * @expectedException \Exception
     */
    public function testPrepareFileNameWithoutResolveCollision()
    {
        $propertyMapping = $this->getPropertyMapping(new DummyEntity());

        //service iphp.filestore.namer.default, method translit  will invoke once,
        //"ab cd" will be translited to "ab-cd"
        $this->namerServiceInvoker
            ->expects($this->once())
            ->method('rename')
            ->with('iphp.filestore.namer.default', 'translit', $propertyMapping, 'ab cd', array())
            ->will($this->returnValue('ab-cd'));


        //always return same name - no resolve
        $this->namerServiceInvoker
            ->expects($this->any())
            ->method('resolveCollision')
            ->will($this->returnValue('ab-cd'));

        $this->fileStorage
            ->expects($this->any())
            ->method('fileExists')
            ->will($this->returnCallback(function()
        {
            $args = func_get_args();

            return $args[0] == '/www/web/images/ab-cd' ? true : false;
        }));


        $propertyMapping->prepareFileName('ab cd', $this->fileStorage);

    }


    /**
     * Exception about need namer for resolving collisions
     * @expectedException \Exception
     */
    function testResolveFileCollisionNoNamer()
    {
        $propertyMapping = $this->getPropertyMapping(new DummyEntity(), array('namer' => null));

        $this->namerServiceInvoker->expects($this->never())->method('rename');
        $this->fileStorage
            ->expects($this->once())
            ->method('fileExists')
        //No translited
            ->with('/www/web/images/ab cd')
            ->will($this->returnValue(true));


        $propertyMapping->prepareFileName('ab cd', $this->fileStorage);
    }


    public function testGetPropertyName()
    {

        $obj = new DummyEntitySeparateDataField();
        $class = new \ReflectionClass($obj);
        $file = Mocks::getFileMock($this);


        $propertyMapping = $this->getPropertyMapping($obj);

        $propertyMapping->setFileUploadProperty($class->getProperty('file'));
        $this->assertSame($propertyMapping->getFileUploadPropertyName(), 'file');

        $propertyMapping->setFileDataProperty($class->getProperty('file_data'));
        $this->assertSame($propertyMapping->getFileDataPropertyName(), 'file_data');


        $propertyMapping->setFileUploadPropertyValue($file);
        $this->assertSame(  $propertyMapping->getFileUploadPropertyValue(), $file);


        $propertyMapping->setFileDataPropertyValue(array(1));
        $this->assertSame(  $propertyMapping->getFileDataPropertyValue(), array(1));
    }



    public function testResolveFileName()
    {
        $obj = new DummyEntitySeparateDataField();
        $class = new \ReflectionClass($obj);




        $propertyMapping = $this->getPropertyMapping($obj);

        $propertyMapping->setFileDataProperty($class->getProperty('file_data'));
        $propertyMapping->setFileDataPropertyValue(array('fileName' => 123));


        $this->assertSame ($propertyMapping->resolveFileName(), $propertyMapping->getUploadDir() . '/123');

    }
}
