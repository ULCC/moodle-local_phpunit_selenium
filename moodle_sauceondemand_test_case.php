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

// Use Pear to install these packages and make sure the Pear include path is added to php.ini
// TODO use the Moodle lib version in 2.3
require_once 'PHPUnit/Autoload.php';
require_once 'PHPUnit/Framework/TestCase.php';
require_once 'PHPUnit/Extensions/SeleniumTestCase.php';
require_once 'PHPUnit/Extensions/SeleniumTestCase/SauceOnDemandTestCase.php';
require_once 'PHPUnit/Extensions/SeleniumTestCase/SauceOnDemandTestCase/Driver.php';

/**
 * This class will take the config settings and load them up ready for the tests to all run when
 * this class is extended.
 */
//class moodle_sauceondemand_test_case extends PHPUnit_Extensions_SeleniumTestCase_SauceOnDemandTestCase {
class moodle_sauceondemand_test_case extends PHPUnit_Extensions_SeleniumTestCase {

    /**
     * @var string The username used for the test script to log into the site as admin
     */
    protected $testadminusername;

    /**
     * @var string The password used for the test script to log into the site as admin
     */
    protected $testadminpassword;

    /**
     * @var mixed Moodle id of the test admin account. Used to tell if we are logged in as admin
     */
    protected $testadminid;

    /**
     * @var string The username used for the test script to log into the site as a teacher
     */
    protected $testteacherusername;

    /**
     * @var string The password used for the test script to log into the site as a teacher
     */
    protected $testteacherpassword;

    /**
     * @var mixed Moodle id of the test teacher account. Used to tell if we are logged in as a teacher
     */
    protected $testteacherid;

    /**
     * @var string The username used for the test script to log into the site as a student
     */
    protected $teststudentusername;

    /**
     * @var string The password used for the test script to log into the site as a student
     */
    protected $teststudentpassword;

    /**
     * @var mixed Moodle id of the test teacher account. Used to tell if we are logged in as a student
     */
    protected $teststudentid;

    /**
     * @var string Username on the saucelabs site
     */
    protected $saucelabsusername;

    /**
     * @var string API token from the profile page of the above user
     */
    protected $saucelabstoken;

    /**
     * @var string Standard login url which can be overridden if SSO is in use and needs bypassing
     */
    protected $loginurl = "/login/index.php";

    /**
     * Construtor needs to grab the config variables that allow the test user account to
     * log into Moodle and feed them into the saucelabs config stuff.
     */
    public function __construct() {

        global $CFG, $DB;

        parent::__construct();

        $this->testadminusername = get_config('local_phpunit_selenium', 'testadminusername');
        $this->testadminuserpass = get_config('local_phpunit_selenium', 'testadminuserpass');
        $this->testadminid = $DB->get_field('user', 'id',
                                             array('username' =>
                                             $this->testadminusername));

        $this->testteacherusername = get_config('local_phpunit_selenium', 'testteacherusername');
        $this->testteacheruserpass = get_config('local_phpunit_selenium', 'testteacheruserpass');
        $this->testadminid = $DB->get_field('user', 'id',
                                            array('username' =>
                                            $this->testteacherusername));

        $this->teststudentusername = get_config('local_phpunit_selenium', 'teststudentusername');
        $this->teststudentuserpass = get_config('local_phpunit_selenium', 'teststudentuserpass');
        $this->testadminid = $DB->get_field('user', 'id',
                                            array('username' =>
                                            $this->teststudentusername));

        $this->testcourseshortname = get_config('local_phpunit_selenium', 'testcourseshortname');
        $this->testcourseid = $DB->get_field('course', 'id',
                                             array('shortname' =>
                                                   $this->testcourseshortname));

        $this->saucelabsusername = get_config('local_phpunit_selenium', 'saucelabsusername');
        $this->saucelabstoken    = get_config('local_phpunit_selenium', 'saucelabstoken');

        if (empty($this->saucelabsusername) ||
            empty($this->saucelabstoken) ) {

            die('Config variables missing - you need sauce labs details!');
        }

        $this->setUsername($this->saucelabsusername);
        $this->setAccessKey($this->saucelabstoken);
        $this->setBrowserUrl($CFG->wwwroot);

    }

    /**
     * Assuming that all pages have the logout link
     *
     * @return bool
     */
    protected function is_logged_in() {
        return !$this->isElementPresent('link=Logout');
    }

    protected function turn_editing_on() {
        if (!$this->isElementPresent('link=Turn editing off')) {
            $this->click('link=Logout');
            $this->waitForPageToLoad("60000");
        }
    }

    /**
     * Logs the user out so we can switch users
     *
     * @return bool
     */
    protected function log_out() {
        if ($this->isElementPresent('link=Logout')) {
            $this->click('link=Logout');
            $this->waitForPageToLoad("60000");
            return true;
        }
        return false;
    }

    /**
     * Extracts the user id from the logout link so we know who we are
     *
     * @return int
     */
    protected function get_logged_in_user_id() {
        $id = 0;
        if ($this->isElementPresent("div[@id='page - footer']/div[@class='logininfo']/a[1]")) {
            $link = $this->getAttribute("div[@id='page - footer']/div[@class='logininfo']/a[1]@href");
            $matches = array();
            if (preg_match('#(\d+)$#', $link, $matches)) {
                $id = $matches[1];
            }
        }
        return $id;
    }

    /**
     * Loads the main page of the site, then logs in.
     *
     * @internal param string $url In case we have a custom login page
     */
    protected function log_in_as_admin() {

        $this->assertNotEmpty($this->testadminusername);
        $this->assertNotEmpty($this->testadminusername);

        // Might already be logged in, but as not-admin
        if ($this->is_logged_in()) {
            if ($this->get_logged_in_user_id() == $this->testadminid) {
                return;
            } else {
                $this->log_out();
            }
        }

        $this->open($this->loginurl);
        $this->waitForPageToLoad("60000");
        $this->type("id=username", $this->testadminusername);
        $this->type("id=password", $this->testadminusername);
        $this->click("id=loginbtn");
        $this->waitForPageToLoad("60000");
    }

    /**
     * Loads the main page of the site, then logs in.
     *
     * @internal param string $url In case we have a custom login page
     */
    protected function log_in_as_teacher() {

        $this->assertNotEmpty($this->testteacherusername);
        $this->assertNotEmpty($this->testteacheruserpass);

        if ($this->is_logged_in()) {
            if ($this->get_logged_in_user_id() == $this->testteacherid) {
                return;
            } else {
                $this->log_out();
            }
        }

        $this->open($this->loginurl);
        $this->waitForPageToLoad("60000");
        $this->type("id=username", $this->testteacherusername);
        $this->type("id=password", $this->testteacheruserpass);
        $this->click("id=loginbtn");
        $this->waitForPageToLoad("60000");
    }

    /**
     * Loads the main page of the site, then logs in.
     *
     * @internal param string $url In case we have a custom login page
     */
    protected function log_in_as_student() {

        $this->assertNotEmpty($this->teststudentusername);
        $this->assertNotEmpty($this->teststudentuserpass);

        if ($this->is_logged_in()) {
            if ($this->get_logged_in_user_id() == $this->teststudentid) {
                return;
            } else {
                $this->log_out();
            }
        }

        $this->open($this->loginurl);
        $this->waitForPageToLoad("60000");
        $this->type("id=username", $this->teststudentusername);
        $this->type("id=password", $this->teststudentuserpass);
        $this->click("id=loginbtn");
        $this->waitForPageToLoad("60000");
    }

    /**
     * Sets the remote browser to IE9 on Windows 8
     */
    protected function set_browser_ie9_windows8() {
        $this->setOs('Windows 2008');
        $this->setBrowserVersion('9');
        $this->setBrowser('iehta');
    }


}
