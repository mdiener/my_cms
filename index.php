<?php
require_once('server/db_connection.php');
$db = DB::getInstance();

if (!($db->getState())) {
	require_once('server/install.php');
	$install = Install::getInstance();
	$install->startInstallation();
} else {
	require_once('server/website.php');
}
