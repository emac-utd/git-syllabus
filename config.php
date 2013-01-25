<?php 

    function github_oauth_retrieval() {
        wp_redirect( 'https://github.com/login/oauth', $status = 302 );
    }


    class gitsyllabus_oauth {

        $oauth_client_id = '';
        $oauth_client_secret = '';

        const OAUTH_URL = 'https://github.com/login/oauth/';

        function __construct() {
            //create oauth object and store developer key
        }

        function oauth_init() {

        }

        function oauth_validate() {

        }
    }
?>