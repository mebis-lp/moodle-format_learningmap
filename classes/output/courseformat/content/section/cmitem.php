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

namespace format_learningmap\output\courseformat\content\section;

use stdClass;

/**
 * Class cmitem
 *
 * @package    format_learningmap
 * @copyright  2024 ISB Bayern
 * @author Stefan Hanauska <stefan.hanauska@csg-in.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class cmitem extends \core_courseformat\output\local\content\section\cmitem {

    /**
     * Returns the output class template path.
     *
     * This method redirects the default template when the section activity item is rendered.
     */
    public function get_template_name(\renderer_base $renderer): string {
        return 'format_learningmap/local/content/section/cmitem';
    }

    /**
     * Export this data so it can be used as the context for a mustache template.
     *
     * @param renderer_base $output typically, the renderer that's calling this function
     * @return array data context for a mustache template
     */
    public function export_for_template(\renderer_base $output): \stdClass {
        $data = parent::export_for_template($output);
        $data->islearningmap = true;

        if ($this->format->main_learningmap_exists()) {
            $mainlearningmap = $this->format->get_main_learningmap();
            if ($this->mod->id == $mainlearningmap->id) {
                $data->isfirstlearningmap = true;
            }
        }

        return $data;
    }
}
