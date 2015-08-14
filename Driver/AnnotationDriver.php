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


    protected $uploadedClass = array();

    protected $uploadedFields = array();

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
     * @param  \ReflectionClass $class The reflection class.
     * @return null|\Iphp\FileStoreBundle\Annotation\Uploadable The annotation.
     */
    public function readUploadable(\ReflectionClass $class)
    {
        $baseClassName = $className = $class->getNamespaceName() . '\\' . $class->getName();
        do {
            if (isset($this->uploadedClass[$className])) {
                if ($baseClassName != $className)
                    $this->uploadedClass[$baseClassName] = $this->uploadedClass[$className];
                return $this->uploadedClass[$baseClassName];
            }

            $annotation = $this->reader->getClassAnnotation($class, 'Iphp\FileStoreBundle\Mapping\Annotation\Uploadable');
            if ($annotation) {
                $this->uploadedClass[$baseClassName] = $annotation;
                if ($baseClassName != $className) $this->uploadedClass[$className] = $annotation;

                return $annotation;
            }
            $class = $class->getParentClass();
            if ($class) $className = $class->getNamespaceName() . '\\' . $class->getName();
        } while ($class);

        return $annotation;
    }

    /**
     * Attempts to read the uploadable field annotations.
     *
     * @param  \ReflectionClass $class The reflection class.
     * @return  \Iphp\FileStoreBundle\Mapping\Annotation\UploadableField[]
     */
    public function readUploadableFields(\ReflectionClass $class)
    {
        $propertyAnnotations = array();

        foreach ($class->getProperties() as $prop) {

            $propertyAnnotation = $this->reader->getPropertyAnnotation($prop, 'Iphp\FileStoreBundle\Mapping\Annotation\UploadableField');
            if (null !== $propertyAnnotation) {
                $propertyAnnotation->setFileUploadPropertyName($prop->getName());
                $propertyAnnotations[] = $propertyAnnotation;
            }
        }

        return $propertyAnnotations;
    }

    /**
     * Attempts to read the uploadable field annotation of the
     * specified property.
     *
     * @param  \ReflectionClass $class The class.
     * @param  string $field The field
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

            $field->setFileUploadPropertyName($prop->getName());

            return $field;
        } catch (\ReflectionException $e) {
            return null;
        }
    }
}
