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
 * Displays the course
 *
 * @package    format_learningmap
 * @copyright  2024 ISB Bayern
 * @author Stefan Hanauska <stefan.hanauska@csg-in.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/** @var format_learningmap $format */
$format = course_get_format($course);
$course = $format->get_course();
$context = context_course::instance($course->id);

// Course section 0 needs to be created if it doesn't exist.
course_create_sections_if_missing($course, 0);

$renderer = $PAGE->get_renderer('format_learningmap');
$outputclass = $format->get_output_classname('content');
$widget = new $outputclass($format);
echo $renderer->render($widget);
