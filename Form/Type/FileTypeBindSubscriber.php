<?php
namespace Iphp\FileStoreBundle\Form\Type;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Iphp\FileStoreBundle\DataStorage\DataStorageInterface;
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


    /**
     * @var \Iphp\FileStoreBundle\DataStorage\DataStorageInterface
     */
    private $dataStorage;

    public function __construct(PropertyMappingFactory $mappingFactory,
                                DataStorageInterface $dataStorage,
                                FileDataTransformer $transformer)
    {
        $this->mappingFactory = $mappingFactory;
        $this->dataStorage = $dataStorage;
        $this->transformer = $transformer;
    }

    public static function getSubscribedEvents()
    {
        return array(FormEvents::PRE_BIND => 'preBind');
    }


    public function preBind(FormEvent $event)
    {

        $form = $event->getForm();
        $obj = $form->getParent()->getData();


        //For oneToMany at SonataAdmin
        if (!$obj) return;

        $mapping =  $this->mappingFactory->getMappingFromField($obj,
            $this->dataStorage->getReflectionClass($obj),
            $form->getName());
        if ($mapping) $this->transformer->setMapping($mapping);
    }


}