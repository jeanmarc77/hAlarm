<?php
/**
 * /srv/http/halarm/index.php
 *
 * @package default
 */


echo "
<!DOCTYPE html>
<html>
<head>
<meta http-equiv='Content-Type' content='text/html; charset=utf-8'>
<meta name='viewport' content='width=device-width, initial-scale=1.0'>
<meta name='theme-color' content='#000'>
<title>hAlarm LAN</title>
<META NAME='ROBOTS' CONTENT='NOINDEX, NOFOLLOW'>
<link rel='icon' type='image/x-icon' href='../images/favicon.ico'>
<link rel='stylesheet' href='style.css' type='text/css'>
</head>
<body>";


/**
 *
 * @param unknown $ip
 * @return unknown
 */
function is_private($ip) {
	$pri_addrs = array (
		'10.0.0.0|10.255.255.255', // single class A network
		'172.16.0.0|172.31.255.255', // 16 contiguous class B network
		'192.168.0.0|192.168.255.255', // 256 contiguous class C network
		'169.254.0.0|169.254.255.255', // Link-local address also refered to as Automatic Private IP Addressing
		'127.0.0.0|127.255.255.255' // localhost
	);
	$long_ip = ip2long($ip);
	if ($long_ip != -1) {
		foreach ($pri_addrs as $pri_addr) {
			list ($start, $end) = explode('|', $pri_addr);
			if ($long_ip >= ip2long($start) && $long_ip <= ip2long($end)) {
				return true;
			}
		}
	}
	return false;
}


$ip = is_private($_SERVER['REMOTE_ADDR']); // return true is local

if (!$ip) { // out network
	echo "
<div align='left'>
<h1><img src='images/shield-error.png' width=24 height=24 border=0>hAlarm is meant to be run on local network only !</h1>
<br>Please secure your webserver, in nginx you can do so by modify /etc/nginx.conf
<br>
<textarea style='resize: none;background-color: #DCDCDC' cols='100' rows='15'>
location  /halarm {
    allow 192.168.0.0/24;
    allow 127.0.0.1;
    deny all;
	index index.php;
	location ~ [^/]\.php(/|$) {
	fastcgi_split_path_info ^(.+?\.php)(/.*)$;
		if (!-f \$document_root\$fastcgi_script_name) {
		return 404;
		}
		fastcgi_pass unix:/run/php-fpm/php-fpm.sock;
		fastcgi_index index.php;
		include fastcgi_params;
	}
}
</textarea>
<br>Also, create .htpasswd credentials and set HTTP authentication to allow WAN access
<br>
<textarea style='resize: none;background-color: #DCDCDC' cols='100' rows='15'>
location  /halarmWAN {
	index index.php;
	auth_basic            \"Restricted\";
	auth_basic_user_file  /srv/http/halarmWAN/.htpasswd;
	location ~ [^/]\.php(/|$) {
	fastcgi_split_path_info ^(.+?\.php)(/.*)$;
		if (!-f \$document_root\$fastcgi_script_name) {
		return 404;
		}
	fastcgi_pass unix:/run/php-fpm/php-fpm.sock;
	fastcgi_index index.php;
	include fastcgi_params;
	}
}
</textarea>
<br>Then <b>nginx -t</b> and <b>systemctl restart nginx</b>
<br><a href='../keypad/'>Continue anyway</a>
</div>";
} else { // local network
	header('Location: keypad/index.php');
}
?>
</body>
</html>
