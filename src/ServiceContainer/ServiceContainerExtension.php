<?php

declare(strict_types=1);

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

/**
 * @internal
 */
final class ServiceContainerExtension implements Extension
{
    /**
     * @var CompilerPassInterface|null
     */
    private $crossContainerProcessor;

    /**
     * {@inheritdoc}
     */
    public function getConfigKey(): string
    {
        return 'fob_service_container';
    }

    /**
     * {@inheritdoc}
     */
    public function initialize(ExtensionManager $extensionManager): void
    {
        /** @var CrossContainerExtension $extension */
        $extension = $extensionManager->getExtension('fob_cross_container');

        if (null !== $extension) {
            $this->crossContainerProcessor = $extension->getCrossContainerProcessor();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configure(ArrayNodeDefinition $builder): void
    {
        $builder
            ->children()
                ->arrayNode('imports')
                    ->performNoDeepMerging()
                    ->prototype('scalar')->end()
                ->end()
            ->end()
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function load(ContainerBuilder $container, array $config): void
    {
        $loader = $this->createLoader($container, $config);

        foreach ($config['imports'] as $file) {
            $loader->load($file);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container): void
    {
        if (null !== $this->crossContainerProcessor) {
            $this->crossContainerProcessor->process($container);
        }
    }

    private function createLoader(ContainerBuilder $container, array $config): LoaderInterface
    {
        $fileLocator = new FileLocator($container->getParameter('paths.base'));

        return new DelegatingLoader(new LoaderResolver([
            new XmlFileLoader($container, $fileLocator),
            new YamlFileLoader($container, $fileLocator),
            new PhpFileLoader($container, $fileLocator),
        ]));
    }
}
