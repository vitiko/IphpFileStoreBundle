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
     * @param \Iphp\FileStoreBundle\Mapping\PropertyMapping $mapping
     * @param $fileName -   file name for this mapping
     * @return mixed
     */
    public function removeFile(PropertyMapping $mapping, $fileName = null);


    /**
     * @abstract
     * @param \Iphp\FileStoreBundle\Mapping\PropertyMapping $mapping
     * @param null $fileName
     * @return mixed
     */
    public function fileExists(PropertyMapping $mapping, $fileName = null);


    /**
     * @abstract
     * @param \Symfony\Component\HttpFoundation\File\File $file
     * @param \Iphp\FileStoreBundle\Mapping\PropertyMapping $mapping
     * @param null $fileName
     * @return mixed
     */
    public function isSameFile (File $file, PropertyMapping $mapping, $fileName = null);


}
