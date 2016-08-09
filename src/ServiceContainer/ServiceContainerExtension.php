<?php

/*
 * This file is part of the ServiceContainerExtension package.
 *
 * (c) FriendsOfBehat
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
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

/**
 * @author Kamil Kokot <kamil@kokot.me>
 */
final class ServiceContainerExtension implements Extension
{
    /**
     * @var callable<ContainerBuilder, array>:LoaderInterface
     */
    private $loaderCallable;

    public function __construct()
    {
        $this->loaderCallable = function (ContainerBuilder $container, array $config) {
            $fileLocator = new FileLocator($container->getParameter('paths.base'));

            return new DelegatingLoader(new LoaderResolver([
                new XmlFileLoader($container, $fileLocator),
                new YamlFileLoader($container, $fileLocator),
                new PhpFileLoader($container, $fileLocator),
            ]));
        };
    }

    /**
     * @api
     *
     * @param callable<ContainerBuilder, array>:LoaderInterface $loaderCallable
     */
    public function setLoaderCallable(callable $loaderCallable)
    {
        $this->loaderCallable = $loaderCallable;
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
        $loaderCallable = $this->loaderCallable;

        /** @var LoaderInterface $loader */
        $loader = $loaderCallable($container, $config);

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

    }
}
