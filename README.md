# Service Container Extension [![License](https://img.shields.io/packagist/l/friends-of-behat/service-container-extension.svg)](https://packagist.org/packages/friends-of-behat/service-container-extension) [![Version](https://img.shields.io/packagist/v/friends-of-behat/service-container-extension.svg)](https://packagist.org/packages/friends-of-behat/service-container-extension) [![Build status on Linux](https://img.shields.io/travis/FriendsOfBehat/ServiceContainerExtension/master.svg)](http://travis-ci.org/FriendsOfBehat/ServiceContainerExtension) [![Scrutinizer Quality Score](https://img.shields.io/scrutinizer/g/FriendsOfBehat/ServiceContainerExtension.svg)](https://scrutinizer-ci.com/g/FriendsOfBehat/ServiceContainerExtension/)

Allows to declare own services inside Behat container without writing an extension.

## Usage

1. Install it:
    
    ```bash
    $ composer require friends-of-behat/service-container-extension --dev
    ```

2. Enable this extension and configure Behat to use it:
    
    ```yaml
    # behat.yml
    default:
        # ...
        extensions:
            FriendsOfBehat\ServiceContainerExtension:
                imports:
                    - "features/bootstrap/config/services.xml"
                    - "features/bootstrap/config/services.yml"
                    - "features/bootstrap/config/services.php"
    ```

3. Write services files definitions:

    ```xml
    <!-- features/bootstrap/config/services.xml -->
    <?xml version="1.0" encoding="UTF-8" ?>
    <container xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns="http://symfony.com/schema/dic/services">
        <services>
            <service id="acme.my_service" class="Acme\MyService" />
        </services>
    </container>
    ```
    
    ```yaml
    # features/bootstrap/config/services.yml
    services:
        acme.my_service:
            class: Acme\MyService
    ```
    
    ```php
    // features/bootstrap/config/services.php
    use Symfony\Component\DependencyInjection\Definition;
    
    $container->setDefinition('acme.my_service', new Definition(\Acme\MyService::class));
    ```
