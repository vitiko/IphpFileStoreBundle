<?php

namespace Iphp\FileStoreBundle\Tests\Functional;

/**
 * @author Vitiko <vitiko@mail.ru>
 */

use Iphp\FileStoreBundle\Tests\Functional\TestXmlConfigBundle\Entity\File;
use Symfony\Component\Console\Tester\CommandTester;
use Iphp\FileStoreBundle\Command\RepairFileDataCommand;
use Symfony\Bundle\FrameworkBundle\Console\Application;

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

        $file->setTitle('new file')
            ->setDate(new \DateTime('2013-04-04'))
            ->setFile($existsFile);


        $this->getEntityManager()->persist($file);
        $this->getEntityManager()->flush();



        $this->assertSame($file->getFile(), array(
            'fileName' => '/File/file/2013/1.txt',
            'originalName' => 'text.txt',
            'mimeType' => 'text/plain',
            'size' => 9,
            'path' => '/file/File/file/2013/1.txt',
        ));

        unset($file);
        $this->getEntityManager()->clear();
        $this->getKernel()->shutdown();




        $client = $this->createClient(array('config' => 'default_newfilepath.yml'));
        $application = new Application($client->getKernel());
        $application->add(new RepairFileDataCommand());

        $command = $application->find('iphp:filestore:repair');


        $commandTester = new CommandTester($command);


        //using web directory setted in config/default.yml
        $commandTester->execute(array(
            'command' => $command->getName(),
            '--entity' => 'TestXmlConfigBundle:File',
            '--field' => 'file',
            '--force' => 1, // move file to new location
            '--webdir' => realpath($this->getContainer()->getParameter('kernel.test_env_dir') . '/web/')
        ));


        $newFile = $this->getEntityManager()->getRepository('TestXmlConfigBundle:File')->findOneByTitle('new file');


        $this->assertSame($newFile->getFile(), array(
            'fileName' => '/1/new-file.txt',
            'originalName' => 'text.txt',
            'mimeType' => 'text/plain',
            'size' => 9,
            'path' => '/other/uploads/1/new-file.txt',
        ));


        unset($newFile);
        unset($commandTester);
        unset( $command );
        unset($application);
        $this->getEntityManager()->clear();
        $this->getKernel()->shutdown();


    }

}