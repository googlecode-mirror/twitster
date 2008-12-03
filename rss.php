<?php

require_once("include/class.twitster.php");
require_once("include/Paginator.php");
$hashmt = new hashmt();
$hashmt->init();

$offset = 0;
$options = array();
$options['offset'] = $offset;
$options['limit'] = PAGE_LIMIT;

if (CACHE_ENABLED && $offset == 0 && !is_cache_stale(CACHE_FEED)) {
    // Serve from the cache if it is younger than $cachetime
    readfile(CACHE_FEED);
    exit; // Quit and don't poll Twitter
} else {
    $tweets = Tweet::find();
    $since = $tweets[0]->id;
    $hashmt->refresh($since);
}

$tweets = Tweet::find($options);
ob_start(); // Start the output buffer for the cache
echo '<?xml version="1.0" encoding="UTF-8"?>';
?>
<rss version="2.0">
  <channel>
    <title>#mt: The Pulse of the Movable Type Community</title>
    <link>http://plasticmind.com/hashmt</link>
    <description>A real-time journal for the Movable Type community.</description>
    <language>en-us</language>
    <ttl>40</ttl>
		<?php
		$i=0;
		foreach ($tweets as $tweet) {
		  $cleantext = $tweet->clean();
		  echo "<item>\n";
		  echo "<title>".utf8_encode($tweet->name)."</title>\n";
		  echo "<description><![CDATA[". linkify($cleantext) . ' <a href="' . $tweet->permalink() . '" class="permalink">#</a>]]></description>';
		  echo "<pubDate>". $tweet->pubished . "</pubDate>\n";
		  echo "<guide>http://twitter.com/" . $tweet->screen_name . '/status/' . $tweet->id . "</guide>\n";
		  echo "<link>".$tweet->permalink()."</link>\n";
		  echo "</item>\n";
                  $i++;
		}
		?>
  </channel>
</rss>


<?
// Cache the output to a file
$fp = fopen(CACHE_FEED, 'w');
fwrite($fp, ob_get_contents());
fclose($fp);
ob_end_flush(); // Send the output to the browser
?>