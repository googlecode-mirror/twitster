<h1>Tweet::find_tags Tests</h1>
<ol>
<?php
require("../class.twitster.php");
$msgs = array(
	      "#foo #bar #baz",
	      "#foo#bar#baz",
	      "#foo,#bar!!!#baz",
	      "This is some text with #tags interspersed #foo"
	      );
foreach ($msgs as $msg) {
  $tweet = new Tweet();
  $tweet->message = $msg;
  echo "<li><strong>tags for '" . $tweet->message . "':</strong><br />" . implode($tweet->find_tags(),", ") . "</li>";
}
?>
</ol>
