<?php

// This class is the bot webhook, so it answers the queries from Telegram by calling the corresponding methods of the Telegram API.
// Its main duties are to send the game message to chats and to provide access to the actual URL of the game so the users can play.
// No one but Telegram should now its URL.

include "api_calls_helper.php";
include "data_management_helper.php";

$in = json_decode(file_get_contents('php://input'), true);

if($in != NULL) {
	
	if(array_key_exists('message', $in) && ($in['message']['text'] == "/start" || $in['message']['text'] == "/play")) {
		
		// Send the game as a response for a command (either /start or /play)

		$chat_id = $in['message']['chat']['id'];
		
		$send_game_params = create_send_game_parameters($chat_id, BOT_NAME, GAME_SHORT_NAME, "Play " . GAME_NAME . "!");
		call_method('sendGame', json_encode($send_game_params));
		
	} else if(array_key_exists('inline_query', $in)) {

		// Send the game as a response for an inline query
		
		$inline_query_id = $in['inline_query']['id'];		
		$answer_inline_query_params = create_answer_inline_query_parameters($inline_query_id, GAME_SHORT_NAME);
		call_method('answerInlineQuery', json_encode($answer_inline_query_params));
		
	} else if(array_key_exists('callback_query', $in)) {
		
		// Send the game URL because a user has clicked the Play button

		$query_id = $in['callback_query']['id'];
		$user_name = $in['callback_query']['from']['first_name'];
		$user_id = $in['callback_query']['from']['id'];
		$chat_id = $in['callback_query']['message']['chat']['id'];
		$message_id = $in['callback_query']['message']['message_id'];
		
		if(!isset($in['callback_query']['message'])) {
			$message_id = $in['callback_query']['inline_message_id'];
		}
		
		$user_data_encoded = encode_user_data($user_name, $user_id, $chat_id, $message_id);
		$url = get_host_base_url() . GAME_FOLDER . "/?v=" . $user_data_encoded . "&c=1";
		
		$answer_callback_query_params = create_answer_callback_query_parameters($query_id, $url);
		
		call_method('answerCallbackQuery', json_encode($answer_callback_query_params));
		
	}
}

