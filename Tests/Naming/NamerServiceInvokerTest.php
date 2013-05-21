<?php
namespace Iphp\FileStoreBundle\Tests\Naming;

use Iphp\FileStoreBundle\Naming\NamerServiceInvoker;
use Iphp\FileStoreBundle\Tests\Mocks;

class NamerServiceInvokerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Iphp\FileStoreBundle\Naming\NamerServiceInvoker
     */
    protected $invoker;

    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected $container;


    protected function setUp()
    {
        $this->container = Mocks::getContainerMock($this);
        $this->invoker = new NamerServiceInvoker ($this->container);
    }


    public function testRename()
    {
        $propertyMapping = Mocks::getPropertyMappingMock($this);

        $namer = $this->getMockBuilder('Iphp\FileStoreBundle\Naming\DefaultNamer')
            ->disableOriginalConstructor()
            ->getMock();


        $this->container
            ->expects($this->once())
            ->method('get')
            ->with('test.service')
            ->will($this->returnValue($namer));


        $namer->expects($this->once())
            ->method('translitRename')
            ->with($propertyMapping, 'sample file name', array('param' => 1))
            ->will($this->returnValue('sample-file-name'));

        $this->assertSame(
            $this->invoker->rename('test.service', 'translit', $propertyMapping, 'sample file name', array('param' => 1)),
            'sample-file-name'
        );


    }


    public function testResolveCollision()
    {
        $namer = $this->getMockBuilder('Iphp\FileStoreBundle\Naming\DefaultNamer')
            ->disableOriginalConstructor()
            ->getMock();


        $this->container
            ->expects($this->once())
            ->method('get')
            ->with('test.service')
            ->will($this->returnValue($namer));


        $namer->expects($this->once())
            ->method('resolveCollision')
            ->with('sample-file-name', 1)
            ->will($this->returnValue('sample-file-name_1'));


        $this->assertSame($this->invoker->resolveCollision('test.service', 'sample-file-name', 1),
            'sample-file-name_1');
    }
}
