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

use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\DelegatingLoader;
use Symfony\Component\Config\Loader\LoaderResolver;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

final class DefaultLoaderFactory implements LoaderFactory
{
    /**
     * {@inheritdoc}
     */
    public function createLoader(ContainerBuilder $container, array $config)
    {
        $fileLocator = new FileLocator($container->getParameter('paths.base'));

        return new DelegatingLoader(new LoaderResolver([
            new XmlFileLoader($container, $fileLocator),
            new YamlFileLoader($container, $fileLocator),
            new PhpFileLoader($container, $fileLocator),
        ]));
    }
}
