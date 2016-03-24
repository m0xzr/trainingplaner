<?php  
	include_once 'planer_dal.php';
	include_once 'runalyze_dal.php';
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
			case "GetSportsAndTypes":
				print_r(json_encode(RunalyzeDAL::GetSportsAndTypes()));
				break;
			case "GetSportNameById":
				if(isset($obj->id))
				{
					print_r(json_encode(RunalyzeDAL::GetSportNameById(intval($obj->id))));
				}
				break;
			case "GetSportTypeNameById":
				if(isset($obj->id))
				{
					print_r(RunalyzeDAL::GetSportTypeNameById(intval($obj->id)));
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
				if(isset($obj->week) && isset($obj->day) && isset($obj->sport) && isset($obj->type) && isset($obj->annotation) && isset($obj->durationhours) && isset($obj->durationminutes))
				{
					print_r(json_encode(PlanerDAL::AddTraining($obj->week, $obj->day, $obj->sport, $obj->type, $obj->annotation, $obj->durationhours, $obj->durationminutes)));
				}
				break;
			case "EditTraining":
				if(isset($obj->id)/* && isset($obj->type)*/ && isset($obj->annotation) && isset($obj->durationhours) && isset($obj->durationminutes))
				{
					print_r(json_encode(PlanerDAL::EditTraining($obj->id, $obj->sport, $obj->type, $obj->annotation, $obj->durationhours, $obj->durationminutes)));
				}
				break;
			case "RemoveTraining":
				if(isset($obj->id))
				{
					PlanerDAL::RemoveTraining($obj->id);
				}
				break;
			//default:
		}
	}
 
?>