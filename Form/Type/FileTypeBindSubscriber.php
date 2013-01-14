<?php
namespace Iphp\FileStoreBundle\Form\Type;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Iphp\FileStoreBundle\Form\DataTransformer\FileDataTransformer;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Iphp\FileStoreBundle\Mapping\PropertyMappingFactory;

/**
 * @author Vitiko <vitiko@mail.ru>
 */
class FileTypeBindSubscriber implements EventSubscriberInterface
{

    /**
     * @var \Iphp\FileStoreBundle\Mapping\PropertyMappingFactory
     */
    private $mappingFactory;

    /**
     * @var \Symfony\Component\Form\DataTransformerInterface
     */
    private $transformer;

    public function __construct(PropertyMappingFactory $mappingFactory,  FileDataTransformer $transformer)
    {
        $this->mappingFactory = $mappingFactory;
        $this->transformer = $transformer;
    }

    public static function getSubscribedEvents()
    {
        return array(FormEvents::PRE_BIND => 'preBind');
    }


    public function preBind(FormEvent $event)
    {
        $obj = $event->getForm()->getParent()->getData();
        $mapping = $this->mappingFactory->fromField($obj, $event->getForm()->getName());
        if ($mapping) $this->transformer->setMapping($mapping);
    }


}