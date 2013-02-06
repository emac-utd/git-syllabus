<?php
/*
Plugin Name: GitSyllabus
Plugin URI: http://emac-utd.github.com/git-syllabus
Description: A tool to move syllabus data from WordPress to GitHub 
Version: 0.1
Author: EMAClab
Author URI: http://github.com/emac-utd
License: MIT
*/

//need to define action upon install


if( !class_exists( 'WP_Http' ) )
    include_once( ABSPATH . WPINC. '/class-http.php' );

require_once('config.php');
require_once('github_api.php');

//Need to define init function
register_activation_hook( __FILE__, 'gitsyllabus_init' );


//Meta box functions
add_action('add_meta_boxes', 'gitsyllabus_meta');
add_action('save_post', 'gitsyllabus_meta_save');

function gitsyllabus_meta(){

    add_meta_box( 'gitsyllabus_publish', //ID
        __('GitSyllabus Publish', 'gitsyllabus_textdomain'), //Title
        __('gitsyllabus_box_publish', 'gitsyllabus_textdomain'), //Rendering function
        'post', //Post type
        'side'); //Location

    add_meta_box( 'gitsyllabus_publish', //ID
        __('GitSyllabus Publish', 'gitsyllabus_textdomain'), //Title
        __('gitsyllabus_box_publish', 'gitsyllabus_textdomain'), //Rendering function
        'page', //Post type
        'side'); //Location

}

//Render meta box
function gitsyllabus_box_publish(){

    global $post;

    wp_nonce_field( plugin_basename(__FILE__), 'gitsyllabus_nonce' );

    echo '<label for="gs_publish">' . __("Publish to GitHub", 'gitsyllabus_textdomain' ) . '</label> ';
    if(get_post_meta($post->ID,'gs_publish',true) == 'publish')
    {
        echo '<input type="checkbox" id="gs_publish" name="gs_publish" value="publish" checked="checked" />';
    }
    else
    {
        echo '<input type="checkbox" id="gs_publish" name="gs_publish" value="publish" />';
    }

}

//Handle meta box input
function gitsyllabus_meta_save($post_id){

    if ( !wp_verify_nonce( $_POST['gitsyllabus_nonce'], plugin_basename(__FILE__) )) {
        return $post_id;
    }
    
    if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) 
        return $post_id;

    if ( !current_user_can( 'edit_post', $post_id ) )
        return $post_id;

    update_post_meta($post_id,'gs_publish',$_POST['gs_publish']);

    return $_POST['gs_publish'];
}

//Settings initialization
add_action('admin_init', 'gitsyllabus_options_init');

function gitsyllabus_options_init(){

    register_setting( 'gitsyllabus_options', 'enable' );

}

//Create settings page
//BUG: Getting function not found
//add_submenu_page( 'options-general.php', 'GitSyllabus Settings', 'GitSyllabus', 'manage_options', 'git-syllabus', $function = 'gitsyllabus_options_page' );

function gitsyllabus_options_page(){
    //Add Github linkup here
    //Need to get application token and API token. GitHub webflow says to 

    //currently storing data more globally, but can attach these as user_meta
    
    add_option($client_id, $value);
    add_option( $client_secret, $value);
    //$oauth = new gitsyllabus_oauth($client_id, $client_secret); 
    add_option($oauth_token, $value);
    
    ?>

    <?php
}

?>

<?php 
    


    function sync_with_github()  {
        global $post;
        if ( $github->$has_repo ) {
            $github->commit($post);
            return $post->$post_id;
        }
    }

//generate state to send to github so that requests can be verified.
    
    function generate_state() {

        return substr(md5(microtime()),rand(0,26),5);

    }

?>

