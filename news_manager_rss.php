<?php
/*
Plugin Name: News Manager RSS
Description: Adds RSS functionality to News Manager
Version: 1.1.0
Author: Reed Murphy
Author URI: http://www.reedmurphy.net/
*/

define('NMRSS_CONFIG_XML', GSDATAOTHERPATH .'nmrss_config.xml');
define('FEEDBURNER_URL', "http://feeds.feedburner.com/");

$thisfile = basename(__FILE__, ".php");
register_plugin(
    $thisfile, //Plugin id
    'News Manager RSS',  //Plugin name
    '1.1.0',      //Plugin version
    'Reed Murphy',  //Plugin author
    'http://www.reedmurphy.net/', //author website
    '', //Plugin description
    'pages', //page type - on which admin tab to display
    'nmrss_admin'  //main function (administration)
);

add_filter('content', 'nmrss_filter');
add_action('pages-sidebar','createSideMenu',array($thisfile,'News Manager RSS Settings'));
add_action('theme-header', 'nmrss_header');

require_once(GSROOTPATH . 'plugins/news_manager/inc/common.php');

function nmrss_read_config() {
    $config = array();
    if (file_exists(NMRSS_CONFIG_XML)) {
        $x = getXML(NMRSS_CONFIG_XML);
        $config['post_count'] = (int)$x->post_count;
        $config['url'] = strval($x->url);
        $config['feedburner_id'] = strval($x->feedburner_id);
    } else {
        # Default config options
        $config['post_count'] = 10;
        $config['url'] = 'rss';
        $config['feedburner_id'] = '';
    }
    return $config;
}

function nmrss_write_config($config) {
    $xml = @new SimpleXMLElement('<item></item>');
    $xml->addChild('post_count', $config['post_count']);
    $xml->addChild('url', $config['url']);
    $xml->addChild('feedburner_id', $config['feedburner_id']);

    return $xml->asXML(NMRSS_CONFIG_XML);
}

function nmrss_admin() {
    $config = nmrss_read_config();
    $error = "";
    $success = "";

	if (isset($_POST['submit'])) {
		if ($_POST['post_count'] != '') {
			if (is_numeric($_POST['post_count']) && (int)$_POST['post_count'] == $_POST['post_count'] and (int)$_POST['post_count'] > 0) {
				$config['post_count'] = $_POST['post_count'];
			} else {
				$error .= 'Post count must be a whole number.';
			}
		}

        $config['url'] = $_POST['url'];
        $config['feedburner_id'] = $_POST['feedburner_id'];
		
		if ($error == "") {
			if (! nmrss_write_config($config)) {
				$error = i18n_r('CHMOD_ERROR');
			} else {
                $config = nmrss_read_config();
				$success = i18n_r('SETTINGS_UPDATED');
			}
		}
	}
	?>
	<h3>News Manager RSS Settings</h3>
	
	<?php 
	if($success) { 
		echo '<p style="color:#669933;"><b>'. $success .'</b></p>';
	} 
	if($error) { 
		echo '<p style="color:#cc0000;"><b>'. $error .'</b></p>';
	}
	?>
	
	<form method="post" action="<?php echo $_SERVER ['REQUEST_URI']?>">
        <p>
            <label for="nmrss_url">Page to display RSS feed</label>
            <select name="url" id="nmrss_url">
<?php
    $pages = get_available_pages();
    foreach ($pages as $page) {
        $slug = $page['slug'];
        if ($slug == $config['url']) {
            echo "<option value=\"$slug\" selected=\"selected\">$slug</option>\n";
        } else {
            echo "<option value=\"$slug\">$slug</option>\n";
        }
    }
?>
            </select>
        </p>
		<p><label for="nmrss_post_count" >Number of posts to display in feed</label><input id="nmrss_post_count" name="post_count" class="text" value="<?php echo $config['post_count']; ?>" /></p>
		<p><label for="nmrss_feedburner_id" ><em>Optional</em>: <a href="http://feedburner.google.com">FeedBurner</a> Feed ID</label><input id="nmrss_feedburner_id" name="feedburner_id" class="text" value="<?php echo $config['feedburner_id']; ?>" /></p>
		<p><input type="submit" id="submit" class="submit" value="<?php i18n('BTN_SAVESETTINGS'); ?>" name="submit" /></p>
	</form>
	
	<?php
}

function nmrss_header() {
    global $SITEURL;
    $config = nmrss_read_config();
    $url = find_url($config['url'], '');
    echo '<link rel="alternate" type="application/rss+xml" title="RSS Feed" href="'. $url ."\" />\n";
}

function nmrss_filter($content) {
    $config = nmrss_read_config();
    if ($config['url'] == get_page_slug(false)) {
        if ($config['feedburner_id'] != '') {
            if (!preg_match("/FeedBurner/", $_SERVER['HTTP_USER_AGENT'])) {
                redirect(FEEDBURNER_URL . $config['feedburner_id']);
                exit;
            }
        }
        $content = nmrss_render_feed();
    }
    return $content;
}

function nmrss_render_feed() {
    $config = nmrss_read_config();
    $posts = nm_get_posts();
    $pages = array_chunk($posts, $config['post_count'], true);
    $posts = $pages[0];

    $rss = "";

    if (!empty($posts)) {
        foreach ($posts as $post) {
            $rss .= nmrss_render_post($post->slug);
        }
    }
    return $rss;
}

function nmrss_render_post($slug) {
    $rss_item = "";
    $file = NMPOSTPATH . "$slug.xml";
    $post = @getXML($file);
    if (!empty($post) && $post->private != 'Y') {
        $url     = htmlspecialchars(nm_get_url('post') . $slug, ENT_QUOTES, 'UTF-8');
        $title   = htmlspecialchars(html_entity_decode($post->title, ENT_QUOTES, 'UTF-8'), ENT_QUOTES, 'UTF-8');
        $date    = date("D, d M Y H:i:s O", strtotime($post->date));
        $content = $post->content;
        $content = str_replace("]]>", "]]]]><![CDATA[>", html_entity_decode(safe_strip_decode($content), ENT_QUOTES, 'UTF-8'));

        $rss_item .= "\t\t\t<item>\n";
        $rss_item .= "\t\t\t\t<title>$title</title>\n";
        $rss_item .= "\t\t\t\t<link>$url</link>\n";
        $rss_item .= "\t\t\t\t<guid>$url</guid>\n";
        $rss_item .= "\t\t\t\t<pubDate>$date</pubDate>\n";
        $rss_item .= "\t\t\t\t<description><![CDATA[ $content ]]></description>\n";
        $rss_item .= "\t\t\t</item>\n";
    }

    return $rss_item;
}
