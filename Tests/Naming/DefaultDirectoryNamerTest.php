<?php
namespace Iphp\FileStoreBundle\Tests\Naming;

use Iphp\FileStoreBundle\Naming\DefaultDirectoryNamer;
use Iphp\FileStoreBundle\Tests\DummyEntity;
use Iphp\FileStoreBundle\Tests\Mocks;

class DefaultDirectoryNamerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Iphp\FileStoreBundle\Naming\DefaultDirectoryNamer
     */
    protected $namer;

    protected function setUp()
    {
        $this->namer = new DefaultDirectoryNamer;
    }


    protected function getMappingWithObject($object)
    {
        $propertyMapping = Mocks::getPropertyMappingMock($this);
        $propertyMapping->expects($this->any())
            ->method('getObj')
            ->will($this->returnValue($object));

        return $propertyMapping;
    }


    public function testPropertyRenameById()
    {

        $obj = new DummyEntity();
        $obj->setId(12345);
        $propertyMapping = $this->getMappingWithObject($obj);

        $this->assertSame($this->namer->propertyRename($propertyMapping, 'some-name.jpg', array()), '12345');

    }


    public function testPropertyRenameByMultiple()
    {
        $obj = new DummyEntity();
        $obj->setId(12345);
        $file = Mocks::getFileMock($this);
        $file->expects($this->once())
            ->method('getSize')
            ->will($this->returnValue('100'));

        $obj->setFile($file);
        $propertyMapping = $this->getMappingWithObject($obj);

        $this->assertSame($this->namer->propertyRename($propertyMapping, 'some-name.jpg', array(
            'field' => 'id/file.size'
        )), '12345/100');

    }




    public function testEntityNameRename()
    {
        $propertyMapping = $this->getMappingWithObject(new DummyEntity());
        $this->assertSame($this->namer->entityNameRename($propertyMapping, 'some-name.jpg', array()), 'DummyEntity');
    }


    public function testReplaceRename()
    {
        $propertyMapping = $this->getMappingWithObject(new DummyEntity());
        $this->assertSame($this->namer->replaceRename($propertyMapping, 'some-dir',
            array('some' => 'new')), 'new-dir');
    }


    public function testDateRename()
    {
        $obj = new DummyEntity();
        $obj->setCreatedAt(new \DateTime ('2013-05-21 12:12:12'));
        $propertyMapping = $this->getMappingWithObject($obj);


        $this->assertSame($this->namer->dateRename($propertyMapping, 'some-dir',
            array('field' => 'createdAt')), '2013/05/21');
    }


    public function testDateRenameByYear()
    {
        $obj = new DummyEntity();
        $obj->setCreatedAt(new \DateTime ('2013-05-21 12:12:12'));
        $propertyMapping = $this->getMappingWithObject($obj);


        $this->assertSame($this->namer->dateRename($propertyMapping, 'some-dir',
            array('field' => 'createdAt', 'depth' => 'year')), '2013');
    }
}
