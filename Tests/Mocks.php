<?php

namespace Iphp\FileStoreBundle\Tests;

/**
 * @author Vitiko <vitiko@mail.ru>
 */
use Iphp\FileStoreBundle\Naming\NamerServiceInvoker;
use Iphp\FileStoreBundle\Driver\AnnotationDriver;

class Mocks
{
    /**
     * Creates a mock args
     *
     * @return \Doctrine\Common\EventArgs EventArgs
     */
    static function getEventArgsMock(\PHPUnit_Framework_TestCase $testCase)
    {
        return $testCase->getMockBuilder('Doctrine\Common\EventArgs')
            ->disableOriginalConstructor()
            ->getMock();
    }


    static function getContainerMock(\PHPUnit_Framework_TestCase $testCase)
    {
        return $testCase->getMockBuilder('Symfony\Component\DependencyInjection\ContainerInterface')
            ->disableOriginalConstructor()
            ->getMock();
    }


    /**
     * Creates a mock uploadable
     *
     * @return \Iphp\FileStoreBundle\Mapping\Annotation\Uploadable Uploadable
     */
    static function getUploadableMock(\PHPUnit_Framework_TestCase $testCase)
    {
        return $testCase->getMockBuilder('Iphp\FileStoreBundle\Mapping\Annotation\Uploadable')
            ->disableOriginalConstructor()
            ->getMock();
    }


    /**
     * Creates a mock uploadable field
     *
     * @return \Iphp\FileStoreBundle\Mapping\Annotation\UploadableField UploadableField
     */
    static function getUploadableFieldMock(\PHPUnit_Framework_TestCase $testCase)
    {
        return $testCase->getMockBuilder('Iphp\FileStoreBundle\Mapping\Annotation\UploadableField')
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * Creates a mock property mapping
     *
     * @return \Iphp\FileStoreBundle\Mapping\PropertyMapping  UploadableField
     */
    static function getPropertyMappingMock(\PHPUnit_Framework_TestCase $testCase)
    {
        return $testCase->getMockBuilder('Iphp\FileStoreBundle\Mapping\PropertyMapping')
            ->disableOriginalConstructor()
            ->getMock();
    }


    /**
     * Creates a mock annotation driver.
     *
     * @return \Iphp\FileStoreBundle\Driver\AnnotationDriver The driver.
     */
    static function getAnnotationDriverMock(\PHPUnit_Framework_TestCase $testCase)
    {
        return $testCase->getMockBuilder('Iphp\FileStoreBundle\Driver\AnnotationDriver')
            ->disableOriginalConstructor()
            ->getMock();
    }


    /**
     * Creates a mock for namer service invoker.
     *
     * @return \Iphp\FileStoreBundle\Naming\NamerServiceInvoker mock namer service invoker
     */
    static function getNamerServiceInvokerMock(\PHPUnit_Framework_TestCase $testCase)
    {
        return $testCase->getMockBuilder('Iphp\FileStoreBundle\Naming\NamerServiceInvoker')
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * Creates a mock data storage
     *
     * @return \Iphp\FileStoreBundle\DataStorage\DataStorageInterface The mock data storage
     */
    static function getDataStorageMock(\PHPUnit_Framework_TestCase $testCase)
    {
        return $testCase->getMockBuilder('Iphp\FileStoreBundle\DataStorage\DataStorageInterface')
            ->disableOriginalConstructor()
            ->getMock();
    }


    /**
     * Creates a mock storage.
     *
     * @return \Iphp\FileStoreBundle\FileStorage\FileStorageInterface mock file storage
     */
    static function getFileStorageMock(\PHPUnit_Framework_TestCase $testCase)
    {
        return $testCase->getMockBuilder('Iphp\FileStoreBundle\FileStorage\FileStorageInterface')
            ->disableOriginalConstructor()
            ->getMock();
    }


    /**
     * Creates a mock mapping factory.
     *
     * @return \Iphp\FileStoreBundle\Mapping\PropertyMappingFactory The factory.
     */
    static function getPropertyMappingFactoryMock(\PHPUnit_Framework_TestCase $testCase,
                                                  NamerServiceInvoker $namerServiceInvoker,
                                                  AnnotationDriver $driver,
                                                  $mappingsConfig = array())
    {
        return $testCase->getMockBuilder('Iphp\FileStoreBundle\Mapping\PropertyMappingFactory')
            ->setConstructorArgs(array($namerServiceInvoker, $driver, $mappingsConfig))
            ->getMock();

    }

    /**
     * Creates a mock file
     *
     * @return \Symfony\Component\HttpFoundation\File\File The file.
     */
    static function getFileMock(\PHPUnit_Framework_TestCase $testCase)
    {
        return $testCase->getMockBuilder('Symfony\Component\HttpFoundation\File\File')
            ->setConstructorArgs(array(null, false))
            ->getMock();
    }


    /**
     * Creates a mock of uploadedFile
     *
     * @return \Symfony\Component\HttpFoundation\File\UploadedFile uploaded file
     */
    static function getUploadedFileMock(\PHPUnit_Framework_TestCase $testCase)
    {
        return $testCase->getMockBuilder('Symfony\Component\HttpFoundation\File\UploadedFile')
            ->disableOriginalConstructor()
            ->getMock();
    }


    /**
     * Creates a mock file
     *
     * @return \Iphp\FileStoreBundle\File\File The file.
     */
    static function getIphpFileMock(\PHPUnit_Framework_TestCase $testCase)
    {
        return $testCase->getMockBuilder('Iphp\FileStoreBundle\File\File')
            ->setConstructorArgs(array(null, false))
            ->getMock();
    }


}
