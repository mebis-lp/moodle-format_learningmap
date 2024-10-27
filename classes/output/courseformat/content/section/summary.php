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
 * Extend the section summary for the learningmap format.
 *
 * @package   format_learningmap
 * @copyright 2024 ISB Bayern
 * @author    Stefan Hanauska <stefan.hanauska@csg-in.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace format_learningmap\output\courseformat\content\section;

use context_course;
use core_courseformat\base as course_format;
use section_info;
use stdClass;

/**
 * Render course section summary for the learningmap format.
 *
 * @package   format_learningmap
 * @copyright 2024 ISB Bayern
 * @author    Stefan Hanauska <stefan.hanauska@csg-in.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class summary extends \core_courseformat\output\local\content\section\summary {

    /** @var section_info the course section class */
    private $section;

    /**
     * Constructor.
     *
     * @param course_format $format the course format
     * @param section_info $section the section info
     */
    public function __construct(course_format $format, section_info $section) {
        parent::__construct($format, $section);
        $this->section = $section;
    }

    /**
     * Get the name of the template to use for this templatable.
     *
     * @param renderer_base $renderer The renderer requesting the template name
     * @return string
     */
    public function get_template_name(\renderer_base $renderer): string {
        return 'format_learningmap/local/content/section/summary';
    }

    /**
     * Export this data so it can be used as the context for a mustache template.
     *
     * @param renderer_base $output typically, the renderer that's calling this function
     * @return array data context for a mustache template
     */
    public function export_for_template(\renderer_base $output): stdClass {

        $section = $this->section;

        $data = parent::export_for_template($output);

        // Add warning if no learningmap is present.
        if (!$this->format->main_learningmap_exists() && $this->section->sectionnum == 0) {
            $data->warning = get_string('nolearningmapwarning', 'format_learningmap');
        }

        return $data;
    }

    /**
     * Generate html for a section summary text
     *
     * @return string HTML to output.
     */
    public function format_summary_text(): string {
        $section = $this->section;
        $context = context_course::instance($section->course);
        $summarytext = file_rewrite_pluginfile_urls($section->summary, 'pluginfile.php',
            $context->id, 'course', 'section', $section->id);

        $options = new stdClass();
        $options->noclean = true;
        $options->overflowdiv = true;
        return format_text($summarytext, $section->summaryformat, $options);
    }
}
