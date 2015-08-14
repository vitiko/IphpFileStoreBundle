<?php

namespace Iphp\FileStoreBundle\Tests\Mapping;

use Iphp\FileStoreBundle\Mapping\PropertyMappingFactory;
use Iphp\FileStoreBundle\Tests\DummyEntity;
use Iphp\FileStoreBundle\Tests\Mocks;

/**
 * PropertyMappingFactoryTest.
 *
 * @author Vitiko <vitiko@mail.ru>
 */
class PropertyMappingFactoryTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var \Iphp\FileStoreBundle\Naming\NamerServiceInvoker
     */
    protected $namerServiceInvoker;


    /**
     * @var \Iphp\FileStoreBundle\Driver\AnnotationDriver $driver
     */
    protected $driver;


    /**
     * Sets up the test.
     */
    public function setUp()
    {
        $this->driver = Mocks::getAnnotationDriverMock($this);
        $this->namerServiceInvoker = Mocks::getNamerServiceInvokerMock($this);
    }

    /**
     *  if a non uploadable object is passed in - return array()
     *
     */
    public function testFromObjectThrowsExceptionIfNotUploadable()
    {
        $obj = new \StdClass();
        $class = new \ReflectionClass($obj);

        $this->driver
            ->expects($this->once())
            ->method('readUploadable')
            ->will($this->returnValue(null));

        $factory = new PropertyMappingFactory($this->namerServiceInvoker, $this->driver, array());
        $this->assertEquals($factory->getMappingsFromObject($obj, $class), array());
    }

    /**
     * Test the fromObject method with one uploadable
     * field.
     */
    public function testFromObjectOneField()
    {
        $obj = new DummyEntity();
        $class = new \ReflectionClass($obj);

        $mappingsConfig = array(
            'dummy_file' => array(
                'upload_dir' => '/www/web/images',
                'upload_path' => '/images'
            )
        );

        $uploadable = Mocks::getUploadableMock($this);
        $fileField = Mocks::getUploadableFieldMock($this);

        $fileField
            ->expects($this->any())
            ->method('getMapping')
            ->will($this->returnValue('dummy_file'));

        $fileField
            ->expects($this->once())
            ->method('getFileUploadPropertyName')
            ->will($this->returnValue('file'));

        $fileField
            ->expects($this->once())
            ->method('getFileDataPropertyName')
            ->will($this->returnValue('file'));


        $this->driver
            ->expects($this->once())
            ->method('readUploadable')
            ->with($class)
            ->will($this->returnValue($uploadable));

        $this->driver
            ->expects($this->once())
            ->method('readUploadableFields')
            ->with($class)
            ->will($this->returnValue(array($fileField)));

        $factory = new PropertyMappingFactory($this->namerServiceInvoker, $this->driver, $mappingsConfig);
        $mappings = $factory->getMappingsFromObject($obj, $class);

        $this->assertEquals(1, count($mappings));

        if (count($mappings) > 0) {
            $mapping = $mappings[0];

            $this->assertEquals('dummy_file', $mapping->getMappingName());
            $this->assertEquals('/www/web/images', $mapping->getUploadDir());
            $this->assertEquals('/images', $mapping->getUploadPath());
            $this->assertTrue($mapping->getDeleteOnRemove());
            $this->assertFalse($mapping->hasNamer());
            $this->assertFalse($mapping->hasDirectoryNamer());
            $this->assertFalse($mapping->isStoreFullDir());
            $this->assertFalse($mapping->isOverwriteDuplicates());

            $this->assertSame($mapping->getObj(), $obj);

        }
    }

    /**
     * Test that an exception is thrown when an invalid mapping name
     * is specified.
     *
     * @expectedException \InvalidArgumentException
     */
    public function testThrowsExceptionOnInvalidMappingName()
    {
        $obj = new DummyEntity();
        $class = new \ReflectionClass($obj);

        $mappingsConfig = array(
            'bad_name' => array()
        );

        $uploadable = Mocks::getUploadableMock($this);
        $fileField = Mocks::getUploadableFieldMock($this);

        $fileField
            ->expects($this->any())
            ->method('getMapping')
            ->will($this->returnValue('dummy_file'));


        $this->driver
            ->expects($this->once())
            ->method('readUploadable')
            ->with($class)
            ->will($this->returnValue($uploadable));

        $this->driver
            ->expects($this->once())
            ->method('readUploadableFields')
            ->with($class)
            ->will($this->returnValue(array($fileField)));

        $factory = new PropertyMappingFactory($this->namerServiceInvoker, $this->driver, $mappingsConfig);
        $mappings = $factory->getMappingsFromObject($obj, $class);
    }

    /**
     * Test that the fromField method returns null when an invalid
     * field name is specified.
     */
    public function testFromFieldReturnsNullOnInvalidFieldName()
    {
        $obj = new DummyEntity();
        $class = new \ReflectionClass($obj);

        $uploadable = Mocks::getUploadableMock($this);

        $this->driver
            ->expects($this->once())
            ->method('readUploadable')
            ->with($class)
            ->will($this->returnValue($uploadable));

        $this->driver
            ->expects($this->once())
            ->method('readUploadableField')
            ->with($class)
            ->will($this->returnValue(null));

        $this->driver
            ->expects($this->once())
            ->method('readUploadableFields')
            ->with($class)
            ->will($this->returnValue(array()));


        $factory = new PropertyMappingFactory($this->namerServiceInvoker, $this->driver, array());
        $mapping = $factory->getMappingFromField($obj, $class, 'oops');

        $this->assertNull($mapping);
    }


    /**
     */
    public function testGetMappingFromField()
    {
        $obj = new DummyEntity();
        $class = new \ReflectionClass($obj);

        $uploadable = Mocks::getUploadableMock($this);
        $fileField = Mocks::getUploadableFieldMock($this);


        $mappingsConfig = array(
            'dummy_file' => array(
                'upload_dir' => '/www/web/images',
                'upload_path' => '/images'
            )
        );


        $fileField
            ->expects($this->any())
            ->method('getMapping')
            ->will($this->returnValue('dummy_file'));

        $fileField
            ->expects($this->once())
            ->method('getFileUploadPropertyName')
            ->will($this->returnValue('file'));

        $fileField
            ->expects($this->once())
            ->method('getFileDataPropertyName')
            ->will($this->returnValue('file'));

        $this->driver
            ->expects($this->once())
            ->method('readUploadable')
            ->with($class)
            ->will($this->returnValue($uploadable));

        $this->driver
            ->expects($this->once())
            ->method('readUploadableField')
            ->with($class)
            ->will($this->returnValue($fileField));


        $factory = new PropertyMappingFactory($this->namerServiceInvoker, $this->driver, $mappingsConfig);
        $mapping = $factory->getMappingFromField($obj, $class, 'file');

        $this->assertSame($mapping->getObj(), $obj);
        $this->assertEquals('dummy_file', $mapping->getMappingName());
        $this->assertEquals('/www/web/images', $mapping->getUploadDir());
        $this->assertEquals('/images', $mapping->getUploadPath());
        $this->assertTrue($mapping->getDeleteOnRemove());
        $this->assertFalse($mapping->hasNamer());
        $this->assertFalse($mapping->hasDirectoryNamer());
        $this->assertFalse($mapping->isStoreFullDir());
        $this->assertFalse($mapping->isOverwriteDuplicates());
    }


    public function testConfiguredNamerRetrievedFromInvoker()
    {
        $obj = new DummyEntity();
        $class = new \ReflectionClass($obj);

        $mappingsConfig = array(
            'dummy_file' => array(
                'upload_dir' => '/www/web/images',
                'upload_path' => '/images',
                'namer' => array('translit' => array('service' => 'iphp.filestore.namer.default'))
            )

        );

        $uploadable = Mocks::getUploadableMock($this);
        $fileField = Mocks::getUploadableFieldMock($this);

        $fileField
            ->expects($this->any())
            ->method('getMapping')
            ->will($this->returnValue('dummy_file'));

        $fileField
            ->expects($this->once())
            ->method('getFileUploadPropertyName')
            ->will($this->returnValue('file'));

        $fileField
            ->expects($this->once())
            ->method('getFileDataPropertyName')
            ->will($this->returnValue('file'));


        $this->driver
            ->expects($this->once())
            ->method('readUploadable')
            ->with($class)
            ->will($this->returnValue($uploadable));

        $this->driver
            ->expects($this->once())
            ->method('readUploadableFields')
            ->with($class)
            ->will($this->returnValue(array($fileField)));

        $factory = new PropertyMappingFactory($this->namerServiceInvoker, $this->driver, $mappingsConfig);
        $mappings = $factory->getMappingsFromObject($obj, $class);

        $this->assertEquals(1, count($mappings));

        if (count($mappings) > 0) {
            $mapping = $mappings[0];

            $this->namerServiceInvoker
                ->expects($this->once())
                ->method('rename')
                ->with('iphp.filestore.namer.default', 'translit', $mapping, 'ab cde', array())
                ->will($this->returnValue('ab-cde'));


            $this->assertEquals($mapping->useFileNamer('ab cde'), 'ab-cde');
            $this->assertTrue($mapping->hasNamer());
        }
    }


}
