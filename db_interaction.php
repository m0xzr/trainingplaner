<?php  
	include_once 'planer_dal.php';
	include_once 'user_dal.php';
	include_once 'sport_dal.php';
	include_once 'models.php';
	
	if(isset($_POST) and $_SERVER['REQUEST_METHOD'] == "POST")
	{
		$request = json_decode(file_get_contents("php://input"));
		$action = $request->action;
		$obj = $request->obj;
		if(!isset($action) || !isset($obj))
		{
			exit;
		}
		
		switch ($action) 
		{
			case "GetUserData":
				if(isset($obj->username))
				{
					print_r(json_encode(UserDAL::GetUserData($obj->username)));
				}
				break;
			case "InsertUserData":
				if(isset($obj->userid) && isset($obj->weight) && isset($obj->hrmax) && isset($obj->hrrest) && ctype_digit((string)$obj->userid) && is_numeric($obj->weight) && ctype_digit((string)$obj->hrmax) && ctype_digit((string)$obj->hrrest))
				{
					print_r(json_encode(UserDAL::InsertUserData($obj->userid, $obj->weight, $obj->hrmax, $obj->hrrest)));
				}
				break;
			case "GetSportsAndTypes":
				print_r(json_encode(SportDAL::GetSportsAndTypes()));
				break;
			case "GetSportNameById":
				if(isset($obj->id))
				{
					print_r(json_encode(SportDAL::GetSportNameById(intval($obj->id))));
				}
				break;
			case "GetSportTypeNameById":
				if(isset($obj->id))
				{
					print_r(SportDAL::GetSportTypeNameById(intval($obj->id)));
				}
				break;
			case "CreatePlan":
				if(isset($obj->title))
				{
					print_r(json_encode(PlanerDAL::CreatePlan($obj->title)));
				}
				break;
			case "GetPlans":
				print_r(json_encode(PlanerDAL::GetPlans()));
				break;
			case "CreatePlan":
				if(isset($obj->title))
				{
					print_r(PlanerDAL::CreatePlan($obj->title));
				}
				break;
			case "EditPlan":
				if(isset($obj->id) && isset($obj->title))
				{
					PlanerDAL::EditPlan($obj->id, $obj->title);
				}
				break;
			case "ActivatePlan":
				if(isset($obj->id))
				{
					PlanerDAL::ActivatePlan($obj->id);
				}
				break;
			case "GetPlan":
				if(isset($obj->id))
				{
					print_r(json_encode(PlanerDAL::GetPlan($obj->id)));
				}
				break;
			case "GetPlanByTitle":
				if(isset($obj->title))
				{
					print_r(json_encode(PlanerDAL::GetPlanByTitle($obj->title)));
				}
				break;
			case "RemovePlan":
				if(isset($obj->id))
				{
					PlanerDAL::RemovePlan($obj->id);
				}
				break;
			case "AddWeek":
				if(isset($obj->id))
				{
					print_r(json_encode(PlanerDAL::AddWeek($obj->id, $obj->annotation)));
				}
				break;
			case "EditWeek":
				if(isset($obj->id) && isset($obj->annotation))
				{
					PlanerDAL::EditWeek($obj->id, $obj->annotation);
				}
				break;
			case "RemoveWeek":
				if(isset($obj->id))
				{
					PlanerDAL::RemoveWeek($obj->id);
				}
				break;
			case "GetTrainings":
				if(isset($obj->id))
				{
					PlanerDAL::GetTrainings($obj->id);
				}
				break;
			case "AddTraining":
				if(isset($obj->week) && isset($obj->day) && isset($obj->sport) && isset($obj->type) && isset($obj->annotation) && isset($obj->durationhours) && isset($obj->durationminutes) && isset($obj->planeddone) && isset($obj->avghr))
				{
					print_r(json_encode(PlanerDAL::AddTraining($obj->week, $obj->day, $obj->sport, $obj->type, $obj->annotation, $obj->durationhours, $obj->durationminutes, $obj->planeddone, $obj->avghr)));
				}
				break;
			case "EditTraining":
				if(isset($obj->id)/* && isset($obj->type)*/ && isset($obj->annotation) && isset($obj->durationhours) && isset($obj->durationminutes) && ctype_digit((string)$obj->durationhours) && ctype_digit((string)$obj->durationminutes) && isset($obj->avghr) && ctype_digit((string)$obj->avghr))
				{
					print_r(json_encode(PlanerDAL::EditTraining($obj->id, $obj->sport, $obj->type, $obj->annotation, $obj->durationhours, $obj->durationminutes, $obj->avghr)));
				}
				break;
			case "RemoveTraining":
				if(isset($obj->id))
				{
					PlanerDAL::RemoveTraining($obj->id);
				}
				break;
			case "AvgHrOfSimilarTrainings":
				if(isset($obj->sport) && isset($obj->type) && isset($obj->durationhours) && isset($obj->durationminutes) && ctype_digit((string)$obj->durationhours) && ctype_digit((string)$obj->durationminutes))
				{
					print_r((PlanerDAL::AvgHrOfSimilarTrainings($obj->sport, $obj->type, $obj->durationhours, $obj->durationminutes)));
				}
				break;
			case "GetThought":
				if(isset($obj->trainingid))
				{
					print_r(PlanerDAL::GetThought($obj->trainingid));
				}
				break;
			case "EditThought":
				if(isset($obj->trainingid) && isset($obj->thought))
				{
					print_r(PlanerDAL::EditThought($obj->trainingid, $obj->thought));
				}
				break;
			case "RemoveThought":
				if(isset($obj->trainingid))
				{
					PlanerDAL::RemoveThought($obj->trainingid);
				}
				break;
			//default:
		}
	}
 
?>