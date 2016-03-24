<?php

include_once 'planer_dal.php';
include_once 'runalyze_dal.php';

class Training implements JsonSerializable 
{
	public $ID;
	public $Day;		//1-7 (Mo-So)
	public $Annotation;
	public $SportAndType;
	public $DurationHours;
	public $DurationMinutes;
	
	public function __construct($iD, $day, $annotation, $durationHours, $durationMinutes, $sportAndType) 
	{
	  $this->ID = $iD;
	  $this->Day = $day;
	  $this->Annotation = $annotation;
	  $this->DurationHours = $durationHours;
	  $this->DurationMinutes = $durationMinutes;
	  $this->SportAndType = $sportAndType;	 
	}
	
	public function jsonSerialize() 
	{
        return 
		[
            'id' => $this->ID,
            'day' => $this->Day,
			'annotation' => $this->Annotation,
			'durationhours' => $this->DurationHours,
			'durationminutes' => $this->DurationMinutes,
			'sportandtype' => $this->SportAndType			
        ];
    }
}
	
class Week implements JsonSerializable
{
	public $ID;
	public $WeekNumber;		//Nummer der Woche im Trainingsplan
	public $Annotation;
	public $Trainings = array();
	public $TotalAmountHours = 0;
	public $TotalAmountMinutes = 0;
	
	public function __construct($iD, $weekNumber, $annotation, $trainings) 
	{
		$this->ID = $iD;
		$this->WeekNumber = $weekNumber;
		$this->Annotation = $annotation;
		$this->Trainings = $trainings;
		
		for($i = 0; $i < sizeof($this->Trainings); $i++)
	    {
	        $this->TotalAmountHours += $this->Trainings[$i]->DurationHours;
	        $this->TotalAmountMinutes += $this->Trainings[$i]->DurationMinutes;
			if($this->TotalAmountMinutes >= 60)
			{
				$this->TotalAmountHours++;
				$this->TotalAmountMinutes %= 60;
			}
	    }
	}
	
	public function jsonSerialize() 
	{
		
        return 
		[
            'id' => $this->ID,
            'weeknumber' => $this->WeekNumber,
			'annotation' => $this->Annotation.' (gesamter Umfang '.$this->TotalAmountHours.'h '.$this->TotalAmountMinutes.'min)',
			'trainings' => $this->Trainings,
        ];
    }
}
	
class Plan implements JsonSerializable
{
	public $ID;
	public $Title;
	public $Weeks = array();
	
	public function __construct($iD, $title, $weeks) 
	{
		$this->ID = $iD;
		$this->Title = $title;
		$this->Weeks = $weeks;
	}
	
	public function jsonSerialize() 
	{
        return 
		[
            'id' => $this->ID,
            'title' => $this->Title,
			'weeks' => $this->Weeks
        ];
    }
}

class SportAndTypes implements JsonSerializable
{
	public $ID;
    public $Types = array();
    public $Sport;
    
	public function __construct($id, $sport, $types) 
	{
		$this->ID = $id;
		$this->Sport = $sport;
		$this->Types = $types;
	}
	
	public function jsonSerialize() 
	{
        return 
		[
			'id' => $this->ID,
            'sport' => utf8_encode($this->Sport),
            'types' => $this->Types
        ];
    }
}

class Type implements JsonSerializable
{
	public $ID;
    public $Type;
    
	public function __construct($id, $type) 
	{
		$this->ID = $id;
		$this->Type = $type;
	}
	
	public function jsonSerialize() 
	{
        return 
		[
			'id' => $this->ID,
            'type' => utf8_encode($this->Type)
        ];
    }
}

?>

