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

namespace tool_lcdelaystep\lifecycle;

global $CFG;
require_once($CFG->dirroot . '/admin/tool/lifecycle/step/lib.php');

use tool_lifecycle\local\manager\settings_manager;
use tool_lifecycle\local\response\step_response;
use tool_lifecycle\settings_type;
use tool_lifecycle\step\instance_setting;
use tool_lifecycle\step\libbase;

defined('MOODLE_INTERNAL') || die();

class step extends libbase {
    public function get_subpluginname()
    {
        return 'tool_lcdelaystep';
    }

    public function get_plugin_description() {
        return "Delay step plugin";
    }

    public function process_course($processid, $instanceid, $course)
    {
        global $DB;

        // Check if the step is already delayed.
        $record = $DB->get_record('tool_lcdelaystep', [
            'courseid' => $course->id,
            'stepinstanceid' => $instanceid
        ]);

        if ($record) {
            // If the step is already delayed, check if the delay is over.
            if ($record->delayeduntil < time()) {
                // If the delay is over, delete the record and go to next step.
                $DB->delete_records('tool_lcdelaystep', [
                    'courseid' => $course->id,
                    'stepinstanceid' => $instanceid
                ]);

                // The delay is over, change to 'proceed' status.
                mtrace("Delay step $instanceid (course $course->id) is over.");
                return step_response::proceed();
            } else {
                // If the delay is not over, change to 'wait' status.
                mtrace("Delaying step $instanceid (course $course->id) for " . ($record->delayeduntil - time()) . " seconds.");
                return step_response::waiting();
            }
        } else {
            // If the step is not delayed, delay it.

            // Get the delay.
            $delay = settings_manager::get_settings($instanceid, settings_type::STEP)['delay'];

            // New delay record.
            $DB->insert_record('tool_lcdelaystep', [
                'courseid' => $course->id,
                'stepinstanceid' => $instanceid,
                'delayeduntil' => time() + $delay
            ]);

            // Change to 'wait' status.
            mtrace("Delaying step $instanceid (course $course->id) for $delay seconds.");
            return step_response::waiting();
        }
    }

    public function process_waiting_course($processid, $instanceid, $course) {
        return $this->process_course($processid, $instanceid, $course);
    }

    public function instance_settings() {
        return array(
            new instance_setting('delay', PARAM_INT, true)
        );
    }

    public function extend_add_instance_form_definition($mform) {
        $mform->addElement('duration', 'delay', get_string('delay', 'tool_lcdelaystep'));
        $mform->addHelpButton('delay', 'delay', 'tool_lcdelaystep');
    }

    public function extend_add_instance_form_definition_after_data($mform, $settings) {
        // Default delay is 1 hour.
        if (is_array($settings) && array_key_exists('delay', $settings)) {
            $default = $settings['delay'];
        } else {
            $default = HOURSECS;
        }
        $mform->setDefault('delay', $default);
    }

}
