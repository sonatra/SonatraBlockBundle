<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\BlockBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class SonatraBlockExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('block.yml');
        $loader->load('twig.yml');
        $loader->load('doctrine.yml');

        if (count($configs) > 1) {
            $initConfig = array_pop($configs);
            $configs = array_reverse($configs);
            $configs[] = $initConfig;
        }

        $configuration = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('sonatra.block.twig.resources', $config['block']['resources']);

        if (0 === strpos($config['profiler']['enabled'], '%')) {
            $config['profiler']['enabled'] = $container->getParameter(trim($config['profiler']['enabled'], '%'));
        }

        if ($config['profiler']['enabled']) {
            $loader->load('profiler.yml');

            foreach ($config['profiler']['engines'] as $engine => $tracable) {
                $container->setDefinition($engine, $container->findDefinition($tracable));
                $container->removeDefinition($tracable);
            }
        }
    }
}
