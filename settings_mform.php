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

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot.'/lib/formslib.php');

/**
 * Form to allow settings for Sauce onDemand to be stored
 */
class sauceondemand_settings_mform extends moodleform {

    /**
     * @var array List of the names of the config vars
     */
    protected $values = array('testusername' => 'text',
                              'testuserpass' => 'passwordunmask',
                              'saucelabsusername' => 'text',
                              'saucelabstoken' => 'text');

    public function definition() {

        $mform =& $this->_form;

        $textattributes = array('size' => '20');

        // All of them are text fields right now. Can expand by making it name => type
        foreach ($this->values as $fieldname => $type) {
                    $mform->addElement($type, $fieldname,
                                       get_string($fieldname, 'local_sauceondemand'),
                                       $textattributes);
        }

        $this->add_action_buttons();
    }

    /**
     * Saves the config values
     * @param $data
     */
    public function save_data($data) {

        foreach ($this->values as $fieldname => $type) {
            set_config($fieldname, $data->$fieldname, 'local_sauceondemand');
        }
    }

    /**
     * Loads the existing config values automatically
     */
    public function set_data() {
        $data = array();
        foreach ($this->values as $fieldname => $type) {
            $data[$fieldname] = get_config($fieldname, 'local_sauceondemand');
        }
        parent::set_data($data);
    }
}
