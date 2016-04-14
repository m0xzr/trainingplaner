<?php

include_once  'models.php';
include_once  'credentials_provider.php';

abstract class DAL
{
	protected static $db_con_isopened = false;
	protected static $db_con;
	protected static $numberOfDbCalls = 0;			//Anzahl an Anfragen an die DB
	
	
	protected static function GetDbConnection()
	{
		self::$numberOfDbCalls++;
		if(self::$db_con_isopened == false)
		{
			self::$db_con = new mysqli(CredentialsProvider::$host, CredentialsProvider::$username, CredentialsProvider::$password, CredentialsProvider::$db);
			if (self::$db_con->connect_error) 
			{
				die("Connection failed: " . self::$db_con->connect_error);
				self::$db_con_isopened = false;
			} 
			else
			{
				self::$db_con_isopened = true;
			}
		}
		
		return self::$db_con;
	}
	
	protected static function CloseDbConnection()
	{
		self::$numberOfDbCalls--;
		if(self::$numberOfDbCalls == 0)
		{
			self::$db_con->close();	
			self::$db_con_isopened = false;
		}
	}
}

?>