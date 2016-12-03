<?php

// This class handles the calls to the API of Telegram.

include "bot_config.php";

class SendGameParameters {
	public $chat_id = "";
	public $game_short_name = "";
	public $reply_markup = "";
	
	function __construct($chat_id, $game_short_name) {
		$this->chat_id = $chat_id;
		$this->game_short_name = $game_short_name;
		$this->reply_markup = new InlineKeyboardMarkup();
	}
}

class InlineKeyboardMarkup {
	public $inline_keyboard = "";
	
	function add_row() {
		if($this->inline_keyboard == "") {
			// Create array of rows if it doesn't exist
			$this->inline_keyboard = array();
		}
		
		$row = array();
		array_push($this->inline_keyboard, $row);
		
		return count($this->inline_keyboard) - 1; // Row number
	}
	
	function add_button($inline_button, $row_number) {
		if($this->inline_keyboard == "") {
			// Create array of rows if it doesn't exist
			$this->inline_keyboard = array();
		}
		
		if($row_number < count($this->inline_keyboard)) {
			array_push($this->inline_keyboard[$row_number], $inline_button);
			
			return true;
		}
		return false;
	}
}

class InlineKeyboardButton {
	public $text = "";
	
	function __construct($text) {
		$this->text = $text;
	}
}

class GameInlineKeyboardButton extends InlineKeyboardButton {
	public $callback_game = "";
	
	function __construct($text, $game_short_name, $url) {
		parent::__construct($text);
		
		$this->callback_game = new CallbackGame($game_short_name, $url);
	}
}

class CallbackGame {
	public $game_short_name = "";
	public $url = "";
	
	function __construct($game_short_name, $url) {		
		$this->game_short_name = $game_short_name;
		$this->url = $url;
	}
}

class InlineQueryResultGame {
	public $type = "";
	public $id = "";
	public $game_short_name = "";
	
	function __construct($id, $game_short_name) {		
		$this->type = "game";
		$this->id = $id;
		$this->game_short_name = $game_short_name;
	}
}

function create_send_game_parameters($chat_id, $bot_name, $game_short_name, $button_text) {
	$game_params = new SendGameParameters($chat_id, $game_short_name);
	$row_number = $game_params->reply_markup->add_row();
	$button = new GameInlineKeyboardButton($button_text, $game_short_name, "https://telegram.me/" . $bot_name . "?game=" . $game_short_name);
	$game_params->reply_markup->add_button($button, $row_number);
	
	return $game_params;
}

function create_set_game_score_parameters($user_id, $score, $chat_id, $message_id) {
	$set_game_score_params = array();
	$set_game_score_params['user_id'] = $user_id;
	$set_game_score_params['score'] = $score;
	$set_game_score_params['chat_id'] = $chat_id;
	$set_game_score_params['message_id'] = $message_id;
	
	return $set_game_score_params;
}

function create_set_game_score_parameters_inline($user_id, $score, $inline_message_id) {
	$set_game_score_params = array();
	$set_game_score_params['user_id'] = $user_id;
	$set_game_score_params['score'] = $score;
	$set_game_score_params['inline_message_id'] = $inline_message_id;
	
	return $set_game_score_params;
}

function create_get_game_score_parameters($user_id, $chat_id, $message_id) {
	$get_game_score_params = array();
	$get_game_score_params['user_id'] = $user_id;
	$get_game_score_params['chat_id'] = $chat_id;
	$get_game_score_params['message_id'] = $message_id;
	
	return $get_game_score_params;
}

function create_get_game_score_parameters_inline($user_id, $inline_message_id) {
	$get_game_score_params = array();
	$get_game_score_params['user_id'] = $user_id;
	$get_game_score_params['inline_message_id'] = $inline_message_id;
	
	return $get_game_score_params;
}

function create_answer_callback_query_parameters($callback_query_id, $url) {
	$answer_callback_query_params = array();
	$answer_callback_query_params['callback_query_id'] = $callback_query_id;
	$answer_callback_query_params['url'] = $url;
	
	return $answer_callback_query_params;
}

function create_answer_inline_query_parameters($inline_query_id, $game_short_name) {
	$answer_inline_query_parameters = array();
	$answer_inline_query_parameters['inline_query_id'] = $inline_query_id;
	
	$answer_inline_query_parameters['results'] = array();
	array_push($answer_inline_query_parameters['results'], new InlineQueryResultGame($inline_query_id, $game_short_name));
	
	return $answer_inline_query_parameters;
}

function call_method($method_name, $params) {
	$url = API_URL . $method_name;
	
	$options = array(
		'http' => array(
			'header'  => "Content-type: application/json\r\n",
			'method'  => 'POST',
			'content' => $params
		)
	);
	$context  = stream_context_create($options);
	$result = @file_get_contents($url, false, $context);
	if ($result === FALSE) {
		return NULL;
	}
	
	return $result;
}

function get_host_base_url() {
	$http = "http" . ((isset($_SERVER['HTTPS'])) ? "s" : "") . "://";
	$host = $_SERVER['HTTP_HOST'];
	$folder_parts = explode("/", $_SERVER['REQUEST_URI']);
	$folder = "";
	if(count($folder_parts) > 0) {
		$f_index = 0;
		while(++$f_index < (count($folder_parts) - 1)) {
			$folder .= "/" . $folder_parts[$f_index];
		}
	}
	$folder .= "/";
	return $http . $host . $folder;
}

