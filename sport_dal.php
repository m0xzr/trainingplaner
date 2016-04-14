<?php

include_once  'dal.php';

class SportDAL extends DAL
{
	public static function GetSportsAndTypes()
	{
		$sportsAndTypes = array();
		
		$conn = self::GetDbConnection();
		$sql = "SELECT id, name FROM Sport ORDER BY id ASC";
		$result = $conn->query($sql);
		
		if ($result->num_rows > 0) 
		{
			while($row = $result->fetch_assoc()) 
			{
			    $st = self::GetSportTypes(intval($row["id"]));

				$sportsAndTypes[] = new SportAndTypes(intval($row["id"]), $row["name"], $st);
			}
		} 
		else 
		{
			//echo "0 results";
		}		
		self::CloseDbConnection();

		return $sportsAndTypes;
	}
	
	public static function GetSportAndType($sportid, $typeid)
	{
		$sportAndType;
		
		$conn = self::GetDbConnection();
		$sql = "SELECT name FROM Sport where id = ".$sportid."";
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
		$sql = "SELECT name FROM Sport where id = ".$id."";
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

		$sql = "SELECT id, name FROM SportType WHERE sportid = ".$sportId." ORDER BY id ASC";
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
		$sql = "SELECT name FROM SportType WHERE id = ".$id."";
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