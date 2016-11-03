<?php

/*
 * This file is part of the ServiceContainerExtension package.
 *
 * (c) Kamil Kokot <kamil@kokot.me>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FriendsOfBehat\ServiceContainerExtension\ServiceContainer;

use Behat\Testwork\ServiceContainer\Extension;
use Behat\Testwork\ServiceContainer\ExtensionManager;
use FriendsOfBehat\CrossContainerExtension\ServiceContainer\CrossContainerExtension;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\DelegatingLoader;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Config\Loader\LoaderResolver;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

final class ServiceContainerExtension implements Extension
{
    /**
     * @var LoaderFactory
     */
    private $loaderFactory;

    /**
     * @var CompilerPassInterface|null
     */
    private $crossContainerProcessor;

    public function __construct()
    {
        $this->loaderFactory = new DefaultLoaderFactory();
    }

    /**
     * @api
     *
     * @param LoaderFactory $loaderFactory
     */
    public function setLoaderFactory(LoaderFactory $loaderFactory)
    {
        $this->loaderFactory = $loaderFactory;
    }

    /**
     * @internal
     *
     * {@inheritdoc}
     */
    public function getConfigKey()
    {
        return 'fob_service_container';
    }

    /**
     * @internal
     *
     * {@inheritdoc}
     */
    public function initialize(ExtensionManager $extensionManager)
    {
        /** @var CrossContainerExtension $extension */
        $extension = $extensionManager->getExtension('fob_cross_container');

        if (null !== $extension) {
            $this->crossContainerProcessor = $extension->getCrossContainerProcessor();
        }
    }

    /**
     * @internal
     *
     * {@inheritdoc}
     */
    public function configure(ArrayNodeDefinition $builder)
    {
        $builder
            ->children()
                ->arrayNode('imports')
                    ->performNoDeepMerging()
                    ->prototype('scalar')
        ;
    }

    /**
     * @internal
     *
     * {@inheritdoc}
     */
    public function load(ContainerBuilder $container, array $config)
    {
        $loader = $this->loaderFactory->createLoader($container, $config);

        foreach ($config['imports'] as $file) {
            $loader->load($file);
        }
    }

    /**
     * @internal
     *
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (null !== $this->crossContainerProcessor) {
            $this->crossContainerProcessor->process($container);
        }
    }
}
