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

//Syllabus custom post type
add_action( 'init', 'create_syllabus_type' );
function create_syllabus_type() {
    register_post_type( 'gitsyllabus_syllabus',
        array(
            'labels' => array(
                'name' => __( 'Syllabi' ),
                'singular_name' => __( 'Syllabus' ),
                'all_items' => __( 'All Syllabi' ),
                'add_new_item' => __( 'Add Syllabus' ),
                'edit_item' => __( 'Edit Syllabus' ),
                'new_item' => __( 'New Syllabus' ),
                'view_item' => __( 'View Syllabus' ),
                'search_items' => __( 'Search Syllabi' ),
                'not_found' => __( 'No syllabi found' ),
                'not_found_in_trash' => __( 'No syllabi found in trash')
            ),
        'public' => true,
        'has_archive' => false,
        'show_ui' => true,
        'supports' => array(
                'title',
                'revisions'
            ),
        'register_meta_box_cb' => 'gitsyllabus_syllabus_post_meta_boxes'
        )
    );
}

//Meta box functions
function gitsyllabus_syllabus_post_meta_boxes()
{

}

//UI scripts
function gitsyllabus_enqueue($hook) {
    //if( 'edit.php' != $hook )
    //    return;
    wp_enqueue_script( 'jquery-ui-datepicker' );
    wp_enqueue_style( 'jquery-ui-datepicker-style', 'http://code.jquery.com/ui/1.10.2/themes/smoothness/jquery-ui.css' );
}
add_action( 'admin_enqueue_scripts', 'gitsyllabus_enqueue' );

add_action('add_meta_boxes', 'gitsyllabus_meta');
add_action('save_post', 'gitsyllabus_meta_save');

function gitsyllabus_meta(){

    add_meta_box( 'gitsyllabus_publish', //ID
        __('GitSyllabus Publish', 'gitsyllabus_textdomain'), //Title
        'gitsyllabus_box_publish', //Rendering function
        'post', //Post type
        'side'); //Location

    add_meta_box( 'gitsyllabus_publish', //ID
        __('GitSyllabus Publish', 'gitsyllabus_textdomain'), //Title
        'gitsyllabus_box_publish', //Rendering function
        'page', //Post type
        'side'); //Location

    add_meta_box( 'gitsyllabus_metadata', //ID
        __('GitSyllabus Metadata', 'gitsyllabus_textdomain'), //Title
        'gitsyllabus_box_data', //Rendering function
        'gitsyllabus_syllabus', //Post type
        'advanced'); //Location
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
            echo '<input type="checkbox" id="gs_publish" name="gs_publish" value="publish" checked="checked" /><br>';
        }
        else
        {
            echo '<input type="checkbox" id="gs_publish" name="gs_publish" value="publish" /><br>';

        }
         get_syllabus_list();

         get_checkboxes();

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

    update_post_meta($post_id,'gs_page_contains',$_POST['gs_page_contains']);
    update_post_meta($post_id,'gs_syllabus_dropdown',$_POST['gs_syllabus_dropdown']);
    update_post_meta($post_id,'gs_publish',$_POST['gs_publish']);
    update_post_meta($post_id,'gs_title',$_POST['gs_title']);
    update_post_meta($post_id,'gs_instructor',$_POST['gs_instructor']);
    update_post_meta($post_id,'gs_discipline',$_POST['gs_discipline']);
    update_post_meta($post_id,'gs_taught',$_POST['gs_taught']);
    update_post_meta($post_id,'gs_level',$_POST['gs_level']);
    update_post_meta($post_id,'gs_startdate',$_POST['gs_startdate']);
    update_post_meta($post_id,'gs_enddate',$_POST['gs_enddate']);

    return $_POST['gs_publish'];
}

//Metadata box render
function gitsyllabus_box_data()
{
    wp_nonce_field( plugin_basename(__FILE__), 'gitsyllabus_nonce' );

    global $post;

    ?>
        <p>
            <label>Title</label><br />
            <input type='text' name='gs_title' value='<?php echo get_post_meta($post->ID, 'gs_title', true); ?>' />
        </p>
        <p>
            <label>Instructor</label><br />
            <input type='text' name='gs_instructor' value='<?php echo get_post_meta($post->ID, 'gs_instructor', true); ?>' />
        </p>
        <p>
            <label>Discipline/Subjects</label><br />
            <input type='text' name='gs_discipline' value='<?php echo get_post_meta($post->ID, 'gs_discipline', true); ?>' />
        </p>
        <p>
            <label>Authors read or referenced</label><br />
            <input type='text' name='gs_taught' value='<?php echo get_post_meta($post->ID, 'gs_taught', true); ?>' />
        </p>
        <p>
            <label>Level</label><br />
            <input type='text' name='gs_level' value='<?php echo get_post_meta($post->ID, 'gs_level', true); ?>' />
        </p>
        <p>
            <label>Start Date</label><br />
            <input type='text' class='datepicker' name='gs_startdate' value='<?php echo get_post_meta($post->ID, 'gs_startdate', true); ?>' />
        </p>
        <p>
            <label>End Date</label><br />
            <input type='text' class='datepicker' name='gs_enddate' value='<?php echo get_post_meta($post->ID, 'gs_enddate', true); ?>' />
        </p>
        <script type="text/javascript">jQuery(function(){jQuery('.datepicker').datepicker()});</script>
    <?
}

//Settings initialization
add_action('admin_init', 'gitsyllabus_options_init');

function gitsyllabus_options_init(){
    //Add setting entry in database
    register_setting( 'gitsyllabus_options', 'gitsyllabus_options' ); 

    //Create section group for setup
    add_settings_section( 'gitsyllabus_options_setup', __('GitSyllabus Setup'), 'gitsyllabus_display_setup', 'git-syllabus');

    //Setup fields
    add_settings_field('gitsyllabus_consumerkey', __('GitHub Client ID'), 'gitsyllabus_display_consumerkey', 'git-syllabus', 'gitsyllabus_options_setup');
    add_settings_field('gitsyllabus_consumersecret', __('GitHub Client Secret'), 'gitsyllabus_display_consumersecret', 'git-syllabus', 'gitsyllabus_options_setup');
    add_settings_field('gitsyllabus_authkey', __('GitHub OAuth token'), 'gitsyllabus_display_authkey', 'git-syllabus', 'gitsyllabus_options_setup');
    add_settings_field('gitsyllabus_repo_name', __('GitHub Repository Name'), 'gitsyllabus_display_repo_name', 'git-syllabus', 'gitsyllabus_options_setup');

    //Create section group for setup
    add_settings_section( 'gitsyllabus_options_meta', __('Metadata'), 'gitsyllabus_display_metadata', 'git-syllabus');

    //Setup fields
    add_settings_field('gitsyllabus_instructor', __('Instructor'), 'gitsyllabus_display_instructor', 'git-syllabus', 'gitsyllabus_options_meta');
    add_settings_field('gitsyllabus_discipline', __('Discipline'), 'gitsyllabus_display_discipline', 'git-syllabus', 'gitsyllabus_options_meta');
    add_settings_field('gitsyllabus_institution', __('Institution'), 'gitsyllabus_display_institution', 'git-syllabus', 'gitsyllabus_options_meta');
    add_settings_field('gitsyllabus_concentration', __('Concentration'), 'gitsyllabus_display_concentration', 'git-syllabus', 'gitsyllabus_options_meta');

}
function gitsyllabus_display_setup(){
    ?>
    <?php
}

function gitsyllabus_display_metadata(){
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
    if($options['gitsyllabus_authkey'])
    {
        echo "Key saved";
    }
    else
    {
        echo "No key saved";
    }

    echo "<input type='hidden' id='gitsyllabus_authkey' name='gitsyllabus_options[gitsyllabus_authkey]' size='7' value='{$options['gitsyllabus_authkey']}' /><br />";

}

function gitsyllabus_display_repo_name()
{
    $options = get_option('gitsyllabus_options');

    echo "<input type='text' id='gitsyllabus_repo_name' name='gitsyllabus_options[gitsyllabus_repo_name]' size='7' value='{$options['gitsyllabus_repo_name']}' /><br />";

}

function gitsyllabus_display_instructor(){

    $options = get_option('gitsyllabus_options');

    echo "<input type='text' id='gitsyllabus_instructor' name='gitsyllabus_options[instructor]' size='7' value='{$options['instructor']}' /><br />";
    
}

function gitsyllabus_display_discipline(){

    $options = get_option('gitsyllabus_options');

    echo "<input type='text' id='gitsyllabus_discipline' name='gitsyllabus_options[discipline]' size='7' value='{$options['discipline']}' /><br />";
    
}

function gitsyllabus_display_institution(){

    $options = get_option('gitsyllabus_options');

    echo "<input type='text' id='gitsyllabus_institution' name='gitsyllabus_options[institution]' size='7' value='{$options['institution']}' /><br />";
    
}

function gitsyllabus_display_concentration(){

    $options = get_option('gitsyllabus_options');

    echo "<input type='text' id='gitsyllabus_concentration' name='gitsyllabus_options[concentration]' size='7' value='{$options['concentration']}' /><br />";
    
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
        }
    ?>

        <form action="options.php" method="post">
            <h2>How to set up GitSyllabus:</h2>
            
            <?php 
                if($options['gitsyllabus_consumerkey'] && $options['gitsyllabus_consumersecret'])
                {
                    $redirect = urlencode($currentUrl);

                    update_option('gitsyllabus_state', generate_state());
                    if($options['gitsyllabus_authkey'])
                    {
                        echo "<p>If GitSyllabus is unable to edit your repository, try refreshing the token here:</p>";
                        echo "<h3><a href='https://github.com/login/oauth/authorize?".
                        "client_id=$options[gitsyllabus_consumerkey]".
                        "&scope=repo".
                        "&state=".get_option('gitsyllabus_state').
                        "&redirect_uri=$redirect'>Refresh token</a></h3>";
                    }
                    else
                    {
                        echo "<p>Now that you've created your application, you can authorize your account and start pushing posts to Github:</p>";
                        echo "<h3><a href='https://github.com/login/oauth/authorize?".
                        "client_id=$options[gitsyllabus_consumerkey]".
                        "&scope=repo".
                        "&state=".get_option('gitsyllabus_state').
                        "&redirect_uri=$redirect'>Get token</a></h3>";
                    }
                    
                }
                else
                {
                    echo "<p>First, you need to go into your Github settings and <a title='Create Github application' href='https://github.com/settings/applications/new'>create an application</a>.
                            Copy and paste the resulting Client ID and Client Secret into the fields below.</p>";
                }
            ?>

            <?php settings_fields('gitsyllabus_options'); ?>
            <?php do_settings_sections('git-syllabus'); ?>
            
            <input name="Submit" type="submit" value="<?php esc_attr_e('Save Changes'); ?>" />
        </form>

    <?php

        
        
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
            $github = new github_api($options['gitsyllabus_authkey'], $options['gitsyllabus_repo_name']);
            if(!$github->check_auth())
            {
                echo "<div class='error'><p>Your current Github authorization is invalid.  <a title='GitSyllabus Settings' href='options-general.php?page=git-syllabus'>Please log in again</a>.</p></div>";
            }
        }
    }
}
    function sync_with_github($post_id)  {

        $options = get_option('gitsyllabus_options');
        $github = new github_api($options['gitsyllabus_authkey'], $options['gitsyllabus_repo_name']);

        $post = get_post($post_id);

        if(get_post_meta($post->ID,'gs_publish',true) == 'publish')
        {
            $github->commit_post($post);
            return $post->post_id;
        }

    }

//generate state to send to github so that requests can be verified.
    
    function generate_state() {

        return substr(md5(microtime()),rand(0,26),5);

    }

    function get_syllabus_list() {
        global $post;

        $args = array(
            'post_type' => 'gitsyllabus_syllabus'
            );

        $syllabus_posts = get_posts($args);

        if(count($syllabus_posts) > 0)
        {
            $dropdown_template = "<option %s value=%s>%s</option>";

            echo "<select name='gs_syllabus_dropdown' id='gs_syllabus_dropdown'>";
            $saved_syllabus = get_post_meta( $post->ID, 'gs_syllabus_dropdown', true);
            foreach ($syllabus_posts as $syllabus) :
                
                if ($saved_syllabus == $syllabus->ID)
                    $selected = "selected='selected'";
                else
                    $selected = '';

                echo sprintf($dropdown_template, $selected, $syllabus->ID,get_the_title($syllabus->ID));

            endforeach;
            echo "</select>";
        }

        else
        {
            echo "You must create a syllabus before publishing to GitHub with accompanying syllabus data.";
        }        
    }

    function get_checkboxes() {
        global $post;

        echo '<h4>Page contains:</h4>';

        $types = array('Schedule', 'Assignment', 'Description');

        $checked = get_post_meta( $post->ID, 'gs_page_contains', true);

        $checkbox_template = "<input type='checkbox' name='gs_page_contains[]' id='%s' value='%s' %s /><br>";

        foreach ($types as $type) {
            if (is_array($checked) && in_array($type, $checked))
                $checkvalue = "checked='checked'";
            else
                $checkvalue = '';

            echo '<label for"' . $type .'">' . __($type, 'gitsyllabus_textdomain' ) . '</label>';

            echo sprintf($checkbox_template, $type, $type, $checkvalue);

        }
        
    }

    function generate_meta_file() {
        $options = get_option('gitsyllabus_options');

        $meta_file = array(
            'instructor' => $options['instructor'],
            'discipline' => $options['discipline'],
            'institution' => $options['institution'],
            'concentration' => $options['concentration'],
        );

        $options['meta_file'] = json_encode($meta_file);

        update_option('gitsyllabus_options', $options);
    }

    function sync_meta_file() {
        $options = get_option('gitsyllabus_options');
        $github = new github_api($options['gitsyllabus_authkey'], $options['gitsyllabus_repo_name']);

        $github->commit_metafile($options['meta_file']);
    }

    function check_metadata($old, $new) {
        if (($old['gitsyllabus_authkey'] != $new['gitsyllabus_authkey']
            || $old['gitsyllabus_repo_name'] != $new['gitsyllabus_repo_name']
            || $old['instructor'] != $new['instructor']
            || $old['discipline'] != $new['discipline']
            || $old['institution'] != $new['institution']
            || $old['concentration'] != $new['concentration'])
            && $new['gitsyllabus_repo_name']
            && $new['gitsyllabus_authkey']) { //Need authkey and repo_name to commit

            generate_meta_file();
            sync_meta_file();
            
        }

    }

    add_action('update_option_gitsyllabus_options', 'check_metadata', 10, 2);

?>
