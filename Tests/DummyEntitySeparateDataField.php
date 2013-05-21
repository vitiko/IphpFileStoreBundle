<?php

namespace Iphp\FileStoreBundle\Tests;

use Iphp\FileStoreBundle\Mapping\Annotation as FileStore;

/**
 * @FileStore\Uploadable
 *
 * @author Vitiko <vitiko@mail.ru>
 */
class DummyEntitySeparateDataField
{
    /**
     * @FileStore\UploadableField(mapping="dummy_file",  )
     */
    protected $file;



    protected $file_data;

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

    public function getFile()
    {
        $this->file;
    }

    public function setFile($file)
    {
        $this->file = $file;
    }

    public function setFileData($file_data)
    {
        $this->file_data = $file_data;
        return $this;
    }

    public function getFileData()
    {
        return $this->file_data;
    }


}
