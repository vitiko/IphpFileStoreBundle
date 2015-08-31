<?php

namespace Iphp\FileStoreBundle\Tests\Functional;

/**
 * @author Vitiko <vitiko@mail.ru>
 */

use Iphp\FileStoreBundle\Tests\Functional\TestBundle\Entity\Photo;

class ImageEditTest extends BaseTestCase
{
    public function testImageEdit()
    {
        $client = $this->createClient();
        $this->importDatabaseSchema();


        $existsFile = new \Symfony\Component\HttpFoundation\File\File(
            __DIR__ . '/../Fixtures/images/front-images-list.jpeg');

        $photo = new Photo();
        $photo->setTitle('Second photo')
            ->setDate(new \DateTime ('2013-04-05 00:00:00'))
            ->setPhoto($existsFile);


        $this->getEntityManager()->persist($photo);
        $this->getEntityManager()->flush();

        unset($photo);
        $photoLoaded = $this->getEntityManager()->getRepository('TestBundle:Photo')->findOneById(1);

        $this->assertSame($photoLoaded->getPhoto(), array(

            'fileName' => '/2013/04/front-images-list.jpeg',
            'originalName' => 'front-images-list.jpeg',
            'mimeType' => 'image/jpeg',
            'size' => $existsFile->getSize(),
            'path' => '/photo/2013/04/front-images-list.jpeg',
            'width' => 445,
            'height' => 531
        ));

        $crawler = $client->request('GET', '/edit/' . $photoLoaded->getId() . '/');
        unset($photoLoaded);

        $this->assertSame($crawler->filter('input[id="form_title"][value="Second photo"]')->count(), 1);
        $this->assertSame($crawler->filter('option[value="2013"][selected="selected"]')->count(), 1);
        $this->assertSame($crawler->filter('option[value="4"][selected="selected"]')->count(), 1);


        //displayed loaded image and checkbox for delete image
        $this->assertSame($crawler->filter('img[src="/photo/2013/04/front-images-list.jpeg"]')->count(), 1);
        $this->assertSame($crawler->filter('input[type="checkbox"][id="form_photo_delete"]')->count(), 1);


        $form = $crawler->selectButton('Save')->form();
        $form['form[photo][delete]']->tick();

        $client->submit($form);

        $crawler = $client->followRedirect();

        //after photo delete NOT displaying loaded image and checkbox for delete image
        $this->assertSame($crawler->filter('img[src="/photo/2013/04/front-images-list.jpeg"]')->count(), 0);
        $this->assertSame($crawler->filter('input[type="checkbox"][id="form_photo_delete"]')->count(), 0);

        $this->getEntityManager()->clear();
        $photoAfterUpdate = $this->getEntityManager()->getRepository('TestBundle:Photo')->findOneById(1);
        $this->assertSame($photoAfterUpdate->getPhoto(), null);
    }
}