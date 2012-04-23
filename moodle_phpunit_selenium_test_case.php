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

/**
 * Constant telling us what is coming back from the settings form
 */
define('PHPUNIT_SELENIUM_USE_SAUCELABS', 1);

/**
 * Constant telling us what is coming back from the settings form
 */
define('PHPUNIT_SELENIUM_USE_LOCAL', 0);

defined('MOODLE_INTERNAL') || die();

// Use Pear to install these packages and make sure the Pear include path is added to php.ini
// TODO use the Moodle lib version in 2.3
require_once 'PHPUnit/Autoload.php';
require_once 'PHPUnit/Framework/TestCase.php';
require_once 'PHPUnit/Extensions/SeleniumTestCase.php';
//require_once 'PHPUnit/Extensions/SeleniumTestCase/SauceOnDemandTestCase.php';
//require_once 'PHPUnit/Extensions/SeleniumTestCase/SauceOnDemandTestCase/Driver.php';


/**
 * This class will take the config settings and load them up ready for the tests to all run when
 * this class is extended. It does a slightly hack trick to include the option to use SauceOnDemand,
 * by taking the methods from the PEAR PHPUnit_Extensions_SeleniumTestCase_SauceOnDemandTestCase
 * class an using them in favour of the superclass' methods if the sauceorlocal setting says to.
 * This was the only workable option to achieve flexible subclassing at runtime because other parts
 * of the PHPUnit code use instanceof to check that our test is in fact a test, which makes the
 * bridge pattern or similar unsuitable without hacking PHPUnit.
 */
class moodle_phpunit_selenium_test_case extends PHPUnit_Extensions_SeleniumTestCase {

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
     * @var bool Lets us know whether to ignored the saucelabs methods in favour of the parent class
     *
     */
    protected $usingsaucelabs;

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
//            'host' => '128.86.248.115',
//            'port' => 5555,
            'timeout' => 10000,
        )
//        array(
//            'name' => 'Safari on MacOS X',
//            'browser' => '*safari',
//            'host' => 'my.macosx.box',
//            'port' => 4444,
//            'timeout' => 30000,
//        ),
//        array(
//            'name' => 'Safari on Windows XP',
//            'browser' => '*custom C:\Program Files\Safari\Safari.exe -url',
//            'host' => 'my.windowsxp.box',
//            'port' => 4444,
//            'timeout' => 30000,
//        ),
//        array(
//            'name' => 'Internet Explorer on Windows XP',
//            'browser' => '*iexplore',
//            'host' => 'my.windowsxp.box',
//            'port' => 4444,
//            'timeout' => 30000,
//        )
    );



    /**
     * Construtor needs to grab the config variables that allow the test user account to
     * log into Moodle and feed them into the saucelabs config stuff.
     */
    public function __construct() {

        global $CFG, $DB;

        $this->captureScreenshotOnFailure = true;
        $this->screenshotPath = $CFG->dirroot.'/local/phpunit_selenium/screenshots';
        $this->screenshotUrl = $CFG->wwwroot.'/local/phpunit_selenium/screenshots';

        $sauceorlocal = get_config('local_phpunit_selenium', 'sauceorlocal');
        $this->usingsaucelabs = $sauceorlocal == PHPUNIT_SELENIUM_USE_SAUCELABS;



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

        // Makes an empty driver, so the setx methods work
        parent::__construct();

        // Can't set static stuff dynamically. Not sure this matters
        if (!$this->usingsaucelabs) {
            $this->host = get_config('local_phpunit_selenium', 'localseleniumhost');
            $this->port = get_config('local_phpunit_selenium', 'localseleniumport');

            //foreach (self::$browsers as &$browser) {
            $this->setHost($this->host);
            $this->setPort((int)$this->port);
            // }

        }

        if ($this->usingsaucelabs) {

            $this->saucelabsusername = get_config('local_phpunit_selenium', 'saucelabsusername');
            $this->saucelabstoken = get_config('local_phpunit_selenium', 'saucelabstoken');

            if (empty($this->saucelabsusername) ||
                empty($this->saucelabstoken) ) {

                die('Config variables missing - you need sauce labs details!');
            }


            $this->setUsername($this->saucelabsusername);
            $this->setAccessKey($this->saucelabstoken);
        }

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

        global $DB;

        $this->assertNotEmpty($this->testadminusername, 'Admin username missing');
        $this->assertNotEmpty($this->testadminuserpass, 'Admin password missing');

        // Might already be logged in, but as not-admin
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

    ///////////////////////////////////////////////
    // Below are methods copied from the saucelabs class. These are also present in the extended
    // class which we want to oveerride conditionally, We can't rely on __call() and the bridge
    // pattern because various places check that this is a testcase using instanceof.
    //////////////////////////////////////////////

    /**
     * @param  array $browser
     * @return PHPUnit_Extensions_SeleniumTestCase_Driver
     * @since  Method available since Release 3.3.0
     */
    protected function getDriver(array $browser) {

        if (!$this->usingsaucelabs) {
            return parent::getDriver($browser);
        }

        if (isset($browser['name'])) {
            if (!is_string($browser['name'])) {
                throw new InvalidArgumentException(
                    'Array element "name" is not an string.'
                );
            }
        } else {
            $browser['name'] = '';
        }

        if (isset($browser['browser'])) {
            if (!is_string($browser['browser'])) {
                throw new InvalidArgumentException(
                    'Array element "browser" is not a string.'
                );
            }
            $this->browserName = $browser['browser'];
        } else {
            $browser['browser'] = '';
        }

        if (isset($browser['host'])) {
            if (!is_string($browser['host'])) {
                throw new InvalidArgumentException(
                    'Array element "host" is not a string.'
                );
            }
        } else {
            $browser['host'] = 'ondemand.saucelabs.com';
        }

        if (isset($browser['port'])) {
            if (!is_int($browser['port'])) {
                throw new InvalidArgumentException(
                    'Array element "port" is not an integer.'
                );
            }
        } else {
            $browser['port'] = 80;
        }

        if (isset($browser['timeout'])) {
            if (!is_int($browser['timeout'])) {
                throw new InvalidArgumentException(
                    'Array element "timeout" is not an integer.'
                );
            }
        } else {
            $browser['timeout'] = 30;
        }

        if (isset($browser['httpTimeout'])) {
            if (!is_int($browser['httpTimeout'])) {
                throw new InvalidArgumentException(
                    'Array element "httpTimeout" is not an integer.'
                );
            }
        } else {
            $browser['httpTimeout'] = 45;
        }

        $driver = new PHPUnit_Extensions_SeleniumTestCase_SauceOnDemandTestCase_Driver;
        $driver->setName($browser['name']);
        $driver->setBrowser($browser['browser']);
        $driver->setHost($browser['host']);
        $driver->setPort($browser['port']);
        $driver->setTimeout($browser['timeout']);
        $driver->setHttpTimeout($browser['httpTimeout']);
        $driver->setTestCase($this);
        $driver->setTestId($this->testId);

        $yml_found = false;
        if (isset($_SERVER['HOME'])) {
            $yml_path = realpath($_SERVER['HOME']).'/.sauce/ondemand.yml';
            $yml_found = file_exists($yml_path);
        }
        if (!$yml_found) {
            $yml_path = '/.sauce/ondemand.yml';
            $yml_found = file_exists($yml_path);
        }
        if ($yml_found) {
            if (!isset($this->yaml)) {
                $this->yaml = new sfYamlParser();
            }
            $pearsauce_config = $this->yaml->parse(file_get_contents($yml_path));
        }

        if (isset($browser['username'])) {
            if (!is_string($browser['username'])) {
                throw new InvalidArgumentException(
                    'Array element "username" is not a string.'
                );
            }

            $driver->setUsername($browser['username']);
        } elseif ($yml_found && isset($pearsauce_config['username'])) {
            $driver->setUsername($pearsauce_config['username']);
        } else {
            error_log('Warning: no username provided. This may result in "Could not connect to Selenium RC serve".  Run "sauce configure <username> <accesskey>" or call $this->setUsername to fix');
        }

        if (isset($browser['accessKey'])) {
            if (!is_string($browser['accessKey'])) {
                throw new InvalidArgumentException(
                    'Array element "accessKey" is not a string.'
                );
            }

            $driver->setAccessKey($browser['accessKey']);
        } elseif ($yml_found && isset($pearsauce_config['access_key'])) {
            $driver->setAccessKey($pearsauce_config['access_key']);
        } else {
            error_log('Warning: no access key provided. This may result in "Could not connect to Selenium RC serve".  Run "sauce configure <username> <accesskey>" or call $this->setAccessKey to fix');
        }

        if (isset($browser['browserVersion'])) {
            if (!is_string($browser['browserVersion'])) {
                throw new InvalidArgumentException(
                    'Array element "browserVersion" is not a string.'
                );
            }

            $driver->setBrowserVersion($browser['browserVersion']);
            $this->browserName .= " ".$browser['browserVersion'];
        }

        if (isset($browser['os'])) {
            if (!is_string($browser['os'])) {
                throw new InvalidArgumentException(
                    'Array element "os" is not a string.'
                );
            }

            $driver->setOs($browser['os']);
            $this->browserName .= " ".$browser['os'];
        }

        if (isset($browser['jobName'])) {
            if (!is_string($browser['jobName'])) {
                throw new InvalidArgumentException(
                    'Array element "jobName" is not a string.'
                );
            }

            $driver->setJobName($browser['jobName']);
        }

        if (isset($browser['public'])) {
            if (!is_bool($browser['public'])) {
                throw new InvalidArgumentException(
                    'Array element "public" is not a boolean.'
                );
            }

            $driver->setPublic($browser['public']);
        }

        if (isset($browser['tags'])) {
            if (!is_array($browser['tags'])) {
                throw new InvalidArgumentException(
                    'Array element "tags" is not an array.'
                );
            }

            $driver->setTags($browser['tags']);
        }

        if (isset($browser['passed'])) {
            if (!is_bool($browser['passed'])) {
                throw new InvalidArgumentException(
                    'Array element "passed" is not a boolean.'
                );
            }

            $driver->setPassed($browser['passed']);
        }

        if (isset($browser['recordVideo'])) {
            if (!is_bool($browser['recordVideo'])) {
                throw new InvalidArgumentException(
                    'Array element "recordVideo" is not a boolean.'
                );
            }

            $driver->setRecordVideo($browser['recordVideo']);
        }

        if (isset($browser['recordScreenshots'])) {
            if (!is_bool($browser['recordScreenshots'])) {
                throw new InvalidArgumentException(
                    'Array element "recordScreenshots" is not a boolean.'
                );
            }

            $driver->setRecordScreenshots($browser['recordScreenshots']);
        }

        if (isset($browser['sauceAdvisor'])) {
            if (!is_bool($browser['sauceAdvisor'])) {
                throw new InvalidArgumentException(
                    'Array element "sauceAdvisor" is not a boolean.'
                );
            }

            $driver->setSauceAdvisor($browser['sauceAdvisor']);
        }

        if (isset($browser['singleWindow'])) {
            if (!is_bool($browser['singleWindow'])) {
                throw new InvalidArgumentException(
                    'Array element "singleWindow" is not a boolean.'
                );
            }

            $driver->setSingleWindow($browser['singleWindow']);
        }

        if (isset($browser['userExtensionsUrl'])) {
            if (!is_string($browser['userExtensionsUrl']) && !is_array($browser['userExtensionsUrl'])) {
                throw new InvalidArgumentException(
                    'Array element "userExtensionsUrl" is not a string/array.'
                );
            }

            $driver->setUserExtensionsUrl($browser['userExtensionsUrl']);
        }

        if (isset($browser['firefoxProfileUrl'])) {
            if (!is_string($browser['firefoxProfileUrl'])) {
                throw new InvalidArgumentException(
                    'Array element "firefoxProfileUrl" is not a string.'
                );
            }

            $driver->setFirefoxProfileUrl($browser['firefoxProfileUrl']);
        }

        if (isset($browser['maxDuration'])) {
            if (!is_int($browser['maxDuration'])) {
                throw new InvalidArgumentException(
                    'Array element "maxDuration" is not an integer.'
                );
            }

            $driver->setMaxDuration($browser['maxDuration']);
        }

        if (isset($browser['idleTimeout'])) {
            if (!is_int($browser['idleTimeout'])) {
                throw new InvalidArgumentException(
                    'Array element "idleTimeout" is not an integer.'
                );
            }

            $driver->setIdleTimeout($browser['idleTimeout']);
        }

        if (isset($browser['build'])) {
            if (!is_string($browser['build'])) {
                throw new InvalidArgumentException(
                    'Array element "build" is not a string.'
                );
            }

            $driver->setBuild($browser['build']);
        }

        if (isset($browser['customData'])) {
            if (!is_array($browser['customData']) && !is_object($browser['customData'])) {
                throw new InvalidArgumentException(
                    'Array element "customData" is not an array/object.'
                );
            }

            $driver->setCustomData($browser['customData']);
        }

        $this->drivers[] = $driver;

        return $driver;
    }

    /**
     * Sets whether to automatically report if a test passed/failed to Sauce OnDemand.
     *
     * @param boolean $flag
     * @throws InvalidArgumentException
     */
    public function setReportPassFailToSauceOnDemand($flag) {
        if (!is_bool($flag)) {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(1, 'boolean');
        }

        $this->reportPassFailToSauceOnDemand = $flag;
    }

    /**
     * Gets whether to automatically report if a test passed/failed to Sauce OnDemand.
     *
     * @return boolean
     */
    public function getReportPassFailToSauceOnDemand() {
        return $this->reportPassFailToSauceOnDemand;
    }

    /**
     * Intercept stop() call.
     */
    public function stop() {

        if (!$this->usingsaucelabs) {
            return parent::stop();
        }

        if ($this->getReportPassFailToSauceOnDemand()) {
            // Set passed option
            if ($this->hasFailed()) {
                $passed = 'false';
            } else {
                $passed = 'true';
            }

            $this->setContext('sauce:job-info={"passed": '.$passed.'}');
        }

        return $this->__call('stop', array());
    }

    /**
     * This method is called when a test method did not execute successfully.
     *
     * @param Exception $e
     * @since Method available since Release 3.4.0
     */
    public function onNotSuccessfulTest(Exception $e) {

        if (!$this->usingsaucelabs) {
            return parent::onNotSuccessfulTest($e);
        }

        try {
            $jobUrl = sprintf(
                'https://saucelabs.com/jobs/%s',
                $this->drivers[0]->getSessionId()
            );

            $buffer = 'Current Browser URL: '.$this->drivers[0]->getLocation().
                "\n".
                'Sauce Labs Job: '.$jobUrl.
                "\n";

            $this->stop();
        } catch (RuntimeException $d) {
            if (!isset($buffer)) $buffer = "";
        }

        $message = $e->getMessage();

        if (!empty($message)) {
            $buffer = "\n".$message."\n".$buffer;
        }

        throw new PHPUnit_Framework_Exception($buffer);
    }

}
