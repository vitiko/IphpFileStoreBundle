<?php

namespace Iphp\FileStoreBundle\Tests\Driver;

use Iphp\FileStoreBundle\Driver\AnnotationDriver;
use Iphp\FileStoreBundle\Tests\ChildOfDummyEntity;
use Iphp\FileStoreBundle\Tests\Mocks;
use Iphp\FileStoreBundle\Mapping\Annotation\Uploadable;
use Iphp\FileStoreBundle\Mapping\Annotation\UploadableField;
use Iphp\FileStoreBundle\Tests\DummyEntity;
use Iphp\FileStoreBundle\Tests\TwoFieldsDummyEntity;

/**
 * AnnotationDriverTest.
 *
 * @author Vitiko <vitiko@mail.ru>
 */
class AnnotationDriverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test that the driver can correctly read the Uploadable
     * annotation.
     */
    public function testReadUploadableAnnotation()
    {
        $uploadable = Mocks::getUploadableMock($this);


        $reader = $this->getMock('Doctrine\Common\Annotations\Reader');
        $reader
            ->expects($this->once())
            ->method('getClassAnnotation')
            ->will($this->returnValue($uploadable));

        $entity = new DummyEntity();
        $driver = new AnnotationDriver($reader);
        $annot = $driver->readUploadable(new \ReflectionClass($entity));

        $this->assertEquals($uploadable, $annot);
    }


    public function testReadUploadableAnnotationFromParent()
    {
        $uploadable = Mocks::getUploadableMock($this);
        $reader = $this->getMock('Doctrine\Common\Annotations\Reader');


        $reader
            ->expects($this->any())
            ->method('getClassAnnotation')
            ->will($this->returnCallBack ( function() use ( $uploadable) {

            $args = func_get_args();

            if ('Iphp\\FileStoreBundle\\Tests\\ChildOfDummyEntity' === $args[0]->getName()) return null;
            if ('Iphp\\FileStoreBundle\\Tests\\DummyEntity' === $args[0]->getName()) return $uploadable;

        }));

        $entity = new ChildOfDummyEntity();
        $driver = new AnnotationDriver($reader);

        $annot = $driver->readUploadable(new \ReflectionClass($entity));

        $this->assertEquals($uploadable, $annot);

    }






    /**
     * Tests that the driver returns null when no Uploadable annotation
     * is found.
     */
    public function testReadUploadableAnnotationReturnsNullWhenNonePresent()
    {
        $reader = $this->getMock('Doctrine\Common\Annotations\Reader');
        $reader
            ->expects($this->once())
            ->method('getClassAnnotation')
            ->will($this->returnValue(null));

        $entity = new DummyEntity();
        $driver = new AnnotationDriver($reader);
        $annot = $driver->readUploadable(new \ReflectionClass($entity));

        $this->assertEquals(null, $annot);
    }

    /**
     * Tests that the driver correctly reads one UploadableField
     * property.
     */
    public function testReadOneUploadableField()
    {
        $uploadableField = Mocks::getUploadableFieldMock($this);

        $uploadableField
            ->expects($this->once())
            ->method('setFileUploadPropertyName');

        $entity = new DummyEntity();
        $class = new \ReflectionClass($entity);

        $reader = $this->getMock('Doctrine\Common\Annotations\Reader');
        $reader
            ->expects($this->any())
            ->method('getPropertyAnnotation')
            ->will($this->returnCallback(function() use ($class, $uploadableField) {
            $args = func_get_args();

            if ( $args[0]->class === $class->getName() && 'file' === $args[0]->getName()) {
                return $uploadableField;
            }

            return null;
        }));

        $driver = new AnnotationDriver($reader);
        $fields = $driver->readUploadableFields($class);

        $this->assertEquals(1, count($fields));
    }




    public function testReadOneUploadableFieldFromParent()
    {
        $uploadableField = Mocks::getUploadableFieldMock($this);
        $uploadableField
            ->expects($this->once())
            ->method('setFileUploadPropertyName')
            ->with ('file');


        $entity = new ChildOfDummyEntity();
        $class = new \ReflectionClass($entity);





        $reader = $this->getMock('Doctrine\Common\Annotations\Reader');
        $reader
            ->expects($this->any())
            ->method('getPropertyAnnotation')
            ->will($this->returnCallback(function() use (   $entity , $uploadableField) {
            $args = func_get_args();
            if (get_parent_class($entity) === $args[0]->class && 'file' === $args[0]->getName()) {
                return $uploadableField;
            }

            return null;
        }));


        $driver = new AnnotationDriver($reader);
        $fields = $driver->readUploadableFields($class);

        $this->assertEquals(1, count($fields));
    }



    /**
     * Tests that the driver correctly reads one UploadableField
     * property.
     */
    public function testReadUploadableFieldSingle()
    {
        $uploadableField = Mocks::getUploadableFieldMock($this);
        $uploadableField->expects($this->once())->method('setFileUploadPropertyName');

        $entity = new DummyEntity();
        $class = new \ReflectionClass($entity);

        $reader = $this->getMock('Doctrine\Common\Annotations\Reader');
        $reader
            ->expects($this->any())
            ->method('getPropertyAnnotation')
            ->will($this->returnCallback(function() use ($uploadableField) {
            $args = func_get_args();
            if ('file' === $args[0]->getName()) {
                return $uploadableField;
            }

            return null;
        }));

        $driver = new AnnotationDriver($reader);
        $this->assertEquals ($driver->readUploadableField($class, 'file'), $uploadableField);


    }


    /**
     * Tests that the driver correctly reads one UploadableField
     * property.
     */
    public function testReadUploadableFieldNoMapping()
    {
        $uploadableField = Mocks::getUploadableFieldMock($this);
        $uploadableField->expects($this->never())->method('setPropertyName');

        $entity = new DummyEntity();
        $class = new \ReflectionClass($entity);

        $reader = $this->getMock('Doctrine\Common\Annotations\Reader');
        $reader
            ->expects($this->any())
            ->method('getPropertyAnnotation')
            ->will($this->returnCallback(function() use ($uploadableField) {
            $args = func_get_args();
            if ('file' === $args[0]->getName()) {
                return null;
            }
            return null;
        }));

        $driver = new AnnotationDriver($reader);
        $this->assertEquals ($driver->readUploadableField($class, 'file'), null);
    }


 


    /**
     * Test that the driver correctly reads two UploadableField
     * properties.
     */
    public function testReadTwoUploadableFields()
    {
        $fileField = Mocks::getUploadableFieldMock($this);
        $fileField->expects($this->once())->method('setFileUploadPropertyName');

        $imageField = Mocks::getUploadableFieldMock($this);
        $imageField->expects($this->once())->method('setFileUploadPropertyName');

        $entity = new TwoFieldsDummyEntity();
        $class = new \ReflectionClass($entity);

        $reader = $this->getMock('Doctrine\Common\Annotations\Reader');
        $reader
            ->expects($this->any())
            ->method('getPropertyAnnotation')
            ->will($this->returnCallback(function() use ($fileField, $imageField) {
            $args = func_get_args();
            if ('file' === $args[0]->getName()) {
                return $fileField;
            } elseif ('image' === $args[0]->getName()) {
                return $imageField;
            }

            return null;
        }));

        $driver = new AnnotationDriver($reader);
        $fields = $driver->readUploadableFields($class);

        $this->assertEquals(2, count($fields));
    }

    /**
     * Test that the driver reads zero UploadableField
     * properties when none exist.
     */
    public function testReadNoUploadableFieldsWhenNoneExist()
    {
        $entity = new DummyEntity();
        $class = new \ReflectionClass($entity);

        $reader = $this->getMock('Doctrine\Common\Annotations\Reader');
        $reader
            ->expects($this->any())
            ->method('getPropertyAnnotation')
            ->will($this->returnValue(null));

        $driver = new AnnotationDriver($reader);
        $fields = $driver->readUploadableFields($class);

        $this->assertEquals(0, count($fields));
    }

}
