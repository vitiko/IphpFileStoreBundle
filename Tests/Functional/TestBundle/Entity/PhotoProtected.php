<?php

namespace Iphp\FileStoreBundle\Tests\Functional\TestBundle\Entity;

use Iphp\FileStoreBundle\Mapping\Annotation as FileStore;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name = "photo_protected")
 * @FileStore\Uploadable
   @author Vitiko <vitiko@mail.ru>
 **/

class PhotoProtected
{

    /**
     * @var integer
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string")
     * @var string
     */
    protected $title;


    /**
     * @var \Datetime
     * @ORM\Column(type="datetime")
     */
    protected $date;

    /**
     * @ORM\Column(type="array")
     * @Assert\Image( maxSize="20M")
     * @FileStore\UploadableField(mapping="photo_protected")
     **/
    private $photo;



    /**
     * @ORM\Column(type="array")
     * @Assert\Image( maxSize="20M")
     * @FileStore\UploadableField(mapping="photo_protected_ondemand")
     **/
    private $photoOndemand;




    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }


    /**
     * @param string $title
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
     * @param mixed $photo
     */
    public function setPhoto($photo)
    {
        $this->photo = $photo;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPhoto()
    {
        return $this->photo;
    }

    /**
     * @param mixed $photoOndemand
     */
    public function setPhotoOndemand($photoOndemand)
    {
        $this->photoOndemand = $photoOndemand;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPhotoOndemand()
    {
        return $this->photoOndemand;
    }





} 