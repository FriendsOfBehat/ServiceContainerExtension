Feature: Usage of ServiceContainerExtension together with CrossContainerExtension
    In order to reference cross-container services and parameters inside Behat container
    As a Behat User
    I need to use ServiceContainerExtension and CrossContainerExtension together

    Scenario: Not crashing Behat
        Given a Behat configuration containing:
        """
        default:
            extensions:
                FriendsOfBehat\ServiceContainerExtension: ~

                FriendsOfBehat\CrossContainerExtension: ~
        """
        And a feature file with passing scenario
        When I run Behat
        Then it should pass
