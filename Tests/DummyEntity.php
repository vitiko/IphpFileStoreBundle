<?php

namespace Iphp\FileStoreBundle\Tests;

use Iphp\FileStoreBundle\Mapping\Annotation as FileStore;

/**
 * @FileStore\Uploadable
 *
 * @author Vitiko <vitiko@mail.ru>
 */
class DummyEntity
{

    protected $id;

    /**
     * @FileStore\UploadableField(mapping="dummy_file")
     */
    protected $file;


    protected $title;

    protected $createdAt;

    /**
     * @var array Default iphpfilestore bundle configuration for DummyEntity
     */
    public  $defaultFileStoreConfig= array(
        'dummy_file' => array(
            'upload_dir' => '/www/web/images',
            'upload_path' => '/images',
            'namer' => array('translit' => array('service' => 'iphp.filestore.namer.default'))
        )
    );


    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    public function getId()
    {
        return $this->id;
    }


    public function getFile()
    {
        return $this->file;
    }

    public function setFile($file)
    {
        $this->file = $file;
    }

    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getCreatedAt()
    {
        return $this->createdAt;
    }


}
