<?php

namespace Iphp\FileStoreBundle\FileStorage;

use Iphp\FileStoreBundle\Mapping\PropertyMapping;
use Symfony\Component\HttpFoundation\File\File;

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
    public function upload(PropertyMapping $mapping, File $file);


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
