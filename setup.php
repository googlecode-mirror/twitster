<?php
/**
 *
 */
function validate_request() {
  global $_REQUEST;
  $errors = array();
  $link = @mysql_connect($_REQUEST['dbhost'], $_REQUEST['dbuser'],
			 $_REQUEST['dbpass']);
  if (!$link) {
    $errors['db'][] = "Unable to connect to the database. Please check your credentials and try again.";
  }
  if (!mysql_select_db($_REQUEST['dbname'])) {
    $errors['db'][] = "The database name you specified does not exist.";
  }
  require_once("include/class.twitter.php");
  $twitter = new twitter();
  $twitter->username = $_REQUEST['twuser'];
  $twitter->password = $_REQUEST['twpass'];
  if (!$twitter->verifyCredentials()) {
    $errors['twitter'][] = 'The Twitter username and password you entered is incorrect. Do you need <a href="http://twitter.com/account/resend_password">a reminder of your password</a>?';
  }
  // make sure tag has a value
  // make sure title and subtitle exists
  // make sure cache is writable
  if (count($errors) == 0) { return NULL; }
  return $errors;
}
if (isset($_REQUEST['submit'])) {
  $errors = validate_request();
  if (!isset($errors)) {
    mysql_query("DROP TABLE IF EXISTS tweets");
    mysql_query("DROP TABLE IF EXISTS tags");
    $sql1 = <<<ENDSQL
CREATE TABLE tags (
  id int(10) NOT NULL auto_increment,
  tweet_id varchar(50) NOT NULL default '0',
  tag varchar(20) NOT NULL default '',
  PRIMARY KEY (id),
  INDEX tag (tag),
  CONSTRAINT tag_tweet UNIQUE (tweet_id,tag)
) TYPE=MyISAM PACK_KEYS=1;
ENDSQL;
    $sql2 = <<<ENDSQL
CREATE TABLE tweets (
  id int(10) NOT NULL auto_increment,
  tweet_id varchar(50) NOT NULL UNIQUE default '0',
  message varchar(180) NOT NULL default '',
  author_screen_name varchar(30) NOT NULL default '',
  author_name varchar(30) NOT NULL default '',
  author_url varchar(255) default '',
  author_userpic varchar(255) default '',
  publish_date datetime NOT NULL default '0000-00-00 00:00:00',
  created_date datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY (id),
  INDEX tweet_id_idx (tweet_id)
) TYPE=MyISAM PACK_KEYS=1;
ENDSQL;
    if (!mysql_query($sql1) || !mysql_query($sql2)) {
      $errors = array();
      $errors['create_table'][] = "Could not create the database table: " . mysql_error();
    } else {
      $conf = fopen("config.php",'w');
      fwrite($conf,"<?php\n\n");
      fwrite($conf,"// Site information\n");
      fwrite($conf,"define('HASHTAG', '".$_REQUEST['tag']."'); // The hashtag to filter by\n");
      fwrite($conf,"define('SITE_TITLE','".$_REQUEST['site']."'); // The site title\n");
      fwrite($conf,"define('SITE_SUBTITLE','".$_REQUEST['tagline']."'); // The site tagline/description\n\n");
      fwrite($conf,"// Database connection information\n");
      fwrite($conf,"define('DBHOST','".$_REQUEST['dbhost']."');\n");
      fwrite($conf,"define('DBNAME','".$_REQUEST['dbname']."');\n");
      fwrite($conf,"define('DBUSER','".$_REQUEST['dbuser']."');\n");
      fwrite($conf,"define('DBPASS','".$_REQUEST['dbpass']."');\n\n");
      fwrite($conf,"// Twitter information\n");
      fwrite($conf,"define('TWITTER_USER','".$_REQUEST['twuser']."');\n");
      fwrite($conf,"define('TWITTER_PASS','".$_REQUEST['twpass']."');\n\n");
      fwrite($conf,"// General Twitster config\n");
      fwrite($conf,"define('SYNDICATE_PROTECTED', 1); // Do not display protected feeds\n");
      fwrite($conf,"define('CACHE_ENABLED', 1); // Cache Twitster results\n");
      fwrite($conf,"define('CACHE_TIME',60 * 5); // Second # is cache time in minutes\n");
      fwrite($conf,"define('PAGE_LIMIT',20); // Tweets per page\n");
      fwrite($conf,"define('DEBUG',0); // Debug mode\n");
      fwrite($conf,"define('TWEET_LIMIT',0); // Max number of tweets in system\n");
      fwrite($conf,"define('QUERY_LIMIT',139); // Twitter API query limit (200 max)\n");
      fwrite($conf,"define('LOG_FILE','twitster.log'); // Logfile for debugging\n");
      fwrite($conf,"?>\n");
      fclose($conf);
      header("Location: index.php");
      exit;
    }
  }
}
if (!is_writable("./") || !is_writable("./cache")) {
  include("header.php");
?>
    <h2>I sense a disturbance in the Force.</h2>
    <p>It appears that your current directory is not writable. To run the twitster wizard please do the following:</p>
    <ul>
<?php if (!is_writable("./")) { echo "<li>Change permissions on the ".dirname(__FILE__)." directory to <code>777</code> (writable).</li>\n"; } ?>
<?php if (!is_writable("./cache")) { echo "<li>Change permissions on the ".dirname(__FILE__)." directory to <code>777</code> (writable).</li>\n"; } ?>
    </ul>
    <p>Once the configuration file is written, you can delete <code>setup.php</code> and set the main directory permissions back to something safer, like <code>755</code>.</p>
<?php
  include("footer.php");
  exit;
}
// TODO - Javascript validation borked, fix for next release.
// $scripts = array();
// array_push($scripts,'js/jquery.js');
// array_push($scripts,'js/jquery.validate.js');
// if ($_REQUEST['validate'] != '0') {
//  array_push($scripts,'js/setup.js');
// }
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
			<form action="setup.php" method="post" accept-charset="utf-8" id="setupform">
				<fieldset>
					<legend>Site Preferences</legend>
					<label for="tag">Tag To Follow:</label>
					<input type="text" name="tag" value="" id="tag"/>
					<span class="hint">e.g. #magicalplace</span>
					<label for="site">Site Name:</label>
					<input type="text" name="site" value="" id="site"/>
					<span class="hint">e.g. Unicorns and Rainbows</span>
					<label for="tagline">Tagline:</label>
					<input type="text" name="tagline" value="" id="tagline"/>
					<span class="hint">e.g. A place where magical things happen!</span>
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
					<label for="dbname">Database Name:</label>
					<input type="text" name="dbname" value="" id="dbname"/>
					<label for="dbuser">Database Username:</label>
					<input type="text" name="dbuser" value="" id="dbuser"/>
					<label for="dbpass">Database Password:</label>
					<input type="password" name="dbpass" value="" id="dbpass"/>
				</fieldset>
				<div class="form-button"><input type="image" name="submit" value="submit" src="themes/mellow/i/button-wizard-setup.png" alt="Set up twitster" /></div>
			</form>
<?php
include("footer.php");
?>
