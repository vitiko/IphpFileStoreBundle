<?php

namespace Iphp\FileStoreBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
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


    protected $mappingFactory;

    public function __construct(PropertyMappingFactory $mappingFactory)
    {
        $this->mappingFactory = $mappingFactory;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'read_only' => false,
            'upload' => true,
            'show_uploaded' => true

        ));
    }


    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $transformer = new FileDataTransformer();
        $subscriber = new FileTypeBindSubscriber($this->mappingFactory, $transformer);
        $builder->addEventSubscriber($subscriber);


        $builder->add('file', 'file', array('required' => false))
            ->add('delete', 'checkbox', array('label' => 'Удалить', 'required' => false))
            ->addViewTransformer($transformer);

        //for sonata admin
        //    ->addViewTransformer(new FileDataViewTransformer());
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'field';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'iphp_file';
    }
}



