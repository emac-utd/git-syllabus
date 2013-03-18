<?php
    add_action( 'publish_post', $github);
    class github_api {

        const API_URL = 'https://api.github.com/';


        function __construct($oauth_token) { 
            $this->oauth_token = $oauth_token;
            $this->url = github_api::API_URL . $oauth_token . '/';
            get_user_data();
            $this->repo_name = 'wordpress';
            
        }

        function get_user_data() {
            $response = wp_remote_get( $url . '/user');


            if ( is_wp_error( $response ) ) {
                echo 'get_user_data effed up';
            }
            else {
                $this->$owner = $response['body']['login'];
                $this->$type = $response['body']['type'];

                if ($this->$type != 'User') {
                    $this->is_org = true;
                }
            }  

        }

        function create_repo($name){
            //TODO: build in a verification that the repo doesn't exist yet

            //create
            $this->$repo_name = $name;

            //add conditional for creating for org? (different pathway)
            $args = array('name' => $name );

            $response = wp_remote_post( $url . 'user/repos', $args );

            if (is_wp_error( $response )) {
                echo 'repo creation effed up';
            }
            else {
                $has_repo = true;
                //right now just returning the url of the newly created repo.
                return $response['body']['url'];
            }

        }

        function access_existing_repo($name) {

        }

        function commit($post) {
            $content = $post['post_content'];
            $file_name = $post['post_title'];
            $git_url = $this->$url . 'repos/' . $this->$owner . '/' . $this->$repo_name . '/git/';

            $args = array(
                'headers' => array( 'Accept' => 'application/json')
            );

            $response = wp_remote_get( $git_url . 'refs/heads/master', $args);


            //get sha from latest commit

            if ( is_wp_error( $response ) || $response['response']['code'] >= 400 ) {
                echo 'getting latest sha failed';
                return "";
            }

            else {
                $body = json_decode($response['body']);
                $sha_latest_commit = $body->object->sha;
            }

            $response = wp_remote_get( $git_url . 'commits/' . $sha_latest_commit, $args);

            //get $sha_base_tree

            if ( is_wp_error( $response ) || $response['response']['code'] >= 400 ) {
                echo 'getting sha tree failed';
                return "";
            }

            else {
                $body = json_decode($response['body']);
                $sha_base_tree = $body->tree->sha;
            }

           $args = array(
               'body' => array(
                   'base_tree' => $sha_base_tree,
                   'tree' => array(
                        'path' => $file_name, //need to define this
                        'content' => $content
                    )

               ),
               'headers' => array( 'Accept' => 'application/json')
           );     


            $response = wp_remote_post( $git_url . '/trees', $args  );


            if ( is_wp_error( $response ) || $response['response']['code'] >= 400 ) {
                echo 'creating tree failed';
                return "";
            }

            else {
                $body = json_decode($response['body']);
                $sha_new_tree = $body->sha;
            }

            $args = array(
                'body' => array(
                    'base_tree' => $sha_base_tree,
                    'parents' => array( $sha_latest_commit ),
                    'tree' => $sha_new_tree

                ),
                'headers' => array( 'Accept' => 'application/json')
            ); 

            $response = wp_remote_post( $git_url . 'commits', $args );

            if ( is_wp_error( $response ) || $response['response']['code'] >= 400 ) {
                echo 'creating commit failed';
                return "";
            }

            else {
                $body = json_decode($response['body']);
                $sha_new_commit = $body->sha;
            }

            $args = array(
                'body' => array(
                    'sha' => $sha_new_commit
                ),
                'headers' => array( 'Accept' => 'application/json')
            ); 


            $response = wp_remote_post( $url . 'refs/head/master', $args );

            if ( is_wp_error( $response ) || $response['response']['code'] >= 400 ) {
                echo 'creating reference failed';
                return "";
            }

            else {
                $body = json_decode($response['body']);
                echo $body;
            }

        }

        function get_repos()
        {
            $args = array('headers' => array('Accept' => 'application/json'));
            $response = wp_remote_get(github_api::API_URL . 'user/repos?access_token=' . $oauth_token, $args);

            if ( is_wp_error( $response ) || $response['response']['code'] >= 400 ) {
                echo 'Get repos failed';
                return "";
            }

            else {
                return json_decode($response['body']);
            }

        }

        function pull () {

        }

        function get_repo_status() {
            //Need to figure out how deep to make this. Eventual thought is sync.
        }
    }

?>