<?php
/**
 * Created by JetBrains PhpStorm.
 * User: MattGibson
 * Date: 23/04/2012
 * Time: 17:26
 * To change this template use File | Settings | File Templates.
 */

require_once(dirname(__FILE__).'/../../../config.php');
global $CFG, $OUTPUT, $PAGE;
require_once($CFG->dirroot.'/local/phpunit_selenium/run_tests_mform.php');

require_login(1);
require_capability('report/unittest:view', get_system_context());

$PAGE->set_url('/local/phpunit_selenium/config_page.php');
$PAGE->set_pagelayout('standard');
$title = 'phpunit_selenium settings';
$PAGE->set_title($title);
$PAGE->set_heading($title);

$testdirectory =
    optional_param('testdirectory', '/local/phpunit_selenium/visualphpunit/tests/', PARAM_SAFEPATH);

$run_tests_form = new phpunit_selenium_run_tests_mform();
$run_tests_form->set_data((object)array('testdirectory' => $testdirectory));

// Sort out slashes that may or may not be there
$testdirectory = $CFG->dirroot.'/'.trim($testdirectory, '/');
if (!is_dir($testdirectory)) {
    $testdirectory = false;
}

$data = $run_tests_form->get_data();
if ($data) {
    redirect(new moodle_url('/local/phpunit_selenium/visualphpunit/run_tests.php', array('testdirectory' => $data->testdirectory)));
}

echo $OUTPUT->header();

$run_tests_form->display();

// The visualphp lib doesn't work well if you include it from a folder that's above its
// actual docroot. This is because all it's urls are relative to the page it's called from.
include ($CFG->dirroot.'/local/phpunit_selenium/visualphpunit/index.php');

echo $OUTPUT->footer();
