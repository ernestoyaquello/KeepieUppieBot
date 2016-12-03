<?php

// This class is used to retrieve the ranking of players from the API of Telegram.
// It is a proxy that allows the JavasScript code to receive the necessary information without calling directly to the API of Telegram.

include "data_management_helper.php";
include "api_calls_helper.php";

if(isset($_GET['i'])) {
	
	$encoded_user_data = base64_decode(urldecode($_GET['i']));
	$user_data = decode_user_data($encoded_user_data);
	
	$user_id = $user_data['user_id'];
	if(isset($user_id) && $user_id != NULL && $user_id != "") {
		
		$get_game_score_parameters = "";
		if(isset($user_data['chat_id']) && $user_data['chat_id'] != "") {
			$chat_id = $user_data['chat_id'];
			$message_id = $user_data['message_id'];
		
			$get_game_score_parameters = create_get_game_score_parameters($user_id, $chat_id, $message_id);
		} else {
			$inline_message_id = $user_data['message_id'];
		
			$get_game_score_parameters = create_get_game_score_parameters_inline($user_id, $inline_message_id);
		}
		
		$call = call_method('getGameHighScores', json_encode($get_game_score_parameters));
		if($call == NULL) {
			header('HTTP/1.1 500 Internal Server Error');
			exit;
		}
		
		$response = json_decode($call, true);
		if(!empty($response) && $response['ok'] == true) {
			
			header('Access-Control-Allow-Origin: *');
			
			$result = $response['result'];
			$result_converted = array();
			foreach ($result as $user_result) {
				
				$user_result_converted = array();
				$user_result_converted['user_position'] = $user_result['position'];
				$user_result_converted['user_id'] = $user_result['user']['id'];
				$user_result_converted['user_name'] = $user_result['user']['first_name'];
				$user_result_converted['user_score'] = $user_result['score'];
				
				array_push($result_converted, $user_result_converted);
			}
			
			$user_result = array();
			foreach ($result_converted as $key => $row)
			{
				$user_result[$key] = $row['user_position'];
			}
			array_multisort($user_result, SORT_ASC, $result_converted);
			
			echo json_encode($result_converted);
		} else {
			header('HTTP/1.1 500 Internal Server Error');
		}
	} else {
		header('HTTP/1.1 500 Internal Server Error');
	}
}