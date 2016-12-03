<?php

include "../../data_management_helper.php";

if(isset($_GET['i'])) {
	$encoded_user_data = $_GET['i'];
	$user_data = decode_user_data($encoded_user_data);
	$user_name = $user_data['user_name'];
	$user_id = $user_data['user_id'];
?>
var canvas;
var context;
var ballImage;
var bgImage;
var result;
var ranking;
var rankingList;
var replayButton;
var recordElement;
var userScore;
var rankingResult;
var pageWrap;
var canvasWidth = 600;
var canvasHeight = 600;
var clicked = false;
var isKicking = false;
var difficultIncrease = 0;
var kickupsToIncreaseDifficulty = 50;
var kickups = 0;
var record = 0;
var maxRecord = 0;
var gameLoop;
var clickTimer;
var frameID;
var kickSound;
var celebrationSound;
var hasCelebrated = true;
var started = false;
var isMobileDevice = false;
var isLandscape = false;
var userId = <?=$user_id?>;
var userName = "<?=$user_name?>";


var time = 0;
var ballRadius = 110;
var ballPositionX = canvasWidth / 2;
var ballPositionY = ballRadius;
var ballSpeedX = 0;
var ballSpeedY = 0;
var ballSpeedRotation = 0;
var ballRotationAngleInRadians = 0;

window.onload = function() {
	
	canvas = document.getElementById("canvas");
	result = document.getElementById("result");
	pageWrap = document.getElementById("page_wrap");
	recordElement = document.getElementById("record");
	ranking = document.getElementById("ranking");
	rankingList = document.getElementById("ranking_list");
	replayButton = document.getElementById("replay_button");
	userScore = document.getElementById("user_score");
	
	initialize();	
	designCorrections();
};

function initialize() {
	if(isCanvasSupported()) {
		
		getRanking();
	
		context = canvas.getContext("2d");	
		
		bgImage = new Image();
		bgImage.onload = function() {
			onLoadedBackgroundImage();
		};
		bgImage.src = 'images/bg.jpg';
		
		ballImage = new Image();
		ballImage.src = 'images/ball.png';	
		
		canvas.addEventListener("touchstart", handleClick);
		canvas.addEventListener("mousedown", handleClick);
		
		kickSound = document.createElement('audio');
		if (kickSound.canPlayType('audio/ogg')) {
			kickSound.setAttribute('src','sounds/kick.ogg');
		}
		
		celebrationSound = document.createElement('audio');
		if (celebrationSound.canPlayType('audio/ogg')) {
			celebrationSound.setAttribute('src','sounds/celebration.ogg');
		}
		
	} else {
		alert("Your browser is not compatible with this game :(");
	}
}

function isCanvasSupported(){
	var elem = document.createElement('canvas');
	return !!(elem.getContext && elem.getContext('2d'));
}

function designCorrections() {
	
	if (/mobile/i.test(navigator.userAgent) || screen.height < 800) {
		var link = document.createElement("link");
		link.href = "css/mobile.css";
		link.type = "text/css";
		link.rel = "stylesheet";
		document.getElementsByTagName("head")[0].appendChild(link);
		
		isMobileDevice = true;
	}
	
	if(window.matchMedia("(orientation: landscape)").matches) {
		isLandscape = true;
	}
	
	window.addEventListener("resize", function() {
		onScreenChanged();
	}, false);
	
	onScreenChanged();
	
}

function onScreenChanged() {
	isLandscape = window.innerHeight < window.innerWidth;
	isLandscape |= window.matchMedia("(orientation: landscape)").matches;
	if(!isMobileDevice || (isMobileDevice && !isLandscape)) {
		if((window.innerWidth / window.innerHeight) > 0.78) {
			newCanvasHeight = Math.round(window.innerHeight * 0.7);
			newCanvasWidth = newCanvasHeight * (canvasWidth / canvasHeight);
			setNewCanvasSize(newCanvasWidth, newCanvasHeight, newCanvasWidth);
		} else {
			newCanvasWidth = window.innerWidth;
			newCanvasHeight = newCanvasWidth * (canvasHeight / canvasWidth);
			setNewCanvasSize(newCanvasWidth, newCanvasHeight, newCanvasWidth);
		}
	} else {
		rectCanvas = canvas.getBoundingClientRect();		
		if(window.innerHeight < rectCanvas.height) {
			newCanvasHeight = window.innerHeight;
			newCanvasWidth = newCanvasHeight * (canvasWidth / canvasHeight);
			setNewCanvasSize(newCanvasWidth, newCanvasHeight, (newCanvasWidth * 2));
		} else {
			setDefaultCanvasSize();
		}
	}
	resetGame(true);
}

function setDefaultCanvasSize() {
	canvas.style.height = "100%";
	canvas.style.width = "100%";
	pageWrap.style.width = "auto";
}

function setNewCanvasSize(newCanvasWidth, newCanvasHeight, newPageWrapWidth) {
	if(newCanvasWidth > canvasWidth) {
		newCanvasWidth = canvasWidth;
		newCanvasHeight = canvasHeight;
	}
	
	canvas.style.height = newCanvasHeight + "px";
	canvas.style.width = newCanvasWidth + "px";
	pageWrap.style.width = newPageWrapWidth + "px";
}

function onLoadedBackgroundImage() {
	context.drawImage(bgImage, 0, 0);
	
	playImage = new Image();
	playImage.onload = function() {
		onLoadedPlayImage();
	};
	playImage.src = 'images/play.png';
}

function onLoadedPlayImage() {
	context.drawImage(playImage, (canvasWidth/2) - (playImage.width/2), (canvasHeight/2) - (playImage.height/2));
}

function Draw() {
	if(started) {
		calculateBallValues();
		updateBallPosition();
		frameID = window.requestAnimationFrame(Draw);
	}
}

function calculateBallValues() {
	if(!isKicking) {
		
		ballSpeedY = ballSpeedY + (0.05 * ++time);
		ballSpeedY *= 1 + ((ballSpeedY > 0) ? (difficultIncrease * 0.01) : 0);
		ballPositionY += ballSpeedY;
		ballPositionX += ballSpeedX;
		
		// Changes in ball rotation and movement when it collides with lateral walls
		if(ballPositionX < (ballRadius - 50) || ballPositionX > (canvasWidth - ballRadius + 50)) {
			ballSpeedX *= -1;
			if(ballPositionX < (ballRadius - 50)) {
				ballSpeedRotation += ((ballSpeedY > 0) ? 0.15 : -0.15) * Math.abs(ballSpeedY);
			} else {
				ballSpeedRotation -= ((ballSpeedY > 0) ? 0.15 : -0.15) * Math.abs(ballSpeedY);
			}
		}
		
		// Changes in ball rotation to slows it down gradually
		if(ballSpeedRotation != 0) {
			if(ballSpeedRotation > 0) {
				ballSpeedRotation -= ballSpeedRotation*0.01;
				ballSpeedRotation = (ballSpeedRotation < 0) ? 0 : ballSpeedRotation;
			} else {
				ballSpeedRotation += Math.abs(ballSpeedRotation)*0.01;
				ballSpeedRotation = (ballSpeedRotation > 0) ? 0 : ballSpeedRotation;
			}
			ballRotationAngleInRadians += ballSpeedRotation * Math.PI/180;
		}
	}
}

function updateBallPosition() {
	context.drawImage(bgImage, 0, 0);
	if(!isGameOver()) {
		drawBall();
	} else {
		resetGame();
	}
}

function resetGame(newGame = false) {
	started = false;
	window.cancelAnimationFrame(frameID);
	context.drawImage(bgImage, 0, 0);
	
	resetImage = new Image();
	resetImage.onload = function() {
		if(newGame || (isMobileDevice && isLandscape)) {
			context.drawImage(resetImage, (canvasWidth/2) - (resetImage.width/2), (canvasHeight/2) - (resetImage.height/2));
		}
	};
	
	if(newGame) {
		resetImage.src = 'images/play.png';
		ranking.style.display = "none";
		replayButton.style.display = "none";
	} else {
		resetImage.src = 'images/replay.png';
		ranking.style.display = "block";
		userScore.textContent = kickups + "";
		
		if(!isMobileDevice || (isMobileDevice && !isLandscape)) {
			replayButton.style.display = "block";
			ranking.style.height = "auto";
			rankingList.style.height = "auto";
		} else {
			replayButton.style.display = "none";
			ranking.style.height = (canvas.getBoundingClientRect().height - 55) + "px";
			rankingList.style.height = (canvas.getBoundingClientRect().height - 115) + "px";
		}
		
		sendRecord();
	}
	
	kickups = 0;
	time = 0;
	ballRadius = 110;
	ballPositionX = canvasWidth / 2;
	ballPositionY = ballRadius;
	ballSpeedX = 0;
	ballSpeedY = 0;
	ballSpeedRotation = 0;
	ballRotationAngleInRadians = 0;
	
	hasCelebrated = false;	
}

function isGameOver() {
	return ballPositionY >= (canvasHeight + ballRadius * 2);
}

function drawBall() {
	context.translate(ballPositionX, ballPositionY);
	context.rotate(ballRotationAngleInRadians);
	context.drawImage(ballImage, -ballImage.width / 2, -ballImage.height / 2, ballImage.width, ballImage.height);
	context.rotate(-ballRotationAngleInRadians);
	context.translate(-ballPositionX, -ballPositionY);
}

function isInsideCircle(x, y) {
	return getDistance(ballPositionX, ballPositionY, x, y) <= ballRadius;
}

function getDistance(x, y, x2, y2) {
	return Math.sqrt((x2-=x)*x2 + (y2-=y)*y2);
}

function clickIsFinished() {
	clicked = false;
}

function handleClick(event) {
	if(!started) {
		started = true;
		result.textContent = 0;
		frameID = window.requestAnimationFrame(Draw);
		ranking.style.display = "none";
		replayButton.style.display = "none";
	}
	
	if(!clicked) {
		clicked = true;
		clickTimer = setTimeout(clickIsFinished, 150);
		
		event.preventDefault();
		position = (event.type == "mousedown") ? getMousePos(event) : getTouchPos(event);
		isInsideBall = isInsideCircle(position.x, position.y);
		if(isInsideBall) {
			isKicking = true;
			
			kickSound.play();
			
			time = 0;
			ballSpeedX = 0.1 * (ballPositionX - position.x);
			if(ballSpeedX == 0) {
				ballSpeedX = Math.random() * 0.5;
				ballSpeedX *= (Math.random() > 0.5) ? 1 : -1;
			}	
			
			ballSpeedY = -(12 + 1 * ((position.y - ballPositionY) / ballRadius) + 7 * ((ballRadius - Math.abs(position.x - ballPositionX)) / ballRadius));
			ballSpeedRotation += 20 * (ballPositionX - position.x) / ballRadius;
			ballSpeedRotation = (ballSpeedRotation <= 30) ? ballSpeedRotation : 30;
			
			ballSpeedX *= (1 + (0.2 * difficultIncrease));
			
			incrementKickupsNumber();
			
			isKicking = false;
		}
	}
}

function getRanking() {
	var xhttp = new XMLHttpRequest();
	xhttp.onreadystatechange = function() {
		if (this.readyState == 4 && this.status == 200) {
			while (rankingList.firstChild) {
				rankingList.removeChild(rankingList.firstChild);
			}
			rankingResult = JSON.parse(this.responseText);			
			for(var k = 0; k < rankingResult.length; k++) {
				addRecord(rankingResult[k]['user_name'], rankingResult[k]['user_score']);
				if(rankingResult[k]['user_id'] == userId) {
					maxRecord = rankingResult[k]['user_score'];
				}
			}
		}
	};
	rankingList.innerHTML = "Loading...";
	xhttp.open("GET", "../get_ranking.php?i=<?=urlencode(base64_encode($_GET['i']))?>", true);
	xhttp.send();
}

function addRecord(playerName, recordNumber) {
	var li = document.createElement("li");
	li.innerHTML = playerName + " <span class=\"score\">" + recordNumber + "</span>";
	rankingList.appendChild(li);
}

function sendRecord() {
	if(record >= kickups && record > maxRecord) {
		var xhttp = new XMLHttpRequest();

		// This function encodes the result to make it look like an hexadecimal hash in order to confuse attackers inspecting the network.
		// It replaces each digit of the result number by an hexadecimal character depending on its value and position and then adds random 
		// characters to the resulting string in order to make it be exactly 32 characters long.
		var v = ["6","a","0","7","d","f","c","4","e","5","3","1","2","9","8","b"];
		c = function(x){x=x.toString();for(y="",i=1;(i-1)<x.length;i++)y+=v[(parseInt(x[i-1])+(i*11*x.length+13))%(v.length-1)];
			e=Math.floor((Math.random()*8)+1);for(q=1;q<e;q++)y=v[Math.floor((Math.random()*(v.length-2)))]+""+y;
			y=e+""+y;for(y+=v[v.length-1],o=y.length;o<32;o++)y+=v[Math.floor((Math.random()*(v.length-2)))];
			return y;};

		xhttp.onreadystatechange = function() {
			if (this.readyState == 4 && this.status == 200) {
				getRanking();
			}
		};
		rankingList.innerHTML = "Loading...";
		xhttp.open("GET", "../update_score.php?i=<?=urlencode(base64_encode($encoded_user_data))?>&s=" + c(record), true);
		xhttp.send();
	} else {
		getRanking();
	}
}

function incrementKickupsNumber() {
	
	difficultIncrease = Math.floor(kickups / kickupsToIncreaseDifficulty) 
			+ ((kickups % kickupsToIncreaseDifficulty) / kickupsToIncreaseDifficulty);
	
	result.textContent = ++kickups;
	if(kickups > record) {
		if(!hasCelebrated) {
			//celebrationSound.play();
			hasCelebrated = true;
		}
		record = kickups;
		recordElement.textContent = record;
	}
}

function getMousePos(event) {
	return getPosition(event.clientX, event.clientY);
}

function getTouchPos(event) {
	return getPosition(event.targetTouches[0].pageX, event.targetTouches[0].pageY);
}

function getPosition(x, y) {
	rectCanvas = canvas.getBoundingClientRect();
	scaleX = canvas.width / rectCanvas.width,
	scaleY = canvas.height / rectCanvas.height;

	return {
		x: (x - rectCanvas.left) * scaleX,
		y: (y - rectCanvas.top) * scaleY
	}
}<?php }