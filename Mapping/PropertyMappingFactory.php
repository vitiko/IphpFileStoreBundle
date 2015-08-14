<?php
namespace Iphp\FileStoreBundle\Mapping;

use Iphp\FileStoreBundle\Mapping\PropertyMapping;
use Iphp\FileStoreBundle\Driver\AnnotationDriver;
use Iphp\FileStoreBundle\Naming\NamerServiceInvoker;


use Iphp\FileStoreBundle\Mapping\Annotation\UploadableField;

/**
 * PropertyMappingFactory.
 *
 * @author Vitiko <vitiko@mail.ru>
 */
class PropertyMappingFactory
{
    /**
     * @var \Iphp\FileStoreBundle\Naming\NamerServiceInvoker $namerServiceInvoker
     */
    protected $namerServiceInvoker;

    /**
     * @var \Iphp\FileStoreBundle\Driver\AnnotationDriver $driver
     */
    protected $driver;


    /**
     * @var array $mappingsConfig MappingConfiguration
     */
    protected $mappingsConfig = array();

    /**
     * Constructs a new instance of PropertyMappingFactory.
     *
     * @param \Iphp\FileStoreBundle\Naming\NamerServiceInvoker $namerServiceInvoker Object for invoke rename methods.
     * @param \Iphp\FileStoreBundle\Driver\AnnotationDriver $driver The driver.
     * @param array $mappings The configured mappings.
     */
    public function __construct(NamerServiceInvoker $namerServiceInvoker,
                                AnnotationDriver $driver,
                                array $mappingsConfig)
    {
        $this->namerServiceInvoker = $namerServiceInvoker;
        $this->driver = $driver;
        $this->mappingsConfig = $mappingsConfig;
    }


    /**
     * Creates an array of PropetyMapping objects which contain the
     * configuration for the uploadable fields in the specified
     * object.
     *
     * @param  object $obj The object.
     * @param  \ReflectionClass $class
     * @return  \Iphp\FileStoreBundle\Mapping\PropertyMapping[] objects.
     */
    public function getMappingsFromObject($obj, \ReflectionClass $class)
    {
        if (!$this->hasAnnotations($class)) return array();

        $mappings = array();
        foreach ($this->driver->readUploadableFields($class) as $field) {
            $mappings[] = $this->createMapping($obj, $class, $field);
        }

        return $mappings;
    }


    /**
     * Creates a property mapping object which contains the
     * configuration for the specified uploadable field.
     *
     * @param  object $obj The object.
     * @param  \ReflectionClass $class
     * @param  string $field entity field name
     * @param  bool $allFields search all fields (if upload field and file data field are separate)
     * @return null|\Iphp\FileStoreBundle\Mapping\PropertyMapping The property mapping.
     */
    public function getMappingFromField($obj, \ReflectionClass $class, $field, $allFields = true)
    {
        if (!$this->hasAnnotations($class)) return null;

        $annotation = $this->driver->readUploadableField($class, $field);

        if (!$annotation && $allFields) {
            $propertyAnnotations= $this->driver->readUploadableFields($class);

            foreach ($propertyAnnotations as $propertyAnnotation)
            {
                if ($propertyAnnotation->getFileDataPropertyName() == $field ||
                    $propertyAnnotation->getFileUploadPropertyName() == $field)
                {
                    $annotation = $propertyAnnotation;
                    break;
                }
            }
        }
        if (null === $annotation) return null;

        return $this->createMapping($obj, $class, $annotation);
    }

    public function hasAnnotations(\ReflectionClass $class)
    {
        return null !== $this->driver->readUploadable($class);
    }

    /**
     * Creates the property mapping from the read annotation and configured mapping.
     *
     * @param  object $obj The object.
     * @param  \ReflectionClass $class
     * @param  \Iphp\FileStoreBundle\Mapping\Annotation\UploadableField $field The read annotation.
     * @return \Iphp\FileStoreBundle\Mapping\PropertyMapping     The property mapping.
     * @throws \InvalidArgumentException
     */
    protected function createMapping($obj, \ReflectionClass $class, UploadableField $field)
    {
        if (!array_key_exists($field->getMapping(), $this->mappingsConfig)) {
            throw new \InvalidArgumentException(sprintf(
                'No mapping named "%s" configured.', $field->getMapping()
            ));
        }

        $config = $this->mappingsConfig[$field->getMapping()];

        $mapping = new PropertyMapping($obj, $config, $this->namerServiceInvoker);
        $mapping->setFileUploadProperty($class->getProperty($field->getFileUploadPropertyName()));
        $mapping->setFileDataProperty($class->getProperty($field->getFileDataPropertyName()));

        $mapping->setMappingName($field->getMapping());


        return $mapping;
    }


}
