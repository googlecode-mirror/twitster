# --- TWITSTER: TWITTER GROUPS FOR YOUR WEBSITE -------------------------------- #
# --- http://plasticmind.com/twitster/ ----------------------------------------- #

## WHAT IS TWITSTER?

Put simply, Twitster is a PHP application that lets you display tweets from everyone you follow in Twitter.  What's more, you can tell Twitster to filter those tweets by a hash tag (#example).  That means you can basically create your own Twitter community, on your own site.

Twitster is an open source application put together by Jesse Gardner (http://plasticmind.com) and Byrne Reese (http://majordojo.com).

## INSTALLATION

1. Upload the Twitster files into the directory you want to use.  (e.g. http://mysite.com/twitster/)

2. Bring up that location in your browser.  Twitster will bring up a setup wizard the first time you access it.  

NOTE: You may need to make your make your Twitster directory writable (777) so the wizard can create your configuration file.  For security's sake, once you run the wizard, you should delete setup.php and set the main directory permissions back to something safer, like 755.

3. Fill in the setup form.  Leave the hash tag field empty if you want to display all your friend's tweets.  Click "Set Up Twitster".

4. Share!


## FAQS

Q. Does Twitster show me tweet from all of my followers?
A. No. Twitster shows the tweets from everyone YOU are following.  This lets you control whose tweets appear on your site.

Q. Do I have to follow a hash tag?  Can't I just show all of my followee's tweets?
A. Absolutely!  Just leave the hash tag field blank in the setup wizard.

Q. Help! I've already run the wizard, but I want to change the configuration.
A. No sweat.  Just open the config.php file located in your Twitster directory and make the changes.  It's well documented and should be easy to figure out how to change what you need.

Q. Can I filter by multiple hash tags?
A. Not yet.  But we're working on that functionality for the next release.

Q. Can I put Twitster on an existing page, like a sidebar module on my blog?
A. Yes and no.  If you're code saavy, you can probably figure out how to cobble together the code from the index file to create a php include, but there's no simple way just yet.  We're planning to create a customizable module for the next release that you can easily pull in from elsewhere on your site.

Q. Why aren't my @replies linking?
A. Username linking should be included in the next release.


## HELP!

Still can't get it?  Check out our support page for handy links:

http://plasticmind.com/twitster/support/


## LICENSE

Twitster — Copyright 2009 Jesse Gardner

This program is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with this program.  If not, see <http://www.gnu.org/licenses/>