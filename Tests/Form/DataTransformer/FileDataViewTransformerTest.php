<?php
namespace Iphp\FileStoreBundle\Tests\Form\DataTransformer;


use Iphp\FileStoreBundle\Form\DataTransformer\FileDataViewTransformer;
use Iphp\FileStoreBundle\Tests\Mocks;

class FileDataViewTransformerTest extends \PHPUnit_Framework_TestCase
{


    /**
     * @var  \Iphp\FileStoreBundle\Form\DataTransformer\FileDataViewTransformer;
     */
    protected $transformer;



    public function setUp()
    {
        $this->transformer = new  FileDataViewTransformer();
    }


    function testTransform()

    {
        $this->assertSame($this->transformer->transform(array(1, 2, 3)), array(1, 2, 3));
    }



    function testReverseTransform()

    {
        $this->assertSame($this->transformer->reverseTransform(array(1, 2, 3)), array(1, 2, 3));
    }
}