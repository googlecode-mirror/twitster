<?php
foreach ($_REQUEST as $k => $v) { 
  error_log( $k . ' => ' . $v ); 
}
function connect($host,$user,$pass) {
  return @mysql_connect($host, $user, $pass);
}
if ($_REQUEST['method'] == "twitter") {
  require("class.twitter.php");
  $twitter = new twitter();
  $twitter->username = $_REQUEST['username'];
  $twitter->password = $_REQUEST['password'];
  error_log("verifying twitter username and password");
  $result = $twitter->verifyCredentials();
  if ($result->{'error'}) {
    echo "false";
  } else {
    echo "true";
  }
} else if ($_REQUEST['method'] == "dbauth") {
  $link = connect($_REQUEST['dbhost'],$_REQUEST['dbuser'],$_REQUEST['dbpass']);
  if (!$link) {
    error_log('fail auth!!');
    echo "false";
  }
  echo "true";
} else if ($_REQUEST['method'] == "dbname") {
  connect($_REQUEST['dbhost'],$_REQUEST['dbuser'],$_REQUEST['dbpass']);
  if (!mysql_select_db($_REQUEST['dbname'])) {
    error_log('fail db!!');
    echo "false";
  }
  echo "true";
}
?>