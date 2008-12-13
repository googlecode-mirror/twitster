<?php

define('HASHTAG', '#foo'); // The hashtag to filter by:
define('SITE_NAME', 'The Foo Community'); // The site title
define('SITE_DESCRIPTION', 'A Twitter-powered community about Foo'); // The site tagline/description

// Turn on/off caching of the first page.
define("CACHE_ENABLED", 1);
// The filename in which to store the cached HTML version.
define("CACHE_INDEX",'cache/cached_page.html');
// The filename in which to store the cached RSS feed.
define("CACHE_FEED",'cache/cached_feed.rss');
// The number of seconds to cache the homepage and feed.
define("CACHE_TIME",60 * 5); // Clear cache ever 3 Minutes

// Database connection information. These should be obvios to most.
define("TWITSTER_DBHOST","localhost");
define("TWITSTER_DBNAME","hashmt");
define("TWITSTER_DBUSER","root");
define("TWITSTER_DBPASS","");

// The number of Tweets to display per page.
define("PAGE_LIMIT",20);

// Turn on/off debug messages to assist in development.
define("DEBUG",0);

// Used to specify the URL string length limit to the Twitter
// Search API. Deprecated.
define("QUERY_LIMIT",139);
?>