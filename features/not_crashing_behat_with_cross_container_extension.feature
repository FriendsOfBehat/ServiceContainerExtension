Feature: Not crashing Behat

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
