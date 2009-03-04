<?php
require_once("config.php");
require_once("util.php");
require_once("class.twitter.php"); // this will include a.php

define("UPDATE_PID_FILE","cache/update.pid");
define("LAST_UPDATE_FILE","cache/last_update");

class Tweet {
  function __construct() {
  }
  public static function fromSimpleXML($xml) {
    $t = new Tweet();
    $t->userpic = $xml->user->profile_image_url;
    $t->author_url = $xml->user->url;
    $t->screen_name = $xml->user->screen_name;
    $t->name = $xml->user->name;
    $t->message = $xml->text;
    $t->protected = $xml->user->protected;
    $timestamp = strtotime($xml->created_at);
    $t->published = date('Y-m-d H:i:s',$timestamp);
    $t->id = $xml->id;
    return $t;
  }
  function find_tags() {
    $tags = array();
    if (preg_match_all("/(\#[^\s\#]+)/",$this->message,$regs)) {
      for ($i = 1; $i < count($regs); $i++) {
	$m = $regs[$i];
	for ($j = 0; $j < count($m); $j++) {
	  $r = $m[$j];
	  $tags[] = $r;
	}
      }
    }
    return $tags;
  }
  function tagify() {
    $tags = $this->find_tags();
    foreach ($tags as $t) {
      $te = urlencode($t);
      $this->message = ereg_replace($t,"<a href=\"index.php?tag=".urlencode($te)."\">$t</a>",$this->message);
    }
  }
  function clean() {
    return (str_replace(HASHTAG, '', $this->message));
  }
  function permalink() {
    return "http://twitter.com/" . $this->screen_name . '/status/' . $this->id;
  }
  function insert() {
    $sql = sprintf("INSERT INTO tweets (tweet_id,message,author_screen_name,author_name,author_url,author_userpic,publish_date,created_date) VALUES ('%s','%s','%s','%s','%s','%s','%s',NOW())",
		   mysql_escape_string($this->id),
		   mysql_escape_string($this->message),
		   mysql_escape_string($this->screen_name),
		   mysql_escape_string($this->name),
		   mysql_escape_string($this->author_url),
		   mysql_escape_string($this->userpic),
		   mysql_escape_string($this->published)
		   );
    mysql_query($sql);
  }
  function insert_tag($tag) {
    $sql = sprintf("INSERT INTO tags (tweet_id,tag) VALUES ('%s','%s')",
		   mysql_escape_string($this->id),
		   mysql_escape_string($tag)
		   );
    mysql_query($sql);
  }
  public static function count($tag = NULL) {
    if ($tag) {
      $sql = sprintf("SELECT count(tweet_id) FROM tags WHERE tag = '%s'",$tag);
    } else {
      $sql = sprintf("SELECT count(tweet_id) FROM tweets");
    }
    $query = mysql_query($sql) or die(mysql_error());
    if ($query && (mysql_num_rows($query) > 0)) {
      $row = mysql_fetch_row($query);
      return $row[0];
    }
  }
  public static function find($options = NULL) {
    if (!isset($options)) { $options = array(); }
    if (!isset($options['limit'])) { $options['limit'] = 20; }
    if (!isset($options['offset'])) { $options['offset'] = 0; }
    if ($options['tag']) {
      $sql = sprintf("SELECT tweets.tweet_id,message,author_screen_name,author_name,author_url,author_userpic,publish_date FROM tweets,tags WHERE tweets.tweet_id = tags.tweet_id AND tags.tag = '%s' ORDER BY publish_date DESC LIMIT %d,%d",$options['tag'],$options['offset'],$options['limit']);
    } else {
      $sql = sprintf("SELECT tweet_id,message,author_screen_name,author_name,author_url,author_userpic,publish_date FROM tweets ORDER BY publish_date DESC LIMIT %d,%d",$options['offset'],$options['limit']);
    }
    $query = mysql_query($sql) or die(mysql_error());
    $tweets = array();
    if ($query && (mysql_num_rows($query) > 0)) {
      while ($row = mysql_fetch_row($query)) {
	$t = new Tweet();
	$t->id = $row[0];
	$t->message = $row[1];
	$t->screen_name = $row[2];
	$t->name = $row[3];
	$t->author_url = $row[4];
	$t->userpic = $row[5];
	$t->published = $row[6];
	$tweets[] = $t;
      }
    }
    return $tweets;
  }

}

class twitster {
  function __construct() {
    $this->twitter = new twitter();
    $this->twitter->username = TWITTER_USER;
    $this->twitter->password = TWITTER_PASS;
  }
  function init() {
    $link = @mysql_connect(DBHOST, DBUSER, DBPASS);
    if (!$link) {
      // couldn't connect
      // TODO - display an error
      die("Could not connect to database.");
    }
    if (!mysql_select_db(DBNAME)) {
      // couldn't connect
      // TODO - display an error
      die("Could not select database.");
    }
  }

  function refresh( $since = false ) {
    return $this->refresh_via_rest_api($since);
  }

  function refresh_via_rest_api( $since = false ) {
    twitlog("Refreshing via REST API (since = " . 
	    ($since == false ? 'false' : $since).").",3,LOG_FILE);
    $pid = fopen(UPDATE_PID_FILE,'w');
    fwrite($pid,getmypid());
    fclose($pid);
    $this->twitter->type = 'xml';
    $page = 0;
    $limit = 200;
    $tweets = array();

    // ok - "continue while this is your first time through the loop,
    //       but stop if we are initiailizing twitster, or if we need
    //       to fetch back to a previous tweet and there are more to 
    //       fetch."
    while ($page == 0 || ($since != false && $added > 0)) {
      $added = 0;
      $page++;
      $timeline = $this->twitter->friendsTimeline( false, $since, $limit, $page );
      if ($timeline->error) {
	twitlog('There was an error: ' . $timeline->error, 3, LOG_FILE);
      }
      twitlog("Fetching page #".$page,3,LOG_FILE);
      foreach ($timeline as $t) { 
	$tweet = Tweet::fromSimpleXML($t);
	if (!$tweet->protected || SYNDICATE_PROTECTED) {
	  twitlog("Adding tweet #".$tweet->id,3,LOG_FILE);
	  $tweets[] = $tweet; 
	  $tweet->insert();
	  $tags = $tweet->find_tags();
	  foreach ($tags as $tag) {
	    twitlog("  Tagging tweet: $tag",3,LOG_FILE);
	    $tweet->insert_tag($tag);
	  }
	  $added++;
	}
      }
    }
    debug("Added " . count($tweets) . " tweets this go-round.");
    touch(LAST_UPDATE_FILE);
    unlink(UPDATE_PID_FILE);
    return $tweets;
  }

  function refresh_via_search_api( $since = false ) {
    $pid = fopen(UPDATE_PID_FILE,'w');
    fwrite($pid,getmypid());
    fclose($pid);
    $this->twitter->type = 'xml';
    $friends = $this->twitter->friends();
    $base_qs = "rpp=100&";
    if (isset($since)) { $base_qs .= "since_id=" . $since . "&"; }
    $base_qs .= "q=";
    $i = 0;
    $queries = array();

    // This attempts to batch calls to the Twitter API. 
    foreach ($friends as $friend) {
      $next = urlencode(HASHTAG . " from:" . $friend->screen_name);

      $next_length = strlen($queries[$j]) + strlen('+OR+' . $next);
      if (strlen($queries[$j]) == 0) {
        // simply initialize the query, this is for the first time through
	$queries[$j] = $base_qs . $next;
      } else if ($next_length > QUERY_LIMIT) { 
	// initialize a new query and advance counters, if query limit exceeded
	$i = 0; // reset friend counter
	$j++;   // advance query index
	$queries[$j] = $base_qs . $next;
      } else {
	$queries[$j] .= '+OR+' . $next;
      }
    }

    $this->twitter->type = 'atom';
    $tweets = array();
    foreach ($queries as $q) {
      twitlog("Sending query $q",3,LOG_FILE);
      $tmp = $this->twitter->search($q);
      debug("query (".strlen($q)."): $q");
      foreach ($tmp as $t) { 
	$tweet = Tweet::fromSimpleXML($t);
	$tweets[] = $tweet; 
	$tweet->insert();
      }
    }
    debug("# of tweets: " . count($tweets));
    touch(LAST_UPDATE_FILE);
    unlink(UPDATE_PID_FILE);
    return $tweets;
  }

}
?>
