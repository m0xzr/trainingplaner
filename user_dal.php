<?php

include_once  'dal.php';

class UserDAL extends DAL
{	
	public static function CheckCredentials($username, $password)
	{
		$retVal = false;
		
		$conn = self::GetDbConnection();
		$sql = sprintf("SELECT password FROM UserAccount where Username='%s'", $conn->real_escape_string($username));
		$result = $conn->query($sql);
		if ($result->num_rows > 0) 
		{
			while($row = $result->fetch_assoc()) 
			{
				$pw = $row["password"];
				if($pw == md5($password))
				{
					$retVal = true;
				}
			}
		} 
		else 
		{
			//echo "0 results";
		}

		self::CloseDbConnection();
		
		return $retVal;
	}
	
	public static function GetUserId($username)
	{
		$id = -1;
		
		$conn = self::GetDbConnection();
		$sql = sprintf("SELECT id FROM UserAccount where Username='%s'", $conn->real_escape_string($username));
		$result = $conn->query($sql);
		if ($result->num_rows > 0) 
		{
			while($row = $result->fetch_assoc()) 
			{
				$id = intval($row["id"]);
			}
		} 
		else 
		{
			//echo "0 results";
		}
		
		self::CloseDbConnection();
		
		return $id;
	}
	
	public static function GetUserData($username)
	{
		$retVal = null;
		$id = self::GetUserId($username);

		
		if($id != -1)
		{
			$retVal = self::GetUserDataById($id);
		}
		
		return $retVal;
	}
	
	public static function GetUserDataById($id)
	{
		$retVal = null;
		
		$conn = self::GetDbConnection();
		$sql = "SELECT weight, hrrest, hrmax, DATE_FORMAT(creationdate, '%d.%m.%Y') as formated FROM UserData where UserID=".$id." ORDER BY creationdate DESC LIMIT 1";
		$result = $conn->query($sql);
		if ($result->num_rows > 0) 
		{
			while($row = $result->fetch_assoc()) 
			{
				$retVal = new UserData($id, floatval($row["weight"]), intval($row["hrrest"]), intval($row["hrmax"]), $row["formated"]);
			}
		} 
		else 
		{
			//echo "0 results";
		}

		self::CloseDbConnection();
		
		return $retVal;
	}
	
	public static function InsertUserData($id, $weight, $hrmax, $hrrest)
	{
		$retVal = null;
		
		$conn = self::GetDbConnection();
		$sql = sprintf("insert into UserData (weight, hrrest, hrmax, userid) values (%f, %d, %d, %d)", $weight, $hrrest, $hrmax, $id);
		if ($conn->query($sql) === TRUE) 
		{
			//echo "New record created successfully";
			$retVal = self::GetUserDataById($userid);
		} 
		else 
		{
			//echo "Error: " . $sql . "<br>" . $conn->error;
		}	
		self::CloseDbConnection();
		
		return $retVal;
	}
}

?>