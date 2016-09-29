<?php

namespace Iphp\FileStoreBundle\Tests\Functional;

/**
 * @author Vitiko <vitiko@mail.ru>
 */

use Iphp\FileStoreBundle\Tests\Functional\TestBundle\Entity\Photo;


class ImageEditTest extends BaseTestCase
{
    public function testImageEditAndDelete()
    {
        $client = $this->createClient();
        $this->importDatabaseSchema();

        $this->getEntityManager()->persist($this->createTestPhotoObject());
        $this->getEntityManager()->flush();
        $this->assertLoadedPhotoParams($this->getEntityManager()->getRepository('TestBundle:Photo')->findOneById(1));


        //go to edit photo via form
        $crawler = $client->request('GET', '/edit/1/');
        $this->assertPhotoExistsInForm($crawler);




        //upload new files
        $newFileToUpload = new \Symfony\Component\HttpFoundation\File\UploadedFile(
            __DIR__ . '/../Fixtures/images/php-elephant.png', 'php-elephant.png');
        $alsoNewFileToUpload = new \Symfony\Component\HttpFoundation\File\UploadedFile(
            __DIR__ . '/../Fixtures/images/github1.png', 'github1.png');

        $form = $crawler->selectButton('Save')->form();

        //this is combined field [file => [FileType file,Checkbox delete] ]
        $form['form[photo][file]']->upload($newFileToUpload);
        $form['form[photoUpload]']->upload($alsoNewFileToUpload);
        $client->submit($form);
        $crawler = $client->followRedirect();
        $this->assertPreviousImageGone($crawler);

        //try to delete images
        $form = $crawler->selectButton('Save')->form();
        $form['form[photo][delete]']->tick();
        $form['form[photoInfo][delete]']->tick();
        $client->submit($form);
        $crawler = $client->followRedirect();
        $this->assertNoPhotoOnForm($crawler);

        $this->getEntityManager()->clear();
        $photoAfterUpdate = $this->getEntityManager()->getRepository('TestBundle:Photo')->findOneById(1);
        $this->assertSame($photoAfterUpdate->getPhoto(), null);
        $this->assertSame($photoAfterUpdate->getPhotoInfo(), null);
    }

    function createTestPhotoObject()
    {
        //Creating photo objecy with 2 images
        $existsFile = new \Symfony\Component\HttpFoundation\File\File(
            __DIR__ . '/../Fixtures/images/front-images-list.jpeg');

        $alsoExistsFile = new \Symfony\Component\HttpFoundation\File\File(
            __DIR__ . '/../Fixtures/images/sonata-admin-iphpfile.jpeg');

        $photo = new Photo();
        $photo->setTitle('Second photo')
            ->setDate(new \DateTime ('2013-04-05 00:00:00'))
            ->setPhoto($existsFile)
            ->setPhotoUpload($alsoExistsFile);

        return $photo;
    }

    function assertLoadedPhotoParams($photoLoaded)
    {
        //params of fixture files
        $this->assertSame($photoLoaded->getPhoto(), [
            'fileName' => '/2013/04/front-images-list.jpeg',
            'originalName' => 'front-images-list.jpeg',
            'mimeType' => 'image/jpeg',
            'size' => 67521,
            'path' => '/photo/2013/04/front-images-list.jpeg',
            'width' => 445,
            'height' => 531
        ]);

        $this->assertSame($photoLoaded->getPhotoInfo(), [
            'fileName' => '/2013/04/sonata-admin-iphpfile.jpeg',
            'originalName' => 'sonata-admin-iphpfile.jpeg',
            'mimeType' => 'image/jpeg',
            'size' => 48332,
            'path' => '/photo/2013/04/sonata-admin-iphpfile.jpeg',
            'width' => 671,
            'height' => 487
        ]);
    }

    function assertPhotoExistsInForm($crawler)
    {
        //check foto exists in form
        $this->assertSame($crawler->filter('input[id="form_title"][value="Second photo"]')->count(), 1);
        $this->assertSame($crawler->filter('option[value="2013"][selected="selected"]')->count(), 1);
        $this->assertSame($crawler->filter('option[value="4"][selected="selected"]')->count(), 1);


        //displayed loaded image and checkbox for delete image
        $this->assertSame($crawler->filter('img[src="/photo/2013/04/front-images-list.jpeg"]')->count(), 1);
        $this->assertSame($crawler->filter('input[type="checkbox"][id="form_photo_delete"]')->count(), 1);


        //displayed loaded second image and checkbox for delete image
        $this->assertSame($crawler->filter('img[src="/photo/2013/04/sonata-admin-iphpfile.jpeg"]')->count(), 1);
        $this->assertSame($crawler->filter('input[type="checkbox"][id="form_photoInfo_delete"]')->count(), 1);
    }

    function assertNoPhotoOnForm($crawler)
    {
        //after photo delete NOT displaying loaded image and checkbox for delete image
        $this->assertSame($crawler->filter('img[src="/photo/2013/04/front-images-list.jpeg"]')->count(), 0);
        $this->assertSame($crawler->filter('input[type="checkbox"][id="form_photo_delete"]')->count(), 0);
        $this->assertSame($crawler->filter('img[src="/photo/2013/04/sonata-admin-iphpfile.jpeg"]')->count(), 0);
        $this->assertSame($crawler->filter('input[type="checkbox"][id="form_photoInfo_delete"]')->count(), 0);
    }

    function assertPreviousImageGone($crawler)
    {
        //previous image gone
        $this->assertSame($crawler->filter('img[src="/photo/2013/04/front-images-list.jpeg"]')->count(), 0);
        $this->assertSame($crawler->filter('img[src="/photo/2013/04/sonata-admin-iphpfile.jpeg"]')->count(), 0);
        //new exists
        $this->assertSame($crawler->filter('img[src="/photo/2013/04/php-elephant.png"]')->count(), 1);
        $this->assertSame($crawler->filter('img[src="/photo/2013/04/github1.png"]')->count(), 1);

    }
}