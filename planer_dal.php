<?php

include_once  'sport_dal.php';
include_once  'dal.php';

class PlanerDAL extends DAL
{	
	public static function GetPlans()
	{
		$retVal = array();
		
		$conn = self::GetDbConnection();
		$sql = "SELECT id, title, active, DATE_FORMAT(creationdate, '%d.%m.%Y') as formated FROM Plan ORDER BY creationdate ASC";
		$result = $conn->query($sql);
		if ($result->num_rows > 0) 
		{
			while($row = $result->fetch_assoc()) 
			{
				$id = intval($row["id"]);
				$title = $row["title"];
				$active = boolval($row["active"]);
				$formated = $row["formated"];
				if(isset($id))
				{
					$weeks = self::GetWeeks($id);
					array_push($retVal, new Plan($id, $title, $active, $weeks, $formated));
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
	
	public static function GetPlanIds()
	{
		$retVal = array();
		
		$conn = self::GetDbConnection();
		$sql = "SELECT id FROM Plan ORDER BY id ASC";
		$result = $conn->query($sql);
		if ($result->num_rows > 0) 
		{
			while($row = $result->fetch_assoc()) 
			{
				array_push($retVal, intval($row["id"]));
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
		$sql = sprintf("INSERT INTO Plan (Title, Active) VALUES ('%s', false)", $conn->real_escape_string($title));
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

		//Alle anderen deaktivieren
		self::ActivatePlan($retVal->ID);
		
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
	
	public static function ActivatePlan($IdToActivate)
	{
		$planIds = self::GetPlanIds();

		$conn = self::GetDbConnection();
		
		for($i = 0; $i < sizeof($planIds); $i++)		//zuerst alle deaktivieren
	    {
			$sql = sprintf("UPDATE Plan set Active = false WHERE ID = %d", $planIds[$i]);
			if ($conn->query($sql) === TRUE) 
			{
				//echo "Record updated successfully";
			} 
			else 
			{
				//echo "Error: " . $sql . "<br>" . $conn->error;
			}
		}		
		
		$sql = sprintf("UPDATE Plan set Active = true WHERE ID = %d", $IdToActivate);
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
		$active;
		$formated;
		$retVal = null;
		
		$conn = self::GetDbConnection();
		$sql = sprintf("SELECT id, title, active, DATE_FORMAT(creationdate, '%%d.%%m.%%Y') as formated FROM Plan WHERE id = %d", $ID);
		$result = $conn->query($sql);
		if ($result->num_rows > 0) 
		{
			while($row = $result->fetch_assoc()) 
			{
				$id = intval($row["id"]);
				$title = $row["title"];
				$active = boolval($row["active"]);
				$formated = $row["formated"];
			}
		} 
		else 
		{
			//echo "0 results";
		}
		
		if($id != -1)
		{
		    $weeks = self::GetWeeks($id);
		    $retVal = new Plan($id, $title, $active, $weeks, $formated);
		}
		
		self::CloseDbConnection();
		
		return $retVal;
	}
	
	public static function GetPlanByTitle($title)
	{
		$id = -1;
		$title;
		$active;
		$formated;
		$retVal = null;
		
		$conn = self::GetDbConnection();
		$sql = sprintf("SELECT id, title, active, DATE_FORMAT(creationdate, '%%d.%%m.%%Y') as formated FROM Plan WHERE Title = '%s'", $conn->real_escape_string($title));
		$result = $conn->query($sql);
		if ($result->num_rows > 0) 
		{
			while($row = $result->fetch_assoc()) 
			{
				$id = intval($row["id"]);
				$title = $row["title"];
				$active = boolval($row["active"]);
				$formated = $row["formated"];
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
		    $retVal = new Plan($id, $title, $active, $weeks, $formated);
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
		$sql = sprintf("SELECT id, Day, Sport, Type, Annotation, DurationHours, DurationMinutes, PlanedDone, AvgHR FROM Training WHERE id = %d", $trainingid);
		$result = $conn->query($sql);
		if ($result->num_rows > 0) 
		{
			while($row = $result->fetch_assoc()) 
			{
				$id = intval($row["id"]);
				$day = intval($row["Day"]);
				$sport = SportDAL::GetSportNameById(intval($row["Sport"]));
				$type = SportDAL::GetSportTypeNameById(intval($row["Type"]));
				$annotation = $row["Annotation"];
				$durationHours = intval($row["DurationHours"]);
				$durationMinutes = intval($row["DurationMinutes"]);
				$planedDone = boolval($row["PlanedDone"]);
				$avgHR = intval($row["AvgHR"]);
				$sportAndType = SportDAL::GetSportAndType(intval($row["Sport"]), intval($row["Type"]));
				$thought = self::GetThought($id);
				
				$retVal = new Training($id, $day, $annotation, $durationHours, $durationMinutes, $sportAndType, $planedDone, $avgHR, $thought);
			}
		} 
		else 
		{
			//echo "0 results";
		}		
		self::CloseDbConnection();

		return $retVal;
	}
	
	public static function AddTraining($week, $day, $sport, $type, $annotation, $durationHours, $durationMinutes, $planedDone, $avgHR)
	{
		$conn = self::GetDbConnection();
		$sql = sprintf("INSERT INTO Training (Week, Day, Sport, Type, Annotation, DurationHours, DurationMinutes, PlanedDone, AvgHR) VALUES (%d, %d, %d, %d, '%s', %d, %d, %b, %d)",
							$week,
							$day,
							$sport,
							$type,
							$conn->real_escape_string($annotation),
							$durationHours,
							$durationMinutes,
							$planedDone,
							0);
		$retVal;
		if ($conn->query($sql) === TRUE) 
		{
			$sportAndType = SportDAL::GetSportAndType($sport, $type);
			$retVal = new Training($conn->insert_id, $day, $annotation, $durationHours, $durationMinutes, $sportAndType, $planedDone, $avgHR);
		} 
		else 
		{
			//echo "Error: " . $sql . "<br>" . $conn->error;
		}
		self::CloseDbConnection();
		
		return $retVal;
	}
	
	public static function EditTraining($id, $sport, $type, $annotation, $durationHours, $durationMinutes, $avgHR)
	{
		$conn = self::GetDbConnection();
		
		$typeSet = "Type = ".$conn->real_escape_string($type);
		if($type == 0)
		{
			$typeSet = "Type = NULL";
		}
		
		$sql = sprintf("UPDATE Training set Sport = %s, ".$typeSet.", Annotation = '%s', DurationHours = %d, DurationMinutes = %d, AvgHR = %d WHERE ID = %d",
								$conn->real_escape_string($sport),
								$conn->real_escape_string($annotation),
								$durationHours, 
								$durationMinutes,
								$avgHR,
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
	
	public static function AvgHrOfSimilarTrainings($sport, $type, $durationHours, $durationMinutes)
	{
	    $retVal = null;
		
		$offSet = 0.05;
		$maxDuration = ($durationHours * 60 + $durationMinutes) * (1 + $offSet);
		$minDuration = ($durationHours * 60 + $durationMinutes) * (1 - $offSet);

		$conn = self::GetDbConnection();
		$sql = sprintf("SELECT avg(AvgHR) as av FROM Training WHERE Sport = %d AND Type = %d and PlanedDone = %b and DurationHours * 60 + DurationMinutes between %d and %d and AvgHR > 0", 
						$sport,
						$type,
						true,
						$minDuration,
						$maxDuration);
		$result = $conn->query($sql);
		if ($result->num_rows > 0) 
		{
			while($row = $result->fetch_assoc()) 
			{
				$retVal = intval($row["av"]);
			}
		} 
		else 
		{
			//echo "0 results";
		}		
		self::CloseDbConnection();

		return $retVal;
	}
	
	public static function GetThought($trainingid)
	{
	    $retVal = null;

		$conn = self::GetDbConnection();
		$sql = sprintf("SELECT thought FROM Thoughts WHERE training = %d", $trainingid);
		$result = $conn->query($sql);
		if ($result->num_rows > 0) 
		{
			while($row = $result->fetch_assoc()) 
			{
				$retVal = $row["thought"];
			}
		} 
		else 
		{
			//echo "0 results";
		}		
		self::CloseDbConnection();

		return $retVal;
	}
	
	public static function HasThought($trainingid)
	{
	    $retVal = false;

		$conn = self::GetDbConnection();
		$sql = sprintf("SELECT count(training) as c FROM Thoughts WHERE training = %d", $trainingid);
		$result = $conn->query($sql);
		if ($result->num_rows > 0) 
		{
			while($row = $result->fetch_assoc()) 
			{
				if(intval($row["c"]) == 1) {
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
	
	public static function AddThought($trainingid, $thought)
	{
		$conn = self::GetDbConnection();
		$sql = sprintf("INSERT INTO Thoughts (Training, Thought) VALUES (%d, '%s')",
							$trainingid,
							$conn->real_escape_string($thought));
		$retVal = null;
		if ($conn->query($sql) == TRUE) 
		{
			$retVal = $thought;
		} 
		else 
		{
			//echo "Error: " . $sql . "<br>" . $conn->error;
		}
		self::CloseDbConnection();
		
		return $retVal;
	}
	
	public static function RemoveThought($trainingid)
	{
		$conn = self::GetDbConnection();
		$sql = sprintf("DELETE FROM Thoughts where training = %d", $trainingid);
		$retVal = 0;
		if ($conn->query($sql) == TRUE) 
		{
			//echo "Record deleted successfully";
		} 
		else 
		{
			echo "Error: " . $sql . "<br>" . $conn->error;
		}
		self::CloseDbConnection();
	}
	
	public static function EditThought($trainingid, $thought)
	{
		if(self::HasThought($trainingid) == false)			//noch kein Gedanke -> erstellen
		{
			return self::AddThought($trainingid, $thought);
		}
		
		$retVal = null;
		
		$conn = self::GetDbConnection();
		
		$sql = sprintf("UPDATE Thoughts set Thought = '%s' WHERE training = %d",
								$conn->real_escape_string($thought),
								$trainingid);

		if ($conn->query($sql) === TRUE) 
		{
			$retVal = $thought;
			//echo "Record updated successfully";
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