<?php
	//load all database handler files.

	$pwdD = dirname(__DIR__);
	$pwd = $pwdD.'/loader/mysql';

	include("$pwd/dbCon.php");
	include("$pwd/Mysql_DB.php");
	include("$pwdD/helperFunctions.php");
	include("$pwd/Logger.php");


	$db = new Mysql_DB("", ...$parser_key);
	$logger = new Logger("", ...$logger_key);
?>