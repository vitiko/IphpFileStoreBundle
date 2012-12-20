<?php
 namespace Iphp\FileStoreBundle\Mapping;


use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\Common\EventArgs;

use Iphp\FileStoreBundle\Mapping\PropertyMapping;
use Iphp\FileStoreBundle\Driver\AnnotationDriver;
use Iphp\FileStoreBundle\FileStorage\FileStorageInterface;
use Iphp\FileStoreBundle\DataStorage\DataStorageInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Iphp\FileStoreBundle\Mapping\Annotation\UploadableField;

/**
 * PropertyMappingFactory.
 *
 * @author Vitiko <vitiko@mail.ru>
 */
class PropertyMappingFactory
{
    /**
     * @var ContainerInterface $container
     */
    protected $container;

    /**
     * @var AnnotationDriver $driver
     */
    protected $driver;

    /**
     * @var \Iphp\FileStoreBundle\DataStorage\DataStorageInterface $dataStorage
     */
    protected $dataStorage;

    /**
     * @var array $mappings
     */
    protected $mappings;

    /**
     * Constructs a new instance of PropertyMappingFactory.
     *
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container The container.
     * @param \Iphp\FileStoreBundle\Driver\AnnotationDriver              $driver    The driver.
     * @param \Iphp\FileStoreBundle\DataStorage\DataStorageInterface             $dataStorage    dataStorage .
     * @param array                                                     $mappings  The configured mappings.
     */
    public function __construct(ContainerInterface $container,
                                AnnotationDriver $driver,
                                DataStorageInterface $dataStorage,
                                array $mappings)
    {
        $this->container = $container;
        $this->driver = $driver;
        $this->dataStorage = $dataStorage;
        $this->mappings = $mappings;
    }


    /**
     * Creates an array of PropetyMapping objects which contain the
     * configuration for the uploadable fields in the specified
     * object.
     *
     * @param  object $obj The object.
     * @return  \Iphp\FileStoreBundle\Mapping\PropertyMapping[] objects.
     */
    public function fromObject($obj)
    {
        $class = $this->dataStorage->getReflectionClass($obj);
        if (!$this->hasAnnotations($class)) return array();

        $mappings = array();
        foreach ($this->driver->readUploadableFields($class) as $field) {
            $mappings[] = $this->createMapping($obj, $field);
        }

        return $mappings;
    }


    public function fromEventArgs(EventArgs $args)
    {
        $obj = $this->dataStorage->getObjectFromArgs($args);

        return $this->fromObject($obj);
    }

    /**
     * Creates a property mapping object which contains the
     * configuration for the specified uploadable field.
     *
     * @param  object               $obj   The object.
     * @param  string               $field The field.
     * @return null|\Iphp\FileStoreBundle\Mapping\PropertyMapping The property mapping.
     */
    public function fromField($obj, $field)
    {
        $class = $this->dataStorage->getReflectionClass($obj);
        if (!$this->hasAnnotations($class)) return null;

        $annot = $this->driver->readUploadableField($class, $field);
        if (null === $annot) {
            return null;
        }

        return $this->createMapping($obj, $annot);
    }

    public function hasAnnotations(\ReflectionClass $class)
    {
        return null !== $this->driver->readUploadable($class);
    }

    /**
     * Creates the property mapping from the read annotation and configured mapping.
     *
     * @param  object                                          $obj   The object.
     * @param  \Iphp\FileStoreBundle\Mapping\Annotation\UploadableField $field The read annotation.
     * @return \Iphp\FileStoreBundle\Mapping\PropertyMapping     The property mapping.
     * @throws \InvalidArgumentException
     */
    protected function createMapping($obj, UploadableField $field)
    {
        $class = $this->dataStorage->getReflectionClass($obj);

        if (!array_key_exists($field->getMapping(), $this->mappings)) {
            throw new \InvalidArgumentException(sprintf(
                'No mapping named "%s" configured.', $field->getMapping()
            ));
        }

        $config = $this->mappings[$field->getMapping()];

        $mapping = new PropertyMapping($obj, $config, $this->container);
        $mapping->setProperty($class->getProperty($field->getPropertyName()));
        $mapping->setFileNameProperty($class->getProperty($field->getFileNameProperty()));


        $mapping->setMappingName($field->getMapping());


        return $mapping;
    }
}
