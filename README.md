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
