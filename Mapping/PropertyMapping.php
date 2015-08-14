<?php

namespace Iphp\FileStoreBundle\Mapping;

use Iphp\FileStoreBundle\FileStorage\FileStorageInterface;
use Iphp\FileStoreBundle\Naming\NamerServiceInvoker;


use Symfony\Component\HttpFoundation\File\File;

/**
 * PropertyMapping.
 *
 * @author Vitiko <vitiko@mail.ru>
 *
 */
class PropertyMapping
{


    protected $obj;

    /**
     * @var array $config
     */
    protected $config;

    /**
     * @var \Iphp\FileStoreBundle\Naming\NamerServiceInvoker $namerServiceInvoker
     */
    protected $namerServiceInvoker;

    /**
     * @var \ReflectionProperty $property reflection property that represents the annotated  property
     */
    protected $fileUploadProperty;

    /**
     * @var \ReflectionProperty $fileNameProperty  reflection property that represents property which holds data
     */
    protected $fileDataProperty;

    /**
     * @var string $mappingName
     */
    protected $mappingName;


    function __construct($obj, $config, NamerServiceInvoker $namerServiceInvoker)
    {
        $this->obj = $obj;
        $this->setConfig($config);
        $this->namerServiceInvoker = $namerServiceInvoker;
    }


    /**
     * Sets the reflection property that represents the annotated
     * property.
     *
     * @param \ReflectionProperty $property The reflection property.
     */
    public function setFileUploadProperty(\ReflectionProperty $property)
    {
        $this->fileUploadProperty = $property;
        $this->fileUploadProperty->setAccessible(true);
    }


    /**
     * Sets the reflection property that represents the property
     * which holds the file name for the mapping.
     *
     * @param \ReflectionProperty $fileNameProperty The reflection property.
     */
    public function setFileDataProperty(\ReflectionProperty $fileNameProperty)
    {
        $this->fileDataProperty = $fileNameProperty;
        $this->fileDataProperty->setAccessible(true);
    }


    /**
     * Invoke file namers
     * @param $fileName
     * @return mixed
     */
    public function useFileNamer($fileName)
    {
        if ($this->hasNamer()) {
            foreach ($this->config['namer'] as $method => $namer) {
                $fileName = $this->namerServiceInvoker->rename($namer['service'], $method, $this, $fileName,
                    isset($namer['params']) ? $namer['params'] : array());
            }
        }
        return $fileName;
    }


    /**
     * Determines if the mapping has a custom namer configured.
     *
     * @return bool True if has namer, false otherwise.
     */
    public function hasNamer()
    {
        return isset($this->config['namer']) && $this->config['namer'];
    }


    /**
     * Determines if the mapping requires store full path to file
     * @return bool
     */
    public function isStoreFullDir()
    {
        return isset($this->config['store_fulldir']) && $this->config['store_fulldir'];
    }


    /**
     * Determines if the mapping has a custom directory namer configured.
     *
     * @return bool True if has directory namer, false otherwise.
     */
    public function hasDirectoryNamer()
    {
        return isset($this->config['directory_namer']) && $this->config['directory_namer'];
    }

    /**
     * create subdirectory name based on chain of directory namers
     *
     * @return array directory name and web path to file
     */
    public function useDirectoryNamer($fileName, $clientOriginalName)
    {
        $path = '';

        if ($this->hasDirectoryNamer()) {
            foreach ($this->config['directory_namer'] as $method => $namer) {

                $replaceMode = $method == 'replace' ||
                    (isset($namer['params']['replace']) && $namer['params']['replace']);


                $subPath = $this->namerServiceInvoker->rename($namer['service'],
                    $method,
                    $this,
                    $replaceMode ? $path : $fileName,
                    isset($namer['params']) ? $namer['params'] : array());


/*                $subPath = call_user_func(
                    array($this->container->get($namer['service']), $method . 'Rename'),
                    $this,
                    $replaceMode ? $path : $fileName,
                    isset($namer['params']) ? $namer['params'] : array());*/


                if ($replaceMode) $path = $subPath;
                else $path .= ($subPath ? '/' : '') . $subPath;
            }

        }

        return $path;
    }


    public function needResolveCollision($fileName, FileStorageInterface $fileStorage)
    {
        //print "\n -->".$fileName;
        return !$this->isOverwriteDuplicates() && $fileStorage->fileExists($this->resolveFileName($fileName));
    }


    /**
     * @param $originalName
     * @param \Iphp\FileStoreBundle\FileStorage\FileStorageInterface $fileStorage
     * @return array relative or full fileName and file path at web
     * @throws \Exception
     */
    public function prepareFileName($originalName, FileStorageInterface $fileStorage)
    {
        $fileName = $origName = $this->useFileNamer($originalName);
        $dirName = $this->useDirectoryNamer($fileName, $originalName);
        if (substr($dirName,-1) != '/') $dirName.='/';

        $try = 0;

        while ($this->needResolveCollision(  $dirName  . $fileName , $fileStorage)) {
            if ($try > 15)
                throw new \Exception ("Can't resolve collision for file  " . $fileName);

            $fileName = $this->resolveFileCollision($origName, $originalName, ++$try);
        }


        return array(
            //file system  path
            ($this->isStoreFullDir() ? $this->getUploadDir() : '')  . $dirName. $fileName ,
            //web path
            $this->getUploadPath() ? $this->getUploadPath() . $dirName . urlencode($fileName) : '');
    }


    /**
     * @param $fileName
     * @param $clientOriginalName
     * @param int $attempt
     * @return string new file path
     * @throws \Exception
     */
    public function resolveFileCollision($fileName, $clientOriginalName, $attempt = 1)
    {

        if ($this->hasNamer()) {
            $firstNamer = current($this->config['namer']);


           return $this->namerServiceInvoker->resolveCollision ($firstNamer['service'],  $fileName, $attempt);
        }

        throw new \Exception ('Filename resolving collision not supported (namer is empty).Duplicate filename ' . $fileName);
    }


    public function getUploadDir()
    {
        return $this->config['upload_dir'];
    }


    public function getUploadPath()
    {
        return $this->config['upload_path'];
    }


    /**
     * Sets the configured configuration mapping.
     *
     * @param array $mapping The mapping;
     */
    public function setConfig(array $config)
    {
        $this->config = $config;
    }

    /**
     * Gets the configured configuration mapping name.
     *
     * @return string The mapping name.
     */
    public function getMappingName()
    {
        return $this->mappingName;
    }

    /**
     * Sets the configured configuration mapping name.
     *
     * @param $mappingName The mapping name.
     */
    public function setMappingName($mappingName)
    {
        $this->mappingName = $mappingName;
    }

    /**
     * Gets the name of the annotated property.
     *
     * @return string The name.
     */
    public function getFileUploadPropertyName()
    {
        return   $this->fileUploadProperty->getName();
    }

    /**
     * Gets the value of the annotated property.
     * @return \Symfony\Component\HttpFoundation\File\UploadedFile
     */
    public function getFileUploadPropertyValue()
    {
        return $this->fileUploadProperty->getValue($this->obj);
    }


    public function setFileUploadPropertyValue($file)
    {
         $this->fileUploadProperty->setValue($this->obj, $file);
    }

    public function setFileDataPropertyValue($fileData)
    {
         $this->fileDataProperty->setValue($this->obj, $fileData);
    }


    public function getFileDataPropertyValue()
    {
        return $this->fileDataProperty->getValue($this->obj);
    }


    /**
     * Gets the configured file name property name.
     *
     * @return string The name.
     */
    public function getFileDataPropertyName()
    {
        return $this->fileDataProperty->getName();
    }

    /**
     * Property for upload and property for file data is one property
     * @return bool
     */
    public function isUseOneProperty()
    {

        return  $this->getFileDataPropertyName() ==  $this->getFileUploadPropertyName() ? true : false;
    }



    /**
     * Determines if the file should be deleted upon removal of the
     * entity. Default true
     *
     * @return bool True if delete on remove, false otherwise.
     */
    public function getDeleteOnRemove()
    {
        return !isset($this->config['delete_on_remove']) || $this->config['delete_on_remove'];
    }


    /**
     * @return bool True if overwrite file duplicates, if false - using resolve collision
     */
    public function isOverwriteDuplicates()
    {
        return isset($this->config['overwrite_duplicates']) && $this->config['overwrite_duplicates'];
    }


    public function getObj()
    {
        return $this->obj;
    }


    public function resolveFileName($fileName = null)
    {
        if (!$fileName) {
            $fileData = $this->getFileDataPropertyValue();
            if ($fileData && isset($fileData['fileName'])) $fileName = $fileData['fileName'];
        }
        if (!$fileName) return null;

        $dir = $this->isStoreFullDir() ? '' : $this->getUploadDir();
        return $dir . (substr($dir,-1) != '/'  &&  substr($fileName,0,1) != '/' ? '/' : ''). $fileName;
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        return $this->config;
    }
}
