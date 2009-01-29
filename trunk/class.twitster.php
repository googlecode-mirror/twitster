<?php
require_once("config.php");
require_once("util.php");
require_once("class.twitter.php"); // this will include a.php

class Tweet {
  function __construct() {
  }
  public static function fromSimpleXML($xml) {
    $t = new Tweet();
    foreach ($xml->link as $link) {
      if ($link['rel'] == "image") { $t->userpic = $link['href']; }
    }
    ereg("([^ ]*) \(([^\)]*)\)",$xml->author->name,$regs);
    $t->author_url = $xml->author->uri;
    $t->screen_name = $regs[1];
    $t->name = $regs[2];
    $t->message = $xml->title;
    $t->published = $xml->published;
    ereg(":([0-9]+)$",$xml->id,$regs2);
    $t->id = $regs2[1];
    return $t;
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
  public static function count() {
    $sql = sprintf("SELECT count(tweet_id) FROM tweets");
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
    $sql = sprintf("SELECT tweet_id,message,author_screen_name,author_name,author_url,author_userpic,publish_date FROM tweets ORDER BY publish_date DESC LIMIT %d,%d",$options['offset'],$options['limit']);
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

  function refresh( $since = NULL ) {
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
      error_log("Sending query $q\n",3,LOG_FILE);
      $tmp = $this->twitter->search($q);
      debug("query (".strlen($q)."): $q");
      foreach ($tmp as $t) { 
	$tweet = Tweet::fromSimpleXML($t);
	$tweets[] = $tweet; 
	$tweet->insert();
      }
    }
    debug("# of tweets: " . count($tweets));
    return $tweets;
  }

}
?>
