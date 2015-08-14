<?php
namespace Iphp\FileStoreBundle\Tests\Form\Type;

use Iphp\FileStoreBundle\Tests\DummyEntity;
use Symfony\Component\Form\FormEvents;
use Iphp\FileStoreBundle\Form\Type\FileTypeBindSubscriber;
use Iphp\FileStoreBundle\Tests\DummyEntitySeparateDataField;
use Iphp\FileStoreBundle\Tests\Mocks;

class FileTypeBindSubscriberTest extends \PHPUnit_Framework_TestCase
{


    /**
     * @var \Iphp\FileStoreBundle\Form\Type\FileTypeBindSubscriber
     */
    protected $subscriber;


    /**
     * @var \Iphp\FileStoreBundle\Mapping\PropertyMappingFactory $propertyMappingfactory
     */
    protected $propertyMappingFactory;


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
     * @var  \Iphp\FileStoreBundle\Form\DataTransformer\FileDataTransformer;
     */
    protected $transformer;


    public function setUp()
    {
        $this->dataStorage = Mocks::getDataStorageMock($this);
        $this->driver = Mocks::getAnnotationDriverMock($this);
        $this->namerServiceInvoker = Mocks::getNamerServiceInvokerMock($this);


        $this->propertyMappingFactory = Mocks::getPropertyMappingFactoryMock($this,
            $this->namerServiceInvoker, $this->driver);


        $this->transformer = $this->getMockBuilder('Iphp\FileStoreBundle\Form\DataTransformer\FileDataTransformer')
            ->disableOriginalConstructor()
            ->getMock();

        $this->subscriber = new FileTypeBindSubscriber ($this->propertyMappingFactory, $this->dataStorage, $this->transformer);
    }


    function testSubscribedEvents()
    {
        $this->assertSame(FileTypeBindSubscriber::getSubscribedEvents(),
            array(FormEvents::PRE_SUBMIT => 'preBind',
                  FormEvents::PRE_SET_DATA => 'preSet'));
    }


    function testPreBind()
    {

        $obj = new DummyEntity();
        $class = new \ReflectionClass($obj);

        $formEvent = $this->getMockBuilder('Symfony\Component\Form\FormEvent')
            ->disableOriginalConstructor()
            ->getMock();

        $form = $this->getMockBuilder('Symfony\Component\Form\Form')
            ->disableOriginalConstructor()
            ->getMock();

        $parentForm = $this->getMockBuilder('Symfony\Component\Form\Form')
            ->disableOriginalConstructor()
            ->getMock();


        $formEvent->expects($this->once())
            ->method('getForm')
            ->will($this->returnValue($form));


        $form->expects($this->once())
            ->method('getParent')
            ->will($this->returnValue($parentForm));

        $parentForm->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($obj));


        $this->dataStorage->expects($this->once())
            ->method('getReflectionClass')
            ->with($obj)
            ->will($this->returnValue($class));


        $propertyMapping = Mocks::getPropertyMappingMock($this);

        $this->propertyMappingFactory->expects($this->once())
            ->method('getMappingFromField')
            ->with($obj, $class, 'file')
            ->will($this->returnValue($propertyMapping));


        $form->expects($this->once())
            ->method('getName')
            ->will($this->returnValue('file'));


        $this->transformer->expects($this->once())
            ->method('setMapping')
            ->with($propertyMapping);


        $this->subscriber->preBind($formEvent);
    }
}