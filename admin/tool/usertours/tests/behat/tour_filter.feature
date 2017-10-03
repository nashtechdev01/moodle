@tool @tool_usertours
Feature: Apply tour filters to a tour
  In order to give more directed tours
  As an administrator
  I need to create a user tour with filters applied

  @javascript
  Scenario: Add a tour for a different theme
    Given I log in as "admin"
    And I add a new user tour with:
      | Name                | First tour |
      | Description         | My first tour |
      | Apply to URL match  | /my/% |
      | Tour is enabled     | 1 |
      | Theme               | More |
    And I add steps to the "First tour" tour:
      | targettype                  | Title             | Content |
      | Display in middle of page   | Welcome           | Welcome to your personal learning space. We'd like to give you a quick tour to show you some of the areas you may find helpful |
    When I am on homepage
    Then I should not see "Welcome to your personal learning space. We'd like to give you a quick tour to show you some of the areas you may find helpful"

  @javascript
  Scenario: Add a tour for a specific role
    Given the following "courses" exist:
      | fullname | shortname | format | enablecompletion |
      | Course 1 | C1        | topics | 1                |
    And the following "users" exist:
      | username |
      | editor1  |
      | teacher1 |
      | student1 |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | editor1  | C1     | editingteacher |
      | teacher1 | C1     | teacher        |
      | student1 | C1     | student        |
    And I log in as "admin"
    And I add a new user tour with:
      | Name                | First tour |
      | Description         | My first tour |
      | Apply to URL match  | /course/view.php% |
      | Tour is enabled     | 1 |
      | Role                | Student,Non-editing teacher |
    And I add steps to the "First tour" tour:
      | targettype                  | Title             | Content |
      | Display in middle of page   | Welcome           | Welcome to your course tour.|
    And I log out
    And I log in as "editor1"
    When I am on "Course 1" course homepage
    Then I should not see "Welcome to your course tour."
    And I log out
    And I log in as "student1"
    And I am on "Course 1" course homepage
    And I should see "Welcome to your course tour."
    And I click on "End tour" "button"
    And I log out
    And I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I should see "Welcome to your course tour."

  @javascript
  Scenario: Check tabbing working correctly.
    Given I log in as "admin"
    And I open the User tour settings page
    And I click on "Enable" "link" in the "Boost - administrator" "table_row"
    And I click on "Enable" "link" in the "Boost - course view" "table_row"
    # First dialogue of the tour, "Welcome". It has Close, Next and End buttons.
    # Nothing highlighted on the page. Initially whole dialogue focussed.
    And I am on site homepage
    And I wait "1" seconds
    When I press tab
    And I pause scenario execution
#    Then the focussed element should be ".close" "css_element" in the "Welcome" "dialogue"
    And I press tab
    And the focussed element should be "Next" "button" in the "Welcome" "dialogue"
    And I press tab
#    And the focussed element should be "End tour" "button" in the "Welcome" "dialogue"
    And I press tab
    # Here the focus loops round to the whole dialogue again, but it is hard to verify this.
    And I press tab
#    And the focussed element should be ".close" "css_element" in the "Welcome" "dialogue"
    # Check looping works properly going backwards too.
    And I press shift tab
    And I press shift tab
#    And the focussed element should be "End tour" "button" in the "Welcome" "dialogue"

    And I press "Next"
    # Now we are on the "Navigation" step, so Previous is also enabled.
    # Also, the "Side panel" button in the page is highlighted, and this comes
    # in the tab order after End buttons, and before focus loops back to the popup.
    And I press tab
    And the focussed element should be ".close" "css_element" in the "Navigation" "dialogue"
    And I press tab
    And the focussed element should be "Previous" "button" in the "Navigation" "dialogue"
    And I press tab
    And the focussed element should be "Next" "button" in the "Navigation" "dialogue"
    And I press tab
    And the focussed element should be "End tour" "button" in the "Navigation" "dialogue"
    And I press tab
    And the focussed element should be "Side panel" "button"
    And I press tab
    # Here the focus loops round to the whole dialogue again, but it is hard to verify this.
    And I press tab
    And the focussed element should be ".close" "css_element" in the "Navigation" "dialogue"
    And I press shift tab
    And I press shift tab
    And the focussed element should be "Side panel" "button"
    And I press shift tab
    And the focussed element should be "End tour" "button" in the "Welcome" "dialogue"
