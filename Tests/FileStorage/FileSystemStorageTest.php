<?php

namespace Iphp\FileStoreBundle\Tests\FileStorage;

use Iphp\FileStoreBundle\FileStorage\FileSystemStorage;
use Symfony\Component\HttpFoundation\File\File;
use org\bovigo\vfs\vfsStreamDirectory;
use org\bovigo\vfs\vfsStreamWrapper;
use org\bovigo\vfs\visitor\vfsStreamStructureVisitor;
use org\bovigo\vfs\vfsStream;
use Iphp\FileStoreBundle\Tests\Mocks;
use Iphp\FileStoreBundle\Tests\DummyEntity;

/**
 * FileSystemStorageTest.
 *
 * @author Vitiko <vitiko@mail.ru>
 */
class FileSystemStorageTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var \Iphp\FileStoreBundle\FileStorage\FileSystemStorage;
     */
    protected $storage;


    protected $uploadedImageFile;

    protected $targetImageFileExistingDir;


    protected $targetImageFileNewDir;


    protected $targetImageFileExistingReadonlyDir;

    /**
     * Sets up the test.
     */
    public function setUp()
    {
        $this->storage = new FileSystemStorage();

        vfsStreamWrapper::register();
        vfsStreamWrapper::setRoot(new vfsStreamDirectory('site_root'));
        vfsStream::setup('site_root', 0700, array(

            'uploaded' => array('123.jpg' => file_get_contents(__DIR__ . '/123.jpg')),
            'web' => array(
                'images' => array(),
               // 'images_readonly' => array()
            )

        ));

        if (version_compare(PHP_VERSION, '5.4.0') >= 0)
        {
         mkdir (vfsStream::url('site_root/web/images-readonly'),0700);
         chown (vfsStream::url('site_root/web/images-readonly'), vfsStream::GROUP_USER_1);
        }

        $this->uploadedImageFile = vfsStream::url('site_root/uploaded/123.jpg');
        $this->targetImageFileExistingDir = vfsStream::url('site_root/web/images/123.jpg');
        $this->targetImageFileExistingReadonlyDir = vfsStream::url('site_root/web/images-readonly/123.jpg');
        $this->targetImageFileNewDir = vfsStream::url('site_root/web/images/new/123.jpg');
    }


    protected function createPropertyMapping($resolveFrom, $resolveTo, $targetData = array())
    {
        $propertyMapping = Mocks::getPropertyMappingMock($this);
        $propertyMapping->expects($this->any())
            ->method('resolveFileName')
            ->with($resolveFrom)
            ->will($this->returnValue($resolveTo));


        if ($targetData) $propertyMapping->expects($this->any())
            ->method('prepareFileName')
            ->with($resolveFrom, $this->storage)
            ->will($this->returnValue($targetData));

        return $propertyMapping;
    }


    public function testFileExists()
    {

        $this->assertTrue($this->storage->fileExists( $this->uploadedImageFile));

    }


    public function testFileNoExists()
    {
        $this->assertFalse($this->storage->fileExists($this->targetImageFileExistingDir));
    }


    public function testRemoveExistingFile()
    {
        $this->assertTrue($this->storage->removeFile($this->uploadedImageFile));
    }


    public function testRemoveNonExistingFile()
    {

        $this->assertNull($this->storage->removeFile($this->targetImageFileExistingDir));
    }


    public function testUploadImageFileToExistingDir()
    {
        //where file will be copied
        $propertyMapping = $this->createPropertyMapping('123.jpg', $this->targetImageFileExistingDir,
            array('123.jpg', '/images/123.jpg'));

        $file = new File(
            $this->uploadedImageFile, '123.jpg', 'image/jpeg', null, null, true);


        $this->storage->setSameFileChecker(function ()
        {
            return false;
        });

        $this->assertFileExists($this->uploadedImageFile);
        $this->assertFileNotExists($this->targetImageFileExistingDir);


        $fileData = $this->storage->upload($propertyMapping, $file);


        $this->assertFileExists($this->uploadedImageFile);
        $this->assertFileExists($this->targetImageFileExistingDir);

    }


    public function testUploadUploadedImageFileToExistingDir()
    {
        //test mode
        $uploadedFile = new \Symfony\Component\HttpFoundation\File\UploadedFile(
            $this->uploadedImageFile, '123.jpg', 'image/jpeg', null, null, true);

        $propertyMapping = $this->createPropertyMapping('123.jpg', $this->targetImageFileExistingDir,
            array('123.jpg', '/images/123.jpg'));

        $this->storage->setSameFileChecker(function ()
        {
            return false;
        });


        $this->assertFileExists($this->uploadedImageFile);
        $this->assertFileNotExists($this->targetImageFileExistingDir);
        $this->assertFileExists(dirname($this->targetImageFileExistingDir));

        $filesize = filesize($this->uploadedImageFile);
        $fileData = $this->storage->upload($propertyMapping, $uploadedFile);
        $this->assertFileExists($this->targetImageFileExistingDir);
        $this->assertFileNotExists($this->uploadedImageFile);
        $this->assertTrue(filesize($this->targetImageFileExistingDir) == $filesize);


        $testFileData = array
        (
            'fileName' => '123.jpg',
            'originalName' => '123.jpg',
            'mimeType' => 'image/jpeg',
            'size' => $filesize,
            'path' => '/images/123.jpg'
        );

        if (function_exists('getimagesize')) {
            $testFileData['width'] = 660;
            $testFileData['height'] = 498;
        }

        $this->assertSame($fileData, $testFileData);


    }


    public function testUploadImageFileToNewDir()
    {
        //where file will be copied
        $propertyMapping = $this->createPropertyMapping('123.jpg', $this->targetImageFileNewDir,
            array('123.jpg', '/images/new/123.jpg'));
        $file = new File($this->uploadedImageFile, '123.jpg', 'image/jpeg', null, null, true);

        $this->storage->setSameFileChecker(function ()
        {
            return false;
        });


        $this->assertFileNotExists($this->targetImageFileNewDir);
        $this->assertFileExists($this->uploadedImageFile);

        $this->assertFileNotExists(dirname($this->targetImageFileNewDir));
        $filesize = filesize($this->uploadedImageFile);

        $fileData = $this->storage->upload($propertyMapping, $file);

        $this->assertFileExists(dirname($this->targetImageFileNewDir));
        $this->assertFileExists($this->targetImageFileNewDir);
        $this->assertFileExists($this->uploadedImageFile);

        $testFileData = array
        (
            'fileName' => '123.jpg',
            'originalName' => '123.jpg',
            'mimeType' => 'image/jpeg',
            'size' => $filesize,
            'path' => '/images/new/123.jpg'
        );

        if (function_exists('getimagesize')) {
            $testFileData['width'] = 660;
            $testFileData['height'] = 498;
        }

        $this->assertSame($fileData, $testFileData);

    }





    /**
     * Test that an exception is thrown when try to move file to readonly dir
     * @expectedException \Symfony\Component\HttpFoundation\File\Exception\FileException
     */
    public function testUploadUploadedImageFileToExistingReadonlyDir()
    {

        if (version_compare(PHP_VERSION, '5.4.0','<'))
        {
            $this->markTestSkipped('vfsStream and chown() works only in PHP 5.4+');
            return;
        }


        //test mode
        $uploadedFile = new \Symfony\Component\HttpFoundation\File\UploadedFile(
            $this->uploadedImageFile, '123.jpg', 'image/jpeg', null, null, true);

        $propertyMapping = $this->createPropertyMapping('123.jpg',   $this->targetImageFileExistingReadonlyDir ,
            array('123.jpg', '/images-readonly/123.jpg'));

        $this->storage->setSameFileChecker(function ()
        {
            return false;
        });

        $fileData = $this->storage->upload($propertyMapping,  $uploadedFile);
    }


    /**
     * Test that an exception is thrown when try to move file to readonly dir
     * @expectedException \Symfony\Component\HttpFoundation\File\Exception\FileException
     */
    public function testUploadImageFileToExistingReadonlyDir()
    {

        if (version_compare(PHP_VERSION, '5.4.0','<'))
        {
            $this->markTestSkipped('vfsStream and chown() works only in PHP 5.4+');
            return;
        }


        //test mode
        $uploadedFile = new File(
            $this->uploadedImageFile, '123.jpg', 'image/jpeg', null, null, true);

        $propertyMapping = $this->createPropertyMapping('123.jpg',   $this->targetImageFileExistingReadonlyDir ,
            array('123.jpg', '/images-readonly/123.jpg'));

        $this->storage->setSameFileChecker(function ()
        {
            return false;
        });

        $fileData = $this->storage->upload($propertyMapping,  $uploadedFile);
    }


    function testSetWebDir()
    {
        $this->storage->setWebDir('/srv/www/web');
        $this->assertSame($this->storage->getWebDir(), '/srv/www/web');
    }


}
