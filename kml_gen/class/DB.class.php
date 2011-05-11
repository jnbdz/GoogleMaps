<?php
class DB{
	public static $pdo_mysql = null;
	public static $pdo_sqlite = null;
}
try{
		DB::$pdo_sqlite = new PDO("sqlite:pizza.db");
	}catch(PDOException $e){
		$e->getMessage();
	}

$hostname = 'localhost';
$dbname = 'athenas_googlemaps';
$username = 'athenas_goomaps';
$password = 'goo123';

try{
		DB::$pdo_mysql = new PDO("mysql:host=$hostname;dbname=".$dbname,$username,$password);
	}catch(PDOException $e){
			$e->getMessage();
		}