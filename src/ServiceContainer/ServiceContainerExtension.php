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
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\DelegatingLoader;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Config\Loader\LoaderResolver;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

/**
 * @internal
 */
final class ServiceContainerExtension implements Extension
{
    #[\Override]
    public function getConfigKey(): string
    {
        return 'fob_service_container';
    }

    #[\Override]
    public function initialize(ExtensionManager $extensionManager): void
    {
    }

    #[\Override]
    public function configure(ArrayNodeDefinition $builder): void
    {
        /** @psalm-suppress UnusedMethodCall */
        $builder
            ->children()
                ->arrayNode('imports')
                    ->performNoDeepMerging()
                    ->prototype('scalar')->end()
                ->end()
            ->end()
        ;
    }

    #[\Override]
    public function load(ContainerBuilder $container, array $config): void
    {
        $loader = $this->createLoader($container);

        foreach ($config['imports'] as $file) {
            $loader->load($file);
        }
    }

    #[\Override]
    public function process(ContainerBuilder $container): void
    {
    }

    private function createLoader(ContainerBuilder $container): LoaderInterface
    {
        $fileLocator = new FileLocator($container->getParameter('paths.base'));

        return new DelegatingLoader(new LoaderResolver([
            new YamlFileLoader($container, $fileLocator),
            new PhpFileLoader($container, $fileLocator),
        ]));
    }
}
