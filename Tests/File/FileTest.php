<?php

namespace Iphp\FileStoreBundle\Tests\File;

use Iphp\FileStoreBundle\File\File;


/**
 * FileTest
 *
 * @author Vitiko <vitiko@mail.ru>
 */
class FileTest extends \PHPUnit_Framework_TestCase
{




    function testCreateEmpty()
    {
        $file = File::createEmpty();

        $this->assertTrue ($file instanceof File);
        $this->assertSame ($file->isDeleted(), false);
        $this->assertSame ($file->getPath(), '');
        $this->assertSame ($file->isValid(), true);
    }



    function testDelete()
    {
        $file = new File();
        $this->assertSame ($file->isDeleted(), false);
        $file->delete();
        $this->assertSame ($file->isDeleted(), true);
    }
}