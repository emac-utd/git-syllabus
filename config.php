<?php 
    
    // function github_oauth_retrieval() {
    //     wp_redirect( 'https://github.com/login/oauth', $status = 302 );
    // }

    class gitsyllabus_oauth {

        const OAUTH_URL = 'https://github.com/login/oauth/';

        function __construct($client_id, $client_secret) {
            //create oauth object and store developer key
            $this->$client_id = $client_id;
            $this->$client_secret = $client_secret;            
        }

        function oauth_init() {
            $args = array(
                'client_id' => $client_id,
                'state' => $state,
                'status' => 302
            );

            $response = wp_remote_get( wp_redirect( ( OAUTH_URL . 'authorize' ), $args) ); 

            if ( is_wp_error( $response ) ) {
                echo 'oauth init effed up';
            }

            else {
                //where to parse out code response?
                // $this->$oauth_code = $response

            }

        }

        function oauth_validate() {
            $args = array(
                'client_id' => $client_id,
                'client_secret' => $client_secret,
                'state' => $state,
                'code' => $code
            );

            $response = wp_remote_post( OAUTH_URL . 'access_token', $args );


            if ( is_wp_error( $response ) ) {
                echo 'oauth validation effed up';
            }

            else {
                //where to parse out code response?
                // $this->$oauth_code = $response

            }

        }
    }
?>