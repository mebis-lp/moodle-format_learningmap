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

/**
 * Main class and callbacks implementations for Learningmap
 *
 * Documentation: {@link https://moodledev.io/docs/apis/plugintypes/format}
 *
 * @package    format_learningmap
 * @copyright  2024 ISB Bayern
 * @author     Stefan Hanauska <stefan.hanauska@csg-in.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use core\navigation\views\secondary;
use core\navigation\views\view;

/**
 * format_learningmap plugin implementation
 *
 * @package    format_learningmap
 * @copyright  2024 ISB Bayern
 * @author     Stefan Hanauska <stefan.hanauska@csg-in.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class format_learningmap extends core_courseformat\base {
    /** @var $mainlearningmap Main learningmap if already discovered. */
    private cm_info|false|null $mainlearningmap = null;

    /**
     * Returns whether the first activity in the course is a learningmap.
     *
     * @return bool
     */
    public function main_learningmap_exists(): bool {
        if ($this->mainlearningmap !== null) {
            return $this->mainlearningmap !== false;
        }
        $cms = $this->get_modinfo()->cms;
        while (!empty($cms)) {
            $activity = array_shift($cms);
            if ($activity->modname == 'learningmap' && $activity->uservisible) {
                $this->mainlearningmap = $activity;
                return true;
            }
        }
        $this->mainlearningmap = false;
        return false;
    }

    /**
     * Returns the first learningmap activity in the course. Throws an exception if there is no learningmap, so you should
     * check with main_learningmap_exists() first.
     *
     * @return cm_info
     * @throws moodle_exception
     */
    public function get_main_learningmap() {
        if ($this->main_learningmap_exists()) {
            return $this->mainlearningmap;
        } else {
            throw new moodle_exception('nolearningmap', 'format_learningmap');
        }
    }

    /**
     * Supports components.
     *
     * @return bool
     */
    public function supports_components() {
        return true;
    }

    /**
     * This format uses sections.
     *
     * @return bool
     */
    public function uses_sections() {
        return true;
    }

    /**
     * Returns true if the course has a front page.
     *
     * @return bool
     */
    public function has_view_page() {
        return $this->show_editor();
    }

    /**
     * This format uses course index only in editing mode.
     *
     * @return bool
     */
    public function uses_course_index() {
        return $this->show_editor();
    }

    /**
     * Return the plugin configs for external functions.
     *
     * @return array the list of configuration settings
     */
    public function get_config_for_external() {
        return $this->get_format_options();
    }

    /**
     * Used to set the secondary navigation for the singleactivity format.
     *
     * @param moodle_page $page
     * @return void
     */
    public function set_singleactivity_navigation($page) {
        $coursehomenode = $page->navigation->find('coursehome', view::TYPE_COURSE);

        // This is really ugly - but right now, there is no other way to use the secondary navigation
        // built for single activity format in other course formats.
        $page->course->format = 'singleactivity';
        $secondarynav = new secondary($page);
        $secondarynav->initialise();
        if (!empty($coursehomenode)) {
            $secondarynav->add_node($coursehomenode);
        }
        $page->set_secondarynav($secondarynav);
        $page->course->format = 'learningmap';
    }

    /**
     * Used to redirect to the main learningmap activity if not in editing mode.
     *
     * @param moodle_page $page
     * @return void
     */
    public function page_set_course(moodle_page $page) {
        global $PAGE;

        if (
            $PAGE == $page &&
            $page->has_set_url() &&
            $page->url->compare(new moodle_url('/course/view.php'), URL_MATCH_BASE) &&
            !$this->show_editor()
        ) {
            if (!$this->main_learningmap_exists()) {
                if (!has_capability('moodle/course:update', context_course::instance($this->courseid))) {
                    redirect(new moodle_url('/course/format/learningmap/error.php?courseid=' . $this->courseid));
                }
            } else {
                $cm = $this->get_main_learningmap();
                if (!$cm->uservisible) {
                    redirect(new moodle_url('/course/format/learningmap/error.php?courseid=' . $this->courseid));
                } else {
                    redirect($cm->url);
                }
            }
        }
    }

    /**
     * Allows course format to execute code on moodle_page::set_cm()
     *
     * @param moodle_page $page instance of page calling set_cm
     */
    public function page_set_cm(moodle_page $page) {
        global $PAGE;

        parent::page_set_cm($page);

        if ($PAGE == $page && $PAGE->has_set_url()) {
            $this->set_singleactivity_navigation($page);
        }
    }

    /**
     * Stealth modules are allowed here (but not necessary). This is set just for better compatibility with
     * courses that are converted from other formats.
     *
     * @param stdClass|cm_info $cm course module (may be null if we are displaying a form for adding a module)
     * @param stdClass|section_info $section section where this module is located or will be added to
     * @return bool
     */
    public function allow_stealth_module_visibility($cm, $section) {
        return true;
    }

    /**
     * Returns the section name.
     *
     * @param stdClass $section
     * @return string
     */
    public function get_section_name($section) {
        $section = $this->get_section($section);
        if ($section->name !== '' && $section->name !== null) {
            return format_string($section->name, true);
        } else {
            return $this->get_default_section_name($section);
        }
    }

    /**
     * Returns the default section name.
     *
     * @param stdClass $section
     * @return string
     */
    public function get_default_section_name($section) {
        if ($section->sectionnum == 0) {
            return get_string('section0name', 'format_learningmap');
        }

        return get_string('newsection', 'format_learningmap');
    }

    /**
     * Whether this format allows to delete sections.
     *
     * @param int|stdClass|section_info $section
     * @return bool
     */
    public function can_delete_section($section) {
        return true;
    }

    /**
     * Returns the information about the ajax support in the given source format.
     *
     * The returned object's property (boolean)capable indicates that
     * the course format supports Moodle course ajax features.
     *
     * @return stdClass
     */
    public function supports_ajax() {
        $ajaxsupport = new stdClass();
        $ajaxsupport->capable = true;
        return $ajaxsupport;
    }
}

/**
 * Implements callback inplace_editable() allowing to edit values in-place.
 *
 * @param string $itemtype
 * @param int $itemid
 * @param mixed $newvalue
 * @return inplace_editable
 */
function format_learningmap_inplace_editable($itemtype, $itemid, $newvalue) {
    global $DB, $CFG;
    require_once($CFG->dirroot . '/course/lib.php');
    if ($itemtype === 'sectionname' || $itemtype === 'sectionnamenl') {
        $section = $DB->get_record_sql(
            'SELECT s.* FROM {course_sections} s JOIN {course} c ON s.course = c.id WHERE s.id = ?',
            [$itemid],
            MUST_EXIST
        );
        return course_get_format($section->course)->inplace_editable_update_section_name($section, $itemtype, $newvalue);
    }
}

/**
 * Implements callback format_learningmap_coursemodule_definition_after_data() allowing to modify course module definition.
 *
 * @param moodleform_mod $moodleform
 * @param MoodleQuickForm $form
 * @return void
 */
function format_learningmap_coursemodule_definition_after_data($moodleform, $form) {
    global $COURSE;
    $current = $moodleform->get_current();
    if ($COURSE->format === 'learningmap' && empty($current->instance)) {        
        // Override default completion setting for learningmap format.
        if ($current->completion == COMPLETION_DISABLED) {
            $form->setDefault('completion', COMPLETION_TRACKING_MANUAL);
        }
    }
}
