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

    /**
     * @var \Iphp\FileStoreBundle\Mapping\PropertyMapping
     */
    protected $mapping;

    /**
     * @var \Iphp\FileStoreBundle\FileStorage\FileStorageInterface
     */
    protected $fileStorage;

    function __construct(FileStorageInterface $fileStorage)
    {
        $this->fileStorage = $fileStorage;
    }


    public function setMapping(PropertyMapping $mapping)
    {
        $this->mapping = $mapping;
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
        if ($this->mapping && $fileDataFromForm['delete']) {

            //File may no exists
            try {
                $this->fileStorage->removeFile($this->mapping, $fileDataFromForm['fileName']);

            } catch (\Exception $e) {
            }

        }
        return $fileDataFromForm['file'];
    }
}
