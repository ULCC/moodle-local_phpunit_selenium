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
 * phpunit_selenium unit test plugin
 *
 * @package    local
 * @subpackage phpunit_selenium
 * @copyright  2012 ULCC {@link http://ulcc.ac.uk}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot.'/lib/formslib.php');
// For constants.
require_once($CFG->dirroot.'/local/phpunit_selenium/moodle_phpunit_selenium_test_case.php');

/**
 * Form to allow settings for Sauce onDemand to be stored
 */
class phpunit_selenium_settings_mform extends moodleform {

    /**
     * @var array List of the names of the config vars
     */
    protected $values = array('testadminusername' => 'text',
                              'testadminuserpass' => 'passwordunmask',
                              'testteacherusername' => 'text',
                              'testteacheruserpass' => 'passwordunmask',
                              'teststudentusername' => 'text',
                              'teststudentuserpass' => 'passwordunmask',
                              'testcourseshortname' => 'text');

    protected function definition() {

        $mform =& $this->_form;

        $textattributes = array('size' => '20');

        // All of them are text fields right now. Can expand by making it name => type.
        foreach ($this->values as $fieldname => $type) {
            $mform->addElement($type, $fieldname,
                               get_string($fieldname, 'local_phpunit_selenium'),
                               $textattributes);
        }

        // Use saucelabs or local? Radio creation is more awkward, so it's not in the loop.
        $radioarray = array();
        $radioarray[] = &MoodleQuickForm::createElement('radio', 'sauceorlocal', '',
                                                        get_string('saucelabs',
                                                                   'local_phpunit_selenium'), 1);
        $radioarray[] = & MoodleQuickForm::createElement('radio', 'sauceorlocal', '',
                                           get_string('localnetwork', 'local_phpunit_selenium'), 0);
        $mform->addGroup($radioarray, 'radioar', '', array(' '), false);

        // Connection details for saucelabs.
        $mform->addElement('text', 'saucelabsusername',
                           get_string('saucelabsusername', 'local_phpunit_selenium'),
                           $textattributes);
        $mform->addElement('text', 'saucelabstoken',
                           get_string('saucelabstoken', 'local_phpunit_selenium'),
                           $textattributes);
        $mform->disabledIf('saucelabsusername', 'sauceorlocal', PHPUNIT_SELENIUM_USE_LOCAL);
        $mform->disabledIf('saucelabstoken', 'sauceorlocal', PHPUNIT_SELENIUM_USE_LOCAL);

        // Connection details for local.
        $mform->addElement('text', 'localseleniumhost',
                           get_string('localseleniumhost', 'local_phpunit_selenium'),
                           $textattributes);
        $mform->addElement('text', 'localseleniumport',
                           get_string('localseleniumport', 'local_phpunit_selenium'),
                           $textattributes);

        $this->add_action_buttons();
    }

    /**
     * Saves the config values
     * @param $data
     */
    public function save_data($data) {

        $otherstuff = array('sauceorlocal',
                            'saucelabsusername',
                            'saucelabstoken',
                            'localseleniumhost',
                            'localseleniumport');

        foreach ($otherstuff as $fieldname) {
            if (isset($data->$fieldname)) {
                set_config($fieldname, $data->$fieldname, 'local_phpunit_selenium');
            }
        }

        foreach ($this->values as $fieldname => $type) {
            set_config($fieldname, $data->$fieldname, 'local_phpunit_selenium');
        }
    }

    /**
     * Loads the existing config values automatically
     */
    public function set_data() {
        $data = array();
        foreach ($this->values as $fieldname => $type) {
            $data[$fieldname] = get_config('local_phpunit_selenium', $fieldname);
        }

        $data['sauceorlocal'] = get_config('local_phpunit_selenium', 'sauceorlocal');
        $data['saucelabsusername'] = get_config('local_phpunit_selenium', 'saucelabsusername');
        $data['saucelabstoken'] = get_config('local_phpunit_selenium', 'saucelabstoken');
        $data['localseleniumhost'] = get_config('local_phpunit_selenium', 'localseleniumhost');
        $data['localseleniumport'] = get_config('local_phpunit_selenium', 'localseleniumport');

        parent::set_data($data);
    }
}
