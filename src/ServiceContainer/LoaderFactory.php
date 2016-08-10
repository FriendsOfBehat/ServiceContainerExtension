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

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @author Kamil Kokot <kamil.kokot@lakion.com>
 */
interface LoaderFactory
{
    /**
     * @param ContainerBuilder $container
     * @param array $config
     *
     * @return LoaderInterface
     */
    public function createLoader(ContainerBuilder $container, array $config);
}
