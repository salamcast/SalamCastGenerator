<?php
 /**
  * SalamCastGen - Podcast Generator class
  * 
  * SalamCastGen and SalamCastGenItem are a fork of [Universal FeedWriter and FeedItem]({@link http://www.phpclasses.org/package/4427-PHP-Generate-feeds-in-RSS-1-0-2-0-an-Atom-formats.html}), 
  * by Anis uddin Ahmad <anisniit@gmail.com>.  Generates an RSS2.0 Feed with a mix of ATOM xmlns and built-in support for 
  * iTunes Podcast xmlns; other XML Name spaces can be added, but you will need to manually add the elements your self with 
  * the proper xmlns prefix.
  * - This feed generator has been targeted at iTunes, XMBC, iPhoto, any podcast (RSS 2.0) player/downloader
  * - Not all tags are supported by all RSS readers, but they can be used for any clients you choose to create to comsume them
  * 
  * @package SalamCastGen
  * @author Karl Holz <newaeon@mac.com>
  * @version 1.0
  * @copyright  Copyright (c) Apr 4, 2012, Karl Holz
  * @license http://opensource.org/licenses/GPL-2.0
  */
class SalamCastGen extends SalamFeedCommon {
	/**
	 * Default header
	 * @var string RSS feed header, text/xml works with IE ajax
	 */
	public $header = 'text/xml';
	/**
	 * @var array Collection of channel elements
	 */
	public $channels = array (); 
	/**
	 * @see SalamCastGen::createNewItem()
	 * @var array  Collection of RSS feed items as object of class.
	 */
	public $items = array (); 
	/**
	 * XSLT template 
	 * use with RSS feed, don't forget to add the xmlns that you use to your style sheet
	 * @var string XSLT filename
	 */
	public $tmpl = NULL;
	/**
	 * CDATA encoding
	 * The tag names which have to encoded as CDATA
	 * @var array XML element data that will be encoded
	 */
	public  $CDATAEncoding = array (				
			'description',
			'content:encoded',
			'atom:content',
			'itunes:summary' 
		); 
	/**
	 * xmlns
	 * ATOM xmlns spec is on by by default
	 * @link http://tools.ietf.org/html/rfc4287 
	 * @var $xmlns array custom XML Name Space support 
	 */
	public $xmlns = array (
			'atom' => "http://www.w3.org/2005/Atom" 
	);
	/**
	 * Feed data
	 * stores the generated RSS 2.0 XML data
	 * @var string
	 */
	public $feed;
	/**
	 * Constructor class
	 * builds an RSS 2.0 feed. Channel elements that are not included are: rating, skipHours, skipDays.
	 * @link http://www.rssboard.org/rss-2-0
	 * @param string $tmpl XSLT template file
	 * @param string $prefix prefix of uuid
	 * @return bool
	 */
	function __construct($tmpl = '', $prefix = __CLASS__) {
		$this->mode = 'channel';
		if ($tmpl != '' && is_file ( $tmpl )) $this->tmpl = $tmpl;
		$this->id = $this->uuid ( NULL, $prefix );
		$this->setLink ( $this->this_uri () );
		$this->setChannelElement ( 'atom:link', array (	'href' => $this->url_base (), 'type' => 'text/xml', 'title' => $this->generator (), 'rel' => "home" ) );
		$this->setChannelElement ( 'atom:link', array (	'href' => $this->this_uri (), 'type' => 'text/xml', 'title' => "Current", 'rel' => "self" ) );
		$this->setChannelElement ( 'generator', $this->generator () );
		/*  Email address for person responsible for technical issues relating to channel. */
		$this->setChannelElement ( 'webMaster', $_SERVER['SERVER_ADMIN'].' (SERVER_ADMIN)');
		return TRUE;
	}
	
	/**
	 * Print with template
	 * apply xslt template to Feed to print an HTML document
	 * @return string
	 */
	public function apply_tmpl() {
		header ( "Content-type: text/html" );
		$html = $this->xsl_out ( $this->tmpl, $this->feed );
		echo $html;
		exit ();
	}
	
	/**
	 * Set channel item
	 * Set the Channel tag to the channels array 
	 * @param  string $elementName of the channel tag
	 * @param  string $content of the channel tag
	 * @return bool
	 */
	public function setChannelElement($elementName, $content) {
		$this->channels [] [$elementName] = $content;
		return TRUE;
	}
	
	/**
	 * Add item
	 * Add a Feed Item to the main class
	 *
	 * @see SalamCastGen::createNewItem()
	 * @param object instance of class
	 * @return bool
	 */
	public function addItem($feedItem) {
		if ($this->itunes) $feedItem->enable_itunes ();
		$this->items [] = $feedItem;
		return TRUE;
	}
	
	/**
	 * More RSS2 xmlns
	 * add optional RSS2 NS, I don't use them myself, but you might find them useful
	 * @return bool
	 */
	public function add_opt_rss2() {
		$this->setXMLNS ( 'content', "http://purl.org/rss/1.0/modules/content/" );
		$this->setXMLNS ( 'wfw', "http://wellformedweb.org/CommentAPI/" );
		return TRUE;
	}
	
	/**
	 * add opt xmlns
	 * add RSS 1 (rdf/dc) support into the RSS2/ATOM feed
	 * @return bool
	 */
	public function add_rss1_ns() {
		$this->setXMLNS ( 'rdf', "http://www.w3.org/1999/02/22-rdf-syntax-ns#" );
		$this->setXMLNS ( 'dc', "http://purl.org/dc/elements/1.1/" );
		return TRUE;
	}
	
	/**
	 * add iTunes
	 * add iTunes Podcast xmlns specs
	 * @link http://www.apple.com/itunes/podcasts/specs.html
	 * @return bool
	 */
	public function add_itunes() {
		if ($this->itunes) $this->setXMLNS ( 'itunes', "http://www.itunes.com/dtds/podcast-1.0.dtd" );
		return TRUE;
	}
	
	/**
	 * Language
	 * RSS 2.0 language tag for channel
	 * @link http://backend.userland.com/stories/storyReader$16
	 * @param string $l
	 * @return bool
	 */
	public function add_lang($l = 'en-us') {
		if (!in_array($l, $this->lang)) $l='en-us';
		$this->setChannelElement ( 'language', $l );
		return TRUE;
	}
	
	/**
	 * Copyright
	 * RSS 2.0 copyright tag for channel
	 * <copyright> Y N not visible
	 * @link http://www.rssboard.org/rss-2-0
	 * @see SalamCastGen::setChannelElement()
	 * @param string $c copyright tag
	 * @return bool  	
	 */
	public function add_copyright($c = '') {
		if ($c != '') { 
			$this->setChannelElement ( 'copyright', $c );
			return TRUE;
		}
		return FALSE;
	}

	
	/**
	 * set XML ns
	 * add an XML Name Space to Feed output
	 *
	 * @param string $x xmlns prefix
	 * @param string $u xmlns url
	 * @return bool
	 */
	public function setXMLNS($x = '', $u='') {
		if ($x != '' && $u != '' ) {
			$this->xmlns [$x] = $u;
			return TRUE;
		}
		return FALSE;
	}
	
	/**
	 * iTunes complete
	 * iTunes complete tag used in channel 
	 * <itunes:complete> Y N indicates completion of podcasts; no more episodes
	 * @link http://www.apple.com/itunes/podcasts/specs.html#complete
	 * @see SalamCastGen::setChannelElement()
	 * @param string $c yes or no
	 * @return bool
	 */
	public function complete($c = 'no') {
		if ($this->itunes) {
			if ($c != "yes") $c = 'no';
			$this->setChannelElement ( 'itunes:complete', $c );
			return TRUE;
		}
		return FALSE;
	}
	
	/**
	 * New feed url
	 * New feed for iTunes
	 * <itunes:new-feed-url> - not visible, used to inform iTunes of new feed, used in channel
	 * @link http://www.apple.com/itunes/podcasts/specs.html#newfeed
	 * @see SalamCastGen::setChannelElement()
	 * @param string $u new feed url
	 * @return bool
	 */
	public function new_feed_url($u = '') {
		if ($this->itunes) {
			if ($u != '') $this->setChannelElement ( 'itunes:new-feed-url', $u );
			return TRUE;
		}
		return FALSE;
	}

	/**
	 * Managing Editor
	 * Email address for person responsible for editorial content.
	 * @link http://www.rssboard.org/rss-2-0
	 * @see SalamCastGen::setChannelElement()
	 * @param string $e info[at]example.com (info)
	 * @return bool
	 */
	public function managingEditor($e = '') {
		if ($e != '') {
			$this->setChannelElement ( 'managingEditor', $e);
		} else {
			$this->setChannelElement ( 'managingEditor', $_SERVER['SERVER_ADMIN'].' (SERVER_ADMIN)' );
		}
		return TRUE;
	}
	/**
	 * Last Build Date
	 * RSS 2.0 lastBuildDate tag in channel for the last time the content of the channel changed.
	 * @link http://www.rssboard.org/rss-2-0
	 * @see SalamCastGen::setChannelElement()
	 * @param string $d dateformat example: Sat, 07 Sep 2002 9:42:31 GMT
	 * @return bool
	 */
	public function lastBuildDate($d) {
		$this->setChannelElement ( 'lastBuildDate', $this->rssDate($d));
		return TRUE;
	}
	
	/**
	 * Docs
	 * Feed format Documentation RSS 2.0 docs tag in Channel
	 * @link http://www.rssboard.org/rss-2-0
	 * @see SalamCastGen::setChannelElement()
	 * @param string $u
	 * @return bool
	 */
	public function docs($u) {
		$this->setChannelElement ( 'docs', $u );
		return TRUE;
	}
	
	/**
	 * Owner
	 * Owner of feed for channel <itunes:owner> Y N not visible, used for contact only Put the email address of the owner in a nested <itunes:email> element. Put the name of the owner in a nested <itunes:name> element.
	 * @link http://www.apple.com/itunes/podcasts/specs.html#owner
	 * @param string $n
	 * @param string $e
	 * @return boolean
	 */
	public function setOwner($n = '', $e = '') {
		if ($n == '') $n = $this->generator ();
		if ($e == '') $e = $_SERVER ['SERVER_ADMIN'];
		if ($this->itunes) {
			if ($this->mode == 'channel') {
				$this->setChannelElement ( 'itunes:owner', $this->makeNode ( 'itunes:name', $n ) . $this->makeNode ( 'itunes:email', $e ) );
				$this->setChannelElement ( 'itunes:author', $n );
			}
		}
		return TRUE;
	}
	
	/**
	 * Cloud
	 * RSS 2.0 cloud tag used in channel
	 * @link http://www.rssboard.org/rss-2-0#ltcloudgtSubelementOfLtchannelgt
	 * @see SalamCastGen::setChannelElement()
	 * @param string $d
	 * @param interger $port
	 * @param string $path
	 * @param string $func
	 * @param string $protocl
	 * @return bool
	 */
	public function cloud($d, $port="80", $path="/", $func="", $protocl="http-post") {
		$p=array('http-post', 'xml-rpc', 'soap');
		if (!in_array($protocl, $p)) $protocl='http-post';
		if (!is_numeric($port)) $port="80";
		$this->setChannelElement ( 'cloud', array(
			'domain' =>	$d, 
			'port' => $port,
			'path' => $path,
			'registerProcedure' => $func, 
			'protocol' => $protocl
		));
		return TRUE;
	}
	
	/**
	 * Time to Live
	 * RSS 2.0 ttl (time to live) tag in channel
	 * @link http://www.rssboard.org/rss-2-0#ltttlgtSubelementOfLtchannelgt 
	 * @see SalamCastGen::setChannelElement()
	 * @param interger $t
	 * @return bool
	 */
	public function ttl($t=60) {
		if (is_numeric($t)) {
			$this->setChannelElement ( 'ttl', $t );
			return TRUE;
		}
		return FALSE;
	}
	
	/**
	 * Text Input
	 * RSS 2.0 textInput tag for channel, won't display in FireFox most readers will ignore it
	 * @link http://www.rssboard.org/rss-2-0#lttextinputgtSubelementOfLtchannelgt
	 * @see SalamCastGen::setChannelElement()
	 * @param string $t
	 * @param string $d
	 * @param string $n
	 * @param string $u
	 * @return bool
	 */
	public function textInput($t='submit', $d='SalamCastGen', $n='textbox', $u='') {
		if ($u == '') $u=$this->this_uri();
		$this->setChannelElement ( 
			'textInput', 
			$this->makeNode('title', $t).$this->makeNode('description', $d).$this->makeNode('name', $n).$this->makeNode('link', $u)
		);
		return TRUE;
	}

	/**
	 * XLST tmpl
	 * Apply an XSLT style sheet to the RSS 2.0 feed
	 * @param string $xsltmpl XSLT stylesheet to be applied to XML
	 * @param string $xml_load XML data
	 * @return string $xslproc->transformToXml($xml) transformed XML data
	 */
	public function xsl_out($xsltmpl, $xml_load) {
		$xml = new DOMDocument ();
		if (! is_file ( $xml_load )) {
			$xml->loadXML ( $xml_load );
		} else {
			if (! $xml->load ( realpath ( $xml_load ) ))  die(" *** XML/XHTML failed to load ***");
		}
		/**
		 * loads XSL template file
		 */ 
		$xsl = new DOMDocument ();
		if (! is_file ( $xsltmpl )) {
			$xsl->loadXML ( $xsltmpl );
		} else {
			if (! $xsl->load ( realpath ( $xsltmpl ) )) die(" *** XSLT style sheet failed to load ***");
		}
		/**
		 * process XML and XSLT files and return result
		 */
		$xslproc = new XSLTProcessor ();
		$xslproc->importStylesheet ( $xsl );
		return $xslproc->transformToXml ( $xml );
	}
	
	/**
	 * New Item 
	 * Create a new feed item 
	 * @see SalamCastGenItem::__construct()
	 * @return object instance of FeedItem class
	 */
	public function createNewItem() {
		$Item = new SalamCastGenItem ();
		if ($this->itunes) $Item->enable_itunes ();
		return $Item;
	}
	
	/**
	 * Print Feed
	 * Print the RSS 2.0 feed
	 * @see SalamCastGen::getNewFeed() 
	 * @return string
	 */
	public function genarateFeed() {
		$this->getNewFeed ();
		if ($this->tmpl !== NULL && is_file ( $this->tmpl )) {
			$this->apply_tmpl ();
		}
		header ( "Content-type: " . $this->header );
		echo $this->feed;
		exit ();
	}
	
	/**
	 * Make Feed
	 * Genarate the actual RSS 2.0 content, wont print
	 * @return string
	 */
	public function getNewFeed() {
		$f = $this->Head ();
		$f .= $this->Channels ();
		$f .= $this->Items ();
		$f .= $this->Tail ();
		$this->feed = $f;
		return $f;
	}
	
	/**
	 * Open Feed
	 * Generate the xml and rss namespace
	 * @return string
	 */
	private function Head() {
		$out = '<?xml version="1.0" encoding="utf-8"?>' . PHP_EOL;
		$out .= '<rss version="2.0" ';
		foreach ( $this->xmlns as $n => $s ) {
			$out .= 'xmlns:' . $n . '="' . $s . '" ';
		}
		$out .= '>' . PHP_EOL;
		return $out;
	}
	
	/** 
	 * Close Feed
	 * Closes the open tags at the end of file
	 * @return string
	 */
	private function Tail() {
		return '</channel>' . PHP_EOL . '</rss>';
	}
	
	/**
	 * Make Channel
	 * Generate RSS 2.0 channels
	 * @return string
	 */
	private function Channels() {
		$out = '<channel>' . PHP_EOL;
		foreach ( $this->channels as $v ) {
			if (is_array ( $v )) {
				foreach ( $v as $key => $value ) {
					/**
					 * added support for channel elements with attribs and no content, just send a hashed array and not string
					 */
					if (is_array ( $value )) {
						$out .= $this->makeNode ( $key, '', $value );
					} else {
						switch ($key) {
							case 'itunes:owner' :
							case 'image':
							case 'textInput':
								$out .= $this->makeNode ( $key, $value, null, false );
								break;
							default :
								$out .= $this->makeNode ( $key, $value );
						}
					}
				}
			}
		}
		if (count($this->category) > 0) {
			$out.=implode('', $this->category);
		}
		return $out;
	}
	
	/**
	 * Make Items
	 * Generate formatted feed items
	 * @return string
	 */
	private function Items() {
		$out = '';
		foreach ( $this->items as $item ) {
			$thisItems = $item->getElements ();
			$out .= $this->startItem ();
			foreach ( $thisItems as $feedItem )
				$out .= $this->makeNode ( $feedItem ['name'], $feedItem ['content'], $feedItem ['attributes'] );
			$out .= $this->endItem ();
		}
		return $out;
	}
	
	/**
	 * open Item
	 * Open RSS 2.0 item tag
	 * @return string
	 */
	private function startItem() {
		return '<item>' . PHP_EOL;
	}
	
	/**
	 * end Item
	 * Closes RSS 2.0 item tag
	 * @return string
	 */
	private function endItem() {
		return '</item>' . PHP_EOL;
	}
	
	/**
	 * Error Feed
	 * prints out an error feed in RSS 2.0 format
	 * @param interger $code
	 * @return string
	 */
	public function errorFeed($code = '404') {
		switch ($code) {
			case '401' : $desc = 'HTTP/1.0 401 Unauthorized'; break;
			case '403' : $desc = "HTTP/1.0 403 Forbidden"; break;
			case '404' : $desc = "HTTP/1.0 404 Not Found"; break;
			case '410' : $desc = "HTTP/1.0 410 Gone"; break;
			case '500' : $desc = "HTTP/1.0 500 Internal Server Error"; break;
			case '501' : $desc = "HTTP/1.0 501 Not Implemented"; break;
			case '503' : $desc = "HTTP/1.0 503 Service Unavailable"; break;
			default : 	 $desc = "HTTP/1.0 404 Not Found"; break;
		}
		$this->channels = array ();
		$this->setTitle ( $desc );
		$this->setDescription ( $desc );
		$this->setChannelElement ( 'language', "en-us" );
		$this->setChannelElement ( 'copyright', $this->generator () );
		$this->managingEditor($_SERVER ['SERVER_ADMIN']);
		$this->setChannelElement ( 'webMaster', $_SERVER['SERVER_ADMIN'].' (SERVER_ADMIN)');
		$this->setChannelElement ( 'atom:id', 'urn:uuid:' . $this->id );
		$this->setLink ( $this->url_base () );
		$this->setChannelElement ( 'atom:link', array (
				'href' => $this->url_base (),
				'type' => 'text/xml',
				'title' => $this->generator (),
				'rel' => "index" 
		) );
		$this->setChannelElement ( 'atom:link', array (
				'href' => $this->this_uri (),
				'type' => 'text/xml',
				'title' => "$desc",
				'rel' => "self" 
		) );
		$this->setChannelElement ( 'generator', $this->generator () );
		$err = $this->createNewItem ( $this->id );
		$err->setTitle ( $desc );
		$err->setLink ( $this->url_base () );
		$err->setChannelElement ( 'atom:link', array (
				'href' => $this->url_base (),
				'type' => 'text/xml',
				'title' => $this->generator (),
				'rel' => "index" 
		) );
		$err->setChannelElement ( 'atom:link', array (
				'href' => $this->this_uri (),
				'type' => 'text/xml',
				'title' => "$desc",
				'rel' => "self" 
		) );
		$err->setDate ( $this->rssDate () );
		$err->setDescription ( $desc );
		$this->addItem ( $err );
		// print feed to browser
		header ( $desc );
		header ( "Content-type: " . $this->header );
		echo $this->getNewFeed ();
		exit ();
	}
} 

 /**
  * SalamCastGenItem - Podcast Gernerator
  * 
  * this class is used as feed element generator in SalamCastGen class
  *
  * @package SalamCastGenItem
  * @author Karl Holz <newaeon@mac.com>
  * @version 1.0
  * @copyright  Copyright (c) Apr 4, 2012, Karl Holz
  * @license http://opensource.org/licenses/GPL-2.0
  */
class SalamCastGenItem extends SalamFeedCommon {
	/**
	 * Element list
	 * @var array Collection of feed elements
	 */
	public $elements = array (); 
	/**
	 * @var string $link item link
	 */
	public $link;
	
	/**
	 * Construct new item
	 * @return boolean
	 */
	function __construct() {
		$this->mode = 'item';
		return TRUE;
	}
	/**
	 * Add to RSS items
	 * Add an item element to elements array
	 * @param string $elementName The tag name of an element
	 * @param string $content The content of tag
	 * @param array $attributes Attributes(if any) in 'attrName' => 'attrValue' format
	 * @return bool
	 */
	public function addElement($elementName, $content, $attributes = null) {
		$this->elements [] = array (
				'name' => $elementName,
				'content' => $content,
				'attributes' => $attributes 
		);
		return TRUE;
	}
	
	/**
	 * Element list
	 * Return the collection of elements in this feed item
	 */
	public function getElements() {
		return $this->elements;
	}
	
	/**
	 * RSS 2.0 enclosure tag
	 * <enclosure> - used in item tag, itunes mode will use itunes mime types
	 * @link http://www.rssboard.org/rss-2-0#ltenclosuregtSubelementOfLtitemgt
	 * @see SalamCastGenItem::addElement()
	 * @param string $u        	
	 * @param string $l        	
	 * @param string $t        	
	 */
	public function setEncloser($u, $l = '4096', $t = '') {
		if ($this->itunes) {
			if ($t != '') {
				$t = $this->search_mime ( $t );
			} else {
				$e = explode ( '.', $u );
				$ext = array_pop ( $e );
				$t = $this->search_mime ( $ext );
			}
		}
		$attributes = array ( 'url' => $u, 'length' => $l, 'type' => $t );
		$this->addElement ( 'enclosure', '', $attributes );
		return TRUE;
	}
	
	/**
	 * iTunes duration tag 
	 * <itunes:duration> - Time column, used in feed item
	 * @link http://www.apple.com/itunes/podcasts/specs.html#duration
	 * @see SalamCastGenItem::addElement()
	 * @param string $d must me all numbers 12345, or time ref 5:59:30; nothing will be added to the document
	 * @return bool
	 */
	public function setDuration($d = '') {
		if ($this->itunes) {
			if (is_numeric ( $d )) {
				$this->addElement ( 'itunes:duration', $d );
				return TRUE;
			} else {
				$tc = explode ( ':', $d );
				$pass = FALSE;
				switch (count ( $tc )) {
					case 3 :
						if (is_numeric ( ltrim ( $tc [0], '0' ) ) && (ltrim ( $tc [1], '0' ) >= 0 || ltrim ( $tc [1], '0' ) < 60) && (ltrim ( $tc [2], '0' ) >= 0 || ltrim ( $tc [2], '0' ) < 60)) {
							$pass = TRUE;
						}
						break;
					case 2 :
						if (is_numeric ( ltrim ( $tc [0], '0' ) ) && (ltrim ( $tc [1], '0' ) >= 0 || ltrim ( $tc [1], '0' ) < 60)) {
							$pass = TRUE;
						}
						break;
				}
				if ($pass) {
					$this->addElement ( 'itunes:duration', $d );
					return TRUE;
				}
			}
		}
		return FALSE;
	}
	
	/**
	 * iTunes order tag 
	 * <itunes:order> - override the order of episodes on the store, used in feed item
	 * @link http://www.apple.com/itunes/podcasts/specs.html#order
	 * @see SalamCastGenItem::addElement()
	 * @param interger $o  
	 * @return bool
	 */
	public function setOrder($o = '') {
		if ($this->itunes) {
			if (is_numeric ( $o )) {
				$this->addElement ( 'itunes:order', $o );
				return TRUE;
			}
		}
		return FALSE;
	}
	/**
	 * iTunes CC tag 
	 * <itunes:isClosedCaptioned> - Closed Caption graphic in Name column, used in the feed item
	 * @link http://www.apple.com/itunes/podcasts/specs.html#isClosedCaptioned
	 * @see SalamCastGenItem::addElement()
	 * @param string $c   yes or no
	 * @return bool     	
	 */
	public function setCC($c = 'no') {
		if ($this->itunes) {
			if (strtolower ( $c ) != 'yes') $c = 'no';
			$this->addElement ( 'itunes:isClosedCaptioned', $c );
			return TRUE;
		}
		return FALSE;
	}
	
	/**
	 * RSS 2.0 comments tag
	 * comments tag in the feed item
	 * @link http://www.rssboard.org/rss-2-0#ltcommentsgtSubelementOfLtitemgt
	 * @see SalamCastGenItem::addElement()
	 * @param string $u url
	 * @return bool
	 */
	public function comments($u) {
		if ($u !='') {
			$this->addElement ( 'comments', $u );
			return TRUE;
		}
		return FALSE;
	}
	
	/**
	 * RSS 2.0 source tag
	 * @link http://www.rssboard.org/rss-2-0#ltsourcegtSubelementOfLtitemgt
	 * @see SalamCastGenItem::addElement()
	 * @param string $t title
	 * @param string $u url
	 * @return bool
	 */
	public function source($t='', $u='') {
		if ($u !='' && $t != '') {
			$this->addElement ( 'source', $t, array('url' => $u) );
			return TRUE;
		}
		return FALSE;
	}
} // end of class SalamCastGenItem

 /**
  * SalamFeedCommon is an Abstract class with all the common functions for both SalamCastGen and SalamCastItem
  * @package SalamFeedCommon
  * @author Karl Holz <newaeon@mac.com>
  * @version 1.0
  * @copyright  Copyright (c) Apr 4, 2012, Karl Holz
  * @license http://opensource.org/licenses/GPL-2.0
  */
abstract class SalamFeedCommon {
	/**
	 * UUID 
	 * @var string $id place holder for uuid value 
	 */
	public $id = null;
	/**
	 * iTunes support
	 * @var bool $itunes is for triggering iTunes support
	 */
	public $itunes = FALSE;
	/** 
	 * Category
	 * @var array $category place holder nested XML data
	 */
	public $category = array ();
	/**
	 * enable iTunes
	 * this enables itunes xmlns support for iTunes Podcasts
	 * @return boolean
	*/
	public function enable_itunes() {
		$this->itunes = TRUE;
		if ($this->mode == 'channel') $this->add_itunes ();
		return TRUE;
	}

	/**
	 * iTunes Podcast Categories
	 * iTunes Store Podcasting Categories
	 * @link http://www.apple.com/itunes/podcasts/specs.html#categories
	 * @var array $itunes_category
	 */
	public $itunes_category=array(
			'Arts' => array('Design','Fashion & Beauty','Food','Literature','Performing Arts','Visual Arts'),
			'Business' => array('Business News','Careers','Investing','Management & Marketing','Shopping'),
			'Comedy' => array(),
			'Education' => array('Education','Education Technology','Higher Education','K-12','Language Courses','Training'),
			'Games & Hobbies' => array('Automotive','Aviation','Hobbies','Other Games','Video Games'),
			'Government & Organizations' => array('Local','National','Non-Profit','Regional'),
			'Health' => array('Alternative Health','Fitness & Nutrition','Self-Help','Sexuality'),
			'Kids & Family' => array(),
			'Music' => array(),
			'News & Politics' => array(),
			'Religion & Spirituality' => array('Buddhism','Christianity','Hinduism','Islam','Judaism','Other','Spirituality'),
			'Science & Medicine' => array('Medicine','Natural Sciences','Social Sciences'),
			'Society & Culture' => array('History','Personal Journals','Philosophy','Places & Travel'),
			'Sports & Recreation' => array('Amateur','College & High School','Outdoor','Professional'),
			'Technology' => array('Gadgets','Tech News','Podcasting','Software How-To'),
			'TV & Film' => array()
	);

	/**
	 * iTunes Podcast mime types
	 * iTunes Podcast mime types, added ibooks
	 * @link http://www.apple.com/itunes/podcasts/specs.html
	 * @var array $itunes_mime
	*/
	public $itunes_mime = array (
			"mp3" => "audio/mpeg",
			"m4a" => "audio/x-m4a",
			"mp4" => "video/mp4",
			"m4v" => "video/x-m4v",
			"mov" => "video/quicktime",
			"pdf" => "application/pdf",
			"epub" => 'document/x-epub',
			"ibooks" => 'application/x-ibooks+zip'
	);

	/**
	 * RSS 2.0 language tag
	 * contains valid values for language tag
	 * @link http://backend.userland.com/stories/storyReader$16
	 * @var array $lang
	*/
	public $lang=array(
			'af','sq','eu','be','bg','ca',
			'zh-cn','zh-tw',
			'hr','cs','da',
			'nl','nl-be','nl-nl',
			'en','en-au','en-bz','en-ca','en-ie','en-jm','en-nz','en-ph','en-za','en-tt','en-gb','en-us','en-zw',
			'et','fo','fi',
			'fr','fr-be','fr-ca','fr-fr','fr-lu','fr-mc','fr-ch',
			'gl','gd',
			'de','de-at','de-de','de-li','de-lu','de-ch',
			'el','haw','hu','is','in','ga',
			'it','it-it','it-ch',
			'ja','ko','mk','no','pl',
			'pt','pt-br','pt-pt',
			'ro','ro-mo','ro-ro',
			'ru','ru-mo','ru-ru',
			'sr','sk','sl',
			'es','es-ar','es-bo','es-cl','es-co','es-cr','es-do','es-ec','es-sv','es-gt','es-hn','es-mx','es-ni','es-pa','es-py','es-pe','es-pr','es-es','es-uy','es-ve',
			'sv','sv-fi','sv-se',
			'tr','uk'
	);

	/**
	 * Feed mode
	 * Function switch mode
	 * @var string $mode the mode determines what elements to build
	 */
	public $mode;
	
	/**
	 * Search iTunes mime-types
	 * searches for matching itunes mime types based on mime or file extention
	 * @param string $key mime or file extention
	 * @return string mime type
	 */
	public function search_mime($key = 'mp3') {
		if (strlen ( $key ) <= 6) {
			if (array_key_exists ( $key, $this->itunes_mime ))
				return $this->itunes_mime [$key];
		} else {
			if (array_search ( $key, $this->itunes_mime ))
				return $key;
		}
		return $this->itunes_mime ['mp3'];
	}
	/**
	 * Search Categories 
	 * Search iTunes Categories, used for building nested RSS XML data for iTunes categories
	 * @param mixed $cat an array of categories or single category
	 * @return array $c
	 */
	public function search_cat($cat) {
		$c = array ();
		if (is_array ( $cat )) {
			foreach ( $cat as $v ) {
				$r = $this->read_cat ( $v );
				switch (count ( $r )) {
					case 1 :
						$c [$r [0]] = array ();
						break;
					case 2 :
						$c [$r [0]] [] = $r [1];
						break;
				}
			}
		} else {
			$r = $this->read_cat ( $cat );
			switch (count ( $r )) {
				case 1 :
					$c [$r [0]] = array ();
					break;
				case 2 :
					$c [$r [0]] [] = $r [1];
					break;
			}
		}
		return $c;
	}
	/**
	 * Read iTunes categories
	 * @param mixed $cat
	 * @return array
	 */
	public function read_cat($cat) {
		foreach ( $this->itunes_category as $k => $v ) {
			if ($k == $cat) {
				return array ($k);
			} else {
				foreach ( $v as $vv ) {
					if ($vv == $cat) {
						return array ($k, $vv);
					}
				}
			}
		}
		return array();
	}

	/**
	 * XML CDATA
	 * Wrap text in XML CDATA tags
	 * @param $d string to wrap
	 * @return CDATA wrapped string
	 */
	public function XMLCdata($d) {
		return "<![CDATA[" . $d . "]]>";
	}
	/**
	 * Cleans text for XML usage
	 *
	 * @param string $strin string to be encoded for XML niceness
	 * @return string $strout fixed up string
	 */
	public function XMLClean($strin) {
		$strout = null;
		if (is_array ( $strin )) $strin = '';
		for($i = 0; $i < strlen ( $strin ); $i ++) {
			$ord = ord ( $strin [$i] );
			if (($ord > 0 && $ord < 32) || ($ord >= 127)) {
				$strout .= "&amp;#{$ord};";
			} else {
				switch ($strin [$i]) {
					case '<' : $strout .= '&lt;'; break;
					case '>' : $strout .= '&gt;'; break;
					case '&' : $strout .= '&amp;'; break;
					case '"' : $strout .= '&quot;'; break;
					case "'" : $strout .= '&apos;'; break;
					case '©' : $strout .= '&#xA9;'; break;
					case '℗' : $strout .= '&#x2117;'; break;
					case "™" : $strout .= '&#x2122;'; break;
					default :  $strout .= $strin [$i];
				}
			}
		}
		return $strout;
	}

	/**
	 * Genarates UUID
	 *
	 * @param string $key unique text to encode
	 * @param string $prefix an optional prefix
	 * @return string the formated uuid
	 */
	public function uuid($key = null) {
		$key = ($key == null) ? $this->this_uri () : $key;
		$chars = md5 ( $key );
		$uuid = substr ( $chars, 0, 8 ) . '-';
		$uuid .= substr ( $chars, 8, 4 ) . '-';
		$uuid .= substr ( $chars, 12, 4 ) . '-';
		$uuid .= substr ( $chars, 16, 4 ) . '-';
		$uuid .= substr ( $chars, 20, 12 );
		return $this->mode . '-' . $uuid;
	}

	/**
	 * RSS timestamp 
	 * 
	 * get the RSS timestamp format (Mon, 02 Jul 2009 11:36:45 +0000)
	 *
	 * @param string $timestamp 
	 * @return string 
	 */
	public function rssDate($timestamp = NULL) {
		$timestamp = ($timestamp == NULL) ? time () : $timestamp;
		if (is_long ( $timestamp )) {
			return date ( DATE_RSS, $timestamp );
		} else {
			return $timestamp;
		}
	}

	/**
	 * this host
	 * this host url, based on server values
	 * @return string
	 */
	public function this_host() {
		if (array_key_exists ( "HTTP_HOST", $_SERVER ) && array_key_exists ( "SERVER_PORT", $_SERVER )) {
			if (array_key_exists ( 'HTTPS', $_SERVER )) {
				return "https://" . $_SERVER ["HTTP_HOST"];
			} else {
				return "http://" . $_SERVER ["HTTP_HOST"];
			}
		} else {
			return 'http://localhost';
		}
	}

	/**
	 * this uri
	 * returns the current uri based on server variables
	 * @return string
	 */
	public function this_uri() {
		if (array_key_exists ( "REQUEST_URI", $_SERVER )) return $this->this_host () . $_SERVER ["REQUEST_URI"];
		return $this->this_host ();
	}

	/**
	 * url base
	 * this scripts base url based on server variables
	 * @return string
	 */
	public function url_base() {
		if (array_key_exists ( "SCRIPT_NAME", $_SERVER )) return $this->this_host () . $_SERVER ["SCRIPT_NAME"];
		return $this->this_host ();
	}

	/**
	 * RSS 2.0 title tag 
	 * <title> - Name column, used in both channel and item
	 * @link http://www.rssboard.org/rss-2-0
	 * @param string $t value of 'title' tag
	 * @return bool
	 */
	public function setTitle($t) {
		if ($t == '') $t = $this->generator ();
		if ($this->mode == 'channel') {
			$this->setChannelElement ( 'title', $t );
		} elseif ($this->mode == 'item') {
			$this->addElement ( 'title', $t );
		}
		return TRUE;
	}

	/**
	 * iTunes subtitle tag
	 * <itunes:subtitle> - Description column, used in both channel and item
	 * @link http://www.apple.com/itunes/podcasts/specs.html#subtitle
	 * @param string $s value of 'subtitle' channel/item tag
	 * @return bool
	 */
	public function setSubTitle($s = '') {
		if ($s == '') $s = $this->generator ();
		if ($this->itunes) {
			if ($this->mode == 'channel') {
				$this->setChannelElement ( 'itunes:subtitle', $s );
			} elseif ($this->mode == 'item') {
				$this->addElement ( 'itunes:subtitle', $s );
			}
			return TRUE;
		}
		return FALSE;
	}

	/**
	 * iTunes summary tag
	 * <itunes:summary> - when the "circled i" in Description column is clicked, used in both channel and item
	 * @link http://www.apple.com/itunes/podcasts/specs.html#summary
	 * @param string $d value of 'description' channel tag
	 * @return bool
	 */
	public function setDescription($d = '') {
		if ($d != '') {
			if ($this->mode == 'channel') {
				if ($this->itunes)
					$this->setChannelElement ( 'itunes:summary', $d );
				$this->setChannelElement ( 'description', $d );
			} elseif ($this->mode == 'item') {
				if ($this->itunes)
					$this->addElement ( 'itunes:summary', $d );
				$this->addElement ( 'description', $d );
			}
			return TRUE;
		}
		return FALSE;
	}

	/**
	 * iTunes author tag
	 * <itunes:author> - Artist column used in both channel and item
	 * @link http://www.apple.com/itunes/podcasts/specs.html#authorId
	 * @param string $n  author
	 * @param string $e  email
	 * @return bool
	 */
	public function setAuthor($n = '', $e = '') {
		if ($n == '') $n = $this->generator ();
		if ($e == '') $e = $_SERVER ['SERVER_ADMIN'];
		if ($this->mode == 'channel') {
			$this->setChannelElement ( 'author', $e );
		} elseif ($this->mode == 'item') {
			$this->addElement ( 'author', $e );
		}
		if ($this->itunes) {
			if ($this->mode == 'channel') {
				$this->setChannelElement ( 'itunes:author', $n );
			} elseif ($this->mode == 'item') {
				$this->addElement ( 'itunes:author', $n );
			}
		}
		return TRUE;
	}

	/**
	 * iTunes keywords tag
	 * <itunes:keywords> - not visible but can be searched, used in both channel and item
	 * @link http://www.apple.com/itunes/podcasts/specs.html#keywords
	 * @param array $arg maximum 12 keywords, best results are with using an array
	 * @return bool
	 */
	public function keywords($arg = array()) {
		if ($this->itunes) {
			if (! is_array ( $arg )) $arg = explode ( ',', $arg );
			if (count ( $arg ) > 1 || count ( $arg ) < 13) {
				$k = implode ( ',', $arg );
				if ($this->mode == 'channel') {
					$this->setChannelElement ( 'itunes:keywords', $k );
				} elseif ($this->mode == 'item') {
					$this->addElement ( 'itunes:keywords', $k );
				}
				return TRUE;
			}
			return FALSE;
		}
	}

	/**
	 * iTunes block tag
	 * <itunes:block> - prevent an episode or podcast from appearing, used in both channel and item
	 * @link http://www.apple.com/itunes/podcasts/specs.html#block
	 * @param string $b yes or no
	 * @return bool
	 */
	public function block($b = 'no') {
		if ($this->itunes) {
			if (strtolower ( $b ) != 'yes') $b = 'no';
			if ($this->mode == 'channel') {
				$this->setChannelElement ( 'itunes:block', $b );
			} elseif ($this->mode == 'item') {
				$this->addElement ( 'itunes:block', $b );
			}
			return TRUE;
		}
		return FALSE;
	}

	/**
	 * iTunes explicit tag 
	 * <itunes:explicit> - parental advisory graphic in Name column used in both channel and item
	 * @link http://www.apple.com/itunes/podcasts/specs.html#explicit
	 * @param string $e clean, yes or no
	 */
	public function explicit($e = 'clean') {
		if ($this->itunes) {
			switch ($e) {
				case 'clean' :	case 'yes' : case 'no' : break;
				default : $e = 'clean';
			}
			if ($this->mode == 'channel') {
				$this->setChannelElement ( 'itunes:explicit', $e );
			} elseif ($this->mode == 'item') {
				$this->addElement ( 'itunes:explicit', $e );
			}
			return TRUE;
		}
		return FALSE;
	}

	/**
	 * iTunes image tag 
	 * <itunes:image> - Same location as album art used in both channel and item
	 * @link http://www.apple.com/itunes/podcasts/specs.html#image
	 * @param string $t title of image
	 * @param string $l link url
	 * @param string $u url of image
	 * @return bool
	 */
	public function setImage($t, $l, $u) {
		if ($this->mode == 'channel') {
			$this->setChannelElement ( 'image', $this->makeNode('title', $t).$this->makeNode('link', $l).$this->makeNode('url', $u) );
			if ($this->itunes) $this->setChannelElement ( 'itunes:image', array ( 'title' => $t, 'link' => $l, 'url' => $u ) );
			return TRUE;
		} elseif ($this->mode == 'item') {
			if ($this->itunes) {
				$this->addElement ( 'itunes:image', array ( 'title' => $t, 'link' => $l, 'url' => $u ) );
				return TRUE;
			} 
		}
		return FALSE;
	}

	/**
	 * RSS 2.0 link tag
	 * <link> - website link and arrow in Name column, used in both channel and item (not in iTunes spec) and guid used in item. 
	 * <guid> {@link http://www.rssboard.org/rss-2-0#ltguidgtSubelementOfLtitemgt}
	 * @link http://www.rssboard.org/rss-2-0
	 * @param string $l value of 'link' channel tag
	 * @param bool $perm switch for perma link
	 * @return bool
	 */
	public function setLink($l = '', $perm=FALSE) {
		if ($l != '') {
			if ($this->mode == 'channel') {
				$this->setChannelElement ( 'link', $l );
			} elseif ($this->mode == 'item') {
				// Attributes have to passed as array in 3rd parameter
				$this->addElement ( 'link', $l );
				if ($perm) {
					$this->addElement ( 'guid', $l, array (
							'isPermaLink' => 'true'
					) );
				} else {
					$this->addElement ( 'guid', $l );
				}
			}
			return TRUE;
		}
		return FALSE;
	}

	/**
	 * iTunes category tag
	 * <itunes:category> - used in category column and in iTunes Store Browser. Used in channel; RSS 2.0 category used in both channel and item {@link http://www.rssboard.org/rss-2-0#ltcategorygtSubelementOfLtitemgt}
	 * @link http://www.apple.com/itunes/podcasts/specs.html#category
	 * @param string $c
	 */
	public function category($c) {
		if ($this->mode == 'channel') {
			if ($this->itunes) {
				$cat=$this->search_cat($c);
				foreach ($cat as $v => $k) {
					if (count($k) == 0) {
						$this->setChannelElement ( 'itunes:category', array ( 'text' => $v 	) );
					} else {
						$e=array();
						foreach ($k as $vv) {
							$e[]=$this->makeNode('itunes:category', '', array ( 'text' => $vv ));
						}
						$this->category[]=$this->makeNode('itunes:category', implode(' ', $e), array ( 'text' => $v ), false);
					}
				}
			}
			if (! is_array($c)) {
				$this->setChannelElement ( 'category', $c );
			} else {
				foreach ($c as $k => $v) {
					$this->setChannelElement ( 'category', $v );
				}
			}
		} elseif ($this->mode == 'item') {
			$this->addElement ( 'category', $c );
		}
	}

	/**
	 * RSS 2.0 pubDate tag
	 * <pubDate> - Release Date column, used in channel (not in iTunes spec) and item
	 * @link http://www.apple.com/itunes/podcasts/specs.html#pubDate
	 * @param string $d date format Wed, 15 Jun 2005 19:00:00 GMT
	 * @return bool
	 */
	public function setDate($d = '') {
		if ($d != '') {
			$date = $this->rssDate ( $d );
		} else {
			$date = $this->rssDate ();
		}
		if (! is_numeric ( $date )) $date = strtotime ( $date );
		$value = date ( DATE_RSS, $date );
		if ($this->mode == 'channel') {
			$this->setChannelElement ( 'pubDate', $value );
		} elseif ($this->mode == 'item') {
			$this->addElement ( 'pubDate', $value );
		}
		return TRUE;
	}

	/**
	 * ATOM formated link tag
	 * @param string $t title
	 * @param string $a url
	 * @param string $m mime type
	 * @param string $r rel link value
	 * @return bool
	 */
	public function addAtomLink($t = 'Atom Link', $a = '', $m='text/html', $r="link") {
		if ($a != '') {
			if ($this->mode == 'channel') {
				$this->setChannelElement ( 'atom:link', array ( 'href' => $a, 'type' => $m, 'title' => $t, 'rel' =>  $r ) );
			} elseif ($this->mode == 'item') {
				$this->addElement ( 'atom:link', '', array ( 'href' => $a, 'type' => $m, 'title' => $t, 'rel' => $r ) );
			}
			return TRUE;
		}
		return FALSE;
	}
	
	/**
	 * Add UUID tags
	 * @todo add caching suppot to make the best use of this option for archiving, etc 
	 * @return bool
	 */
	public function add_uuid() {
		$this->id = $this->uuid ( $this->link, __CLASS__ );
		$u = $this->url_base () . '/uuid/' . $this->id;
		if ($this->mode == 'channel') {
			$this->setChannelElement ( 'atom:id', 'urn:uuid:' . $this->id );
			$this->setChannelElement ( 'atom:link', array (	'href' => $u, 'type' => 'text/xml', 'title' => "self", 'rel' => "self" ) );
		} elseif ($this->mode == 'item') {
			$this->addElement ( 'atom:id', 'urn:uuid:' . $this->id );
			$this->addElement ( 'atom:link', '', array ('href' => $u,'type' => 'text/xml','title' => "self",'rel' => "self"	) );
		}
		return TRUE;
	}
	/**
	 * Podcast Generator String
	 * @return string
	 */
	public function generator() {
		return "SalamCast Podcast Generator";
	}

	/**
	 * Make XML
	 * Creates a single node as xml format
	 *
	 * @param string $tagName  name of the tag
	 * @param string $tagContent tag value as string
	 * @param array $attributes Attributes(if any) in 'Name' => 'Value'
	 * @param bool $encode false omits encodeing, ideal if $tagContent has XML
	 * @return string formatted xml tag
	 */
	public function makeNode($tagName, $tagContent, $attributes = null, $encode = true) {
		$nodeText = '';
		$attrText = '';
		if (is_array ( $attributes )) {
			foreach ( $attributes as $key => $value ) {
				if (is_numeric ( $value )) {
					$attrText .= " $key='" . $value . "' ";
				} else {
					$attrText .= " $key='" . $this->XMLClean ( $value ) . "' ";
				}
			}
		}
		$nodeText .= "<{$tagName} {$attrText}>";
		if (is_array ( $tagContent )) {
			foreach ( $tagContent as $key => $value ) {
				$nodeText .= $this->makeNode ( $key, $value );
			}
		} else {
			if ($encode) {
				$nodeText .= (in_array ( $tagName, $this->CDATAEncoding )) ? $this->XMLCdata ( $tagContent ) : $this->XMLClean ( $tagContent );
			} else {
				$nodeText .= $tagContent;
			}
		}
		$nodeText .= "</$tagName>";
		return $nodeText . PHP_EOL;
	}
}
