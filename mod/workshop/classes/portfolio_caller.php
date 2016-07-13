<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

defined('MOODLE_INTERNAL') || die();
require_once($CFG->libdir . '/portfolio/caller.php');

/**
 * Workshop portfolio caller subclass to handle export submission and assessment to portfolio.
 *
 * @package   mod_workshop_portfolio_caller
 * @copyright Loc Nguyen <ndloc1905@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class mod_workshop_portfolio_caller extends portfolio_module_caller_base {
    protected $submissionid;
    protected $assessmentid;
    protected $cm;
    protected $submissionfiles = array();

    private $submission;
    private $assessment;
    private $author;
    private $showauthor;

    /**
     * Return array of expected call back arguments
     *
     * and whether they are required or not.
     *
     * @return array
     */
    public static function expected_callbackargs() {
        return array(
            'submissionid' => false,
            'assessmentid' => false,
        );
    }

    /**
     * Load data required for the export.
     *
     * Load submission and assessment by submissionid, assessmentid.
     *
     * @return void
     */
    public function load_data() {
        global $DB;
        global $PAGE;

        $this->submission = $DB->get_record('workshop_submissions', array('id' => $this->submissionid), '*', MUST_EXIST);
        $this->cm = get_coursemodule_from_instance('workshop', $this->submission->workshopid, 0, false, MUST_EXIST);
        $this->author = $DB->get_record('user', array('id' => $this->submission->authorid));

        $workshoprecord = $DB->get_record('workshop', array('id' => $this->cm->instance), '*', MUST_EXIST);
        $course = $DB->get_record('course', array('id' => $this->cm->course), '*', MUST_EXIST);
        $this->workshop = new workshop($workshoprecord, $this->cm, $course);

        // Load data for export assessment.
        if ($this->submissionid && $this->assessmentid) {
            $assessments    = $this->workshop->get_assessments_of_submission($this->submission->id);
            $this->assessment = new stdClass();
            $this->assessment = $assessments[$this->assessmentid];
        }
        // Load data for export submission.
        if ($this->submissionid && !$this->assessmentid) {
            $fs = get_file_storage();
            $this->submissionfiles  = $fs->get_area_files($this->workshop->context->id,
                'mod_workshop', 'submission_attachment', $this->submissionid);

            // Check anonymity of exported, show author or not.
            $ispublished    = ($this->workshop->phase == workshop::PHASE_CLOSED
                and $this->submission->published == 1
                and has_capability('mod/workshop:viewpublishedsubmissions', $this->workshop->context));
            $seenaspublished = false; // Is the submission seen as a published submission?

            if ($ispublished) {
                $seenaspublished = true;
            }
            if ($seenaspublished) {
                $this->showauthor = has_capability('mod/workshop:viewauthorpublished', $this->workshop->context);
            } else {
                $this->showauthor = has_capability('mod/workshop:viewauthornames', $this->workshop->context);
            }
        }
    }

    /**
     * Prepare the package ready to be passed off to the portfolio plugin.
     *
     * @return void
     */
    public function prepare_package() {
        if ($this->submissionid && $this->assessmentid) {
            $assessmenthtml = $this->prepare_assessment($this->assessment);
            $content = $assessmenthtml;
            $name = 'assessment.html';
        }
        if ($this->submissionid && !$this->assessmentid) {
            $submissionhtml = $this->prepare_submission($this->submission);
            $content = $submissionhtml;
            $name = 'submission.html';
        }
        $manifest = ($this->exporter->get('format') instanceof PORTFOLIO_FORMAT_RICH);
        $this->get('exporter')->write_new_file($content, $name, $manifest);
    }

    /**
     * Helper function to output submission for export.
     *
     * This is cut down version of workshop submission renderer function.
     *
     * @global object
     * @param object $submission
     * @return string
     */
    private function prepare_submission($submission) {
        global $CFG;
        $output = '';

        $output .= html_writer::tag("h2", $this->workshop->name);
        if (trim($this->workshop->instructauthors)) {
            $output .= html_writer::tag("h3", get_string('instructauthors', 'workshop'));
            $output .= $this->workshop->instructauthors;
        }

        $submissionurl = $this->workshop->submission_url($submission->id);
        // Write submission content.
        $output .= html_writer::start_tag('div', array('class' => 'submissionheader', 'style' => 'background-color: #ddd;'));
        $output .= html_writer::tag("h3", html_writer::link($submissionurl, format_string($submission->title)));
        if ($this->showauthor) {
            $userurl            = new moodle_url('/user/view.php',
                array('id' => $this->author->id, 'course' => $this->cm->id));
            $a                  = new stdclass();
            $a->name            = fullname($this->author);
            $a->url             = $userurl->out();
            $byfullname         = get_string('byfullname', 'workshop', $a);
            $output .= html_writer::tag('div', $byfullname);
        }
        $created = get_string('userdatecreated', 'workshop', userdate($submission->timecreated));
        $output .= html_writer::tag("div", $created, array('style' => 'font-size:x-small'));

        if ($submission->timemodified > $submission->timecreated) {
            $modified = get_string('userdatemodified', 'workshop', userdate($submission->timemodified));
            $output .= html_writer::tag("div", $modified, array('style' => 'font-size:x-small'));
        }
        $output .= html_writer::end_tag('div');
        $content = format_text($submission->content, $submission->contentformat);
        $output .= html_writer::tag("div", $content);

        $outputfiles = '';
        foreach ($this->submissionfiles as $file) {
            if ($file->is_directory()) {
                continue;
            }
            $filepath   = $file->get_filepath();
            $filename   = $file->get_filename();
            $fileurl    = moodle_url::make_pluginfile_url($this->workshop->context->id, 'mod_workshop', 'submission_attachment',
                $submission->id, $filepath, $filename, true);
            $type       = $file->get_mimetype();
            $linkhtml   = html_writer::link($fileurl, $filename);
            $outputfiles .= html_writer::tag('li', $linkhtml, array('class' => $type));

            if (!empty($CFG->enableplagiarism)) {
                require_once($CFG->libdir.'/plagiarismlib.php');
                $outputfiles .= plagiarism_get_links(array('userid' => $file->get_userid(),
                    'file' => $file,
                    'cmid' => $this->cmid,
                    'course' => $this->cm->course));
            }
        }
        $output .= $outputfiles;

        return $output;
    }

    /**
     * Helper function to output assessment for export.
     *
     * This is a cut down function of workshop assessment renderer function.
     *
     * @global object
     * @param stdClass $displayassessment
     * @return string
     */
    private function prepare_assessment($assessment) {
        global $PAGE;
        $strategy       = $this->workshop->grading_strategy_instance();
        $mform      = $strategy->get_assessment_form($PAGE->url, 'assessment', $assessment, false);
        $showreviewer   = has_capability('mod/workshop:viewreviewernames', $this->workshop->context);
        $options    = array(
            'showreviewer'  => $showreviewer,
            'showauthor'    => $this->showauthor,
            'showform'      => !is_null($assessment->grade),
            'showweight'    => true,
        );
        $displayassessment = $this->workshop->prepare_assessment($assessment, $mform, $options);

        $output = '';

        // Start write assessment content.
        $anonymous = is_null($displayassessment->reviewer);

        $output .= html_writer::start_tag('div', array('class' => 'assessmentheader', 'style' => 'background-color: #ddd;'));

        if (!empty($displayassessment->title)) {
            $title = s($displayassessment->title);
        } else {
            $title = get_string('assessment', 'workshop');
        }

        if ($displayassessment->url instanceof moodle_url) {
            $output .= html_writer::tag("div", html_writer::link($displayassessment->url, $title));
        } else {
            $output .= html_writer::tag("div", $title);
        }

        if (!$anonymous) {
            $reviewer   = $displayassessment->reviewer;
            $userurl    = new moodle_url('/user/view.php',
                array('id' => $reviewer->id, 'course' => $this->cm->id));
            $a          = new stdClass();
            $a->name    = fullname($reviewer);
            $a->url     = $userurl->out();
            $byfullname = get_string('assessmentby', 'workshop', $a);
            $output .= html_writer::tag("div", $byfullname);
        }

        if (is_null($displayassessment->realgrade)) {
            $output .= html_writer::tag("div", get_string('notassessed', 'workshop'));
        } else {
            $a              = new stdClass();
            $a->max         = $displayassessment->maxgrade;
            $a->received    = $displayassessment->realgrade;
            $output .= html_writer::tag("div", get_string('gradeinfo', 'workshop', $a));

            if (!is_null($displayassessment->weight) and $displayassessment->weight != 1) {
                $output .= html_writer::tag("div", get_string('weightinfo', 'workshop', $displayassessment->weight));
            }
        }
        $output .= html_writer::end_tag('div');
        if (!is_null($displayassessment->form)) {
            $output .= html_writer::tag("div", get_string('assessmentform', 'workshop'));
            $output .= html_writer::tag("div", self::moodleform($displayassessment->form));

            if (!$displayassessment->form->is_editable()) {
                $content = $displayassessment->get_overall_feedback_content();
                if ($content != '') {
                    $output .= html_writer::tag("div", get_string('overallfeedback', 'workshop'));
                    $output .= html_writer::tag("div", $content);
                }
            }
        }

        $attachments = $displayassessment->get_overall_feedback_attachments();

        if (!empty($attachments)) {
            $files = '';
            foreach ($attachments as $attachment) {
                $link = html_writer::link($attachment->fileurl, substr($attachment->filepath.$attachment->filename, 1));
                $files .= html_writer::tag('li', $link, array('class' => $attachment->mimetype));
            }
            $output .= html_writer::tag('ul', $files, array('class' => 'files'));
        }

        return $output;
    }

    /**
     * Return url for redirecting user when cancel or go back.
     *
     * @global object
     * @return string
     */
    public function get_return_url() {
        global $CFG;
        $returnurl = new moodle_url('/mod/workshop/submission.php',
            array('cmid' => $this->cm->id));
        return $returnurl->out();
    }

    /**
     * Get navigation that logically follows from the place the user was before.
     *
     * @global object
     * @return array
     */
    public function get_navigation() {
        global $CFG;
        $link = new moodle_url('/mod/workshop/submission.php',
            array('cmid' => $this->cm->id));
        $navlinks = array();
        $navlinks[] = array(
            'name' => format_string($this->submission->title),
            'link' => $link->out(),
            'type' => 'title'
        );
        return array($navlinks, $this->cm);
    }

    /**
     * How long might we expect this export to take.
     *
     * @return constant one of PORTFOLIO_TIME_XX
     */
    public function expected_time() {
        // ...@Todo check for attachment size.
        return PORTFOLIO_TIME_LOW;
    }

    /**
     * Make sure that the current user is allowed to do the export.
     *
     * @uses CONTEXT_MODULE
     * @return boolean
     */
    public function check_permissions() {
        $context = context_module::instance($this->cm->id);
        if ($this->submissionid && $this->assessmentid) {
            return has_capability('mod/workshop:exportownsubmission', $context)
                && has_capability('mod/workshop:exportownsubmissionassessment', $context);
        }
        return has_capability('mod/workshop:exportownsubmission', $context);
    }

    /**
     * Return the sha1 of this content.
     *
     * @return string
     */
    public function get_sha1() {
        if ($this->submissionid && $this->assessmentid) {
            return sha1($this->assessment->id . ',' . $this->assessment->title . ',' . $this->assessment->timecreated);
        }
        if ($this->submissionid && !$this->assessmentid) {
            return sha1($this->submission->id . ',' . $this->submission->title . ',' . $this->submission->timecreated);
        }
    }

    /**
     * Return a nice name to be displayed about this export location.
     *
     * @return string
     */
    public static function display_name() {
        return get_string('modulename', 'workshop');
    }

    /**
     * What formats this function *generally* supports.
     *
     * @return array
     */
    public static function base_supported_formats() {
        return array(PORTFOLIO_FORMAT_FILE, PORTFOLIO_FORMAT_RICHHTML, PORTFOLIO_FORMAT_PLAINHTML, PORTFOLIO_FORMAT_LEAP2A);
    }

    /**
     * Helper method dealing with the fact we can not just fetch the output of moodleforms.
     *
     * @param moodleform $mform
     * @return string HTML
     */
    protected static function moodleform(moodleform $mform) {
        ob_start();
        $mform->display();
        $o = ob_get_contents();
        ob_end_clean();
        return $o;
    }
}

