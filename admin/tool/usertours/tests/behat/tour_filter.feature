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
    And I am on site homepage
    When I follow "Course 1"
    Then I should not see "Welcome to your course tour."
    And I log out
    And I log in as "student1"
    And I am on site homepage
    And I follow "Course 1"
    And I should see "Welcome to your course tour."
    And I click on "End tour" "button"
    And I log out
    And I log in as "teacher1"
    And I am on site homepage
    And I follow "Course 1"
    And I should see "Welcome to your course tour."

  @javascript
  Scenario: Check tabbing working correctly.
    Given the following "courses" exist:
      | fullname | shortname |
      | Course 1 | C1        |
    Given I log in as "admin"
    And I open the User tour settings page
    And I click on "Enable" "link" in the "Boost - course view" "table_row"
    And I am on "Course 1" course homepage
    # First dialogue of the tour, "Welcome". It has Close, Next and End buttons.
    # Nothing highlighted on the page. Initially whole dialogue focused.
    And I wait "1" seconds
    When I press tab
    Then the focused element should be ".close" "css_element" in the "Welcome" "dialogue"
    And I press tab
    And the focused element should be "Next" "button" in the "Welcome" "dialogue"
    And I press tab
    And the focused element should be "End tour" "button" in the "Welcome" "dialogue"
    And I press tab
    # Here the focus loops round to the whole dialogue again.
    And I press tab
    And the focused element should be ".close" "css_element" in the "Welcome" "dialogue"
    # Check looping works properly going backwards too.
    And I press shift tab
    And I press shift tab
    And the focused element should be "End tour" "button" in the "Welcome" "dialogue"

    And I press "Next"
    # Now we are on the "Customisation" step, so Previous is also enabled.
    # Also, the "Course Header" section in the page is highlighted, and this
    # section contain breadcrumb Dashboard / Course 1 / C1 and setting drop down,
    # so the focus have to go though them and back to the dialogue.
    And I wait "1" seconds
    And I press tab
    And the focused element should be ".close" "css_element" in the "Customisation" "dialogue"
    And I press tab
    And the focused element should be "Previous" "button" in the "Customisation" "dialogue"
    And I press tab
    And the focused element should be "Next" "button" in the "Customisation" "dialogue"
    And I press tab
    And the focused element should be "End tour" "button" in the "Customisation" "dialogue"
    # We tab 3 times from "End Tour" button to header container, drop down then go to "Dashboard" link.
    And I press tab
    And I press tab
    And I press tab
    And the focused element should be "Dashboard" "link" in the ".breadcrumb" "css_element"
    And I press tab
    And the focused element should be "Courses" "link"
    And I press tab
    And the focused element should be "C1" "link"
    # Standing at final element of "Course Header" section, tab twice will lead our focus back to
    # whole dialog then to close button on dialog header.
    And I press tab
    And I press tab
    And the focused element should be ".close" "css_element" in the "Customisation" "dialogue"
    # Press shift-tab twice should lead us back to "C1" link.
    And I press shift tab
    And I press shift tab
    And the focused element should be "C1" "link"

    And I press "Next"
    # Now we are on the "Navigation" step, so Previous is also enabled.
    # Also, the "Side panel" button in the page is highlighted, and this comes
    # in the tab order after End buttons, and before focus loops back to the popup.
    And I wait "1" seconds
    And I press tab
    And the focused element should be ".close" "css_element" in the "Navigation" "dialogue"
    And I press tab
    And the focused element should be "Previous" "button" in the "Navigation" "dialogue"
    And I press tab
    And the focused element should be "Next" "button" in the "Navigation" "dialogue"
    And I press tab
    And the focused element should be "End tour" "button" in the "Navigation" "dialogue"
    And I press tab
    And the focused element should be "Side panel" "button"
    And I press tab
    # Here the focus loops round to the whole dialogue again.
    And I press tab
    And the focused element should be ".close" "css_element" in the "Navigation" "dialogue"
    And I press shift tab
    And I press shift tab
    And the focused element should be "Side panel" "button"
    And I press shift tab
    And the focused element should be "End tour" "button" in the "Navigation" "dialogue"
