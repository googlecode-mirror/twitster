<?php

require_once("include/class.twitster.php");
require_once("include/Paginator.php");
$hashmt = new hashmt();
$hashmt->init();

$offset = isset($_REQUEST['offset']) ? $_REQUEST['offset'] : 0;
$options = array();
$options['offset'] = $offset;
$options['limit'] = PAGE_LIMIT;

if (CACHE_ENABLED && $offset == 0 && !is_cache_stale(CACHE_INDEX)) {
    // Serve from the cache if it is younger than $cachetime
    readfile(CACHE_INDEX);
    exit; // Quit and don't poll Twitter
} else {
    $tweets = Tweet::find();
    $since = $tweets[0]->id;
    $hashmt->refresh($since);
}

$tweets = Tweet::find($options);

ob_start(); // Start the output buffer for the cache
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
   	<meta http-equiv="Content-type" content="text/html; charset=utf-8" />
   	<title><?php echo HASHTAG ?>: The Pulse of the Movable Type Community</title>
	<script src="http://plasticmind.com/mint/?js" type="text/javascript"></script>

   	<link rel="stylesheet" href="styles.css" type="text/css" media="screen, projection" />
   	<link rel="stylesheet" href="paginator.css" type="text/css" media="screen, projection" />

   	<link rel="icon" href="http://plasticmind.com/favicon.ico" type="image/x-icon" />
   	<link rel="shortcut icon" href="http://plasticmind.com/favicon.ico" type="image/x-icon" />
	<link rel="alternate" type="application/rss+xml" title="RSS" href="http://feeds.feedburner.com/hashmt/" />
	
   	<meta name="description" content="Real-time updates of the Movable Type community, powered by Twitter." />
</head>
<body>
	<div id="container">
		<div id="banner">
			<h1><a href="http://plasticmind.com/hashmt/">#mt</a></h1>
			<p><a href="http://twitter.com/hashmt/">Feel like you should be on this list?  Follow @hashmt on Twitter, and if you've got something beneficial to say about Movable Type, we'll follow you.  Then you can post a tweet here by ending it with #mt.</a></p>
		</div>
		<div id="content">
			<div class="feedicon"><a href="http://feeds.feedburner.com/hashmt" title="Subscribe to #mt via RSS"><img src="i/rss.png" alt="Feed Icon"></a></div>
			<h2>Right now in the Movable Type community:</h2>
			<ul id="updatelist">
				<?php
				$i=0;

				foreach ($tweets as $tweet) {
				  $cleantext = $tweet->clean();
				  echo "<li id=\"tweet-".$tweet->id."\" " . (($i % 2 == 0) ? " class='altrow'" : "") . ">\n";
				  echo "<div class=\"userpic\"><a href=\"http://twitter.com/". $tweet->screen_name ."\"><img src=\"" . $tweet->userpic . "\" alt=\"\" /></a></div>\n";
				  echo "<div class=\"meta\"><a href=\"http://twitter.com/". $tweet->screen_name ."\">" . $tweet->name . "</a> ". relativeTime(strtotime($tweet->published)) ."</div>";
				  echo "<div class=\"tweet\">" . linkify($cleantext) . ' <a href="' . $tweet->permalink() . '" class="permalink">#</a></div>';
				  echo "</li>\n";
				  $i++;
				}
				?>
			</ul>
                        <?php 
			echo Paginator::paginate($offset,Tweet::count(),PAGE_LIMIT,"index.php?offset="); 
			?>
		</div>
		<div id="footer">
			<p>Copyright &copy; 2008 &bull; #mt is hand-coded and designed by <a href="http://plasticmind.com/">Plasticmind</a> &bull; #mt is not affiliated with <a href="http://twitter.com/">Twitter</a></p>
		</div>
	</div>
</body>
</html>

<?
// Cache the output to a file
$fp = fopen(CACHE_INDEX, 'w');
fwrite($fp, ob_get_contents());
fclose($fp);
ob_end_flush(); // Send the output to the browser
?>
