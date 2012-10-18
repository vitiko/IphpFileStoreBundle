<?php

namespace Iphp\FileStoreBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class IphpFileStoreExtension extends Extension
{


    /**
     * @var array $tagMap
     */
    protected $tagMap = array(
        'orm' => 'doctrine.event_subscriber',
        // 'document' => ''
    );

    /**
     * @var array $adapterMap
     */
    protected $adapterMap = array(
        'orm' => 'Iphp\FileStoreBundle\DataStorage\OrmDataStorage',
        //'document' => ''
    );


    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {


        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);



        $driver = strtolower($config['db_driver']);
        if (!in_array($driver, array_keys($this->tagMap))) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Invalid "db_driver" configuration option specified: "%s"',
                    $driver
                )
            );
        }

        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.xml');

        $mappings = isset($config['mappings']) ? $config['mappings'] : array();

        $container->setParameter('iphp.filestore.mappings', $mappings);


        $container->setParameter('iphp.filestore.datastorage.class', $this->adapterMap[$driver]);

        //Add tag orm or mongodb to listener service
        $container->getDefinition('iphp.filestore.event_listener.uploader')->addTag($this->tagMap[$driver]);
    }
}
