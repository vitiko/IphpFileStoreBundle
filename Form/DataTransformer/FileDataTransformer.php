<?php

namespace Iphp\FileStoreBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Iphp\FileStoreBundle\FileStorage\FileStorageInterface;
use Iphp\FileStoreBundle\Mapping\PropertyMapping;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Iphp\FileStoreBundle\File\File as IphpFile;


/**
 * @author Vitiko <vitiko@mail.ru>
 */
class FileDataTransformer implements DataTransformerInterface
{

    const MODE_UPLOAD_FIELD = 'upload_field';

    const MODE_FILEDATA_FIELD = 'filedata_field';

    /**
     * @var \Iphp\FileStoreBundle\Mapping\PropertyMapping
     */
    protected $mapping;

    /**
     * @var \Iphp\FileStoreBundle\FileStorage\FileStorageInterface
     */
    protected $fileStorage;


    protected $mode;


    function __construct(FileStorageInterface $fileStorage)
    {
        $this->fileStorage = $fileStorage;
    }


    /**
     * Sets in PRE_BIND form event
     * @param PropertyMapping $mapping
     * @param $mode
     * @return $this
     */
    public function setMapping(PropertyMapping $mapping, $mode)
    {
        $this->mapping = $mapping;
        $this->mode = $mode;
        return $this;
    }


    public function transform($fileDataFromDb)
    {
        return $fileDataFromDb;
    }


    /**
     * array with 2 items - file (UploadedFile) and delete (checkbox)
     * @param $fileDataFromForm
     * @return int
     */
    public function reverseTransform($fileDataFromForm)
    {
        //if file field != file upload field - no need to store 'delete' in serialized file data
        if (isset($fileDataFromForm['delete']) && !$fileDataFromForm['delete'])
            unset($fileDataFromForm['delete']);


        if ($this->mapping && isset($fileDataFromForm['delete']) && $fileDataFromForm['delete']) {

            if ($this->mode == self::MODE_FILEDATA_FIELD) {
                return null;
            }

            //Todo: move to uploaderListener
            //File may no exists
            try {
                $this->fileStorage->removeFile($this->mapping->resolveFileName($fileDataFromForm['fileName']));

            } catch (\Exception $e) {
            }

        }

        return isset($fileDataFromForm['file']) ? $fileDataFromForm['file'] :
            ($this->mode == self::MODE_UPLOAD_FIELD ? null : $fileDataFromForm);
    }
}
