<?php
namespace Iphp\FileStoreBundle\File;

use Symfony\Component\HttpFoundation\File\UploadedFile as BaseUploadedFile;


/**
 * @author Vitiko <vitiko@mail.ru>
 */
class UploadedFile extends BaseUploadedFile implements UploadedFileInterface
{


    protected $protected = false;


    static function createFrom(BaseUploadedFile $baseUploadedFile, $test = false)
    {
        return new UploadedFile (
            $baseUploadedFile->getPathname(),
            $baseUploadedFile->getClientOriginalName(),
            $baseUploadedFile->getClientMimeType(),
            $baseUploadedFile->getClientSize(),
            $baseUploadedFile->getError(),
            $test);


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


}