<?php

// This class contains methods to encode and decode the data verifying its integrity.

define('DATA_SEPARATOR', '/|#');
define('HASH_KEY', 'hsdil78hs9gtd9Kf0g308uj4bcz'); // Random string to encrypt information with

function encode_user_data($user_name, $user_id, $chat_id, $message_id) {		
	$user_data_string = $user_name;
	$user_data_string .= DATA_SEPARATOR . $user_id;
	$user_data_string .= DATA_SEPARATOR . $chat_id;
	$user_data_string .= DATA_SEPARATOR . $message_id;
	$user_data_string .= DATA_SEPARATOR . generate_hash($user_id . $chat_id);
	$user_data_encrypted = base64_encode(base64_encode($user_data_string));	
	
	return urlencode($user_data_encrypted);
}

function decode_user_data($encoded_data) {	
	$user_data_decrypted = base64_decode(base64_decode(urldecode($encoded_data)));
	$user_data_array = explode(DATA_SEPARATOR, $user_data_decrypted);
	
	$user_data = array();
	$user_data['user_name'] = (isset($user_data_array[0])) ? $user_data_array[0] : "";
	$user_data['user_id'] = (isset($user_data_array[1])) ? $user_data_array[1] : "";
	$user_data['chat_id'] = (isset($user_data_array[2])) ? $user_data_array[2] : "";
	$user_data['message_id'] = (isset($user_data_array[3])) ? $user_data_array[3] : "";
	
	$hash = (isset($user_data_array[4])) ? $user_data_array[4] : "";
	if(!check_hash($user_data['user_id'] . $user_data['chat_id'], $hash)) {
		$user_data['user_name'] = NULL;
		$user_data['user_id'] = NULL;
		$user_data['chat_id'] = NULL;
		$user_data['message_id'] = NULL;
	}
	
	return $user_data;
}

// This function decodes the score, which has been encoded in a string by the JavaScript code of the game
function decrypt_score($encrypted_score) {

	// This is the code of this PHP function in obfuscated JavaScript
	// var m = 4; // Max digits
	// var dec = function(x){if(x.length!=32)return 0;for(y="",i=1;(i-1)<(x.indexOf(v[v.length-1])-parseInt(x[0]));i++){for(j=0;v[j]!=x[i+parseInt(x[0])-1];j++);while((j-(i*11*(x.indexOf(v[v.length-1])-parseInt(x[0]))+13))<0||x[i+parseInt(x[0])-1]!=v[j%(v.length-1)]){j+=v.length-1;if(j>((m+1)*m*11+13))return 0;}y+=j-(i*11*(x.indexOf(v[v.length-1])-parseInt(x[0]))+13);}return parseInt(y);};

	if(strlen($encrypted_score) != 32)
		return 0;
	$v = array("6","a","0","7","d","f","c","4","e","5","3","1","2","9","8","b");
	$m = 4;
	$y = "";
	
	for($i = 1; ($i - 1) < (strpos($encrypted_score, $v[count($v) - 1]) - $encrypted_score[0]); $i++) {
		$j = 0;
		while($v[$j] != $encrypted_score[$i + $encrypted_score[0] - 1]) {
			$j++;
		}
		while(($j - ($i * 11 * (strpos($encrypted_score, $v[count($v) - 1]) - $encrypted_score[0]) + 13)) < 0 || $encrypted_score[$i + $encrypted_score[0] - 1] != $v[$j % (count($v)-1)]) {
			$j += count($v) - 1;
			if($j > (($m + 1) * $m * 11 + 13)) {
				return 0;
			}
		}
		
		$y .= $j - ($i * 11 * (strpos($encrypted_score, $v[count($v)-1]) - $encrypted_score[0]) + 13);
	}

	return $y;
}

function generate_hash($message) {
	return hash_hmac("sha256", $message, HASH_KEY);
}

function check_hash($message, $hash) {
	return (generate_hash($message) == $hash);
}