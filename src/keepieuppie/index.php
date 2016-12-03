<?php

include "../data_management_helper.php";
include "../bot_config.php";

if(isset($_GET['v'])) {
	$encoded_user_data = $_GET['v'];
	$user_data = decode_user_data($encoded_user_data);
	$user_id = $user_data['user_id'];
	
	if(isset($user_id) && !empty($user_id)) {

?><!DOCTYPE html>
<html>
  <head>
    <title><?=GAME_NAME?></title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="format-detection" content="telephone=no">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="MobileOptimized" content="176">
    <meta name="HandheldFriendly" content="True">
    <meta name="robots" content="noindex,nofollow">
    <link href="css/main.css" rel="stylesheet">
  </head>
  <body>
    <div id="page_wrap" class="page_wrap">
      <div id="canvas_wrap" class="canvas_wrap">
	    <div id="ranking" class="ranking">
		  <div class="user_score_wrapper">You scored <span id="user_score" class="user_score">0</span></div>
		  <ol id="ranking_list" class="ranking_list"></ol>
		</div>
	    <canvas id="canvas" width="600" height="600"></canvas>
	  </div>
	  <div class="result_wrap">
	    <div>
		  <p>Kick ups: <span id="result" class="result">0</span></p>
		  <p>Record: <span id="record" class="result">0</span></p>
		  </div>
		<img id="replay_button" class="replay_button" src="images/replay.png" onclick="javascript:handleClick(null)">
	  </div>
    </div>
	<script src="js/<?=urlencode($_GET['v'])?>/main.js"></script>	
	<script src="https://telegram.org/js/games.js"></script>
  </body>
</html><?php } }