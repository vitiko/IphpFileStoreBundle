<?php

namespace Iphp\FileStoreBundle\Mapping\Annotation;

/**
 * UploadableField.
 *
 * @Annotation
 *
 * @author Vitiko <vitiko@mail.ru>
 */
class UploadableField
{
    /**
     * @var string $mapping
     */
    protected $mapping;

    /**
     * @var string $name
     */
    protected $fileUploadPropertyName;

    /**
     * @var string $fileNameProperty
     */
    protected $fileDataPropertyName;

    /**
     * Constructs a new instance of UploadableField.
     *
     * @param array $options The options.
     */
    public function __construct(array $options)
    {
        if (isset($options['mapping'])) {
            $this->mapping = $options['mapping'];
        } else {
            throw new \InvalidArgumentException('The "mapping" attribute of UploadableField is required.');
        }
        if (isset($options['fileDataProperty']))
        {
            $this->setFileDataPropertyName($options['fileDataProperty']);
        }
    }

    /**
     * Gets the mapping name.
     *
     * @return string The mapping name.
     */
    public function getMapping()
    {
        return $this->mapping;
    }

    /**
     * Sets the mapping name.
     *
     * @param $mapping The mapping name.
     */
    public function setMapping($mapping)
    {
        $this->mapping = $mapping;
    }

    /**
     * Gets the property name.
     *
     * @return string The property name.
     */
    public function getFileUploadPropertyName()
    {
        return $this->fileUploadPropertyName;
    }

    /**
     * Sets the property name.
     *
     * @param $propertyName The property name.
     */
    public function setFileUploadPropertyName($propertyName)
    {
        $this->fileUploadPropertyName = $propertyName;
    }

    /**
     * Gets the file name property.
     * By default using propertyName
     * @return string The file name property.
     */
    public function getFileDataPropertyName()
    {
        return $this->fileDataPropertyName ? $this->fileDataPropertyName : $this->fileUploadPropertyName;
    }

    /**
     * Sets the file data property name.
     *
     * @param $fileNameProperty The file name property.
     */
    public function setFileDataPropertyName ($fileDataPropertyName)
    {
        $this->fileDataPropertyName = $fileDataPropertyName;
    }
}
