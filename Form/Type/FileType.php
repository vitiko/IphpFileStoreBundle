<?php

namespace Iphp\FileStoreBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Iphp\FileStoreBundle\FileStorage\FileStorageInterface;
use Iphp\FileStoreBundle\DataStorage\DataStorageInterface;
use Iphp\FileStoreBundle\Mapping\PropertyMappingFactory;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormBuilderInterface;

use Symfony\Component\Form\FormView;


use Symfony\Component\OptionsResolver\OptionsResolver;
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

    public function __construct(PropertyMappingFactory $mappingFactory,
                                DataStorageInterface $dataStorage,
                                FileStorageInterface $fileStorage)
    {
        $this->mappingFactory = $mappingFactory;
        $this->dataStorage = $dataStorage;
        $this->fileStorage = $fileStorage;
    }

    public function configureOptions (OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'read_only' => false,
            'upload' => true,
            'show_uploaded' => true,
            'show_preview' => true
        ));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'read_only' => false,
            'upload' => true,
            'show_uploaded' => true,
            'show_preview' => true
        ));
    }


    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

            $transformer = new FileDataTransformer($this->fileStorage);
            $subscriber = new FileTypeBindSubscriber(
                $this->mappingFactory,
                $this->dataStorage,
                $transformer,
                $options);
            $builder->addEventSubscriber($subscriber);


           // $builder->add('file', 'file', array('required' => false))
           //     ->add('delete', 'checkbox', array('required' => false))
            $builder->addViewTransformer($transformer);



        //for sonata admin
        //    ->addViewTransformer(new FileDataViewTransformer());
    }

    //for using iphp_file_widget from Resources/views/Form/fields.html.twig
    public function getBlockPrefix()
    {
        return 'iphp_file';
    }


    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['upload'] = $options['upload'];
        $view->vars['show_preview'] = $options['show_preview'];

        $view->vars['file_data'] = $view->vars['value'];

    }


}



