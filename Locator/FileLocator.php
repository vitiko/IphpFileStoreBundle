<?php


namespace Iphp\FileStoreBundle\Locator;


use Iphp\FileStoreBundle\Mapping\PropertyMappingFactory;
use Symfony\Component\HttpFoundation\File\File;

/**
 * @author Vitiko <vitiko@mail.ru>
 */
class FileLocator
{


    /**
     * @var \Iphp\FileStoreBundle\Mapping\PropertyMappingFactory
     */
    protected $propertyMappingFactory;

    function __construct(PropertyMappingFactory $propertyMappingFactory)
    {
        $this->propertyMappingFactory = $propertyMappingFactory;
    }


    function getFileFromEntity($entity, $field)
    {
        $mapping = $this->propertyMappingFactory->getMappingFromField($entity, new \ReflectionClass ($entity), $field);
        if (!$mapping) return null;

        $fileName = $mapping->resolveFileName();
        if (!$fileName ) return null;

        return new File ($fileName);
    }


}