<?php

namespace Iphp\FileStoreBundle\Tests\Functional;

/**
 * @author Vitiko <vitiko@mail.ru>
 */

use Iphp\FileStoreBundle\Tests\Functional\TestBundle\Entity\Photo;

class ImageUploadTest extends BaseTestCase
{


    public function testImageUpload()
    {
        $client = $this->createClient();
        $this->importDatabaseSchema();

        $client->enableProfiler();
        $crawler = $client->request('GET', '/');

        $this->assertTrue($client->getResponse()->isSuccessful());
        //Photos not uploaded yet
        $this->assertSame($crawler->filter('div.photo')->count(), 0);


        $client->enableProfiler();

        $fileToUpload = new \Symfony\Component\HttpFoundation\File\UploadedFile(
            __DIR__ . '/../Fixtures/images/sonata-admin-iphpfile.jpeg', 'sonata-admin-iphpfile.jpeg');

        $client->submit($crawler->selectButton('Upload')->form(), array(
            'title' => 'Some title',
            'photo' => $fileToUpload,
            'date[year]' => '2013',
            'date[month]' => '3',
            'date[day]' => '15'
        ));


        $crawler = $client->followRedirect();

        //added 1 photo
        $this->assertSame($crawler->filter('div.photo')->count(), 1);


        $photos = $this->getEntityManager()->getRepository('TestBundle:Photo')->findAll();
        $this->assertSame(sizeof($photos), 1);
        $photo = $photos[0];
        $this->assertSame($photo->getTitle(), 'Some title');

        //path to images dir and directory naming config in Tests/Functional/config/default.yml
        $this->assertSame($photo->getPhoto(), array(

            'fileName' => '/2013/03/sonata-admin-iphpfile.jpeg',
            'originalName' => 'sonata-admin-iphpfile.jpeg',
            'mimeType' => 'application/octet-stream',
            'size' => $fileToUpload->getSize(),
            'path' => '/photo/2013/03/sonata-admin-iphpfile.jpeg',
            'width' => 671,
            'height' => 487
        ));
    }


}