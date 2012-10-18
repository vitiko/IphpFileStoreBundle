<?php

namespace Iphp\FileStoreBundle\Driver;

use Doctrine\Common\Annotations\Reader;

/**
 * AnnotationDriver.
 *
 * @author Vitiko <vitiko@mail.ru>
 */
class AnnotationDriver
{
    /**
     * @var Reader $reader
     */
    protected $reader;

    /**
     * Constructs a new instance of AnnotationDriver.
     *
     * @param \Doctrine\Common\Annotations\Reader $reader The  annotation reader.
     */
    public function __construct(Reader $reader)
    {
        $this->reader = $reader;
    }

    /**
     * Attempts to read the uploadable annotation.
     *
     * @param  \ReflectionClass                                $class The reflection class.
     * @return null|\Iphp\FileStoreBundle\Annotation\Uploadable The annotation.
     */
    public function readUploadable(\ReflectionClass $class)
    {
        return $this->reader->getClassAnnotation($class, 'Iphp\FileStoreBundle\Mapping\Annotation\Uploadable');
    }

    /**
     * Attempts to read the uploadable field annotations.
     *
     * @param  \ReflectionClass $class The reflection class.
     * @return array            An array of UploadableField annotations.
     */
    public function readUploadableFields(\ReflectionClass $class)
    {
        $fields = array();

        foreach ($class->getProperties() as $prop) {
            $field = $this->reader->getPropertyAnnotation($prop, 'Iphp\FileStoreBundle\Mapping\Annotation\UploadableField');
            if (null !== $field) {
                $field->setPropertyName($prop->getName());
                $fields[] = $field;
            }
        }

        return $fields;
    }

    /**
     * Attempts to read the uploadable field annotation of the
     * specified property.
     *
     * @param  \ReflectionClass                                     $class The class.
     * @param  string                                               $field The field
     * @return null|\Iphp\FileStoreBundle\Annotation\UploadableField The uploadable field.
     */
    public function readUploadableField(\ReflectionClass $class, $field)
    {
        try {
            $prop = $class->getProperty($field);

            $field = $this->reader->getPropertyAnnotation($prop, 'Iphp\FileStoreBundle\Mapping\Annotation\UploadableField');
            if (null === $field) {
                return null;
            }

            $field->setPropertyName($prop->getName());

            return $field;
        } catch (\ReflectionException $e) {
            return null;
        }
    }
}
