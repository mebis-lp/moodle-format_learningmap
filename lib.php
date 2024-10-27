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
    /**
     * Returns whether the first activity in the course is a learningmap.
     *
     * @return bool
     */
    public function main_learningmap_exists(): bool {
        $modinfo = $this->get_modinfo();
        if (empty($modinfo->cms)) {
            return false;
        }
        $firstactivity = reset($modinfo->cms);
        return $firstactivity->modname === 'learningmap' && $firstactivity->uservisible;
    }

    /**
     * Returns the first learningmap activity in the course. Throws an exception if there is no learningmap, so you should
     * check with main_learningmap_exists() first.
     *
     * @return cm_info|false
     * @throws moodle_exception
     */
    public function get_main_learningmap() {
        $modinfo = $this->get_modinfo();
        if (empty($modinfo->cms)) {
            return false;
        }
        $firstactivity = reset($modinfo->cms);
        if ($firstactivity->modname !== 'learningmap' || !$firstactivity->uservisible) {
            throw new moodle_exception('nolearningmap', 'format_learningmap');
        }
        return $firstactivity;
    }

    /**
     * Returns true if currently in editing mode. Then course index is supported as well as drag and drop.
     *
     * @return bool
     */
    public function supports_components() {
        return $this->show_editor();
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
            !$this->show_editor()) {
            $cm = $this->get_main_learningmap();
            if (!$cm) {
                if (has_capability('moodle/course:update', context_course::instance($this->courseid))) {
                    // Display an error message to the trainer.
                } else {
                    return;
                }
            }
            if (!$cm->uservisible) {
                return;
            } else {
                redirect($cm->url);
            }
        }
    }

    /**
     * Allows course format to execute code on moodle_page::set_cm()
     *
     * If we are inside the main module for this course, remove extra node level
     * from navigation: substitute course node with activity node, move all children
     *
     * @param moodle_page $page instance of page calling set_cm
     */
    public function page_set_cm(moodle_page $page) {
        parent::page_set_cm($page);

        $this->set_singleactivity_navigation($page);
    }

    /**
     * Sections can be removed from navigation if not in editing mode.
     *
     * @param cm_info $cm
     * @return bool
     */
    public function can_sections_be_removed_from_navigation(): bool {    
        return !$this->show_editor();
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
        if ($section->name !== '') {
            return format_string($section->name, true);
        } else {
            return $this->get_default_section_name($section);
        }
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
            [$itemid], MUST_EXIST);
        return course_get_format($section->course)->inplace_editable_update_section_name($section, $itemtype, $newvalue);
    }
}
