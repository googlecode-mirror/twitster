DROP TABLE IF EXISTS tweets;
CREATE TABLE tweets (
  id int(10) NOT NULL auto_increment,
  tweet_id varchar(50) NOT NULL UNIQUE default '0',
  message varchar(180) NOT NULL default '',
  author_screen_name varchar(30) NOT NULL default '',
  author_name varchar(30) NOT NULL default '',
  author_url varchar(255) default '',
  author_userpic varchar(255) default '',
  publish_date datetime NOT NULL default '0000-00-00 00:00:00',
  created_date datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY (id)
) TYPE=MyISAM PACK_KEYS=1;
