<?php
/**
 * /srv/http/halarm/keypad/index.php
 *
 * @package default
 */


define('checkaccess', TRUE);
include '../config/config.php';
include '../config/memory.php';
include '../config/lang.php';
include '../secure.php';

if (!empty($_GET['startstop'])) {
	$startstop = $_GET['startstop'];
} else {
	$startstop = null;
}
if (!empty($_GET['scenario'])) {
	$scenario = $_GET['scenario'];
} else {
	$scenario = 'scenario0';
}
$cntsena = count($SCENARN);

if(isset($_COOKIE['kypident'])) {
	$kpident = $_COOKIE['kypident'];
} else {
	$kpident = null;
}

date_default_timezone_set($DTZ);

if (file_exists('../scripts/alarm.pid')) {
	$PID = (int) file_get_contents('../scripts/alarm.pid');
	exec("ps -ef | grep $PID | grep alarm.php", $ret);
	if (!isset($ret[1])) {
		$PID = null;
		unlink('../scripts/alarm.pid');
	}
	if (!is_null($PID) && $startstop != 'arm' && $startstop != 'stop' && $startstop != 'cancel') {
		header("Location: keyp.php");
	}
} else {
	$PID = null;
}

echo "
<!DOCTYPE html>
<html>
<head>
<meta http-equiv='Content-Type' content='text/html; charset=utf-8'>
<meta name='viewport' content='width=device-width, initial-scale=1.0'>
<meta name='mobile-web-app-capable' content='yes'>
<meta name='theme-color' content='#000'>
<title>hAlarm LAN</title>
<META NAME='ROBOTS' CONTENT='NOINDEX, NOFOLLOW'>
<link rel='icon' type='image/x-icon' href='../images/favicon.ico'>
<link rel='shortcut icon' sizes='192x192' href='../images/burglar192.png'>
<link rel='stylesheet' href='../style.css' type='text/css'>
<link rel='preload' as='audio' href='../snd/cancel.mp3'>
<link rel='preload' as='audio' href='../snd/valid.mp3'>
<link rel='preload' as='audio' href='../snd/leave2.mp3'>
<script src='https://code.jquery.com/jquery-3.4.1.min.js' integrity='sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo=' crossorigin='anonymous'></script>
<style>
  div.hideme {";
if ($startstop == 'arm' || $startstop == 'start') {
	echo 'visibility: hidden;';
}
echo "
    opacity:1;
    background-color:#000;
    width:100%;
    height:100%;
    z-index:10;
    top:0;
    left:0;
    position:fixed;
  }

</style>
</head>
<body>
<script type='text/javascript'>
var click = true;
var night = false;
var clickdate = Date.now();

$(document).ready(function()
{
function updateit() {
	$.getJSON('../programs/programlive.php', function(json){
	if (typeof json['msg'] === 'undefined') {
	document.getElementById('rstat').innerHTML = '$lgOFF';
	document.getElementById('rSTAMP2').innerHTML = '<img src=\'../images/burglar48g.png\' width=48 height=48><h1>hAlarm<br><br>' + json['stamp'] + '</h1>';
		if(click) {
			date = Date.now();
			if(date - clickdate > 60000) {
			click = false;
			$('#black').show();
			}
		}
	} else {
	document.getElementById('rstat').innerHTML = json['msg'];
	}
	document.getElementById('rSTAMP').innerHTML = json['stamp'];

	if (json['status'] == 'Alarm') {
	window.location.href = 'index.php?startstop=cancel';";
if (is_null($startstop)) {
	echo "
	} else if (json['status'] == 'Leaving') {
	window.location.href = 'index.php?scenario=$scenario';";
}
echo "
	} else if (json['status'] == 'Armed') {
	window.location.href = 'keyp.php?scenario=$scenario';
	}
	}).fail(function (xhr, status, error) {
		document.getElementById('rstat').innerHTML = 'Error';
		setTimeout(function(){
		location.reload();
		}, 5000);
	});
}

function updatenight() {
	$.getJSON('../programs/programnight.php', function(json){
		if (json['night']) {
		 night = true;
		 $('#widgets').hide();
		 $('body').css('background-image', 'none');
		 $('body').css('color', '#474747');
		 $('.BUTTON_KTC').addClass('BUTTON_KTCn').removeClass('BUTTON_KTC');
			 if(json['run']) {
			 $(startstopb).attr('src', '../images/arm.night.png');
			 } else {
			 $(startstopb).attr('src', '../images/disarm.night.png');
			 }
		} else {
		 night = false;
		 $('#widgets').show();
		 $('body').css('background-image', '../images/bgb.png');
		 $('body').css('color', '#FFF');
		 $('.BUTTON_KTCn').addClass('BUTTON_KTC').removeClass('BUTTON_KTCn');
		 	 if(json['run']) {
			 $(startstopb).attr('src', '../images/arm.png');
			 } else {
			 $(startstopb).attr('src', '../images/disarm.png');
			 }
		}
	})
}

function screensaver() {
	if(!click) {
		posx = (Math.random() * ($(document).width() - 100)).toFixed();
		posy = (Math.random() * ($(document).height() - 50)).toFixed();
		d = document.getElementById('rSTAMP2');
		d.style.position = 'absolute';
		d.style.left = posx+'px';
		d.style.top = posy+'px';
		if(night) {
		d.style.color = null;
		} else {
		color = '#'+ Math.round(0xffffff * Math.random()).toString(16);
		d.style.color = color;
		}
		setInterval(function () {
		$('#rSTAMP2').fadeIn(5000).fadeOut(5000);
		}, 5000);
    } else {
    	$('#black').hide();
    }
}

updatenight();
setInterval(updatenight, 18000);

updateit();
setInterval(updateit, 1000);
";
if(isset($kpident)) {
echo "
function alive() {
	$.getJSON('../programs/programkpalive.php?kpident=$kpident', function(json){
	})
}

alive();
setInterval(alive, 30000);
";
}
if ($startstop != 'arm') {
	echo "$('#progress').hide();
screensaver();
setInterval(screensaver, 60000);
";
}
echo "});

function removeblack() {
$('#black').hide();
click = true;
clickdate = Date.now();
}

var elem = document.documentElement;
function openFullscreen() {
  if (elem.requestFullscreen) {
    elem.requestFullscreen();
  } else if (elem.mozRequestFullScreen) {
    elem.mozRequestFullScreen();
  } else if (elem.webkitRequestFullscreen) {
    elem.webkitRequestFullscreen();
  } else if (elem.msRequestFullscreen) {
    elem.msRequestFullscreen();
  }
  document.getElementById('screen').innerHTML = \"<INPUT TYPE='button' class='BUTTON_KTC' onclick='closeFullscreen();' value='$lgFULLS &#x21F2'>\";
}
function closeFullscreen() {
  if (document.exitFullscreen) {
    document.exitFullscreen();
  } else if (document.mozCancelFullScreen) {
    document.mozCancelFullScreen();
  } else if (document.webkitExitFullscreen) {
    document.webkitExitFullscreen();
  } else if (document.msExitFullscreen) {
    document.msExitFullscreen();
  }
  document.getElementById('screen').innerHTML = \"<INPUT TYPE='button' class='BUTTON_KTC' onclick='openFullscreen();' value='&#x21F1; $lgFULLS'>\";
}
</script>
<div id='black' class='hideme' onclick='removeblack();'><div align='center' id='rSTAMP2'>--</div></div>
<div id='myProgress' align='center'><div id='progress'></div></div>
<table border=0 cellspacing=0 cellpadding=0 align='left'>
<tr><th width='50%' align=left>
<h1>$lgKEYB $kpident</h1>
<h3><span id='rSTAMP'>--</span> - $lgSTAT : <span id='rstat'>--</span></h3>
<h3><span id='messageSpan'></span></h3></th>
</tr>
<tr><th align=center><form action='index.php' method='GET'>";
if (is_null($PID)) {
	echo "<input type='image' value='' width=128 height=128 id='startstopb'><input type='hidden' name='startstop' value='start'>";
} else {
	echo "<input type='image' value='' width=128 height=128 id='startstopb'><input type='hidden' name='startstop' value='stop'>";
}
echo "
<input type='hidden' name='scenario' value='$scenario'>
</form><br>
</th></tr>";
if ($startstop == 'start' || $startstop == 'stop' || $startstop == 'cancel') {
	$now = date($DATEFORMAT . ' H:i:s');
	if ($startstop == 'start' && is_null($PID)) {
		//$command    = 'php scripts/alarm.php' . ' > /dev/null 2>&1 & echo $!;';
		$command    = "php ../scripts/alarm.php $scenario" . ' >> ../data/alarm.err 2>&1 & echo $!; ';
		$PID        = exec($command);
		$val = substr($scenario, -1);
		$stringData = "$now\tStarting by LAN ($SCENARN[$val])\n\n";
		file_put_contents('../scripts/alarm.pid', $PID);
		$stringData .= file_get_contents('../data/events.txt');
		file_put_contents('../data/events.txt', $stringData);
		$stringData = "$now\tStarting debug by LAN ($SCENARN[$val]) ($PID)\n\n";
		file_put_contents('../data/alarm.err', $stringData, FILE_APPEND);
	}
	if ($startstop == 'stop' || $startstop == 'cancel') {
		if (!is_null($PID)) {
			if ($startstop == 'stop') {
				$stringData = "$now\tCancel on keypad LAN\n\n";
			} else {
				$stringData = "$now\tStartup canceled detector in alarm\n\n";
			}
			$command = exec("kill $PID > /dev/null 2>&1 &");
			unlink('../scripts/alarm.pid');
			$PID = null;
			$stringData .= file_get_contents('../data/events.txt');
			file_put_contents('../data/events.txt', $stringData);
			$stringData = "$now\tCancel debug by LAN ($PID)\n\n";
			file_put_contents('../data/alarm.err', $stringData, FILE_APPEND);
			if (file_exists($MEMORY)) {
				unlink($MEMORY);
			}
			echo "<audio controls autoplay hidden><source src='../snd/cancel.mp3' type='audio/mpeg' ></audio>
			<script type='text/javascript'>
			  setTimeout(function () {
				window.location.href = 'index.php?scenario=$scenario';
			  }, 500);
			</script>
			";
		}
	}
}
if ($startstop == 'start') { // Go arming
	echo "
<script type='text/javascript'>
  document.getElementById('messageSpan').innerHTML = \"...$lgWAIT...\";
  setTimeout(function () {
    window.location.href = 'index.php?startstop=arm&scenario=$scenario';
  }, 1000);
</script>
<audio controls autoplay hidden><source src='../snd/valid.mp3' type='audio/mpeg' ></audio>
";
}
if ($startstop == 'stop' && !is_null($PID)) { // Disarming
	header("Location: index.php?startstop=stop");
}
if ($startstop == 'arm') {
	$TENTR--;
	$Tprogress = $TENTR*10;
	echo "
<script type='text/javascript'>
var timeleft = $TENTR;
var downloadTimer = setInterval(function(){
  $.getJSON('../programs/programlive.php', function(json){
	if (json['status'] != 'Leaving') {
	window.location.href = 'index.php?scenario=$scenario';
	}
  })

  if(timeleft <= 0){
    clearInterval(downloadTimer);
    window.location.href = 'keyp.php?scenario=$scenario';
  } else {
    document.getElementById('messageSpan').innerHTML = '$lgARM ' + timeleft + ' $lgSEC';
  }
  timeleft -= 1;
}, 1000);

var i = 0;
if (i == 0) {
i = 1;
var elem = document.getElementById('progress');
var width = 1;
var id = setInterval(frame, $Tprogress);
function frame() {
  if (width >= 100) {
    clearInterval(id);
    i = 0;
  } else {
    width++;
    elem.style.width = width + '%';
  }
}
}
</script>
<audio controls autoplay hidden loop><source src='../snd/leave2.mp3' type='audio/mpeg' ></audio>
";
}
if ($startstop == 'cancel' && !is_null($PID)) {
	echo "
<script type='text/javascript'>
document.getElementById('messageSpan').innerHTML = \"$now\tStartup canceled detector in alarm\";
</script>
";
}
echo "
<tr><th>
<table width='100%' border=0 cellspacing=0 cellpadding=2 align='center'>
<tr>
<td align='right'><INPUT TYPE='button' class='BUTTON_KTC' onClick=\"location.href='../../'\" value='&#x21A9; $lgBACK'></td>";
echo "
<td align='center'>
<form action='index.php' method='GET'>
<select name='scenario' class='BUTTON_KTC' onchange='this.form.submit()'>";
for ($i = 0; $i < $cntsena; $i++) {
	if ($scenario == 'scenario'.$i) {
		echo "<option SELECTED value='scenario$i'>";
	} else {
		echo "<option value='scenario$i'>";
	}
	echo "$SCENARN[$i]</option>";

}
echo "
</select>
<input type='hidden' name='startstop' value=null>
</form>
</td>
<td align='left'><INPUT TYPE='button' class='BUTTON_KTC' onClick=\"location.href='histo.php'\" value='&#x25A4; $lgHISTO'></td>
</tr><tr>
<td align='right'><span id='screen'><INPUT TYPE='button' class='BUTTON_KTC' onclick='openFullscreen();' value='&#x21F1; $lgFULLS'></span></td>
<td align='center'><INPUT TYPE='button' class='BUTTON_KTC' onClick=\"location.href='kypident.php'\" value='&#x2710 Keypad id.'</td>
<td align='left'><INPUT TYPE='button' class='BUTTON_KTC' onClick=\"location.href='debug.php'\" value='&#x221E; Debug'></td></tr>
</table>
</th></tr>
<tr><th>
<div id='widgets'>
";
include '../config/widgets/bottom.php';
echo "
</div>
</th></tr>
</table>";
?>
</body>
</html>
