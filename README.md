# Service Container Extension [![License](https://img.shields.io/packagist/l/friends-of-behat/service-container-extension.svg)](https://packagist.org/packages/friends-of-behat/service-container-extension) [![Version](https://img.shields.io/packagist/v/friends-of-behat/service-container-extension.svg)](https://packagist.org/packages/friends-of-behat/service-container-extension) [![Build status on Linux](https://img.shields.io/travis/FriendsOfBehat/ServiceContainerExtension/master.svg)](http://travis-ci.org/FriendsOfBehat/ServiceContainerExtension) [![Scrutinizer Quality Score](https://img.shields.io/scrutinizer/g/FriendsOfBehat/ServiceContainerExtension.svg)](https://scrutinizer-ci.com/g/FriendsOfBehat/ServiceContainerExtension/)

Allows to declare own services inside Behat container without writing an extension.

## Usage

1. Install it:
    
    ```bash
    $ composer require friends-of-behat/service-container-extension --dev
    ```

2. Enable and configure:
    
    ```yaml
    default:
        # ...
        extensions:
            FriendsOfBehat\ServiceContainerExtension:
                imports:
                    - "features/bootstrap/config/services.xml"
                    - "features/bootstrap/config/services.yml"
                    - "features/bootstrap/config/services.php"
    ```
