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

namespace format_learningmap\local;

/**
 * Hook callbacks for format_learningmap
 *
 * @package    format_learningmap
 * @copyright  2025 ISB Bayern
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class hook_callbacks {

    /**
     * Allow plugins to extend a validation of the course editing form
     *
     * @param \core_course\hook\after_form_validation $hook
     */
    public static function check_course_activity_completion(\core_course\hook\after_form_validation $hook): void {
        $data = $hook->get_data();

        if ($data['format'] === 'learningmap') {
            if (empty($data['enablecompletion'])) {
                $hook->add_errors(['enablecompletion' => get_string('completionrequired', 'format_learningmap')]);
            }
        }
    }
}
