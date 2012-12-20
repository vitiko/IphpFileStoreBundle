<?php

namespace Iphp\FileStoreBundle\Mapping;

use Iphp\FileStoreBundle\Naming\NamerInterface;
use Iphp\FileStoreBundle\FileStorage\FileStorageInterface;
use Iphp\FileStoreBundle\Naming\DirectoryNamerInterface;

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
     * @var ContainerInterface $container
     */
    protected $container;

    /**
     * @var \ReflectionProperty $property
     */
    protected $property;

    /**
     * @var \ReflectionProperty $fileNameProperty
     */
    protected $fileNameProperty;

    /**
     * @var string $mappingName
     */
    protected $mappingName;


    function __construct($obj, $config, $container)
    {
        $this->obj = $obj;
        $this->setConfig($config);
        $this->container = $container;
    }

    /**
     * Gets the reflection property that represents the
     * annotated property.
     *
     * @return \ReflectionProperty The property.
     */
    /*    public function getProperty()
    {
        return $this->property;
    }*/

    /**
     * Sets the reflection property that represents the annotated
     * property.
     *
     * @param \ReflectionProperty $property The reflection property.
     */
    public function setProperty(\ReflectionProperty $property)
    {
        $this->property = $property;
        $this->property->setAccessible(true);
    }




    /**
     * Gets the reflection property that represents the property
     * which holds the file name for the mapping.
     *
     * @return \ReflectionProperty The reflection property.
     */
    /*   public function getFileNameProperty()
    {
        return $this->fileNameProperty;
    }*/


    public function useFileNamer($fileName)
    {
        if ($this->hasNamer()) {
            foreach ($this->config['namer'] as $method => $namer) {
                $fileName = call_user_func(
                    array($this->container->get($namer['service']), $method . 'Rename'),
                    $this,
                    $fileName,
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


                $subPath = call_user_func(
                    array($this->container->get($namer['service']), $method . 'Rename'),
                    $this,
                    $replaceMode ? $path : $fileName,
                    isset($namer['params']) ? $namer['params'] : array());


                if ($replaceMode) $path = $subPath;
                else $path .= ($subPath ? '/' : '') . $subPath;
            }

        }

        return  $path;



    }


    public function needResolveCollision($fileName, FileStorageInterface $fileStorage)
    {
        return !$this->isOverwriteDuplicates() && $fileStorage->fileExists($this, $fileName);

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

        $try = 0;
        while ($this->needResolveCollision($dirName.'/'.$fileName, $fileStorage)) {
            if ($try > 15)
                throw new \Exception ("Can't resolve collision for file  " . $fileName);

            $fileName = $this->resolveFileCollision($origName, $originalName, ++$try);
        }

        return array (
            ($this->isStoreFullDir() ? $this->getUploadDir() : '') .$dirName.'/'.$fileName,
            $this->getUploadPath() ? $this->getUploadPath() . $dirName. '/' . urlencode($fileName) : '');
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

            return call_user_func(
                array($this->container->get($firstNamer['service']), 'resolveCollision'), $fileName, $attempt);
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
     * Sets the reflection property that represents the property
     * which holds the file name for the mapping.
     *
     * @param \ReflectionProperty $fileNameProperty The reflection property.
     */
    public function setFileNameProperty(\ReflectionProperty $fileNameProperty)
    {
        $this->fileNameProperty = $fileNameProperty;
        $this->fileNameProperty->setAccessible(true);
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
    public function getPropertyName()
    {
        return $this->property->getName();
    }

    /**
     * Gets the value of the annotated property.
     * @return \Symfony\Component\HttpFoundation\File\UploadedFile
     */
    public function getFilePropertyValue()
    {
        return $this->property->getValue($this->obj);
    }


    public function setFilePropertyValue($file)
    {
        return $this->property->setValue($this->obj, $file);
    }

    public function setFileDataPropertyValue($fileData)
    {
        return $this->fileNameProperty->setValue($this->obj, $fileData);
    }


    public function getFileDataPropertyValue()
    {
        return $this->fileNameProperty->getValue($this->obj);
    }


    /**
     * Gets the configured file name property name.
     *
     * @return string The name.
     */
    public function getFileDataPropertyName()
    {
        return $this->fileNameProperty->getName();
    }


    /**
     * Determines if the file should be deleted upon removal of the
     * entity.
     *
     * @return bool True if delete on remove, false otherwise.
     */
    public function getDeleteOnRemove()
    {
        return $this->config['delete_on_remove'];
    }


    public function isOverwriteDuplicates()
    {
        return $this->config['overwrite_duplicates'];
    }


    public function getObj()
    {
        return $this->obj;
    }



    public function resolveFileName ($fileName = null)
    {
        if (!$fileName)
        {
            $fileData = $this->getFileDataPropertyValue();
            if ($fileData) $fileName = $fileData['fileName'];
        }
        if (!$fileName) return null;

        return ($this->isStoreFullDir() ? '' : $this->getUploadDir()). $fileName;
    }
}
