<?php
/**
 * Created by Vitiko
 * Date: 08.08.12
 * Time: 11:32
 */

namespace Iphp\FileStoreBundle\File;


use Symfony\Component\HttpFoundation\File\File as BaseFile;

class File extends BaseFile implements LocalFileInterface
{
    protected $deleted = false;

    protected $protected = false;

    protected $originalName;

    protected $saveSource = true;

    static function createEmpty()
    {
        return new File();
    }

    public function   __construct($path = null, $originalName = null, $mimeType = null,
                                  $size = null, $error = null, $test = false)
    {
        if ($path !== null)
            parent::__construct($path, $originalName, $mimeType, $size, $error, $test);
    }


    public function delete()
    {
        // parent::__construct (__FILE__,'just for pass validator');
        $this->deleted = true;
        return $this;
    }

    public function isDeleted()
    {
        return $this->deleted ? true : false;
    }


    /**
     * @param boolean $protected
     */
    public function setProtected($protected)
    {
        $this->protected = $protected;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isProtected()
    {
        return $this->protected;
    }

    /**
     * @param mixed $originalName
     */
    public function setOriginalName($originalName)
    {
        $this->originalName = $originalName;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getOriginalName()
    {
        return $this->originalName;
    }

    /**
     * @param boolean $saveSource
     */
    public function setSaveSource($saveSource)
    {
        $this->saveSource = $saveSource;
        return $this;
    }

    /**
     * @return boolean
     */
    public function getSaveSource()
    {
        return $this->saveSource;
    }




}
