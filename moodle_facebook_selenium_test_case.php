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



global $CFG;
include_once($CFG->dirroot.'/local/phpunit_selenium/php-webdriver/__init__.php');

/**
 * This class will take the config settings and load them up ready for the tests to all run when
 * this class is extended. It does a slightly hack trick to include the option to use SauceOnDemand,
 * by taking the methods from the PEAR PHPUnit_Extensions_SeleniumTestCase_SauceOnDemandTestCase
 * class an using them in favour of the superclass' methods if the sauceorlocal setting says to.
 * This was the only workable option to achieve flexible subclassing at runtime because other parts
 * of the PHPUnit code use instanceof to check that our test is in fact a test, which makes the
 * bridge pattern or similar unsuitable without hacking PHPUnit.
 * @property mixed testcourseshortname
 */
abstract class moodle_facebook_selenium_test_case extends PHPUnit_Framework_TestCase {

    /**
     * @var string The username used for the test script to log into the site as admin
     */
    protected static $testadminusername;

    /**
     * @var string The password used for the test script to log into the site as admin
     */
    protected static $testadminpassword;

    /**
     * @var mixed Moodle id of the test admin account. Used to tell if we are logged in as admin
     */
    protected static $testadminid;

    /**
     * @var string The username used for the test script to log into the site as a teacher
     */
    protected static $testteacherusername;

    /**
     * @var string The password used for the test script to log into the site as a teacher
     */
    protected static $testteacheruserpass;

    /**
     * @var mixed Moodle id of the test teacher account. Used to tell if we are logged in as a teacher
     */
    protected static $testteacherid;

    /**
     * @var string The username used for the test script to log into the site as a student
     */
    protected static $teststudentusername;

    /**
     * @var string The password used for the test script to log into the site as a student
     */
    protected static $teststudentuserpass;

    /**
     * @var mixed Moodle id of the test teacher account. Used to tell if we are logged in as a student
     */
    protected static $teststudentid;

    /**
     * @var string Standard login url which can be overridden if SSO is in use and needs bypassing
     */
    protected $loginurl = "/login/index.php";

    /**
     * @var WebDriverSession This is the object representing the remote browser session.
     */
    protected static $session;

    /**
     * @var array Prevents errors due to multiple destruction of copies of DB
     */
    protected $backupGlobalsBlacklist = array('DB');

    /**
     * @var array
     */
    public static $browsers = array(
        array(
            'name' => 'Firefox on Windows',
            'browser' => '*firefox',
            /*
            'host' => '128.86.248.115',
            'port' => 5555,
            */
            'timeout' => 10000,
        )
        /*
        array(
            'name' => 'Safari on MacOS X',
            'browser' => '*safari',
            'host' => 'my.macosx.box',
            'port' => 4444,
            'timeout' => 30000,
        ),
        array(
            'name' => 'Safari on Windows XP',
            'browser' => '*custom C:\Program Files\Safari\Safari.exe -url',
            'host' => 'my.windowsxp.box',
            'port' => 4444,
            'timeout' => 30000,
        ),
        array(
            'name' => 'Internet Explorer on Windows XP',
            'browser' => '*iexplore',
            'host' => 'my.windowsxp.box',
            'port' => 4444,
            'timeout' => 30000,
        )
        */
    );

    protected static $testcourseshortname;

    protected static $testcourseid;

    /**
     * Construtor needs to grab the config variables that allow the test user account to
     * log into Moodle.
     */
    public static function setUpBeforeClass() {

        global $CFG, $DB;

        self::$testteacherusername = get_config('local_phpunit_selenium', 'testteacherusername');
        self::$testteacheruserpass = get_config('local_phpunit_selenium', 'testteacheruserpass');
        self::$testadminid = $DB->get_field('user', 'id',
                                            array('username' =>
                                                  self::$testteacherusername));

        self::$teststudentusername = get_config('local_phpunit_selenium', 'teststudentusername');
        self::$teststudentuserpass = get_config('local_phpunit_selenium', 'teststudentuserpass');
        self::$testadminid = $DB->get_field('user', 'id',
                                            array('username' =>
                                                  self::$teststudentusername));

        self::$testcourseshortname = get_config('local_phpunit_selenium', 'testcourseshortname');
        self::$testcourseid = $DB->get_field('course', 'id',
                                             array('shortname' =>
                                                   self::$testcourseshortname));

        $host = get_config('local_phpunit_selenium', 'localseleniumhost');
        $port = get_config('local_phpunit_selenium', 'localseleniumport');

        $web_driver = new WebDriver('http://'.$host.':'.$port.'/wd/hub'); // URL of the selenium server.
        self::$session = $web_driver->session('firefox');
        self::$session->open($CFG->wwwroot);

    }

    /**
     * Assuming that all pages have the logout link, this just checks to see if it's there.
     *
     * @return bool
     */
    protected function is_logged_in() {
        $element = $this->session->element('link', 'Logout');
        return $element instanceof WebDriverElement;
    }

    /**
     * Switches on editing functions
     */
    protected function turn_editing_on() {
        if (!$this->isElementPresent('link=Turn editing off')) {
            $this->click('link=Turn editing on');
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

        $this->assertNotEmpty($this->testadminusername, 'Admin username missing');
        $this->assertNotEmpty($this->testadminuserpass, 'Admin password missing');

        // Might already be logged in, but as not-admin.
        if ($this->is_logged_in()) {
            if ($this->get_logged_in_user_id() === $this->testadminid) {
                return;
            } else {
                $this->log_out();
            }
        }

        $this->open($this->loginurl);
        $this->waitForPageToLoad("60000");
        $this->type("id=username", $this->testadminusername);
        $this->type("id=password", $this->testadminuserpass);
        $this->click("id=loginbtn");
        $this->waitForPageToLoad("60000");
    }

    /**
     * Loads the main page of the site, then logs in.
     *
     * @internal param string $url In case we have a custom login page
     */
    protected function log_in_as_teacher() {

        $this->assertNotEmpty($this->testteacherusername, 'Teacher username missing');
        $this->assertNotEmpty($this->testteacheruserpass, 'Teacher password missing');

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

        $this->assertNotEmpty($this->teststudentusername, 'Student username missing');
        $this->assertNotEmpty($this->teststudentuserpass, 'Student password missing');

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

    /**
     * @static
     * Ends the browser session
     */
    public static function tearDownAfterClass() {
        self::$session->close();
    }

}
