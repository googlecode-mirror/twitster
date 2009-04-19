<?php
if (!file_exists('config.php')) {
   header("Location: setup.php");
   exit;
}

require_once("include/class.twitster.php");
require_once("include/class.paginator.php");
$twitster = new twitster();
$twitster->init();

$offset = isset($_REQUEST['offset']) ? $_REQUEST['offset'] : 0;
if (!isset($_REQUEST['tag']) && HASHTAG) { $tag = HASHTAG; }
$feed_link='rss.php?tag='.urlencode($tag);
$options = array();
$options['tag']    = $tag;
$options['offset'] = $offset;
$options['limit']  = PAGE_LIMIT;

if ($offset == 0) { do_update_if_needed($twitster); }
$tweets = Tweet::find($options);

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
   	<meta http-equiv="Content-type" content="text/html; charset=utf-8" />
   	<title><?php echo SITE_TITLE ?>: <?php echo SITE_SUBTITLE ?></title>
   	<meta name="description" content="<?php echo SITE_SUBTITLE ?>" />
   	<link rel="stylesheet" href="themes/mellow/css/twitster-mellow.css" type="text/css" media="screen, projection" />
   	<link rel="icon" href="themes/mellow/i/favicon.ico" type="image/x-icon" />
   	<link rel="shortcut icon" href="themes/mellow/i/favicon.ico" type="image/x-icon" />
	<link rel="alternate" type="application/rss+xml" title="RSS" href="<?php echo $feed_link; ?>" />
</head>

<body>
	<div id="container">
		<div id="container-in">
			<div id="banner">
				<h1><a href="index.php"><?php echo SITE_TITLE ?></a></h1>
				<h2><?php echo SITE_SUBTITLE ?></h2>
			</div>
			<div id="content">
				<div class="feedicon"><a href="<?php echo $feed_link; ?>" title="Subscribe to <?php echo SITE_TITLE ?> via RSS"><img src="themes/mellow/i/rss.png" alt="Feed Icon"></a></div>
				<ul id="updatelist">
					<?php
					  $i=0;
					  foreach ($tweets as $tweet) {
					    $tweet->tagify();
					    $cleantext = $tweet->message; //$tweet->clean();
					    echo "<li id=\"tweet-".$tweet->id."\" class=\"tweet" . (($i % 2 == 0) ? " altrow" : "") . "\">\n";
					    echo "<div class=\"userpic\"><a href=\"http://twitter.com/". $tweet->screen_name ."\"><img src=\"" . $tweet->userpic . "\" alt=\"\" /></a></div>\n";
					    echo "<div class=\"tweet-meta\"><a href=\"http://twitter.com/". $tweet->screen_name ."\">" . $tweet->name . "</a> ". relativeTime(strtotime($tweet->published)) ."</div>";
					    echo "<div class=\"tweet-body\">" . linkify($cleantext) . ' <a href="' . $tweet->permalink() . '" class="permalink">&infin;</a></div>';
					    echo "</li>\n";
					    $i++;
					  }
					?>
				</ul>
				<?php 
					if ($tweets) {
						echo Paginator::paginate($offset,Tweet::count($tag),PAGE_LIMIT,"index.php?".($tag ? 'tag='.urlencode($tag).'&' : '')."offset="); 
					} else {
						echo "<div class=\"tweetless\">\n";
						echo "No tweets.\n";
						echo "</div>\n";
					}
				?>
	    	</div>
		</div>
		<div id="footer">
			<p>Powered by <a href="http://plasticmind.com/twitster/?referral=1">Twitster</a></p>
		</div>
	</div>

</body>
</html>
<?php $twitster->close(); ?>
