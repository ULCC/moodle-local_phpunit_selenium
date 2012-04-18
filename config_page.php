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
 * SauceOnDemand unit test plugin
 *
 * @package    local
 * @subpackage sauceondemand
 * @copyright  2012 ULCC {@link http://ulcc.ac.uk}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(__FILE__).'../../../config.php');
global $CFG, $OUTPUT, $PAGE;
require_once($CFG->dirroot.'/local/sauceondemand/settings_mform.php');

require_login(1);
require_capability('report/unittest:view', get_system_context());

$PAGE->set_url('/local/sauceondemand/config_page.php');
$PAGE->set_pagelayout('standard');
$title = 'SauceOnDemand settings';
$PAGE->set_title($title);
$PAGE->set_heading($title);

$settingsform = new sauceondemand_settings_mform();
$fromform = $settingsform->get_data();

echo $OUTPUT->header();

if (!empty($fromform) and confirm_sesskey()) {

    $settingsform->save_data($fromform);
    //display confirmation
    echo $OUTPUT->notification(get_string('changessaved'), 'notifysuccess');
}

$settingsform->display();

echo $OUTPUT->footer();
