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
                                FileDataTransformer $transformer,
                                array $options = array())
    {
        $this->mappingFactory = $mappingFactory;
        $this->dataStorage = $dataStorage;
        $this->transformer = $transformer;
    }

    public static function getSubscribedEvents()
    {
        return array(FormEvents::PRE_SUBMIT => 'preBind',
            FormEvents::PRE_SET_DATA => 'preSet');
    }


    public function preSet(FormEvent $event)
    {
        $form = $event->getForm();
        $propertyName = $form->getName();

        $obj = $form->getParent()->getData();
        //For oneToMany at SonataAdmin

        if (!$obj) return;

        $mapping = $this->mappingFactory->getMappingFromField($obj,
            $this->dataStorage->getReflectionClass($obj),
            $propertyName );


        if ($mapping)
        {

            if ($propertyName  == $mapping->getFileUploadPropertyName())
            $form->add('file', 'file', array('required' => false));

            if ($propertyName == $mapping->getFileDataPropertyName())
            $form->add('delete', 'checkbox', array('required' => false));

            //);
        }

        /*  $form->add('file', 'file', array('required' => false))
              ->add('delete', 'checkbox', array('required' => false));*/
    }

    public function preBind(FormEvent $event)
    {

        $form = $event->getForm();
        $propertyName = $form->getName();
        $obj = $form->getParent()->getData();


        //For oneToMany at SonataAdmin
        if (!$obj) return;

        $mapping = $this->mappingFactory->getMappingFromField($obj,
            $this->dataStorage->getReflectionClass($obj),
            $propertyName );


        if ($mapping) {
    /*        $form->add('file', 'file', array('required' => false))
                ->add('delete', 'checkbox', array('required' => false));*/

            $this->transformer->setMapping($mapping,
                $mapping->getFileUploadPropertyName() == $propertyName  ?
                    FileDataTransformer::MODE_UPLOAD_FIELD :  FileDataTransformer::MODE_FILEDATA_FIELD
               );
        }
    }


}