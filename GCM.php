<?php
class GCM { 
    function __construct() {     
    } 
    /*--- Enviando notificaciones push ----*/ 
    public function send_notification($registatoin_ids, $message,$blog, $lugar) {
		
		// API access key from Google API's Console
		$API_ACCESS_KEY = 'AIzaSyB0Ec5CEte_BZQ5IyeEfEom1pXFp2aZ0Vw';
		
		$registrationIds = $registatoin_ids;
		
		
		// prep the bundle
		$fields = array(
			'registration_ids'  =>  $registrationIds,
			"priority" => "high",
			'content-available' => true,
			"notification" => array(
   					"title"=> $blog,
    				"text"=> $message,
					'sound' => "default"
  			),
			'data' => array(
				'body' => $message,
				'title' => $blog,
				'sound' => "default",
				'lugar' => $lugar,
				'url' => get_site_url(),
				'token' => get_option('readblog_token'),
				'name' => get_bloginfo(),
				'color' => get_option('readblog_colorTheme'),
				'categorias' => get_option('readblog_categoriasShow')
			)
		);
		
		$headers = array(
			'Authorization: key=' . $API_ACCESS_KEY,
			'Content-Type: application/json'
		);
		$fields = json_encode( $fields );
		
		$ch = curl_init();
		curl_setopt( $ch,CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send' );
		curl_setopt( $ch,CURLOPT_POST, true );
		curl_setopt( $ch,CURLOPT_HTTPHEADER, $headers );
		curl_setopt( $ch,CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $ch,CURLOPT_SSL_VERIFYPEER, true );
		curl_setopt( $ch,CURLOPT_POSTFIELDS, $fields );
		$result = curl_exec($ch );
		curl_close( $ch );
    } 
}