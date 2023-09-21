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
 * Contains the sectionnavigation class.
 *
 * @package   format_multitopic
 * @copyright 2022 Te Wānanga o Aotearoa
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace format_multitopic\output\courseformat\content;

use core_courseformat\output\local\content\sectionnavigation as sectionnavigation_base;
use context_course;
use core_courseformat\base as course_format;
use core_courseformat\output\local\courseformat_named_templatable;
use stdClass;


/**
 * Class to render a sectionnavigation inside a Multitopic course format.
 *
 * @package   format_multitopic
 * @copyright 2022 Te Wānanga o Aotearoa
 * @author    Jeremy FitzPatrick
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class sectionnavigation extends sectionnavigation_base {

    use courseformat_named_templatable;

    /** @var int the course displayed section number */
    protected $sectionno;

    /** @var \format_multitopic\section_info the course displayed section */
    protected $section;

    /** @var ?stdClass the calculated data to prevent calculations when rendered several times */
    private $data = null;

    /**
     * Constructor.
     *
     * @param \format_multitopic $format the course format
     * @param \format_multitopic\section_info $section section info
     */
    public function __construct(\format_multitopic $format, $section) {
        $this->format = $format;
        $section = $format->fmt_get_section($section);
        $this->section = $section;
        $this->sectionno = $section->section;
    }

    /**
     * Export this data so it can be used as the context for a mustache template.
     *
     * @param \core_renderer $output typically, the renderer that's calling this function
     * @return stdClass data context for a mustache template
     */
    public function export_for_template(\renderer_base $output): stdClass {
        global $CFG, $USER;
        require_once($CFG->dirroot . '/course/lib.php');

        if ($this->data !== null) {
            return $this->data;
        }

        $format = $this->format;
        $course = $format->get_course();

        $sections = $format->fmt_get_sections();

        $data = (object)[
            'previousurl' => '',
            'nexturl' => '',
            'larrow' => $output->larrow(),
            'rarrow' => $output->rarrow(),
            'currentsection' => $this->sectionno,
        ];

        $back = $this->section;
        while (isset($back->prevpageid)) {
            $back = $sections[$back->prevpageid];
            if ($back->uservisible) {
                $data->previousname = get_section_name($course, $back);
                $data->previousurl = course_get_url($course, $back);
                $data->hasprevious = true;
                break;
            }
        }

        $next = $this->section;
        while (isset($next->nextpageid)) {
            $next = $sections[$next->nextpageid];
            if ($next->uservisible) {
                $data->nextname = get_section_name($course, $next);
                $data->nexturl = course_get_url($course, $next);
                $data->hasnext = true;
                break;
            }
        }

        $this->data = $data;
        return $data;
    }
}
