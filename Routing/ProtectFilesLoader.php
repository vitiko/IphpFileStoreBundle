<?php

namespace Iphp\FileStoreBundle\Routing;

use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

class ProtectFilesLoader extends Loader
{
    protected $mappings;


    protected $defaultController = 'IphpFileStoreBundle:Protected:download';

    public function __construct($mappings)
    {
        $this->mappings = $mappings;
    }

    public function supports($resource, $type = null)
    {
        return $type === 'iphp_filestore';
    }

    public function load($resource, $type = null)
    {

        $requirements = array('_method' => 'GET', /*'filter' => '[A-z0-9_\-]*',*/
            'path' => '.+');
        $routes = new RouteCollection();
        foreach ($this->mappings as $name => $mapping) {
            if (!$mapping['protected']) continue;

            $pattern = $mapping['upload_path'];

            $defaults = array(
                '_iphpfilestore_mapping' => $name,
                '_controller' => empty($mapping['protected_contriller']) ?
                        $this->defaultController : $mapping['protected_contriller']
                /*'filter' => $filter,*/
            );


            $routes->add('_iphpfilestore_' . $name, new Route(
                    $pattern . '/{path}',
                    $defaults,
                    $requirements/*,
                    $routeOptions*/
                ));
        }

        return $routes;

    }
}
