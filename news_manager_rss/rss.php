<?php if(!defined('IN_GS')){ die('you cannot load this page directly.'); }
header("Content-Type: application/rss+xml; charset=utf-8");
header("Content-Type: text/xml; charset=utf-8"); ?>
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
	<channel>
		<atom:link href="<?php get_page_url(); ?>" rel="self" type="application/rss+xml" />
		<title><?php get_site_name(); ?></title>
		<description><?php get_page_meta_desc(); ?></description>
		<link><?php get_site_url(); ?></link>
<?php get_page_content(); ?>
	</channel>
</rss>
