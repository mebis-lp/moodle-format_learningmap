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
 * Empty view.php file for course format learningmap
 *
 * @package    format_learningmap
 * @copyright  2025 ISB Bayern
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../../config.php');

require_login();

$courseid = required_param('id', PARAM_INT);

$url = new moodle_url('/course/format/learningmap/view.php', ['id' => $courseid]);
$PAGE->set_url($url);
$PAGE->set_context(context_course::instance($id));

$course = get_course($courseid);

$PAGE->set_heading($course->name);
echo $OUTPUT->header();
echo $OUTPUT->footer();
