<?php

include_once 'models.php';
include_once 'planer_dal.php';
include_once  'credentials_provider.php';

class RunalyzeDAL
{
	private static $db_con_isopened = false;
	private static $db_con;
	private static $numberOfDbCalls = 0;			//Anzahl an Anfragen an die DB
	
	private static function GetDbConnection()
	{
		self::$numberOfDbCalls++;
		if(self::$db_con_isopened == false)
		{
			self::$db_con = new mysqli(CredentialsProvider::$ralhost, CredentialsProvider::$ralusername, CredentialsProvider::$ralpassword, CredentialsProvider::$raldb);
			if (self::$db_con->connect_error) 
			{
				die("Connection failed: " . self::$db_con->connect_error);
				self::$db_con_isopened = false;
			} 
			else
			{
				//echo("Connection opened");
				self::$db_con_isopened = true;
			}
		}
		
		return self::$db_con;
	}
	
	private static function CloseDbConnection()
	{
		self::$numberOfDbCalls--;
		if(self::$numberOfDbCalls == 0)
		{
			self::$db_con->close();	
			self::$db_con_isopened = false;
		}
	}
	
	public static function GetSportsAndTypes()
	{
		$sportsAndTypes = array();
		
		$conn = self::GetDbConnection();
		$sql = "SELECT id, name FROM runalyze_sport ORDER BY id ASC";
		$result = $conn->query($sql);
		
		if ($result->num_rows > 0) 
		{
			//print_r($result->fetch_array);
			while($row = $result->fetch_assoc()) 
			{
			    $st = self::GetSportTypes(intval($row["id"]));
				
				//echo "xxxxxxxxxxxxx".intval($row["id"]);
				//print_r(new SportAndTypes(intval($row["id"]), $row["name"], $st));
				//var_dump(new SportAndTypes(intval($row["id"]), $row["name"], $st));
				$sportsAndTypes[] = new SportAndTypes(intval($row["id"]), $row["name"], $st);
			    //array_push($sportsAndTypes, new SportAndTypes(intval($row["id"]), $row["name"], $st));
			}
		} 
		else 
		{
			//echo "0 results";
		}		
		self::CloseDbConnection();
		//print_r($sportsAndTypes);
		return $sportsAndTypes;
	}
	
	public static function CheckPw($pw) 
	{
		$salt;
		$passwordHash;
		
		$conn = self::GetDbConnection();
		$sql = "SELECT salt, password FROM runalyze_account where username = 'MaxLerchenmueller'";
		$result = $conn->query($sql);
		if ($result->num_rows > 0) 
		{
			while($row = $result->fetch_assoc()) 
			{
				$salt = $row["salt"];
				$passwordHash = $row["password"];
				break;
			}
		} 
		else 
		{
			//echo "0 results";
		}		
		self::CloseDbConnection();
		
		//echo 'salt'.$salt.'pw'.$passwordHash;
		return (self::passwordToHash($pw, $salt) == $passwordHash);		
	}

	/**
	 * Transforms a password (given as string) to internal hash
	 * @param string $realString
	 * @return string
	 */
	private static function passwordToHash($realString, $salt) {
		if (is_null($salt) || strlen($salt) == 0) {
			return md5(trim($realString).self::$SALT);
		} else {
			return hash("sha256", trim($realString).$salt);
		}
	}
	
	public static function GetSportAndType($sportid, $typeid)
	{
		$sportAndType;
		
		$conn = self::GetDbConnection();
		$sql = "SELECT name FROM runalyze_sport where id = ".$sportid."";
		$result = $conn->query($sql);
		if ($result->num_rows > 0) 
		{
			while($row = $result->fetch_assoc()) 
			{
				$type = self::GetSportTypeNameById($typeid);
				$types = array();
				array_push($types, new Type($typeid, $type));
			    $sportAndType = new SportAndTypes($sportid, $row["name"], $types);
			}
		} 
		else 
		{
			//echo "0 results";
		}		
		self::CloseDbConnection();
		
		return $sportAndType;
	}
	
	public static function GetSportNameById($id)
	{
		$retVal = null;
		
		$conn = self::GetDbConnection();
		$sql = "SELECT name FROM runalyze_sport where id = ".$id."";
		$result = $conn->query($sql);
		if ($result->num_rows > 0) 
		{
			while($row = $result->fetch_assoc()) 
			{
			    $retVal = $row["name"];
			}
		} 
		else 
		{
			//echo "0 results";
		}		
		self::CloseDbConnection();
		
		return $retVal;
	}
	
	public static function GetSportTypes($sportId)
	{
	    $types = array();
	    
	    $conn = self::GetDbConnection();

		$sql = "SELECT id, name FROM runalyze_type WHERE sportid = ".$sportId." ORDER BY id ASC";
		$result = $conn->query($sql);
		
		if ($result->num_rows > 0) 
		{
			while($row = $result->fetch_assoc()) 
			{
				$types[] = new Type(intval($row["id"]), $row["name"]);
			    //array_push($types, new Type(intval($row["id"]), $row["name"]));
			}
		} 
		else 
		{
			//echo "0 results";
		}		
		self::CloseDbConnection();
		
		return $types;
	}
	
	public static function GetSportTypeNameById($id)
	{
	    $retVal = null;
	    
	    $conn = self::GetDbConnection();
		$sql = "SELECT name FROM runalyze_type WHERE id = ".$id."";
		$result = $conn->query($sql);
		if ($result->num_rows > 0) 
		{
			while($row = $result->fetch_assoc()) 
			{
			    $retVal = $row["name"];
			}
		} 
		else 
		{
			//echo "0 results";
		}		
		self::CloseDbConnection();
		
		return $retVal;
	}
}

?>