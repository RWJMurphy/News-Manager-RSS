<?php
/*
Plugin Name: News Manager RSS
Description: Adds RSS functionality to News Manager
Version: 1.0
Author: Reed Murphy
Author URI: http://www.reedmurphy.net/
*/

define('NMRSS_TAG', '[newsrss]');
define('NMRSS_POSTS_IN_FEED', 10);

$thisfile = basename(__FILE__, ".php");
register_plugin(
    $thisfile, //Plugin id
    'News Manager RSS',  //Plugin name
    '1.0',      //Plugin version
    'Reed Murphy',  //Plugin author
    'http://www.reedmurphy.net/', //author website
    '', //Plugin description
    'pages', //page type - on which admin tab to display
    'news_manager_rss_admin'  //main function (administration)
);

add_filter('content', 'news_manager_rss_filter');
add_action('pages-sidebar','createSideMenu',array($thisfile,'News Manager RSS Settings'));

require_once('news_manager/inc/common.php');

function news_manager_rss_admin() {
    echo "<p>Eventually, options go here.</p>";
}

function news_manager_rss_filter($content) {
    if (strpos($content, NMRSS_TAG) !== FALSE) {
       $content = render_rss_feed();
    }
    return $content;
}

function render_rss_feed() {
    $rss .= "<rss version=\"2.0\">\n";
    $rss .= "<channel>\n";

    $posts = nm_get_posts();
    $pages = array_chunk($posts, NMRSS_POSTS_IN_FEED, true);
    $posts = $pages[0];

    if (!empty($posts)) {
        foreach ($posts as $post) {
            $rss .= nmrss_render_post($post->slug);
        }
    }

    $rss .= "</channel>\n";
    $rss .= "</rss>\n";
    return $rss;
}

function nmrss_render_post($slug) {
    $file = NMPOSTPATH . "$slug.xml";
    $post = @getXML($file);
    if (!empty($post) && $post->private != 'Y') {
        $url     = nm_get_url('post') . $slug;
        $title   = strip_tags(strip_decode($post->title));
        $date    = nm_get_date(i18n_r('news_manager/DATE_FORMAT'), strtotime($post->date));
        $content = strip_decode($post->content);
    }

    $rss_item  = "<item>\n";
    $rss_item .= "<title>$title</title>\n";
    $rss_item .= "<link>$url</link>\n";
    $rss_item .= "<guid>$url</guid>\n";
    $rss_item .= "<pubDate>$date</pubDate>\n";
    $rss_item .= "<description><![CDATA[ $content ]]></description>\n";
    $rss_item .= "</item>\n";

    return $rss_item;
}
