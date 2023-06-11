<?php
/**
 * /srv/http/halarm/keypad/keyp.php
 *
 * @package default
 */


define('checkaccess', TRUE);
include '../secure.php';
include '../config/config.php';
include '../config/lang.php';

if(isset($_COOKIE['kypident'])) {
	$kpident = $_COOKIE['kypident'];
} else {
	$kpident = null;
}
if ($ip && !file_exists('../scripts/alarm.pid')) { // local network, not running
	header("Location: index.php");
}
if (!empty($_GET['fail'])) {

	$fail = (int)$_GET['fail'];
	if ($fail >= $MAXFAIL) {
		sleep($MAXFAIL);
	}
} else {
	$fail = 0;
}
if (!empty($_GET['scenario'])) {
	$scenario = $_GET['scenario'];
	$val = substr($scenario, -1);
	$msg = $SCENARN[$val];
} else {
	$scenario = null;
	$msg = '--';
}

$TENTR--;
$Tprogress = $TENTR*10;
?>
<!DOCTYPE HTML PUBLIC '-//W3C//DTD HTML 4.0 Transitional//EN'>
<html>
<head>
<meta http-equiv='Content-Type' content='text/html; charset=utf-8'>
<meta name='viewport' content='width=device-width, initial-scale=1.0'>
<meta name='theme-color' content='#000'>
<title>hAlarm LAN</title>
<META NAME='ROBOTS' CONTENT='NOINDEX, NOFOLLOW'>
<link rel='icon' type='image/x-icon' href='../images/favicon.ico'>
<link rel='stylesheet' href='../style.css' type='text/css'>
<link rel='preload' as='audio' href='../snd/sweetwarn.mp3'>
<link rel='preload' as='audio' href='../snd/alarm.mp3'>
<link rel='preload' as='audio' href='../snd/bleep.mp3'>
<link rel='preload' as='audio' href='../snd/metro.mp3'>
<script src='https://code.jquery.com/jquery-3.4.1.min.js' integrity='sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo=' crossorigin='anonymous'></script>
<style>
  div.hideme {
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
<body onload="emptyCode();">
<script type='text/javascript'>
var click = true;
var night = false;
var clickdate = Date.now();

$(document).ready(function()
{
$('#progress').hide();
var once = false;
var onca = false;
document.getElementById('rsnd').innerHTML = "<audio controls autoplay hidden><source src='../snd/metro.mp3' type='audio/mpeg'></audio>"
function updateit() {
	$.getJSON('../programs/programlive.php', function(json){
	if (typeof json['msg'] === 'undefined') {
	document.getElementById('rmsg').innerHTML = '';
	} else {
		document.getElementById('rSTAMP').innerHTML = json['stamp'];
		document.getElementById('rmsg').innerHTML = json['msg'];

		if (json['status'] == 'Armed') {
			document.getElementById('rSTAMP2').innerHTML = '<img src=\'../images/burglar48g.png\' width=48 height=48><h1>' + json['msg'] + '<br><br>' + json['stamp'] + '</h1>';
			if(click) {
				date = Date.now();
				if(date - clickdate > 60000) {
				click = false;
				$('#black').show();
				}
			}
		} else if (json['status'] == 'Warn') {
			$('#black').hide();
<?php
if ($fail==0) {
	echo '		if(!once) {
				once = true;
				entering();
			}';
}
?>
		document.getElementById('rsnd').innerHTML = "<audio controls autoplay loop hidden><source src='../snd/sweetwarn.mp3' type='audio/mpeg'></audio>"
		} else if (json['status'] == 'Alarm') {
			$('#black').hide();
			if(!onca) {
				onca = true;
				document.getElementById('rsnd').innerHTML = "<audio controls autoplay loop hidden><source src='../snd/alarm.mp3' type='audio/mpeg'></audio>"
			}
		}
	}
		if (typeof json['status'] === 'undefined') {
		window.location.href = 'index.php';
		}
	}).fail(function (xhr, status, error) {
		document.getElementById('rstat').innerHTML = 'Error';
		setTimeout(function(){
		location.reload();
		}, 5000);
	});
}
<?php
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
?>

function updatenight() {
	$.getJSON('../programs/programnight.php', function(json){
		if (json['night']) {
		 night = true;
		 $('body').css('background-image', 'none');
		 $('.keypad').css('background-color', '#111111');
		 $('.keypad').css('color', '#474747');
		 $('.display').css('color', '#474747');
		 $('.display').css('border', 'none');
		 $('.message').css('border', 'none');
		 $('body').css('color', '#474747');
		} else {
		 night = false;
		 $('body').css('background-image', '../images/bgb.png');
		 $('.keypad').css('background-color', '#666666');
		 $('.keypad').css('color', '#CCCCCC');
		 $('.display').css('color', '#CCCCCC');
		 $('.display').css('border', '1px solid #999999');
		 $('.message').css('border', 'solid #CCCCCC 1px;');
		 $('body').css('color', '#FFF');
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

screensaver();
setInterval(screensaver, 180000);

updateit();
setInterval(updateit, 1000);
});

function removeblack() {
$('#black').hide();
click = true;
clickdate = Date.now();
}

var urlParams = new URLSearchParams(window.location.search);
const scenario = urlParams.get('scenario')
const fail = urlParams.get('fail')

if (typeof scenario === 'undefined'|| !scenario) {
	$.getJSON('../programs/programlive.php', function(json){
	  var scenario = json['scenario'];
	  if (typeof fail === 'undefined'|| !fail) {
	  window.location.href = 'keyp.php?scenario=' + scenario; 
	  } else {
	  window.location.href = 'keyp.php?scenario=' + scenario + '&fail=' + fail;
	  }
	})
}

function addCode(key){
	var code = document.forms[0].code;
	if(code.value.length < 4){
		document.getElementById('rsnd').innerHTML = "<audio controls autoplay hidden><source src='../snd/bleep.mp3' type='audio/mpeg'></audio>"
		code.value = code.value + key;
	}
	if(code.value.length == 4){
		document.getElementById('rsnd').innerHTML = "<audio controls autoplay hidden><source src='../snd/valid.mp3' type='audio/mpeg'></audio>"
		document.getElementById("message").style.display = "block";
		setTimeout(submitForm,500);
	}
}

function submitForm(){
	document.forms[0].submit();
}

function emptyCode(){
	document.forms[0].code.value = "";
}

function entering(){
	$('#progress').show();
	var timeleft = <?php echo $TENTR;?>;
	var downloadTimer = setInterval(function(){
	  if(timeleft < 0){
		clearInterval(downloadTimer);
		$('#progress').hide();
	  } else {
		document.getElementById('messageSpan').innerHTML = timeleft + ' <?php echo $lgSEC;?>';
	  }
	  timeleft -= 1;
	}, 1000);

  var i = 0;
  if (i == 0) {
    i = 1;
    var elem = document.getElementById('progress');
    var width = 1;
    var id = setInterval(frame, <?php echo $Tprogress;?>);
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

}
</script>
<?php
echo "
<div id='black' class='hideme' onclick='removeblack();'><div align='center' id='rSTAMP2'>--</div></div>
<div align='center'>
<div id='myProgress'><div id='progress'></div></div>
<h2><span id='rSTAMP'>--</span> - <span id='rmsg'>--</span><h2>
<h3>(<span id='messageSpan'>$lgSCEN: $msg</span>)</h3>
<form action='codecheck.php' method='get'>
<table cellpadding=5 cellspacing=3>
	<tr>
    	<td class='keypad' onclick=\"addCode('1');\">1</td>
        <td class='keypad' onclick=\"addCode('2');\">2</td>
        <td class='keypad' onclick=\"addCode('3');\">3</td>
    </tr>
    <tr>
    	<td class='keypad' onclick=\"addCode('4');\">4</td>
        <td class='keypad' onclick=\"addCode('5');\">5</td>
        <td class='keypad' onclick=\"addCode('6');\">6</td>
    </tr>
    <tr>
    	<td class='keypad' onclick=\"addCode('7');\">7</td>
        <td class='keypad' onclick=\"addCode('8');\">8</td>
        <td class='keypad' onclick=\"addCode('9');\">9</td>
    </tr>
    <tr>
    	<td class='keypad' onclick=\"addCode('*');\">*</td>
        <td class='keypad' onclick=\"addCode('0');\">0</td>
        <td class='keypad' onclick=\"addCode('#');\">#</td>
    </tr>
</table>
<input type='text' name='code' value='' maxlength=4 class='display' readonly='readonly'>
<input type='hidden' name='fail' value=$fail>
<input type='hidden' name='scenario' value=$scenario>
</form>";

if ($fail>0 && $fail < $MAXFAIL) {
	echo "<h2>Essai $fail</h2>";
}
if ($fail>0 && $fail >= $MAXFAIL) {
	echo "<font color='#FF0000'><h2>$lgTRY $fail !</h2></font>";
}
echo "<p class='message' id='message'>...$lgCHK...</p>";
?>
</div>
<span id='rsnd'></span>
</body>
</html>
