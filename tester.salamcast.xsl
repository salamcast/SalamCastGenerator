<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns:atom="http://www.w3.org/2005/Atom" 
  xmlns:itunes="http://www.itunes.com/dtds/podcast-1.0.dtd"
  version="1.0">
  <xsl:output  method="html" />
  <xsl:template match="/">
<html>
  <head>
    <title><xsl:value-of select="/rss/channel/title|/rss/channel/generator"/></title>
    
  </head>
  <body>
   <xsl:apply-templates select="/rss/channel" />
  </body>
</html>
  </xsl:template>
  <xsl:template match="/rss/channel">
   <div>
     <h2><xsl:value-of select="/rss/channel/title|/rss/channel/generator"/></h2>
     
     <p>Links</p>
     <ul>
      <xsl:apply-templates select="atom:link" />
     </ul>
     <p></p>
   </div>
   <br /><hr />
   <xsl:apply-templates select="item" />
  </xsl:template>
  <xsl:template match="atom:link|enclosure">
    <li><a href="{@href|@url}" rel="{@rel}"><xsl:value-of select="@title|../title"/></a></li>    
  </xsl:template>
  <xsl:template match="item">
   <div>
     <h2><a href="{link}"><xsl:value-of select="title"/></a></h2>
     <p><xsl:value-of select="description|itunes:summary"/></p>
     <hr />
     <p>Links</p>
     <ul>
       <xsl:apply-templates select="atom:link|enclosure" />
     </ul>
   </div>
  </xsl:template>
</xsl:stylesheet>