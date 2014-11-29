<?php

require_once('BaseException.php');


/**
* Singleton allowing the access to the database
* through the getConnection() method. 
* Configuration on config.php.
*/

class Base
{
	
	// Attributes	
	

	private static $dblink;	// The PDO connecting to the database.
	
	
	// Méthods	
	
	
	/**
	* Tries to connect wirh the database.
	* 
	* @return The PDO connected to the database.
	*/

	private static function connect()
	{
		try
		{
			include('config.php');
			$db = new PDO($host, $user, $pass, array(PDO::ERRMODE_EXCEPTION=>true,
					PDO::ATTR_PERSISTENT=>true));
		}
		catch(PDOException $e) 
		{
			throw new BaseException("connection: $dsn ".$e->getMessage(). '<br/>');
		}
		return $db;
	}
	
	
	/**
	* Connects this class to the database.
	* 
	* @return The PDO connected to the database.
	*/

	public static function getConnection()
	{
		if(isset(self::$dblink))
		{
			return self::$dblink;
		}
		else
		{
			self::$dblink=self::connect();
			return self::$dblink;
		}
	}
}