<?php

namespace Iphp\FileStoreBundle\Mapping;

use Iphp\FileStoreBundle\Naming\NamerInterface;
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

        return array(
            $this->getUploadDir() . $path,
            $this->getUploadPath() ? $this->getUploadPath() . $path . '/' . urlencode($fileName) : '');
    }


    public function needResolveCollision()
    {
        return !$this->isOverwriteDuplicates();
    }


    public function resolveFileCollision($fileName, $clientOriginalName, $attempt = 1)
    {

        if ($this->hasNamer()) {
            $firstNamer = current($this->config['namer']);

            $newFileName = call_user_func(
                array($this->container->get($firstNamer['service']), 'resolveCollision'), $fileName, $attempt);

            //return dirName and path
            $resolveData = $this->useDirectoryNamer($newFileName, $clientOriginalName);
            $resolveData[] = $newFileName;
            return $resolveData;
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

}
