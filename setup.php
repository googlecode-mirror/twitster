<?php
/**
 *
 */
function validate_request() {
  $errors = array();
  $link = @mysql_connect($_REQUEST['dbhost'], $_REQUEST['dbuser'],
			 $_REQUEST['dbuser'], $_REQUEST['dbpass']);
  if (!$link) {
    $errors['db'][] = "Unable to connect to the database. Please check your credentials and try again.";
  }
  if (!mysql_select_db(HASHMT_DBNAME)) {
    $errors['db'][] = "The database name you specified does not exist.";
  }
  require_once("class.twitter.php");
  $twitter = new twitter();
  $twitter->username = $_REQUEST['twuser'];
  $twitter->password = $_REQUEST['twpass'];
  if (!$twitter->verifyCredentials()) {
    $errors['twitter'][] = "The Twitter username and password you entered is incorrect. Do you need a reminder of your password?";
  }
  // make sure tag has a value
  // make sure title and subtitle exists
  // make sure cache is writable
  if (count($errors) == 0) { return NULL; }
  return $errors;
}

if ($_REQUEST['submit']) {
  $conf = fopen("config.php",'w');
  $errors = validate_request();
  if ($errors) {
    fwrite($conf,"<?php\n");
    fwrite($conf,"define('HASHTAG', '".$_REQUEST['tag']."');\n");
    fwrite($conf,"define('SITE_TITLE','".$_REQUEST['site']."');\n");
    fwrite($conf,"define('SITE_SUBTITLE','".$_REQUEST['tagline']."');\n");
    fwrite($conf,"define('DBHOST','".$_REQUEST['dbhost']."');\n");
    fwrite($conf,"define('DBNAME','".$_REQUEST['dbname']."');\n");
    fwrite($conf,"define('DBUSER','".$_REQUEST['dbuser']."');\n");
    fwrite($conf,"define('DBPASS','".$_REQUEST['dbpass']."');\n");
    fwrite($conf,"define('TWITTER_USER','".$_REQUEST['twuser']."');\n");
    fwrite($conf,"define('TWITTER_PASS','".$_REQUEST['twpass']."');\n");
    fwrite($conf,"define('CACHE_ENABLED', 1);\n");
    fwrite($conf,"define('CACHE_TIME',60 * 5);\n");
    fwrite($conf,"define('PAGE_LIMIT',20);\n");
    fwrite($conf,"define('DEBUG',0);\n");
    fwrite($conf,"define('QUERY_LIMIT',139);\n");
    fwrite($conf,"define('LOG_FILE','twitster.log');\n");
    fwrite($conf,"?>\n");
    fclose($conf);
    header("Location: index.php");
    exit;
  }
}
if (!is_writable("./") || !is_writable("./cache")) {
  include("header.php");
?>
    <h2>I sense a disturbance in the Force.</h2>
    <p>It appears that your current directory is not writable. To run the twitster wizard please do one of the following:</p>
    <ul>
<?php if (!is_writable("./")) { echo "<li>Run the command <code>chmod g+w ".dirname(__FILE__);."</code></li>\n"; ?>
<?php if (!is_writable("./cache")) { echo "<li>Run the command <code>chmod g+w ".dirname(__FILE__);."/cache</code></li>\n"; ?>
    </ul>
<?php
  include("footer.php");
  exit;
}
$scripts = array();
array_push($scripts,'js/jquery.js');
array_push($scripts,'js/jquery.validate.js');
array_push($scripts,'js/setup.js');
include("header.php");
if (isset($errors)) {
  foreach ($errors as $errclass) {
    foreach ($errclass as $error) {
      echo "<h2>".$error."</h2>\n";
    }
  }
}
?>
			<h2>Let's get you set up:</h2>
			<form action="#" method="get" accept-charset="utf-8" id="setupform">
				<fieldset>
					<legend>Site Preferences</legend>
					<label for="tag">Tag To Follow:</label>
					<input type="text" name="tag" value="" id="tag"/>
					<label for="site">Site Name:</label>
					<input type="text" name="site" value="" id="site"/>
					<label for="tagline">Tagline:</label>
					<input type="text" name="tagline" value="" id="tagline"/>
				</fieldset>
				<fieldset>
					<legend>Twitter Info</legend>
					<label for="twuser">Twitter Username:</label>
					<input type="text" name="twuser" value="" id="twuser"/>
					<label for="twpass">Twitter Password:</label>
					<input type="password" name="twpass" value="" id="twpass"/>
				</fieldset>
				<fieldset>
					<legend>Database Info</legend>
					<label for="dbhost">Database Host:</label>
					<input type="text" name="dbhost" value="localhost" id="dbhost"/>
					<label for="dbuser">Database Username:</label>
					<input type="text" name="dbuser" value="" id="dbuser"/>
					<label for="dbpass">Database Password:</label>
					<input type="password" name="dbpass" value="" id="dbpass"/>
					<label for="dbname">Database Name:</label>
					<input type="text" name="dbname" value="" id="dbname"/>
				</fieldset>
				<div class="form-button"><input type="image" src="i/button-wizard-setup.png" alt="Set up twitster" name="submit" value="submit" /></div>
			</form>
<?php
include("footer.php");
?>
