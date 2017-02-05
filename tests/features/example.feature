Feature: The simplest example of the Gherkin language
  To show that behat tests work
  As a developer
  I want to see that behat tests work correctly
  Scenario: Adding two numbers
    Given there are "5" and "2"
    When I sum them
    Then the sum should be "7"