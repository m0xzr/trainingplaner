<?php

include_once  'models.php';
include_once  'runalyze_dal.php';
include_once  'credentials_provider.php';

/*

CREATE TABLE trainingplaner.Training
(
ID int(10) unsigned NOT NULL AUTO_INCREMENT,
Week int(10) unsigned NOT NULL,
Day int NOT NULL,
Sport int(10) unsigned,
Type int(11),
DurationHours int(10) unsigned DEFAULT 0,
DurationMinutes int(10) unsigned DEFAULT,
PlanedDone bool NOT NULL DEFAULT FALSE,
Annotation varchar(255),
PRIMARY KEY (ID),
CONSTRAINT fk_Week FOREIGN KEY (Week)
REFERENCES trainingplaner.Week(id),
CONSTRAINT fk_Sport FOREIGN KEY (Sport)
REFERENCES runalyze.runalyze_sport(id),
CONSTRAINT fk_Type FOREIGN KEY (Type)
REFERENCES runalyze.runalyze_type(id)
)

CREATE TABLE trainingplaner.Week
(
ID int(10) unsigned NOT NULL AUTO_INCREMENT,
Plan int(10) unsigned NOT NULL,
WeekNumber int(10) unsigned NOT NULL,
Annotation varchar(255),
PRIMARY KEY (ID),
CONSTRAINT fk_Plan FOREIGN KEY (Plan)
REFERENCES trainingplaner.Plan(id)
)

CREATE TABLE trainingplaner.Plan
(
ID int(10) unsigned NOT NULL AUTO_INCREMENT,
Title varchar(255) NOT NULL UNIQUE,
PRIMARY KEY (ID)
)

*/
class PlanerDAL
{
	private static $db_con_isopened = false;
	private static $db_con;
	private static $numberOfDbCalls = 0;			//Anzahl an Anfragen an die DB
	
	
	private static function GetDbConnection()
	{
		self::$numberOfDbCalls++;
		if(self::$db_con_isopened == false)
		{
			self::$db_con = new mysqli(CredentialsProvider::$tphost, CredentialsProvider::$tpusername, CredentialsProvider::$tppassword, CredentialsProvider::$tpdb);
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
	
	private static function CloseDbConnection()
	{
		self::$numberOfDbCalls--;
		if(self::$numberOfDbCalls == 0)
		{
			self::$db_con->close();	
			self::$db_con_isopened = false;
		}
	}
	
	public static function GetPlans()
	{
		$retVal = array();
		
		$conn = self::GetDbConnection();
		$sql = "SELECT id, title FROM Plan ORDER BY id ASC";
		$result = $conn->query($sql);
		if ($result->num_rows > 0) 
		{
			while($row = $result->fetch_assoc()) 
			{
				$id = intval($row["id"]);
				$title = $row["title"];
				if(isset($id))
				{
					$weeks = self::GetWeeks($id);
					array_push($retVal, new Plan($id, $title, $weeks));
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
	
	public static function CreatePlan($title)
	{
		$conn = self::GetDbConnection();		
		$retVal = 0;		
		$sql = sprintf("INSERT INTO Plan (Title) VALUES ('%s')", $conn->real_escape_string($title));
		if ($conn->query($sql) === TRUE) 
		{
			//echo "New record created successfully";
			$retVal = self::GetPlan($conn->insert_id);
		} 
		else 
		{
			//echo "Error: " . $sql . "<br>" . $conn->error;
		}		
		self::CloseDbConnection();	
		return $retVal;
	}
	
	public static function EditPlan($id, $newTitle)
	{
		$conn = self::GetDbConnection();
		$sql = sprintf("UPDATE Plan set Title = '%s' WHERE ID = %d", $conn->real_escape_string($newTitle), $id);
		if ($conn->query($sql) === TRUE) 
		{
			//echo "Record updated successfully";
		} 
		else 
		{
			//echo "Error: " . $sql . "<br>" . $conn->error;
		}		
		self::CloseDbConnection();
	}
	
	public static function RemovePlan($id)
	{
	    $weeks = self::GetWeeks($id);
	    for($i = 0; $i < sizeof($weeks); $i++)
	    {
	        self::RemoveWeek($weeks[$i]->ID);
	    }
	    
	    $conn = self::GetDbConnection();
		$sql = sprintf("DELETE FROM Plan where id = %d", $id);
		if ($conn->query($sql) === TRUE) 
		{
			//echo "Record deleted successfully";
		} 
		else 
		{
			//echo "Error: " . $sql . "<br>" . $conn->error;
		}
		self::CloseDbConnection();
	}
	
	public static function GetPlan($ID)
	{
		$id = -1;
		$title;
		$retVal = null;
		
		$conn = self::GetDbConnection();
		$sql = sprintf("SELECT id, title FROM Plan WHERE id = %d", $ID);
		$result = $conn->query($sql);
		if ($result->num_rows > 0) 
		{
			while($row = $result->fetch_assoc()) 
			{
				$id = intval($row["id"]);
				$title = $row["title"];
			}
		} 
		else 
		{
			//echo "0 results";
		}
		
		if($id != -1)
		{
		    $weeks = self::GetWeeks($id);
		    $retVal = new Plan($id, $title, $weeks);
		}
		
		self::CloseDbConnection();
		
		return $retVal;
	}
	
	public static function GetPlanByTitle($title)
	{
		$id = -1;
		$title;
		$retVal = null;
		
		$conn = self::GetDbConnection();
		$sql = sprintf("SELECT id, title FROM Plan WHERE Title = '%s'", $conn->real_escape_string($title));
		$result = $conn->query($sql);
		if ($result->num_rows > 0) 
		{
			while($row = $result->fetch_assoc()) 
			{
				$id = intval($row["id"]);
				$title = $row["title"];
			}
		} 
		else 
		{
			$id = self::CreatePlan($title);			
			//echo "0 results";
		}		
		
		if($id != -1)
		{
		    $weeks = self::GetWeeks($id);
		    $retVal = new Plan($id, $title, $weeks);
		}
		
		self::CloseDbConnection();
		
		return $retVal;
	}
	
	public static function GetWeeks($planId)
	{
		$weeks = array();
		
		$conn = self::GetDbConnection();
		$sql = sprintf("SELECT id FROM Week WHERE Plan = %d", $planId);
		$result = $conn->query($sql);
		if ($result->num_rows > 0) 
		{
			while($row = $result->fetch_assoc()) 
			{
			    $w = self::GetWeek(intval($row["id"]));
			    if($w != null)
			    {
			        array_push($weeks, $w);
			    }
			}
		} 
		else 
		{
			//echo "0 results";
		}		
		self::CloseDbConnection();
		
		return $weeks;
	}
	
	public static function GetWeek($weekid)
	{
	    $retVal = null;
	    
		$trainings = array();
		$id;
		$weekNumber;
		$annotation;
		
		$conn = self::GetDbConnection();
		$sql = sprintf("SELECT id, WeekNumber, Annotation FROM Week WHERE id = %d", $weekid);
		$result = $conn->query($sql);
		if ($result->num_rows > 0) 
		{
			while($row = $result->fetch_assoc()) 
			{
				$id = intval($row["id"]);
				$weekNumber = intval($row["WeekNumber"]);
				$annotation = $row["Annotation"];
				$trainings = self::GetTrainings($id);
				$retVal = new Week($id, $weekNumber, $annotation, $trainings);
			}
		} 
		else 
		{
			//echo "0 results";
		}		
		self::CloseDbConnection();
		
		return $retVal;
	}
	
	public static function AddWeek($planID, $annotation)
	{
		$conn = self::GetDbConnection();
		
		$weeks = self::GetWeeks($planID);
		$weekNumber = 0;
		for($i = 0; $i < sizeof($weeks); $i++)
		{
			if($weeks[$i]->WeekNumber > $weekNumber)
			{
				$weekNumber = $weeks[$i]->WeekNumber;
			}
		}
		$weekNumber++;
		

		if(!isset($annotation))
		{
			$annotation = NULL;
		}
		
		$sql = sprintf("INSERT INTO Week (Plan, WeekNumber, Annotation) VALUES (%d, %d, '%s')", 
							$planID, 
							$weekNumber,
							$conn->real_escape_string($annotation));
		$id = -1;
		if ($conn->query($sql) === TRUE) 
		{
			//echo "New record created successfully";
			$id = $conn->insert_id;
		} 
		else 
		{
			//echo "Error: " . $sql . "<br>" . $conn->error;
		}
		self::CloseDbConnection();
		
		if($id != -1)
		{
			return self::GetWeek($id);
		}
		
		return NULL;
	}
	
	public static function EditWeek($id, $annotation)
	{
		$conn = self::GetDbConnection();
		$sql = sprintf("UPDATE Week set Annotation = '%s' WHERE ID = %d", $conn->real_escape_string($annotation), $id);
		if ($conn->query($sql) === TRUE) 
		{
			//echo "Record updated successfully";
		} 
		else 
		{
			//echo "Error: " . $sql . "<br>" . $conn->error;
		}
		self::CloseDbConnection();
	}
	
	public static function RemoveWeek($id)
	{
	    $trainings = self::GetTrainings($id);
	    for($i = 0; $i < sizeof($trainings); $i++)
	    {
	        self::RemoveTraining($trainings[$i]->ID);
	    }
	    
	    $conn = self::GetDbConnection();
		$sql = sprintf("DELETE FROM Week where id = %d", $id);
		if ($conn->query($sql) === TRUE) 
		{
			//echo "Record deleted successfully";
		} 
		else 
		{
			//echo "Error: " . $sql . "<br>" . $conn->error;
		}
		self::CloseDbConnection();
	}
	
	public static function GetTrainings($weekId)
	{
		$trainings = array();
		
		$conn = self::GetDbConnection();
		$sql = sprintf("SELECT id FROM Training WHERE Week = %d", $weekId);
		$result = $conn->query($sql);
		if ($result->num_rows > 0) 
		{
			while($row = $result->fetch_assoc()) 
			{
			    $t = self::GetTraining(intval($row["id"]));
			    if($t != null)
			    {
				    array_push($trainings, $t);
			    }
			}
		} 
		else 
		{
			//echo "0 results";
		}		
		self::CloseDbConnection();
		
		return $trainings;
	}
	
	public static function GetTraining($trainingid)
	{
	    $retVal = null;

		$conn = self::GetDbConnection();
		$sql = sprintf("SELECT id, Day, Sport, Type, Annotation, DurationHours, DurationMinutes, PlanedDone FROM Training WHERE id = %d", $trainingid);
		$result = $conn->query($sql);
		if ($result->num_rows > 0) 
		{
			while($row = $result->fetch_assoc()) 
			{
				$id = intval($row["id"]);
				$day = intval($row["Day"]);
				$sport = RunalyzeDAL::GetSportNameById(intval($row["Sport"]));
				$type = RunalyzeDAL::GetSportTypeNameById(intval($row["Type"]));
				$annotation = $row["Annotation"];
				$durationHours = intval($row["DurationHours"]);
				$durationMinutes = intval($row["DurationMinutes"]);
				$planedDone = boolval($row["PlanedDone"]);
				$sportAndType = RunalyzeDAL::GetSportAndType(intval($row["Sport"]), intval($row["Type"]));
				
				$retVal = new Training($id, $day, $annotation, $durationHours, $durationMinutes, $sportAndType, $planedDone);
			}
		} 
		else 
		{
			//echo "0 results";
		}		
		self::CloseDbConnection();

		return $retVal;
	}
	
	public static function AddTraining($week, $day, $sport, $type, $annotation, $durationHours, $durationMinutes, $planedDone)
	{
		$conn = self::GetDbConnection();
		$sql = sprintf("INSERT INTO Training (Week, Day, Sport, Type, Annotation, DurationHours, DurationMinutes, PlanedDone) VALUES (%d, %d, %d, %d, '%s', %d, %d, %b)",
							$week,
							$day,
							$sport,
							$type,
							$conn->real_escape_string($annotation),
							$durationHours,
							$durationMinutes,
							$planedDone);
		$retVal;
		if ($conn->query($sql) === TRUE) 
		{
			$sportAndType = RunalyzeDAL::GetSportAndType($sport, $type);
			$retVal = new Training($conn->insert_id, $day, $annotation, $durationHours, $durationMinutes, $sportAndType, $planedDone);
		} 
		else 
		{
			//echo "Error: " . $sql . "<br>" . $conn->error;
		}
		self::CloseDbConnection();
		
		return $retVal;
	}
	
	public static function EditTraining($id, $sport, $type, $annotation, $durationHours, $durationMinutes)
	{
		$conn = self::GetDbConnection();
		
		$typeSet = "Type = ".$conn->real_escape_string($type);
		if($type == 0)
		{
			$typeSet = "Type = NULL";
		}
		
		$sql = sprintf("UPDATE Training set Sport = %s, ".$typeSet.", Annotation = '%s', DurationHours = %d, DurationMinutes = %d WHERE ID = %d",
								$conn->real_escape_string($sport),
								$conn->real_escape_string($annotation),
								$durationHours, 
								$durationMinutes,
								$id);

		if ($conn->query($sql) === TRUE) 
		{
			//echo "Record updated successfully";
		} 
		else 
		{
			//echo "Error: " . $sql . "<br>" . $conn->error;
		}
		self::CloseDbConnection();

		return self::GetTraining($id);
	}
	
	public static function RemoveTraining($id)
	{
		$conn = self::GetDbConnection();
		$sql = sprintf("DELETE FROM Training where id = %d", $id);
		$retVal = 0;
		if ($conn->query($sql) === TRUE) 
		{
			//echo "Record deleted successfully";
			$retVal = $conn->insert_id;
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