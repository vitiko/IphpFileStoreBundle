IphpFileStoreBundle - Symfony 2 Doctrine ORM file upload bundle 
===================

[![Build Status](https://api.travis-ci.org/vitiko/IphpFileStoreBundle.png?branch=master)](http://travis-ci.org/vitiko/IphpFileStoreBundle)
[![Total Downloads](https://poser.pugx.org/iphp/filestore-bundle/downloads.png)](https://packagist.org/packages/iphp/filestore-bundle)
[![Code Climate](https://codeclimate.com/github/vitiko/IphpFileStoreBundle/badges/gpa.svg)](https://codeclimate.com/github/vitiko/IphpFileStoreBundle)

The IphpFileStoreBundle is a Symfony2 bundle that automates file uploads that are attached to an entity. 
The bundle will automatically name and save the uploaded file according to the configuration specified on a per property
basis using a mix of configuration and annotations. 
After the entity has been created and the file has been saved,  array with data of uploaded file
will be saved to according property. 
The bundle provide different ways to naming uploaded files and directories.   
 
For Russian documentation see http://symfonydev.ru/iphpfilestorebundle/ 
 
 
## Installation

### Get the bundle
 

Add the following lines in your composer.json:
```
{
    "require": {
        "iphp/filestore-bundle" : "@stable" 
    }
}
```

### Initialize the bundle

To start using the bundle, register the bundle in your application's kernel class:

``` php
// app/AppKernel.php
public function registerBundles()
{
    $bundles = array(
        // ...
         new Iphp\FileStoreBundle\IphpFileStoreBundle(),
    );
)
```


## Usage

IphpFileStoreBundle try to handle file uploads according to a combination
of configuration parameters and annotations. In order to have your upload
working you have to:
 
* Define a basic configuration set
* Annotate your Entities


### Configuration

``` yaml
# app/config/config.yml
iphp_file_store:
    mappings:
       photo:
           upload_dir:  %kernel.root_dir%/../web/photo
           upload_path: /photo
```

The `upload_dir` and `upload_path` is the only required configuration options for an entity mapping.

All options are listed below:

- `upload_dir`: directory to upload the file to
- `upload_path`: web path of upload dir 
- `namer`: configuration of file naming (See [Namers](#namers) section below)
- `directory_namer`: configuration of directory naming  
- `delete_on_remove`: Set to true if the file should be deleted from the
filesystem when the entity is removed
- `overwrite_duplicates`: Set to true if the file with same name will be overwritten by a new file. 
  In another case (by default), to the name of the new file will be added extra digits
 
 
 
### Annotate Entities

In order for your entity  to work with the bundle, you need to add a
few annotations to it. First, annotate your class with the `Uploadable` annotation.
This lets the bundle know that it should look for files to upload in your class when
it is saved, inject the files when it is loaded and check to see if it needs to
remove files when it is removed. Next, you should annotate the fields which hold
the instance of `Symfony\Component\HttpFoundation\File\UploadedFile` when the form
is submitted with the `UploadableField` annotation. The `UploadableField` annotation
has a few required options. They are as follows:

- `mapping`: The mapping specified in the bundle configuration to use
 

Lets look at an example using a fictional `Photo` ORM entity:

``` php
<?php
#src/Iphpsandbox/PhotoBundle/Entity/Photo.php
namespace Iphpsandbox\PhotoBundle\Entity;
use Iphp\FileStoreBundle\Mapping\Annotation as FileStore;
use Symfony\Component\Validator\Constraints as Assert;
 
/**
 * @FileStore\Uploadable
 */
class Photo
{
    /**
     * @var integer
     */
    private $id;
 
    /**
     * @var string
     */
    private $title;
 
 
    /**
     * @var \Datetime
     */
    private $date;
 
    /**
     * @Assert\File( maxSize="20M")
     * @FileStore\UploadableField(mapping="photo")
     **/
    private $photo;
 
    ...
     /* Getters and setters */
    ...
}
```

[Source code of Photo entity in test bundle](https://github.com/vitiko/IphpFileStoreBundle/blob/master/Tests/Functional/TestBundle/Entity/Photo.php)

### Doctrine configuration

Field with file data must have type=array. Example of xml doctrine configuration. Annotation configuration above.

``` xml
<?xml version="1.0" encoding="utf-8"?>
<!-- src/Iphpsandbox/PhotoBundle/Ressources/config/doctrine/Photo.orm.xml -->
<doctrine-mapping xmlns="http://doctrine-project.org/schemas/orm/doctrine-mapping"
                  xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                  xsi:schemaLocation="http://doctrine-project.org/schemas/orm/doctrine-mapping http://doctrine-project.org/schemas/orm/doctrine-mapping.xsd">
    <entity name="Iphpsandbox\PhotoBundle\Entity\Photo">
        <id name="id" type="integer" column="id">
            <generator strategy="AUTO"/>
        </id>
        <field name="title" type="string" column="title" length="255"/>
        <field name="date" type="datetime" column="date"/>
        <field name="photo" type="array" column="photo"/>
   </entity>
</doctrine-mapping>
```


## Uploaded file data
 
### Annotated field data

Аfter file upload annotated field contains array with elements:

- path — full path to file in web dir, including web path to upload dir
- size — file size in bytes
- fileName — path to file , relative to web path of upload dir
- originalName — original file name before upload
- mimeType — file mime type


If uploaded file im image, array also contains:
 
- width — image width
- height — image height


### Using file data


To get a path for the file you can use this PHP code:

``` php
// PHP 5.3
$photo = ... // load entity from db
 
$photoData = $photo->getPhoto(); //array with image data
$path = $photoData['path'];
 
//Or PHP 5.4.
$path = $photo->getPhoto()['path'];
```

or in a Twig template:

``` html
<img src="{{ photo.photo.path }}" alt="{{ photo.title}}" />
```

Example of using entities with uploadable can be seen in [controller](https://github.com/vitiko/IphpFileStoreBundle/blob/master/Tests/Functional/TestBundle/Controller/DefaultController.php) 
and twig template [for uploading](https://github.com/vitiko/IphpFileStoreBundle/blob/master/Tests/Functional/TestBundle/Resources/views/Photo/index.html.twig) 
and [editing](https://github.com/vitiko/IphpFileStoreBundle/blob/master/Tests/Functional/TestBundle/Resources/views/Photo/edit.html.twig) entities. 


###Example of interface with list of uploaded photos 

![interface with list of uploaded photos](https://raw.github.com/vitiko/IphpFileStoreBundle/master/Tests/Fixtures/images/front-images-list.jpeg)




## Using form field type

Form field type `iphp_file` can be used in admin class, created for SonataAdminBundle. 
If entity already has uploaded file - information about this file will be displayed near file upload field. Also 
delete checkbox allows to delete uploaded file

``` php
<?php
#src/Iphpsandbox/PhotoBundle/Admin/PhotoAdmin.php
namespace Iphpsandbox\PhotoBundle\Admin;
 
use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
 
class PhotoAdmin extends Admin
{
    protected function configureListFields(ListMapper $listMapper)
    {
        return $listMapper->addIdentifier('title')
                          ->add ('date');
    }
 
    protected function configureFormFields(FormMapper $formMapper)
    {
        return $formMapper->add('title')
                         ->add ('date')
                         ->add('photo', 'iphp_file');
    }
}
```


###Example of sonata admin form for uploaded photo
![Example of edit form for uploaded photo](https://raw.github.com/vitiko/IphpFileStoreBundle/master/Tests/Fixtures/images/sonata-admin-iphpfile.jpeg)




## Namers

The bundle uses namers to name the files and directories it saves to the filesystem. If no namer is
configured for a mapping, the bundle will use default transliteration namer for files was uploaded. 
if you would like to change this then you can use one of the provided namers or implement a custom one.

### File Namers
 
 
#### Translit

Transliteration - replace cyrillic and other chars to ascii

``` yaml
# app/config/config.yml
iphp_file_store:
    mappings:
       some_entity:
           ...
           namer: ~ // default
```


To cancel transliteration
``` yaml
# app/config/config.yml
iphp_file_store:
    mappings:
       some_entity:
           ...
           namer: false
```

#### Using entity field value

File name by value of entity field ( field name - title)
``` yaml
# app/config/config.yml
iphp_file_store:
    mappings:
       some_entity:
          namer:
             property:
                params: { field : title }
             translit: ~
```

#### Adding entity field value

Adding to the beginnng  (propertyPrefix) or end (propertyPostfix) of file name value of entity field
``` yaml
# app/config/config.yml
iphp_file_store:
    mappings:
       some_entity:
          namer:
             translit: ~
             propertyPrefix:    #or propertyPostfix 
                   params: { field : id, delimiter: "_" }
```

#### Using entity field name

One mapping can be used in multiple fields. Name of the field can be used for naming file
 
``` yaml 
# app/config/config.yml
iphp_file_store:
    mappings:
       some_entity:
          namer:
             translit: ~
             propertyPostfix:
                  params: { use_field_name : true }
```

#### Replacing strings

Params of replace namer are key-value pairs with search and replace strings
``` yaml
# app/config/config.yml
iphp_file_store:
    mappings:
       some_entity:
          namer:
             translit: ~
             propertyPostfix:
                  params: { use_field_name : true }
             replace:
                 params: { File : ~ }
```


### Directory Namers

#### Create subdirectory by date

For example: Uploaded file 123.jpg, entity createdAt field value 2013-01-01 - path to file will be 2013/01/123.jpg.
Depth options - year, month, date.

``` yaml 
# app/config/config.yml
iphp_file_store:
    mappings:
       some_entity:
         directory_namer:
             date:
                 params: { field : createdAt, depth : month }
```

#### Using entity field value
 
``` yaml
# app/config/config.yml
iphp_file_store:
    mappings:
       some_entity:
         directory_namer:
             property:
                 params: { field : "id"}
```

#### Using entity field name
 
``` yaml
# app/config/config.yml
iphp_file_store:
    mappings:
       some_entity:
         directory_namer:
             property:
                params: { use_field_name : true }
```

#### Using entity class name
``` yaml 
# app/config/config.yml
iphp_file_store:
    mappings:
       some_entity:
         directory_namer:
             entityName: ~
```

#### Using chain of directory namers

Using entity class name and entity field name

``` yaml
# app/config/config.yml
iphp_file_store:
    mappings:
       some_entity:
         directory_namer:
             entityName: ~
             property:
                params: { use_field_name : true }
```
