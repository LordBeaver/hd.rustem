<!DOCTYPE html>
<html>
<head>
<title>LDAP Import</title>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<!-- Bootstrap -->
<link href="css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container">
<p>
<?php

// MySQL settings
$mysql_host	= "";
$mysql_user	= "";
$mysql_pass	= "";
$mysql_db	= "";

//LDAP settings
$ldap_host		= "";
$ldap_user		= "";
$ldap_pass		= "";
$ldap_base		= "";
$ldap_filter	= "";

$connection = mysql_connect($mysql_host, $mysql_user, $mysql_pass) or die ("Error SQL connection");
mysql_select_db($mysql_db, $connection);
mysql_query("SET NAMES utf8");
function getsid($in) {
	$sid = "S-";
	$sidinhex = str_split($in, 2);
	$sid = $sid.hexdec($sidinhex[0])."-";
	$sid = $sid.hexdec($sidinhex[6].$sidinhex[5].$sidinhex[4].$sidinhex[3].$sidinhex[2].$sidinhex[1]);
	$subauths = hexdec($sidinhex[7]);
	for($i = 0; $i < $subauths; $i++) {
		$start = 8 + (4 * $i);
		$sid = hexdec($sidinhex[$start+3].$sidinhex[$start+2].$sidinhex[$start+1].$sidinhex[$start]);
	}
return $sid;
}
error_reporting(0);
$ds=ldap_connect($ldap_host);
if ($ds) {
	$r=ldap_bind($ds, $ldap_user, $ldap_pass);
	$sr=ldap_search($ds, $ldap_base, $ldap_filter);
	$info = ldap_get_entries($ds, $sr);
	echo "<h2>Импорт пользователей из Active Directory</h2>";
	echo "<p class='text-muted'><small>Всего записей: " . $info["count"] . "</small></p>";
	?><table class='table table-hover table-condensed table-bordered'><thead><tr class='warning'>
	<td><b><center>ID</center></b></td>
	<td><b><center>ФИО</center></b></td>
	<td><b><center>Телефон</center></b></td>
	<td><b><center>Логин</center></b></td>
	<td><b><center>Подразделение</center></b></td>
	<td><b><center>E-mail</center></b></td>
	<td><b><center>Должность</center></b></td>
	</tr></thead><?php
	for ($i=0; $i<$info["count"]; $i++) {
		$id=getsid(bin2hex($info[$i]["objectsid"][0]));
		$fio=iconv("windows-1251", "UTF-8", $info[$i]["name"][0]);
		$tel=iconv("windows-1251", "UTF-8", $info[$i]["telephonenumber"][0]);
		$login=$info[$i]["samaccountname"][0];
		$unit=iconv("windows-1251", "UTF-8", $info[$i]["department"][0]);
		$email=$info[$i]["mail"][0];
		$posada=iconv("windows-1251", "UTF-8", $info[$i]["title"][0]);
		$query_add_client= "REPLACE INTO clients (id, fio, tel, login, unit_desc, email, posada) VALUES ('$id', '$fio', '$tel', '$login', '$unit', '$email', '$posada')";
		mysql_query ( $query_add_client );
		?><tbody><tr>
		<td><?=$id?></td>
		<td><?=$fio?></td>
		<td><?=$tel?></td>
		<td><?=$login?></td>
		<td><?=$unit?></td>
		<td><?=$email?></td>
		<td><?=$posada?></td>
		</tr></tbody><?php
	}
	?></table><hr><?php
	ldap_close($ds);
} else {
	echo "Unable to connect to LDAP server";
}
?></div></p>
<!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
<script src="https://code.jquery.com/jquery.js"></script>
<!-- Include all compiled plugins (below), or include individual files as needed -->
<script src="js/bootstrap.min.js"></script>
</body></html>
