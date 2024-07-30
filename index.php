<?php
/*
Plugin Name: WP Youtube Feed
Plugin URI: https://github.com/fulltimeforce/wp-youtube-feed
Description: Plugin to get YouTube Feed
Version: 0.0.1
Author: FullTimeForce
Author URI: https://www.fulltimeforce.com/
*/

require_once dirname(__FILE__).'/classes/shortcode.class.php';

function activate(){
    global $wpdb;

    $sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}yt_feed(
        `feed_id` INT NOT NULL AUTO_INCREMENT,
        `name` VARCHAR(255) NULL,
        `channel_id` VARCHAR(255) NULL,
        `google_key` TEXT NULL,
        `shortcode` VARCHAR(255) NULL,
        `hex` VARCHAR(8) NULL,
        PRIMARY KEY (`feed_id`)  
    );";

    $wpdb->query($sql);
}

function desactivate(){
    flush_rewrite_rules();
}

register_activation_hook(__FILE__,'activate');
register_deactivation_hook(__FILE__,'desactivate');


add_action('admin_menu', 'create_menu');
function create_menu(){
    add_menu_page(
        'Youtube Feed', //page title
        'Youtube Feed', //menu title
        'manage_options', //capability
        plugin_dir_path(__FILE__).'admin/content.php', //slug
        null, //function to show content in admin page
        plugin_dir_url(__FILE__).'admin/img/icon.png',
        '1'
    );
}


function reg_bootstrap($hook){ //register bootstrap
    if($hook != 'wp-youtube-feed/admin/content.php'){
        return;
    }

    wp_enqueue_script('bootstrapjs', plugins_url('admin/bootstrap/js/bootstrap.min.js', __FILE__, array('jquery')));
    wp_enqueue_script('ytadminfeedjs', plugins_url('admin/js/yt_admin_feed.js', __FILE__, array('jquery')));
    wp_enqueue_style('bootstrapcss', plugins_url('admin/bootstrap/css/bootstrap.min.css', __FILE__));

    wp_localize_script('ytadminfeedjs', 'solicitudAjax', [
        'url' => admin_url('admin-ajax.php'),
        'security' => wp_create_nonce('sec')
    ]);
}
add_action('admin_enqueue_scripts', 'reg_bootstrap');

function reg_scripts($hook){ //register bootstrap
    
    wp_enqueue_script('ytfrontfeedjs', plugins_url('frontend/js/yt_front_feed.js', __FILE__, array('jquery')));
    wp_enqueue_style('ytfrontfeedcss', plugins_url('frontend/css/yt_front_feed.css', __FILE__));

    wp_localize_script('ytfrontfeedjs', 'solicitudAjax', [
        'url' => admin_url('admin-ajax.php'),
        'security' => wp_create_nonce('sec2')
    ]);
}
add_action('wp_enqueue_scripts', 'reg_scripts');


function delete_feed(){
    $nonce = $_POST['nonce'];
    if(!wp_verify_nonce($nonce, 'sec')){
        die('No tiene permisos para ejecutar esa petición.');  
    }

    $id = $_POST['id'];
    
    global $wpdb;
    $table = "{$wpdb->prefix}yt_feed";

    $wpdb->delete($table, array('feed_id' => $id));
    return true;
}
add_action('wp_ajax_deleteFeedPetition', 'delete_feed');


function frm_yt_feed_shortcode($atts){
    $_short = new Shortcode;
    $hex = $atts['id'];
    
    $response = $_short->calling_posts($hex);
    return $response;
}
add_shortcode('FRM_YT_FEED', 'frm_yt_feed_shortcode');




add_action('wp_ajax_load_more_posts', 'load_more_posts');
add_action('wp_ajax_nopriv_load_more_posts', 'load_more_posts');

function load_more_posts() {
    $nonce = $_POST['nonce'];
    if(!wp_verify_nonce($nonce, 'sec2')){
        die('No tiene permisos para ejecutar esa petición.');  
    }

    $channel_id = $_POST['channel_id'];
    $google_key = $_POST['google_key'];
    $next_page_token = $_POST['next_page_token'];

    $nro_post_to_show = 9;

    $url = "https://www.googleapis.com/youtube/v3/playlistItems?part=snippet,contentDetails&maxResults=$nro_post_to_show&playlistId=$channel_id&key=$google_key&pageToken=$next_page_token";

    $response = file_get_contents($url);

    if ($response !== FALSE) {
        $data = json_decode($response, true);
        ob_start();
        (new Shortcode())->get_posts($data, 2);
        $content = ob_get_clean();

        // Verificar si hay un nextPageToken en la respuesta
        $has_more_posts = isset($data['nextPageToken']);

        wp_send_json_success([
            'content' => $content,
            'has_more_posts' => $has_more_posts,
            'next_page_token' => $data['nextPageToken'] ?? ''
        ]);
        // wp_send_json_success($content);

    } else {
        wp_send_json_error('Error al realizar la solicitud');
    }
}