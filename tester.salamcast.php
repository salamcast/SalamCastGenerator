<?php
require_once 'salamcast.class.php';
if ($_SERVER ['QUERY_STRING'] != '') {
	$s = new SalamCastGen ( 'tester.salamcast.xsl' );
} else {
	$s = new SalamCastGen ();
}
$s->enable_itunes ();
$s->setTitle ( 'Tester Feed' );
$s->setChannelElement ( 'itunes:subtitle', 'Shows how xml name space prefixes are used' );
$s->setDescription ( 'SalamCast is a RSS 2.0 feed generator with a bit of ATOM and iTunes xmlns' );
$s->setImage ( 'GitHub image', 'https://github.com/salamcast', 'https://secure.gravatar.com/avatar/7e12d7a134e97fe34046a40d56730131?s=420&d=https://a248.e.akamai.net/assets.github.com%2Fimages%2Fgravatars%2Fgravatar-user-420.png' );
$s->add_lang ( 'en-us' );
$s->add_copyright ( 'copyright of Karl Holz' );
$s->setAuthor ( 'Karl Holz', 'tester@example.com' );
$s->keywords ( "RSS, ATOM, iTunes, PHP" );
$s->category ( 'Podcasting') ;
$s->explicit();
$s->textInput();
$s->category ( array('Society & Culture', 'Video Games')) ;
$s->addAtomLink ( "GitHub Site", 'https://github.com/salamcast'  );
$s->addAtomLink ( "Linkedin", 'http://www.linkedin.com/pub/karl-holz/50/306/3b8'  );

//
// add feed item
$item = $s->createNewItem ();
$item->setTitle ( 'RSS media source in XBMC' );
$item->setLink ( 'http://wiki.xbmc.org/index.php?title=RSS_media_source' );
$item->setDate ();
$item->setDescription ( 'Use RSS Podcasts in XBMC' );
$item->setEncloser ( 'http://wiki.xbmc.org/index.php?title=RSS_media_source', '4096', 'text/html' );
$item->addAtomLink ( "XBMC RSS media source", 'http://wiki.xbmc.org/index.php?title=RSS_media_source' );
$item->setAuthor ( 'tester@example.com' );
$item->keywords ( "XBMC, Podcast, Media" );
$s->addItem ( $item );
unset ( $item );

// add feed item
$item = $s->createNewItem ();
$item->setTitle ( 'GitHub' );
$item->setLink ( 'https://github.com/salamcast' );
$item->setDate ('Mon, 02 Jul 2009 11:36:45 +0000');
$item->setDescription ( 'My GitHub Profile will my published works' );
$item->addAtomLink ( "GitHub Site", 'https://github.com/salamcast' );
$item->setAuthor ( 'tester@example.com' );
$item->keywords ( "Javascript, PHP5, jQuery, Perl, CSS, HTML" );
$s->addItem ( $item );
unset ( $item );

// add feed item
$item = $s->createNewItem ();
$item->setTitle ( 'Linkedin' );
$item->setLink ( 'http://www.linkedin.com/pub/karl-holz/50/306/3b8' );
$item->setDate ();
$item->setDescription ( 'My Linkedin Profile, work stuff' );
$item->addAtomLink ( "Linkedin", 'http://www.linkedin.com/pub/karl-holz/50/306/3b8' );
$item->setAuthor ( 'tester@example.com' );
$item->keywords ( "Work, gigs, projects" );
$s->addItem ( $item );
unset ( $item );

// add feed item
$item = $s->createNewItem ();
$item->setTitle ( 'PHP Innovation Award Winner of 2012 - Lately in PHP podcast episode 33' );
$item->setLink ( 'http://www.phpclasses.org/blog/post/202-PHP-Innovation-Award-Winner-of-2012--Lately-in-PHP-podcast-episode-33.html' );
$item->setDate ('Thu, 07 Mar 2013 17:56:24 GMT');
$item->setDescription ( '<div style="clear: both"> <div style="margin-top: 1ex"><a href="http://www.phpclasses.org/blog/post/202-PHP-Innovation-Award-Winner-of-2012--Lately-in-PHP-podcast-episode-33.html">PHP Innovation Award Winner of 2012 - Lately in PHP podcast episode 33</a></div> <div style="margin-top: 1ex">By Manuel Lemos</a></div> <div style="margin-top: 1ex">The PHP Programming Innovation Award Winner of 2012 was announced. An interview with the winner, Karl Holz from Canada, was one of the main topics of the episode 33 of the Lately in PHP podcast conducted by Manuel Lemos and Ernani Joppert.<br /> <br /> They also discussed the usual batch of PHP topics of interest like Zend Optimizer+ source code that was released, the PHP 5.5 feature freeze and roadmap, as well an article that compares PHP to an Hobbit, as well other languages to Lord Of The Rings story characters.<br /> <br /> Listen to the podcast, or watch the podcast video, or read the transcript to learn about these and other interesting PHP topics.</a></div> </div> ' );
$item->setEncloser('http://www.phpclasses.org/blog/post/202/file/165/name/Lately-In-PHP-33.mp3', '32626046', 'mp3');
$item->source('PHP Classes', 'http://www.phpclasses.org/blog/category/podcast/post/latest.rss');
$item->setAuthor ('Manuel Lemos', 'tester@example.com' );
$item->keywords ( "PHP5, HTML, phpclasses.org" );
$s->addItem ( $item );
unset ( $item );

//echo '<pre>'.print_r($s,true).'</pre>'; exit();

$s->genarateFeed ();