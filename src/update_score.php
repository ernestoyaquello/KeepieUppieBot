<?php

// This is used to send the score of a player to the API of Telegram.
// It is a proxy that allows the JavasScript code to send and receive the necessary information without calling directly to the API of Telegram.

include "data_management_helper.php";
include "api_calls_helper.php";

// TODO Check referer in headers to discard queries from external sources?

if(isset($_GET['i']) && isset($_GET['s'])) {

	$encoded_user_data = base64_decode(urldecode($_GET['i']));
	$user_data = decode_user_data($encoded_user_data);
	
	$encrypted_score = urldecode($_GET['s']);		
	$score = decrypt_score($encrypted_score);
	
	if(is_numeric($score) && $score > 0 && $score < 9999) {
		
		$set_game_score_parameters = "";
		
		if(isset($user_data['chat_id']) && $user_data['chat_id'] != "") {
			$user_id = $user_data['user_id'];
			$chat_id = $user_data['chat_id'];
			$message_id = $user_data['message_id'];
		
			$set_game_score_parameters = create_set_game_score_parameters($user_id, $score, $chat_id, $message_id);
		} else {
			$user_id = $user_data['user_id'];
			$inline_message_id = $user_data['message_id'];
		
			$set_game_score_parameters = create_set_game_score_parameters_inline($user_id, $score, $inline_message_id);
		}		
		
		$call = call_method('setGameScore', json_encode($set_game_score_parameters));
		if($call == NULL) {
			header('HTTP/1.1 500 Internal Server Error');
			exit;
		}
		
		$response = json_decode($call, true);
		if(empty($response) || $response['ok'] == false) {
			header('HTTP/1.1 500 Internal Server Error');
		} else {
			header('Access-Control-Allow-Origin: *');
		}
		
	} else {
		header('HTTP/1.1 500 Internal Server Error');
	}
	
}