<?php 

    class gitsyllabus_oauth {

        const OAUTH_URL = 'https://github.com/login/oauth/';

        function __construct($client_id, $client_secret) {
            //create oauth object and store developer key
            $this->client_id = $client_id;
            $this->client_secret = $client_secret;            
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

        function oauth_validate($oauth_code) {
            $args = array(
                'body' => array(
                    'client_id' => $this->client_id,
                    'client_secret' => $this->client_secret,
                    'code' => $oauth_code
                ),
                'headers' => array( 'Accept' => 'application/json')
            );
            $response = wp_remote_post( gitsyllabus_oauth::OAUTH_URL . 'access_token', $args );

            if ( is_wp_error( $response ) || $response['response']['code'] >= 400 ) {
                echo 'oauth validation failed';
                return "";
            }

            else {
                $body = json_decode($response['body']);
                return $body->access_token;
            }

        }
    }
?>