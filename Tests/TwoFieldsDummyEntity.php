<?php

namespace Iphp\FileStoreBundle\Tests;

use Iphp\FileStoreBundle\Mapping\Annotation as FileStore;

/**
 * @FileStore\Uploadable
 *
 * @author Vitiko <vitiko@mail.ru>
 */
class TwoFieldsDummyEntity
{
    /**
     * @FileStore\UploadableField(mapping="dummy_file")
     */
    protected $file;

    /**
     * @FileStore\UploadableField(mapping="dummy_image")
     */
    protected $image;

    public function getFile()
    {
        $this->file;
    }

    public function setFile($file)
    {
        $this->file = $file;
    }

    public function getImage()
    {
        $this->image;
    }

    public function setImage($image)
    {
        $this->image = $image;
    }
}
