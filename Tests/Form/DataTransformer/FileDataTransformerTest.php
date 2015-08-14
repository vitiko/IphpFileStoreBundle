<?php
namespace Iphp\FileStoreBundle\Tests\Form\DataTransformer;


use Iphp\FileStoreBundle\Form\DataTransformer\FileDataTransformer;
use Iphp\FileStoreBundle\Tests\Mocks;

class FileDataTransformerTest extends \PHPUnit_Framework_TestCase
{


    /**
     * @var  \Iphp\FileStoreBundle\Form\DataTransformer\FileDataTransformer;
     */
    protected $transformer;

    /**
     * @var \Iphp\FileStoreBundle\FileStorage\FileStorageInterface
     */
    protected $fileStorage;

    public function setUp()
    {

        $this->fileStorage = Mocks::getFileStorageMock($this);
        $this->transformer = new  FileDataTransformer($this->fileStorage);
    }


    function testTransform()

    {
        $this->assertSame($this->transformer->transform(array(1, 2, 3)), array(1, 2, 3));
    }


    function testReverseTransformDeleteFile()
    {
        $propertyMapping = Mocks::getPropertyMappingMock($this);
        $file = Mocks::getFileMock($this);
        $this->transformer->setMapping($propertyMapping, FileDataTransformer::MODE_UPLOAD_FIELD);


        $propertyMapping->expects($this->once())
            ->method('resolveFileName')
            ->with('123.jpg')
            ->will($this->returnValue('/path/to/123.jpg'));


        $this->fileStorage
            ->expects($this->once())
            ->method('removeFile')
            ->with('/path/to/123.jpg');

        $this->assertSame(
            $this->transformer->reverseTransform(array('delete' => 1, 'file' => $file, 'fileName' => '123.jpg')),
            $file);
    }


    function testReverseTransformNoDeleteFile()
    {
        $propertyMapping = Mocks::getPropertyMappingMock($this);
        $file = Mocks::getFileMock($this);
        $this->transformer->setMapping($propertyMapping, FileDataTransformer::MODE_UPLOAD_FIELD);

        $this->fileStorage
            ->expects($this->never())
            ->method('removeFile');

        $this->assertSame(
            $this->transformer->reverseTransform(array('delete' => 0, 'file' => $file, 'fileName' => '123.jpg')),
            $file);
    }

}