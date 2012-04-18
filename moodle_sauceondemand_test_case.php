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
require_once 'PHPUnit/Framework/TestCase.php';
require_once 'PHPUnit/Extensions/SeleniumTestCase.php';
require_once 'PHPUnit/Extensions/SeleniumTestCase/SauceOnDemandTestCase.php';
require_once 'PHPUnit/Extensions/SeleniumTestCase/SauceOnDemandTestCase/Driver.php';

/**
 * This class will take the config settings and load them up ready for the tests to all run when
 * this class is extended.
 */
class moodle_sauceondemand_test_case extends PHPUnit_Extensions_SeleniumTestCase_SauceOnDemandTestCase {

    /**
     * @var string The username used for the test script to log into the site
     */
    protected $testloginusername;

    /**
     * @var string The password used for the test script to log into the site
     */
    protected $testloginpassword;

    /**
     * @var string Username on the saucelabs site
     */
    protected $saucelabsusername;

    /**
     * @var string API token from the profile page of the above user
     */
    protected $saucelabstoken;

    /**
     * Construtor needs to grab the config variables that allow the test user account to
     * log into Moodle and feed them into the saucelabs config stuff.
     */
    public function __construct() {

        global $CFG;

        parent::__construct();

        $this->testadminusername = get_config('local_sauceondemand', 'testusername');
        $this->testadminuserpass = get_config('local_sauceondemand', 'testuserpass');
        $this->testteacherusername = get_config('local_sauceondemand', 'testusername');
        $this->testteacheruserpass = get_config('local_sauceondemand', 'testuserpass');
        $this->teststudentusername = get_config('local_sauceondemand', 'testusername');
        $this->teststudentuserpass = get_config('local_sauceondemand', 'testuserpass');

        $this->saucelabsusername = get_config('local_sauceondemand', 'saucelabsusername');
        $this->saucelabstoken    = get_config('local_sauceondemand', 'saucelabstoken');

        if (empty($this->saucelabsusername) ||
            empty($this->saucelabstoken) ) {

            die('Config variables missing - you need sauce labs details!');
        }

        $this->setUsername($this->saucelabsusername);
        $this->setAccessKey($this->saucelabstoken);
        $this->setBrowserUrl($CFG->wwwroot);

        $this->open("/");
        $this->waitForPageToLoad("60000");
    }

    /**
     * Assuming that all pages have the logout link
     *
     * @return bool
     */
    protected function is_logged_in() {
        return !$this->isElementPresent('link=Logout');
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
     * Loads the main page of the site, then logs in.
     *
     * @param string $url In case we have a custom login page
     */
    protected function log_in_as_admin($url = "/login/index.php") {

        if ($this->is_logged_in()) {
            $this->log_out();
        }

        $this->open($url);
        $this->waitForPageToLoad("60000");
        $this->type("id=username", $this->testadminusername);
        $this->type("id=password", $this->testadminusername);
        $this->click("id=loginbtn");
        $this->waitForPageToLoad("60000");
    }

    /**
     * Loads the main page of the site, then logs in.
     *
     * @param string $url In case we have a custom login page
     */
    protected function log_in_as_teacher($url = "/login/index.php") {

        if ($this->is_logged_in()) {
            $this->log_out();
        }

        $this->open($url);
        $this->waitForPageToLoad("60000");
        $this->type("id=username", $this->testteacherusername);
        $this->type("id=password", $this->testteacheruserpass);
        $this->click("id=loginbtn");
        $this->waitForPageToLoad("60000");
    }

    /**
     * Loads the main page of the site, then logs in.
     *
     * @param string $url In case we have a custom login page
     */
    protected function log_in_as_student($url = "/login/index.php") {

        if ($this->is_logged_in()) {
            $this->log_out();
        }

        $this->open($url);
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
