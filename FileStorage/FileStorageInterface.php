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
     * Removes the files associated with the object if configured to
     * do so.
     *
     * @param object $obj The object.
     */
    public function removeByMapping(PropertyMapping $mapping);

    public function removeFile(array $fileData);

    public function checkFileExists(array $fileData);

    public function isSameFile (File $file, array $currentFileData);

    /**
     * Resolves the path for a file based on the specified object
     * and field name.
     *
     * @param  object $obj   The object.
     * @param  string $field The field.
     * @return string The path.
     */
    //public function resolvePath($obj, $field);
}
