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

    $options = get_option('gitsyllabus_options');

    if($options['gitsyllabus_authkey'])
    {
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
    //Add setting entry in database
    register_setting( 'gitsyllabus_options', 'gitsyllabus_options' ); 

    //Create section group for setup
    add_settings_section( 'gitsyllabus_options_setup', __('GitSyllabus Setup'), 'gitsyllabus_display_setup', 'git-syllabus');

    //Setup fields
    add_settings_field('gitsyllabus_consumerkey', __('GitHub consumer key'), 'gitsyllabus_display_consumerkey', 'git-syllabus', 'gitsyllabus_options_setup');
    add_settings_field('gitsyllabus_consumersecret', __('GitHub consumer secret'), 'gitsyllabus_display_consumersecret', 'git-syllabus', 'gitsyllabus_options_setup');
    add_settings_field('gitsyllabus_authkey', __('GitHub OAuth token'), 'gitsyllabus_display_authkey', 'git-syllabus', 'gitsyllabus_options_setup');

}
function gitsyllabus_display_setup(){
    ?>
    <?php
}

function gitsyllabus_display_consumerkey(){

    $options = get_option('gitsyllabus_options');

    echo "<input type='text' id='gitsyllabus_consumerkey' name='gitsyllabus_options[gitsyllabus_consumerkey]' size='7' value='{$options['gitsyllabus_consumerkey']}' /><br />";
    
}

function gitsyllabus_display_consumersecret(){
    $options = get_option('gitsyllabus_options');

    echo "<input type='text' id='gitsyllabus_consumersecret' name='gitsyllabus_options[gitsyllabus_consumersecret]' size='7' value='{$options['gitsyllabus_consumersecret']}' /><br />";
    
}

function gitsyllabus_display_authkey(){
    $options = get_option('gitsyllabus_options');

    echo "<input type='text' id='gitsyllabus_authkey' name='gitsyllabus_options[gitsyllabus_authkey]' size='7' value='{$options['gitsyllabus_authkey']}' /><br />";
    
}

function gitsyllabus_display_authsecret(){
    $options = get_option('gitsyllabus_options');

    echo "<input type='text' id='gitsyllabus_authsecret' name='gitsyllabus_options[gitsyllabus_authsecret]' size='7' value='{$options['gitsyllabus_authsecret']}' /><br />";
    
}

add_action('admin_menu', 'gitsyllabus_init_menu');

function gitsyllabus_init_menu(){

    //Create settings page
    add_submenu_page( 'options-general.php', 'GitSyllabus Settings', 'GitSyllabus', 'manage_options', 'git-syllabus', $function = 'gitsyllabus_options_page' );

}

function gitsyllabus_options_page(){
    //Add Github linkup here
    //Need to get application token and API token. GitHub webflow says to 

    //currently storing data more globally, but can attach these as user_meta
    
    /*update_option($client_id, $value);
    update_option( $client_secret, $value);
    //$oauth = new gitsyllabus_oauth($client_id, $client_secret); 
    update_option($oauth_token, $value);
    update_option($)    */

        $protocol = strpos(strtolower($_SERVER['SERVER_PROTOCOL']),'https')
            === FALSE ? 'http' : 'https';
        $host     = $_SERVER['HTTP_HOST'];
        $script   = $_SERVER['SCRIPT_NAME'];
        $params   = $_SERVER['QUERY_STRING'];

        $currentUrl = $protocol . '://' . $host . $script . '?' . $params;

        $options = get_option('gitsyllabus_options');

        if($_GET['code'] && $_GET['state'] == get_option('gitsyllabus_state'))
        {
            $getToken = new gitsyllabus_oauth($options['gitsyllabus_consumerkey'], $options['gitsyllabus_consumersecret']);

            $token = $getToken->oauth_validate($_GET['code']);
            $options['gitsyllabus_authkey'] = $token;
            update_option('gitsyllabus_options', $options);
            echo "Token: $token";
        }
        else if($_GET['code'])
        {
            echo "CSRF attempt detected";
            echo "State should be ".get_option('gitsyllabus_state'); //Is showing as empty
        }
    ?>

        <form action="options.php" method="post">
            <?php settings_fields('gitsyllabus_options'); ?>
            <?php do_settings_sections('git-syllabus'); ?>
            
            <input name="Submit" type="submit" value="<?php esc_attr_e('Save Changes'); ?>" />
        </form>

    <?php

        
        if($options['gitsyllabus_consumerkey'] && $options['gitsyllabus_consumersecret'])
        {
            $redirect = urlencode($currentUrl);

            update_option('gitsyllabus_state', generate_state());
            echo "<a href='https://github.com/login/oauth/authorize?".
                "client_id=$options[gitsyllabus_consumerkey]".
                "&scope=repo".
                "&state=".get_option('gitsyllabus_state').
                "&redirect_uri=$redirect'>Login with Github</a>";
        }
}

add_action('publish_post', 'sync_with_github');

add_action('admin_notices', 'check_github_auth');

function check_github_auth()
{
    $is_post_editor = in_array($GLOBALS['pagenow'], array('post.php', 'post-new.php'));;
    if($is_post_editor)
    {
        $options = get_option('gitsyllabus_options');

        if(!$options['gitsyllabus_authkey'])
        {
            echo "<div class='error'><p>To publish to Github, you need to <a title='GitSyllabus Settings' href='options-general.php?page=git-syllabus'>add an authorization for your Github account</a>.</p></div>";
        }
        else
        {
            $github = new github_api($options['gitsyllabus_authkey']);
            if(!$github->check_auth())
            {
                echo "<div class='error'><p>Your current Github authorization is invalid.  <a title='GitSyllabus Settings' href='options-general.php?page=git-syllabus'>Please log in again</a>.</p></div>";
            }
        }
    }
}

?>

<?php 
    
    

    function sync_with_github($post_id)  {

        $options = get_option('gitsyllabus_options');
        $github = new github_api($options['gitsyllabus_authkey']);

        $post = get_post($post_id);

        if(get_post_meta($post->ID,'gs_publish',true) == 'publish')
        {
            $github->commit($post);
            return $post->post_id;
        }

    }

//generate state to send to github so that requests can be verified.
    
    function generate_state() {

        return substr(md5(microtime()),rand(0,26),5);

    }

?>

