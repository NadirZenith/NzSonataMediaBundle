<?php

namespace Nz\SonataMediaBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class NzSonataMediaExtension extends Extension
{

    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('media.xml');
        $loader->load('provider.xml');

        $this->loadVideoProviderConfig($configs, $container);
    }

    function loadVideoProviderConfig($configs, ContainerBuilder $container)
    {

        $ffmpeg_config = [];
        $ffmpeg_lib = $container->getParameter('ffmpeg.binaries');
        $ffprobe_lib = $container->getParameter('ffprobe.binaries');

        if (!empty($ffmpeg_lib)) {
            $ffmpeg_config['ffmpeg.binaries'] = $ffmpeg_lib;
        }
        if (!empty($ffprobe_lib)) {
            $ffmpeg_config['ffprobe.binaries'] = $ffprobe_lib;
        }

        if (empty($ffmpeg_config)) {
            return;
        }

        $provider = $container->getDefinition('sonata.media.provider.video');
        $provider->addMethodCall('setFFMpegConfig', array($ffmpeg_config));
    }
}
