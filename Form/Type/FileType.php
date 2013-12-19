<?php

namespace Iphp\FileStoreBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Iphp\FileStoreBundle\FileStorage\FileStorageInterface;
use Iphp\FileStoreBundle\DataStorage\DataStorageInterface;
use Iphp\FileStoreBundle\Mapping\PropertyMappingFactory;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Exception\CreationException;
use Symfony\Component\Form\FormView;


use Symfony\Component\Form\ReversedTransformer;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;

use Iphp\FileStoreBundle\Form\DataTransformer\FileDataTransformer;
use Iphp\FileStoreBundle\Form\DataTransformer\FileDataViewTransformer;

/**
 * @author Vitiko <vitiko@mail.ru>
 */
class FileType extends AbstractType
{

    /**
     * @var \Iphp\FileStoreBundle\Mapping\PropertyMappingFactory
     */
    protected $mappingFactory;

    /**
     * @var \Iphp\FileStoreBundle\DataStorage\DataStorageInterface
     */
    protected $dataStorage;

    /**
     * @var \Iphp\FileStoreBundle\FileStorage\FileStorageInterface
     */
    protected $fileStorage;

    protected $transformer;

    public function __construct(PropertyMappingFactory $mappingFactory,
                                DataStorageInterface $dataStorage,
                                FileStorageInterface $fileStorage)
    {
        $this->mappingFactory = $mappingFactory;
        $this->dataStorage = $dataStorage;
        $this->fileStorage = $fileStorage;
        $this->transformer = new FileDataTransformer($this->fileStorage);
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'read_only' => false,
            'upload' => true,
            'show_uploaded' => true

        ));
    }


    public function buildView(FormView $view, FormInterface $form, array $options)
    {

        $mapping = $this->transformer->getMapping();
        $view->vars['is_mapped'] = $mapping ? true : false;

        if (!$mapping) return;


       //  $view->vars['mappingProtected'] = $mapping->getProtected();
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $subscriber = new FileTypeBindSubscriber(
            $this->mappingFactory,
            $this->dataStorage,
            $this->transformer,
            $builder->getFormFactory()
        );
        $builder->addEventSubscriber($subscriber);


        $builder->add('file', 'file', array('required' => false))
            ->add('delete', 'checkbox', array('required' => false))
            ->addViewTransformer($this->transformer);




        //for sonata admin
        //    ->addViewTransformer(new FileDataViewTransformer());
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'form';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'iphp_file';
    }
}



