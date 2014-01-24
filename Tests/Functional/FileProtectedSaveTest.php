<?php

namespace Iphp\FileStoreBundle\Tests\Functional;


use Iphp\FileStoreBundle\File\File as FileStoreFile;
use Iphp\FileStoreBundle\Tests\Functional\TestXmlConfigBundle\Entity\FileProtected;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\File\File;

/**
 * @author Vitiko <vitiko@mail.ru>
 */
class FileProtectedSaveTest extends BaseTestCase
{


    /**
     * test saving entity with file property from parent abstract uploadable class
     */
    public function testFileSaveUpload()
    {
        $client = $this->createClient();
        $this->importDatabaseSchema();


        $file = new FileProtected();

        $existsFile = new  File(
            __DIR__ . '/../Fixtures/files/text.txt');



        $file->setTitle('new protected file')
            ->setDate(new \DateTime('2013-04-04'))
            ->setFile($existsFile);


        $this->getEntityManager()->persist($file);
        $this->getEntityManager()->flush();


        $mappingData =  $this->getContainer()->get ("iphp.filestore.mapping.factory")->getMappingConfig ('file_protected');



        $uploadedFileData = $file->getFile();

        $this->assertSame($uploadedFileData, array(
            'fileName' => '/FileProtected/file/2013/1.txt',
            'originalName' => 'text.txt',
            'mimeType' => 'text/plain',
            'size' => 9,
            'path' => '/file/FileProtected/file/2013/1.txt',
            'protected' => false
        ));


        $this->assertFileExists($mappingData['protected_dir'].$uploadedFileData['fileName']);



        unset($file);
        $this->getEntityManager()->clear();
        $this->getKernel()->shutdown();



        //request protected file, client has no authorization
        $crawler = $client->request('GET',  $uploadedFileData['path'] );


      //  print_r ($client->getResponse());

        $this->assertEquals(
            Response::HTTP_UNAUTHORIZED,
            $client->getResponse()->getStatusCode()
        );







        //client with admin auth (ROLE_ADMIN)
        $adminClient = $this->createClient(array(), array(
            'PHP_AUTH_USER' => 'admin',
            'PHP_AUTH_PW'   => 'adminpass'));


        $adminClient->request('GET',  $uploadedFileData['path'] );

        $this->assertTrue (  $adminClient->getResponse()->isOk() );
        $this->assertInstanceOf('Symfony\\Component\\HttpFoundation\\BinaryFileResponse', $adminClient->getResponse());


        $file = $adminClient->getResponse()->getFile();
        $this->assertEquals($mappingData['protected_dir'].$uploadedFileData['fileName'], $file->getPathname());


        //client without admin auth (NO ROLE_ADMIN )
        $userClient = $this->createClient(array(), array(
            'PHP_AUTH_USER' => 'user',
            'PHP_AUTH_PW'   => 'userpass'));

        $userClient->request('GET',  $uploadedFileData['path'] );
        $this->assertTrue (  $userClient->getResponse()->isForbidden() );

        //createFromprint_r ($userClient->getResponse());

    }

}