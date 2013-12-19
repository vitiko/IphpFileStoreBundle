<?php

namespace Iphp\FileStoreBundle\EventListener;


use Doctrine\Common\EventSubscriber;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Doctrine\Common\EventArgs;

use Iphp\FileStoreBundle\FileStorage\FileStorageInterface;
use Iphp\FileStoreBundle\DataStorage\DataStorageInterface;

use Iphp\FileStoreBundle\Driver\AnnotationDriver;
use Iphp\FileStoreBundle\Mapping\PropertyMappingFactory;
use Iphp\FileStoreBundle\Mapping\PropertyMapping;

use Symfony\Component\HttpFoundation\File\File;

/**
 * UploaderListener.
 *
 * @author Vitiko <vitiko@mail.ru>
 */
class UploaderListener implements EventSubscriber
{
    /**
     * Adapter for ORMor MongoDb
     * @var \Iphp\FileStoreBundle\DataStorage\DataStorageInterface $dataStorage
     */
    protected $dataStorage;


    /**
     * @var \Iphp\FileStoreBundle\FileStorage\FileStorageInterface $fileStorage
     */
    protected $fileStorage;


    /**
     * @var \Iphp\FileStoreBundle\Mapping\PropertyMappingFactory $mappingFactory
     */
    protected $mappingFactory;


    /**
     * @var \SplObjectStorage Temporary store for using in fileStorage
     */
    protected $deferredFiles;


    /**
     * Constructs a new instance of UploaderListener.
     *
     * @param \Iphp\FileStoreBundle\DataStorage\DataStorageInterface       $dataStorage  The dataStorage
     * @param \Iphp\FileStoreBundle\FileStorage\FileStorageInterface       $fileStorage  The storage.
     * @param \Iphp\FileStoreBundle\Mapping\PropertyMappingFactory $mappingFactory Mapping Factore
     */
    public function __construct(DataStorageInterface $dataStorage,
                                FileStorageInterface $fileStorage,
                                PropertyMappingFactory $mappingFactory)
    {
        $this->dataStorage = $dataStorage;
        $this->fileStorage = $fileStorage;
        $this->mappingFactory = $mappingFactory;

        $this->deferredFiles = new \SplObjectStorage();
    }


    public function hasDeferredObject($obj)
    {
        return isset($this->deferredFiles [$obj]) && $this->deferredFiles [$obj];
    }

    public function hasDeferredPropertyMapping($obj, PropertyMapping $mapping)
    {
        return $this->hasDeferredObject($obj) &&
            isset($this->deferredFiles [$obj][$mapping]) && $this->deferredFiles [$obj][$mapping];
    }


    public function getDeferredObjectNum()
    {
        return count($this->deferredFiles);
    }


    /**
     * The events the listener is subscribed to.
     *
     * @return array The array of events.
     */
    public function getSubscribedEvents()
    {
        return array(
            'prePersist',
            'postFlush',
            'preUpdate',
            'postRemove',
        );
    }


    /**
     * @return \Iphp\FileStoreBundle\Mapping\PropertyMapping[]
     */
    protected function getMappingsFromArgs(EventArgs $args)
    {
        $obj = $this->dataStorage->getObjectFromArgs($args);
        return $this->mappingFactory->getMappingsFromObject($obj, $this->dataStorage->getReflectionClass($obj));
    }

    /**
     * Checks for for file to upload and store it for store at postFlush event
     *
     * @param \Doctrine\Common\EventArgs $args The event arguments.
     */
    public function prePersist(EventArgs $args)
    {
        $obj = $this->dataStorage->getObjectFromArgs($args);
        $mappings = $this->mappingFactory->getMappingsFromObject($obj, $this->dataStorage->getReflectionClass($obj));
        $curFiles = new \SplObjectStorage();

        foreach ($mappings as $mapping) {
            $file = $mapping->getFileUploadPropertyValue();
            if ($file instanceof File) $curFiles[$mapping] = $file;
            $mapping->setFileUploadPropertyValue(null);
        }

        //if ($curFiles) $this->deferredFiles [$mappings[0]->getObj()] = $curFiles;
        if (count($curFiles)) $this->deferredFiles [$obj] = $curFiles;
    }


    /**
     * Store at postFlush event because file namer mey need entity id, at prePersist event
     * system does not now auto generated entity id
     * @param \Doctrine\Common\EventArgs $args
     */
    public function postFlush(EventArgs $args)
    {
        if (!$this->deferredFiles) return;

        foreach ($this->deferredFiles as $obj) {
            if (!$this->deferredFiles[$obj]) continue;

            foreach ($this->deferredFiles[$obj] as $mapping) {
                $fileData = $this->fileStorage->saveFile($mapping, $this->deferredFiles[$obj][$mapping]);
                $mapping->setFileDataPropertyValue($fileData);
            }

            unset($this->deferredFiles[$obj]);
            $this->dataStorage->postFlush($obj, $args);
        }
    }


    /**
     * Update the mapped file for Entity (obj)
     *
     * @param \Doctrine\Common\EventArgs  $args
     */
    public function preUpdate(\Doctrine\Common\EventArgs $args)
    {
        $mappings = $this->getMappingsFromArgs($args);

        foreach ($mappings as $mapping) {

            //Uploaded or setted file
            $file = $mapping->getFileUploadPropertyValue();

            $currentFileData = $this->dataStorage->currentFieldData($mapping->getFileDataPropertyName(), $args);
            $currentFileName = $currentFileData ?
                $mapping->resolveFileName($currentFileData['fileName'],
                    isset($currentFileData['protected']) && $currentFileData['protected'] ? true : false) : null;


            //If no new file
            if (is_null($file) || !($file instanceof File)) {

                if ($currentFileData) {
                    if (!$this->fileStorage->fileExists($currentFileName)) {


                        //try to restore file by web path
                        $fileNameByWebDir = $_SERVER['DOCUMENT_ROOT'].$currentFileData['path'];

                        if ($this->fileStorage->fileExists($fileNameByWebDir))
                        {
                            $file = new UploadedFile ($fileNameByWebDir,
                                                      $currentFileData['originalName'], $currentFileData['mimeType'],
                                                      null,  null, true);
                            $fileData = $this->fileStorage->upload($mapping, $file);
                            $mapping->setFileDataPropertyValue($fileData);
                        }

                    } //Preserve old fileData if current file exist
                    else $mapping->setFileDataPropertyValue($currentFileData);

                }
 

            } //uploaded file has deleted status
            else if ($file instanceof \Iphp\FileStoreBundle\File\File && $file->isDeleted()) {
                if ($this->fileStorage->removeFile($currentFileName)) $mapping->setFileDataPropertyValue(null);
            }


            //changed protect attribute
/*            else if ($file instanceof \Iphp\FileStoreBundle\File\File && $file->isProtected()) {


                $fileData = $this->fileStorage->move($mapping, $file);
                $mapping->setFileDataPropertyValue($fileData);

                die ('Protect in action!');
                //if ($this->fileStorage->removeFile($currentFileName)) $mapping->setFileDataPropertyValue(null);




            }*/


            else {

                //Old value (file) exits and uploaded new file
                if ($currentFileData && !$this->fileStorage->isSameFile($file, $currentFileName))
                    //before upload new file delete old file
                    $this->fileStorage->removeFile($currentFileName);

                $fileData = $this->fileStorage->saveFile ($mapping, $file);




                $mapping->setFileDataPropertyValue($fileData);
            }
        }
        $this->dataStorage->recomputeChangeSet($args);
    }


    /**
     * Removes the file if necessary.
     *
     * @param \Doctrine\Common\EventArgs $args The event arguments.
     */
    public function postRemove(EventArgs $args)
    {
        $mappings = $this->getMappingsFromArgs($args);

        foreach ($mappings as $mapping) {
            if ($mapping->getDeleteOnRemove()) $this->fileStorage->removeFile($mapping->resolveFileName());
        }

    }

}
