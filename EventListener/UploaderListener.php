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
     * @param \Iphp\FileStoreBundle\DataStorage\DataStorageInterface $dataStorage The dataStorage
     * @param \Iphp\FileStoreBundle\FileStorage\FileStorageInterface $fileStorage The storage.
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
                $fileData = $this->fileStorage->upload($mapping, $this->deferredFiles[$obj][$mapping]);
                $mapping->setFileDataPropertyValue($fileData);
            }

            unset($this->deferredFiles[$obj]);
            $this->dataStorage->postFlush($obj, $args);
        }
    }


    /**
     * Update the mapped file for Entity (obj)
     *
     * @param \Doctrine\Common\EventArgs $args
     */
    public function preUpdate(\Doctrine\Common\EventArgs $args)
    {
        //All mappings from updated object
        $mappings = $this->getMappingsFromArgs($args);

        foreach ($mappings as $mapping) {
            if ($mapping->isUseOneProperty()) $this->updateUseOneProperties($args, $mapping);
            else  $this->updateSeparateProperties($args, $mapping);
        }
        $this->dataStorage->recomputeChangeSet($args);
    }


    /**
     * upload field and file data field are NOT SAME ($obj->file and $obj->uploadFile)
     * @param EventArgs $args
     * @param PropertyMapping $mapping
     */
    protected function updateUseOneProperties(\Doctrine\Common\EventArgs $args, PropertyMapping $mapping)
    {
        $uploadedFile = $mapping->getFileUploadPropertyValue();

        //use getOldValue from ORM
        $currentFileData = $this->dataStorage->previusFieldDataIfChanged($mapping->getFileDataPropertyName(), $args);
        $currentFileName = $currentFileData ? $mapping->resolveFileName($currentFileData['fileName']) : null;


        //If no new file
        if (is_null($uploadedFile) || !($uploadedFile instanceof File)) {

            if ($currentFileData) {
                if (!$this->fileStorage->fileExists($currentFileName)) {

                    $fileNameByWebDir = $_SERVER['DOCUMENT_ROOT'] . $currentFileData['path'];

                    if ($this->fileStorage->fileExists($fileNameByWebDir)) {
                        $uploadedFile = new UploadedFile ($fileNameByWebDir,
                            $currentFileData['originalName'], $currentFileData['mimeType'],
                            null, null, true);
                        $fileData = $this->fileStorage->upload($mapping, $uploadedFile);
                        $mapping->setFileDataPropertyValue($fileData);
                    }

                } //Preserve old fileData if current file exist
                else $mapping->setFileDataPropertyValue($currentFileData);

            }


        } //set new File and uploaded file has deleted status - remove file
        else if ($uploadedFile instanceof \Iphp\FileStoreBundle\File\File && $uploadedFile->isDeleted()) {
            if ($this->fileStorage->removeFile($currentFileName)) $mapping->setFileDataPropertyValue(null);
        } //set new file - upload new file
        else {

            //Old value (file) exits and uploaded new file
            if ($currentFileData && !$this->fileStorage->isSameFile($uploadedFile, $currentFileName))
                //before upload new file delete old file
                $this->fileStorage->removeFile($currentFileName);

            $fileData = $this->fileStorage->upload($mapping, $uploadedFile);
            $mapping->setFileDataPropertyValue($fileData);
        }

    }


    /**
     * upload field and file data field are SAME ($obj->file)
     * @param EventArgs $args
     * @param PropertyMapping $mapping
     */
    protected function updateSeparateProperties(\Doctrine\Common\EventArgs $args, PropertyMapping $mapping)
    {
        $uploadedFile = $mapping->getFileUploadPropertyValue();
        $currentFileData = $mapping->getFileDataPropertyValue();
        $previousFileData = $this->dataStorage->previusFieldDataIfChanged($mapping->getFileDataPropertyName(), $args);


        $currentFileName = $previousFileData ? $mapping->resolveFileName($previousFileData['fileName']) : null;


        //delete current file
        if ($previousFileData && (
                // $obj->setFile (null)
                is_null($currentFileData) ||
                //$obj->setUploadFile (Iphp\File::createEmpty()->delete())
                $uploadedFile && $uploadedFile instanceof \Iphp\FileStoreBundle\File\File && $uploadedFile->isDeleted())
        ) {
            if ($this->fileStorage->removeFile($currentFileName)) $mapping->setFileDataPropertyValue(null);
        } //upload new file
        else if ($uploadedFile && $uploadedFile instanceof File) {

            //Old value (file) exists and uploaded new file
            if ($currentFileName && !$this->fileStorage->isSameFile($uploadedFile, $currentFileName))
                //before upload new file delete old file
                $this->fileStorage->removeFile($currentFileName);

            $fileData = $this->fileStorage->upload($mapping, $uploadedFile);
            $mapping->setFileDataPropertyValue($fileData);
        }
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
