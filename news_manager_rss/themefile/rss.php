<?php if(!defined('IN_GS')){ die('you cannot load this page directly.'); }
header("Content-Type: application/rss+xml; charset=utf-8");
header("Content-Type: text/xml; charset=utf-8"); ?>
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
	<channel>
		<atom:link href="<?php get_page_url(); ?>" rel="self" type="application/rss+xml" />
		<title><?php echo htmlspecialchars(html_entity_decode(get_site_name(false), ENT_QUOTES, 'UTF-8'), ENT_QUOTES, 'UTF-8'); ?></title>
		<description><?php echo htmlspecialchars(html_entity_decode(get_page_meta_desc(false), ENT_QUOTES, 'UTF-8'), ENT_QUOTES, 'UTF-8'); ?></description>
		<link><?php get_site_url(); ?></link>
<?php get_page_content(); ?>
	</channel>
</rss>
