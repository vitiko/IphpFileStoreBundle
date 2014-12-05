<?php
namespace Iphp\FileStoreBundle\Form\Type;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Iphp\FileStoreBundle\DataStorage\DataStorageInterface;
use Iphp\FileStoreBundle\Form\DataTransformer\FileDataTransformer;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Iphp\FileStoreBundle\Mapping\PropertyMappingFactory;
use Symfony\Component\Form\FormFactoryInterface;

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

    /**
     * @var \Symfony\Component\Form\FormFactoryInterface
     */
    private $formFactory;


    public function __construct(PropertyMappingFactory $mappingFactory,
                                DataStorageInterface $dataStorage,
                                FileDataTransformer $transformer,
                                FormFactoryInterface $formFactory)
    {
        $this->mappingFactory = $mappingFactory;
        $this->dataStorage = $dataStorage;
        $this->transformer = $transformer;
        $this->formFactory = $formFactory;
    }

    public static function getSubscribedEvents()
    {
        return array(
            FormEvents::PRE_BIND => 'preBind',
            FormEvents::POST_SET_DATA => 'postSet'
        );
    }


    public function postSet(FormEvent $event)
    {
        $data = $event->getData();
        $form = $event->getForm();

        //for first call event parent is null, cause of setData(null) in Form constructor.
        if (!$form->getParent()) return;
        $obj = $form->getParent()->getData();
        if (!$obj) return;

        $mapping = $this->mappingFactory->getMappingFromField($obj,
            $this->dataStorage->getReflectionClass($obj),
            $form->getName());
        if ($mapping) {
            $this->transformer->setMapping($mapping);



            //print '---'.$mapping->getProtected();

            if ($mapping->getProtected() == 'ondemand') {

               // print 55;

           $form->add($this->formFactory->createNamed('isprotected', 'checkbox', null, array(

                    'label' => 'Protected file',
                    'required' => false
                )));

         /*       $form->add ('isprotected', 'checkbox', array(

                    'label' => 'Protected file',
                    'required' => false
                ));*/
            }
        }

    }

    public function preBind(FormEvent $event)
    {

        $form = $event->getForm();
        $obj = $form->getParent()->getData();


        //For oneToMany at SonataAdmin
        if (!$obj) return;

        $mapping = $this->mappingFactory->getMappingFromField($obj,
            $this->dataStorage->getReflectionClass($obj),
            $form->getName());
        if ($mapping) $this->transformer->setMapping($mapping);
    }


}