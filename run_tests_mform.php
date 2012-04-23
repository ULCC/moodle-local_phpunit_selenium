<?php
/**
 * Created by JetBrains PhpStorm.
 * User: MattGibson
 * Date: 23/04/2012
 * Time: 17:45
 * To change this template use File | Settings | File Templates.
 */

/**
 * Settings form for the code checker.
 *
 * @copyright  2011 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined("MOODLE_INTERNAL") || die();

global $CFG;
require_once($CFG->dirroot.'/lib/formslib.php');

/**
 * Just provides a box for the test directory to be specified.
 */
class phpunit_selenium_run_tests_mform extends moodleform {
    protected function definition() {

        $mform = $this->_form;

//        $a = new stdClass();
//        $a->link = html_writer::link('http://docs.moodle.org/en/Development:Coding_style',
//                                     get_string('moodlecodingguidelines', 'local_codechecker'));
//        $a->path = html_writer::tag('tt', 'local/codechecker');
//        $mform->addElement('static', '', '', get_string('info', 'local_codechecker', $a));

        $grouparray = array(); // Puts them on the same line
        $grouparray[] = $mform->createElement('text', 'testdirectory', '');

        $grouparray[] = $mform->createElement('submit', 'submitbutton', get_string('submit'));
        $groupname = 'testdirectory_group';
        $mform->addGroup($grouparray, $groupname,
                         get_string('testdirectory', 'local_phpunit_selenium'), array(' '), false);
    }

    /**
     * Sets an error if there's no such directory
     *
     * @param $data
     */
    public function validation($data) {

        global $CFG;

        $errors = array();

        if (!empty($data['testdirectory'])) {
            $testdirectory = $CFG->dirroot.'/'.trim($data['testdirectory'], '/');
            if (!is_dir($testdirectory)) {
                $errors['testdirectory'] = get_string('notadirectory', 'local_phpunit_selenium');
            }
        }

        return $errors;

    }
}
