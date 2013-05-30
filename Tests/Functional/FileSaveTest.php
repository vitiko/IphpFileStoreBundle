<?php

namespace Iphp\FileStoreBundle\Tests\Functional;

/**
 * @author Vitiko <vitiko@mail.ru>
 */

use Iphp\FileStoreBundle\Tests\Functional\TestBundle\Entity\File;

class FileSaveTest extends BaseTestCase
{


    /**
     * test saving entity with file property from parent abstract uploadable class
     */
    public function testFileSaveUpload()
    {
        $client = $this->createClient();
        $this->importDatabaseSchema();



        $file = new File();

        $existsFile = new \Symfony\Component\HttpFoundation\File\File(
            __DIR__ . '/../Fixtures/files/text.txt');

        $file->setTitle ('new file')
            ->setDate(new \DateTime('2013-04-04'))
            ->setFile ($existsFile);



        $em = $this->getEntityManager();
        $em->persist($file);
        $em->flush();


        $this->assertSame ($file->getFile(), array (
            'fileName' => '/File/file/2013/1.txt',
            'originalName' => 'text.txt',
            'mimeType' => 'text/plain',
            'size' => 9,
            'path' => '/file/File/file/2013/1.txt',
        ));

    }

}