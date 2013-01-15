IphpFileStoreBundle
===================

The IphpFileStoreBundle is a Symfony2 bundle that automates file uploads that are attached to an entity. 
The bundle will automatically name and save the uploaded file according to the configuration specified on a per property
basis using a mix of configuration and annotations. 
After the entity has been created and the file has been saved,  array with data of uploaded file
will be saved to according property. 
The bundle provide different ways to naming uploaded files and directories.   
 
## Installation

### Get the bundle
 

Add the following lines in your composer.json:

```
{
    "require": {
        "iphp/filestore-bundle":"dev-master" 
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
         new Iphp\FilestoreBundle\IphpFileStoreBundle(),
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
           directory_namer:
               date:
                  params: { field : date, depth : month }
           namer: ~
```

The `upload_dir` and `upload_path` is the only required configuration options for an entity mapping.

All options are listed below:

- `upload_dir`: directory to upload the file to
- `upload_path`: web path to upload dir 
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
