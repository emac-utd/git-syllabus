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
    //Need to get application token and API token. 

    //currently storing data more globally, but can attach these as user_meta
    add_option($repo_name, $value);
    add_option($dev_key, $value);
    add_option($oauth_token, $value);
    
    ?>

    <?php
}

?>


//functions for OAuth
<?php 
    //creating objects to instantiate as of right now but will likely change to straight functions

    class gitsyllabus_oauth {

        function __construct() {
            //create oauth object and store developer key
        }

        function oauth_init() {

        }

        function oauth_validate() {

        }
    }
?>

//functions for GitHub API calls

<?php
    add_action( 'publish_post', $github);
    class github_api {

        function __construct() {

        }

        function create_repo(){

        }

        function commit() {

        }

        function pull () {

        }

        function get_repo_status() {

        }
    }

?>