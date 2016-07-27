Feature: Importing files
    In order to declare own services inside Behat container without writing an extension
    As a Behat User
    I need to import service definitions and parameters from files

    Scenario: Importing a service from a Yaml file
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
        And a context file "features/bootstrap/FeatureContext.php" containing:
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
        When I run Behat
        Then it should pass
