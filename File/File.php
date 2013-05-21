<?php
/**
 * Created by Vitiko
 * Date: 08.08.12
 * Time: 11:32
 */

namespace Iphp\FileStoreBundle\File;


use Symfony\Component\HttpFoundation\File\UploadedFile;

class File extends UploadedFile
{
    protected $deleted = false;

    static function createEmpty()
    {
        return new File();
    }

    public function   __construct($path = null, $originalName = null, $mimeType = null,
                                  $size = null, $error = null, $test = false)
    {
       if ($path !== null)
           parent::__construct($path, $originalName, $mimeType,$size, $error, $test);
    }


    public function delete ()
    {
       // parent::__construct (__FILE__,'just for pass validator');
        $this->deleted = true;
        return $this;
    }

    public function isDeleted()
    {
        return $this->deleted ? true : false;
    }


    function isValid()
    {
        return true;
    }
}
