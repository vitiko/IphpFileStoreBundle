<?php

namespace Iphp\FileStoreBundle\Form\DataTransformer;

use Iphp\FileStoreBundle\File\File;
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

    /**
     * @return \Iphp\FileStoreBundle\Mapping\PropertyMapping
     */
    public function getMapping()
    {
        return $this->mapping;
    }


    public function transform($fileDataFromDb)
    {
        if (is_array($fileDataFromDb) && isset($fileDataFromDb['fileName']))
            $fileDataFromDb['protected'] = $fileDataFromDb['isprotected'] = isset($fileDataFromDb['protected']) && $fileDataFromDb['protected'];

        return $fileDataFromDb;
    }


    /**
     * array with 2 items - file (UploadedFile) and delete (checkbox)
     * @param $fileDataFromForm
     * @return int
     */
    public function reverseTransform($fileDataFromForm)
    {
        if (!$this->mapping) return $fileDataFromForm['file'];

        if ($fileDataFromForm['delete']) {

            $existFileProtected = isset($fileDataFromForm['protected']) && $fileDataFromForm['protected'];


            //File may no exists
            try {
                $this->fileStorage->removeFile(
                    $this->mapping->resolveFileName($fileDataFromForm['fileName'], $existFileProtected));
            } catch (\Exception $e) {
            }

        }


        if ($this->mapping->getProtected() == 'ondemand') {


            $protected = isset($fileDataFromForm['isprotected']) && $fileDataFromForm['isprotected'];

            //transform to \Iphp\FileStoreBundle\File\UploadedFile with attribute protected = true
            if ($fileDataFromForm['file']) {
                $fileDataFromForm['file'] = \Iphp\FileStoreBundle\File\UploadedFile::createFrom($fileDataFromForm['file']);
                $fileDataFromForm['file']->setProtected($protected);

            } else {

                //cur file data exists
                if (isset($fileDataFromForm['fileName'])) {

                    //print_r($fileDataFromForm);

                    $setFileProtected = isset($fileDataFromForm['isprotected']) && $fileDataFromForm['isprotected'];
                    $existFileProtected = isset($fileDataFromForm['protected']) && $fileDataFromForm['protected'];


                    //change status of exists file
                    if ($setFileProtected != $existFileProtected) {
                        $existFileName = $this->mapping->resolveFileName($fileDataFromForm['fileName'],
                            $existFileProtected);


                        if ($this->fileStorage->fileExists($existFileName)) {
                            //  print $existFileName.' '.$setFileProtected;
                            $fileDataFromForm['file'] = new File($existFileName);
                            $fileDataFromForm['file']
                                ->setProtected($setFileProtected)
                                ->setOriginalName($fileDataFromForm['originalName'])
                                ->setSaveSource(false);


                        }
                        //exit();

                    }
                    //exit();

                }


                //


                /*                print_r($fileDataFromForm);

                                print $fileDataFromForm['fileName'] . '-' . $protected;
                                exit();


                                // Данные из текущего положения файла
                                $fileDataFromForm['file'] = new File($this->mapping->resolveFileName($fileDataFromForm['fileName'],

                                    isset($fileDataFromForm['fileName'])
                                ));
                                $fileDataFromForm['file']->setProtected($protected);*/
            }
        }

        return $fileDataFromForm['file'];
    }
}
