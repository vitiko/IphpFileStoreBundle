<?php

namespace Iphp\FileStoreBundle\Tests\Functional\TestXmlConfigBundle\Entity;

use Iphp\FileStoreBundle\Mapping\Annotation as FileStore;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;



/**
 * @ORM\Entity
 * @ORM\Table(name = "file_protected")
 * @FileStore\Uploadable
 */
class FileProtected
{
    /**
     * @var integer
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="string")
     * @var string
     */
    private $title;


    /**
     * @var \Datetime
     * @ORM\Column(type="datetime")
     */
    private $date;

    /**
     * @Assert\File( maxSize="20M")
     * @FileStore\UploadableField(mapping="file_protected")
     **/
    protected $file;


    /**
     * @Assert\File( maxSize="20M")
     * @FileStore\UploadableField(mapping="file_protected_ondemand")
     **/
    protected $fileOndemand;

    public function setFile($file)
    {
        $this->file = $file;
        return $this;
    }

    public function getFile()
    {
        return $this->file;
    }


    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $title
     * @return File
     */
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }


    /**
     * @param \Datetime $date
     */
    public function setDate($date)
    {
        $this->date = $date;
        return $this;
    }

    /**
     * @return \Datetime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @param mixed $fileOndemand
     */
    public function setFileOndemand($fileOndemand)
    {
        $this->fileOndemand = $fileOndemand;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getFileOndemand()
    {
        return $this->fileOndemand;
    }


}
