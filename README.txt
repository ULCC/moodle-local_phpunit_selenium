This is a small plugin which provides a class which can be extended to run selenium test remotely on
 saucelabs' ondemand infrastructure. You will need to configure your username and API token (as well
 as provide details for a test user account that can be used to log into your site) at
 Settings -> Site Administration -> Development -> Sauceondemand Unit Tests

 To get PHP code that will work, use SauceBuilder (Firefox plugin) to export as PHP code, then paste
 the test method into your class and make sure log_in_to_moodle() is called before any testing
 starts.
