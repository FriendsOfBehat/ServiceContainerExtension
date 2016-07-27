Feature: Importing files
    In order to declare own services and parameters inside Behat container without writing an extension
    As a Behat User
    I need to import service definitions and parameters from files

    Background:
        Given a context file "features/bootstrap/FeatureContext.php" containing:
        """
        <?php

        use Behat\Behat\Context\Context;

        class FeatureContext implements Context
        {
            private $parameter;

            public function __construct($parameter)
            {
                $this->parameter = $parameter;
            }

            /**
             * @Given the parameter was injected to the context
             */
            public function theParameterWasInjectedToTheContext()
            {
                if (null === $this->parameter) {
                    throw new \DomainException('No parameter was injected (or null one)!');
                }
            }

            /**
             * @Then it should contain :content
             */
             public function itShouldContain($content)
             {
                 if ($content !== $this->parameter) {
                    throw new \DomainException(sprintf('Expected to get "%s", got "%s"!', $content, $this->parameter));
                 }
             }
        }
        """
        And a feature file "features/my.feature" containing:
        """
        Feature: Injecting a parameter

            Scenario:
                Given the parameter was injected to the context
                Then it should contain "shit happens"
        """


    Scenario: Importing a parameter from a Yaml file
        Given a Behat configuration containing:
        """
        default:
            suites:
                default:
                    contexts:
                        - FeatureContext:
                            - "%foobar%"
            extensions:
                FriendsOfBehat\ServiceContainerExtension:
                    imports:
                        - features/bootstrap/config/services.yml
        """
        And a config file "features/bootstrap/config/services.yml" containing:
        """
        parameters:
            foobar: "shit happens"
        """
        When I run Behat
        Then it should pass

    Scenario: Importing a parameter from a XML file
        Given a Behat configuration containing:
        """
        default:
            suites:
                default:
                    contexts:
                        - FeatureContext:
                            - "%foobar%"
            extensions:
                FriendsOfBehat\ServiceContainerExtension:
                    imports:
                        - features/bootstrap/config/services.xml
        """
        And a config file "features/bootstrap/config/services.xml" containing:
        """
        <container xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns="http://symfony.com/schema/dic/services">
            <parameters>
                <parameter key="foobar">shit happens</parameter>
            </parameters>
        </container>
        """
        When I run Behat
        Then it should pass

    Scenario: Importing a parameter from a PHP file
        Given a Behat configuration containing:
        """
        default:
            suites:
                default:
                    contexts:
                        - FeatureContext:
                            - "%foobar%"
            extensions:
                FriendsOfBehat\ServiceContainerExtension:
                    imports:
                        - features/bootstrap/config/services.php
        """
        And a config file "features/bootstrap/config/services.php" containing:
        """
        <?php

        $container->setParameter('foobar', 'shit happens');
        """
        When I run Behat
        Then it should pass
