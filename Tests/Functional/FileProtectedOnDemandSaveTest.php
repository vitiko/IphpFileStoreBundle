<?php

namespace Iphp\FileStoreBundle\Tests\Functional;


use Iphp\FileStoreBundle\File\File as FileStoreFile;


use Iphp\FileStoreBundle\Tests\Functional\TestXmlConfigBundle\Entity\FileProtected;
use Symfony\Component\HttpFoundation\Response;


/**
 * @author Vitiko <vitiko@mail.ru>
 */
class FileProtectedOnDemandSaveTest extends BaseTestCase
{


    /**
     * test saving entity with file property from parent abstract uploadable class
     */
    public function testFileSaveUpload()
    {
        $client = $this->createClient();
        $this->importDatabaseSchema();


        $file = new FileProtected();

        //create not protected file (ondemand by default - not protected )
        $existsFile = new  \Symfony\Component\HttpFoundation\File\File (__DIR__ . '/../Fixtures/files/text.txt');

        $file->setTitle('new unprotected file')
            ->setDate(new \DateTime('2013-04-04'))
            ->setFileOndemand($existsFile);

        $this->getEntityManager()->persist($file);
        $this->getEntityManager()->flush();


        $mappingData = $this->getContainer()->get("iphp.filestore.mapping.factory")->getMappingConfig('file_protected_ondemand');

        $uploadedFileData = $file->getFileOndemand();


        $this->assertSame($uploadedFileData, array(
            'fileName' => '/FileProtected/fileOndemand/2013/1.txt',
            'originalName' => 'text.txt',
            'mimeType' => 'text/plain',
            'size' => 9,
            'path' => '/file/FileProtected/fileOndemand/2013/1.txt',
            'protected' => false
        ));


        $this->assertFileExists($mappingData['upload_dir'] . $uploadedFileData['fileName']);
        $this->assertFileNotExists($mappingData['protected_dir'] . $uploadedFileData['fileName']);


        unset($file);


        //create protected
        $protectedFile = new  \Iphp\FileStoreBundle\File\File(__DIR__ . '/../Fixtures/files/text.txt');
        $protectedFile->setProtected(true);

        $file = new FileProtected();
        $file->setTitle('new protected file')
            ->setDate(new \DateTime('2013-04-04'))
            ->setFileOndemand( $protectedFile );

        $this->getEntityManager()->persist($file);
        $this->getEntityManager()->flush();


        $uploadedFileData = $file->getFileOndemand();

        $this->assertSame($uploadedFileData, array(
            'fileName' => '/FileProtected/fileOndemand/2013/2.txt',
            'originalName' => 'text.txt',
            'mimeType' => 'text/plain',
            'size' => 9,
            'path' => '/file/FileProtected/fileOndemand/2013/2.txt',
            'protected' => true
        ));


        $this->assertFileNotExists($mappingData['upload_dir'] . $uploadedFileData['fileName']);
        $this->assertFileExists($mappingData['protected_dir'] . $uploadedFileData['fileName']);


        $this->getEntityManager()->clear();
        $this->getKernel()->shutdown();




    }

}