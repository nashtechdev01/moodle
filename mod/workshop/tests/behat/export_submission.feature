@mod @mod_workshop
@javascript
Feature: Workshop submission export to portfolio
  In order to be able to reuse my submission content
  As a student
  I need to be able to export my submission

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email                 |
      | student1 | Sam1      | Student1 | student1@example.com  |
      | student2 | Sam2      | Student2 | student2@example.com  |
      | teacher1 | Terry1    | Teacher1 | teacher1@example.com  |
    And the following "courses" exist:
      | fullname  | shortname |
      | Course1   | c1        |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | student1 | c1     | student        |
      | student2 | c1     | student        |
      | teacher1 | c1     | editingteacher |
    And the following "activities" exist:
      | activity | name         | intro                     | course | idnumber  |
      | workshop | TestWorkshop | Test workshop description | c1     | workshop1 |
    # Admin enable portfolio plugin
    And I log in as "admin"
    And I expand "Site administration" node
    And I follow "Advanced features"
    And I set the following administration settings values:
      | Enable portfolios | 1 |
    And I expand "Plugins" node
    And I expand "Portfolios" node
    And I follow "Manage portfolios"
    And I set portfolio instance "File download" to "Enabled and visible"
    And I log out
    # Teacher sets up assessment form and changes the phase to submission.
    And I log in as "teacher1"
    And I follow "Course1"
    And I edit assessment form in workshop "TestWorkshop" as:"
      | id_description__idx_0_editor | Aspect1 |
      | id_description__idx_1_editor | Aspect2 |
      | id_description__idx_2_editor |         |
    And I change phase in workshop "TestWorkshop" to "Submission phase"
    And I log out
    # Student1 submits.
    And I log in as "student1"
    And I follow "Course1"
    And I follow "TestWorkshop"
    And I add a submission in workshop "TestWorkshop" as:"
      | Title              | Submission1  |
      | Submission content | Some content |
    And I log out
    # Student2 submits.
    And I log in as "student2"
    And I follow "Course1"
    And I add a submission in workshop "TestWorkshop" as:"
      | Title              | Submission2  |
      | Submission content | Some content |
    And I log out


  Scenario: Students can export to portfolio their own submission
    Given I log in as "student1"
    And I follow "Course1"
    And I follow "TestWorkshop"
    When I follow "My submission"
    Then I should see "Submission1"
    And "Export submission to portfolio" "button" should exist
    And I click on "Export submission to portfolio" "button"
    And I should see "Available export formats"
    And I click on "Next" "button"
    And I should see "Summary of your export"
    Then following "Continue" should download between "1" and "500000" bytes
    And I log out



