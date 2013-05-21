<?php

namespace Iphp\FileStoreBundle\Tests\FileStorage;

use Iphp\FileStoreBundle\FileStorage\FileSystemStorage;
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

    /**
     * Sets up the test.
     */
    public function setUp()
    {
        $this->storage = new FileSystemStorage();
    }


    public function testGetOriginalName()
    {

        $fileName  = '123.jpg';

/*        $file = Mocks::getFileMock($this);
        $file->expects($this->once())
            ->method('getRealPath')
            ->will($this->returnValue('/path/to/file/123.jpg'));

        $propertyMapping = Mocks::getPropertyMappingMock($this);
        $propertyMapping->expects($this->once())
            ->method('resolveFile')
            ->with ($fileName)
            ->will($this->returnValue('/path/to/file/123.jpg'));*/

        //$this->assertSame($this->storage->isSameFile($file, $propertyMapping, $fileName), true);
    }


}
