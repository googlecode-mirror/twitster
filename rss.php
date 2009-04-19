<?php
if (!file_exists('config.php')) {
   header("Location: setup.php");
   exit;
}

require_once("include/class.twitster.php");
$twitster = new twitster();
$twitster->init();

if (!isset($_REQUEST['tag']) && HASHTAG) { $tag = HASHTAG; }
$options = array();
$options['tag']    = $tag;
$options['offset'] = 0;
$options['limit'] = 15;

function this_url() {
  $domain = $_SERVER['HTTP_HOST'];
  $path = $_SERVER['SCRIPT_NAME'];
  $url = 'http' . ($_SERVER['HTTPS'] ? 's' : '') . '://' . $domain . $path;
  return ereg_replace('rss.php','',$url);
}

do_update_if_needed($twitster);
$tweets = Tweet::find($options);

echo '<?xml version="1.0" encoding="UTF-8"?>';
?>
<rss version="2.0">
  <channel>
    <title><?php echo SITE_TITLE; ?></title>
    <link><?php echo this_url(); ?></link>
    <description><?php echo SITE_SUBTITLE; ?></description>
    <language>en-us</language>
    <ttl>40</ttl>
		<?php
		$i=0;
		foreach ($tweets as $tweet) {
	    	$tweet->tagify();
		    $cleantext = $tweet->message; //$tweet->clean();
		  echo "<item>\n";
		  echo "<title>".utf8_encode($tweet->name)."</title>\n";
		  echo "<description><![CDATA[". linkify($cleantext) . ' <a href="' . $tweet->permalink() . '" class="permalink">#</a>]]></description>';
		  echo "<pubDate>". $tweet->published . "</pubDate>\n";
		  echo "<guide>".$tweet->permalink()."</guide>\n";
		  echo "<link>".$tweet->permalink()."</link>\n";
		  echo "</item>\n";
                  $i++;
		}
		?>
  </channel>
</rss>
<?php $twitster->close(); ?>
