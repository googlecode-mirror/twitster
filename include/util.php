<?php

function is_cache_stale( $file ) {
  if (!file_exists($file)) { return 1; }
  return (time() - CACHE_TIME < filemtime($file));
}

function debug($str) {
  if (DEBUG) {
    echo '<p style="font-family: monospace;">'.$str.'</p>';
  }
}

// $time should be a Unix timestamp - get it with strtotime()
function relativeTime($time) {
	$gap = time() - $time;
	if ($gap < 5) {
	return 'less than 5 seconds ago';
	} else if ($gap < 10) {
	return 'less than 10 seconds ago';
	} else if ($gap < 20) {
	return 'less than 20 seconds ago';
	} else if ($gap < 40) {
	return 'half a minute ago';
	} else if ($gap < 60) {
	return 'less than a minute ago';
	}
	$gap = round($gap / 60);
	if ($gap < 60)  { 
	return $gap.' minute'.($gap > 1 ? 's' : '').' ago';
	}
	$gap = round($gap / 60);
	if ($gap < 24)  { 
	return 'about '.$gap.' hour'.($gap > 1 ? 's' : '').' ago';
	}
	return date('h:i A F d, Y', $time);
}

function linkify($text) {
	$text = ereg_replace("[[:alpha:]]+://[^<>[:space:]]+[[:alnum:]/]","<a href=\"\\0\">\\0</a>", $text);
	return $text;
}
?>