<?php

namespace Iphp\FileStoreBundle\Tests\Functional\TestXmlConfigBundle\Entity;

use Iphp\FileStoreBundle\Mapping\Annotation as FileStore;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * @author Vitiko <vitiko@mail.ru>
 * @FileStore\Uploadable
 */

abstract class UploadableEntity
{


    /**
     * @Assert\Image( maxSize="20M")
     * @FileStore\UploadableField(mapping="file")
     **/
    protected $file;

    public function setFile($file)
    {
        $this->file = $file;
        return $this;
    }

    public function getFile()
    {
        return $this->file;
    }
}
