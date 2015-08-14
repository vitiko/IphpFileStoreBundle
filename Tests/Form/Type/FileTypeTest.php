<?php
namespace Iphp\FileStoreBundle\Tests\Form\Type;

use Iphp\FileStoreBundle\Tests\DummyEntity;
use Iphp\FileStoreBundle\Form\Type\FileTypeBindSubscriber;
use Iphp\FileStoreBundle\Form\DataTransformer\FileDataTransformer;
use Iphp\FileStoreBundle\Form\Type\FileType;
use Iphp\FileStoreBundle\Tests\DummyEntitySeparateDataField;
use Iphp\FileStoreBundle\Tests\Mocks;

class FileTypeTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var \Iphp\FileStoreBundle\Form\Type\FileType
     */
    protected $fileType;


    /**
     * @var \Iphp\FileStoreBundle\Mapping\PropertyMappingFactory $propertyMappingfactory
     */
    protected $propertyMappingfactory;


    /**
     * @var \Iphp\FileStoreBundle\DataStorage\DataStorageInterface $dataStorage
     */
    protected $dataStorage;

    /**
     * @var \Iphp\FileStoreBundle\Driver\AnnotationDriver $driver
     */
    protected $driver;


    /**
     * @var \Iphp\FileStoreBundle\Naming\NamerServiceInvoker
     */
    protected $namerServiceInvoker;


    /**
     * @var \Iphp\FileStoreBundle\FileStorage\FileStorageInterface
     */
    protected $fileStorage;

    function setUp()
    {

        $this->dataStorage = Mocks::getDataStorageMock($this);
        $this->fileStorage = Mocks::getFileStorageMock($this);
        $this->driver = Mocks::getAnnotationDriverMock($this);

        $this->namerServiceInvoker = Mocks::getNamerServiceInvokerMock($this);


        $this->propertyMappingfactory = Mocks::getPropertyMappingFactoryMock($this,
            $this->namerServiceInvoker, $this->driver /*, DummyEntity::getDefaultFileStoreConfig()*/);

        $this->fileType = new FileType ($this->propertyMappingfactory, $this->dataStorage, $this->fileStorage);
    }


    function testSetDefaultOptions()
    {

        $resolver = $this->getMockBuilder('Symfony\Component\OptionsResolver\OptionsResolverInterface')
            ->disableOriginalConstructor()
            ->getMock();


        $resolver->expects($this->once())
            ->method('setDefaults')
            ->with(array(
                'read_only' => false,
                'upload' => true,
                'show_uploaded' => true,
                'show_preview' => true
            ));

        $this->fileType->setDefaultOptions($resolver);
    }


    function testBuildForm()
    {

        $formBuilder = $this->getMockBuilder('Symfony\Component\Form\FormBuilder')
            ->disableOriginalConstructor()
            ->getMock();


        $transformer = new FileDataTransformer($this->fileStorage);
        $subscriber = new FileTypeBindSubscriber($this->propertyMappingfactory, $this->dataStorage, $transformer);


        $formBuilder->expects($this->once())
            ->method('addEventSubscriber')
            ->with($subscriber);


        $formBuilder->expects($this->once())
            ->method('addViewTransformer')
            ->with($transformer);


        $formBuilder->expects($this->any())
            ->method('add')
            ->will($this->returnValue($formBuilder));


        $this->fileType->buildForm($formBuilder, array('upload' => true));
    }


    function testGetParent()
    {
        $this->assertSame($this->fileType->getParent(), 'form');
    }

    function testGetName()
    {
        $this->assertSame($this->fileType->getName(), 'iphp_file');
    }
}