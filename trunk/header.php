<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
   	<meta http-equiv="Content-type" content="text/html; charset=utf-8" />
   	<title>:: the twitster wizard ::</title>
   	<meta name="description" content="Doing his magic." />
   	<link rel="stylesheet" href="css/twitster-mellow.css" type="text/css" media="screen, projection" />
   	<link rel="icon" href="favicon.ico" type="image/x-icon" />
   	<link rel="shortcut icon" href="favicon.ico" type="image/x-icon" />
	<link rel="alternate" type="application/rss+xml" title="RSS" href="rss.php" />
<?php if (isset($scripts)) { foreach ($scripts as $script) { ?>
        <script src="<?php echo $script; ?>"></script>
<?php } } ?>
</head>
<body id="wizard">
	<div id="container"><div id="container-in">
		<div id="banner">
			<h1><img src="i/logo.jpg" alt="twitster"/></h1>
		</div>
		<div id="content">
