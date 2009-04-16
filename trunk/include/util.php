<?php

function time_for_refresh() {
  if (!file_exists(LAST_UPDATE_FILE)) { return 1; }
  $now = time();
  $since = filemtime(LAST_UPDATE_FILE);
  if ($now - $since > CACHE_TIME) {
    debug("It is time for a refresh."); 
    return 1;
  }
  debug("It is NOT time for a refresh."); 
  return 0;
}

function do_update_if_needed($twitster) {
  if (time_for_refresh()) {
    if (file_exists(UPDATE_PID_FILE)) {
      twitlog("Time for an update, but one is already in progress. Skipping.");
    } else {
      twitlog("It is time to refresh the cache, beginning now.");
      $tweets = Tweet::find();
      $since = (is_array($tweets) && $tweets[0] ? $tweets[0]->id : false);
      $twitster->refresh($since);
    }
  }
}


function debug($str) {
  if (DEBUG) {
    //echo '<p style="font-family: monospace;">'.$str.'</p>';
    twitlog("DEBUG: $str");
  }
}

function twitlog($str) {
    error_log("$str\n",3,LOG_FILE);
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
	$text = ereg_replace("[[:alpha:]]+://[^<>[:space:]]+[[:alnum:]/]","<a href=\"\\0\">\\0</a>", $text); // Linkify URLs
	$text = preg_replace('%(?<!\S)@([A-Za-z0-9_]+)%', '<a href="http://twitter.com/$1">@$1</a>', $text); // Linkify @replies
	return $text;
}
?>
