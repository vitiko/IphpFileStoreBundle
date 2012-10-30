<?php

namespace Iphp\FileStoreBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Configuration.
 *
 * @author Vitiko <vitiko@mail.ru>
 */
class Configuration implements ConfigurationInterface
{
    /**
     * Gets the configuration tree builder for the extension.
     *
     * @return Tree The configuration tree builder
     */
    public function getConfigTreeBuilder()
    {
        $tb = new TreeBuilder();
        $root = $tb->root('iphp_file_store');

        $root
            ->children()
                ->scalarNode('db_driver')->defaultValue('orm')->end()

                ->arrayNode('mappings')
                    ->useAttributeAsKey('id')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('upload_dir')->end()
                            ->scalarNode('upload_path')->end()
                            ->scalarNode('delete_on_remove')->defaultTrue()->end()
                            ->scalarNode('overwrite_duplicates')->defaultFalse()->end()


                            ->arrayNode('namer')
                               ->treatFalseLike(array ())
                               ->treatNullLike(array ('translit' => array('service' => 'iphp.filestore.namer.default')))
                               ->treatTrueLike(array ('translit' => array('service' => 'iphp.filestore.namer.default')))
                               ->useAttributeAsKey('id')
                               ->prototype('array')

                                  ->children()

                                     ->scalarNode('service')->defaultValue('iphp.filestore.namer.default')->end()
                                     ->arrayNode('params')
                                        ->useAttributeAsKey('name')
                                        ->prototype('scalar')->end()
                                     ->end()
                                 ->end()
                               ->end()
                            ->end()



                           ->arrayNode('directory_namer')

                             ->useAttributeAsKey('id')
                             ->prototype('array')

                               ->children()

                                  ->scalarNode('service')->defaultValue('iphp.filestore.directory_namer.default')->end()
                                  ->arrayNode('params')
                                    ->useAttributeAsKey('name')
                                    ->prototype('scalar')->end()
                                  ->end()

                               ->end()
                             ->end()
                            ->end()

                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $tb;
    }
}
