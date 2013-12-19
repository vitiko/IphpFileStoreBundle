<?php

namespace Iphp\FileStoreBundle\FileStorage;

use Iphp\FileStoreBundle\File\FileInterface;
use Iphp\FileStoreBundle\File\LocalFileInterface;
use Iphp\FileStoreBundle\File\UploadedFileInterface;
use Iphp\FileStoreBundle\Mapping\PropertyMapping;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * StorageInterface.
 *
 * @author Vitiko <vitiko@mail.ru>
 */
interface FileStorageInterface
{


    /**
     * Uploads the files in the uploadable fields of the
     * specified object according to the property configuration.
     *
     * @param object $obj The object.
     */
    public function saveUploadedFile (PropertyMapping $mapping, UploadedFile $file);

    public function saveLocalFile (PropertyMapping $mapping, File  $file);


    public function saveFile  (PropertyMapping $mapping, File $file);


    /**
     * @abstract
     * @param $fullFileName
     * @return boolean
     */
    public function removeFile($fullFileName);


    /**
     * @abstract
     * @param $fullFileName
     * @return boolean
     */
    public function fileExists($fullFileName);


    /**
     * @abstract
     * @param \Symfony\Component\HttpFoundation\File\File $file
     * @param $fullFileName
     * @return boolean
     */
    public function isSameFile (File $file, $fullFileName);


}
