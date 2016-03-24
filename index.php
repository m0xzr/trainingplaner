<!DOCTYPE html>
<html ng-app="app">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Trainingplaner</title>
<link rel="icon" type="image/png" href="favicon-32x32.png" sizes="32x32" />
<link rel="icon" type="image/png" href="favicon-16x16.png" sizes="16x16" />

<link href="bootstrap/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="css/xeditable.css">
<link rel="stylesheet" href="css/trainingplaner.css">
<script type="text/javascript" src="js/angular.min.js"></script>
<script type="text/javascript" src="js/xeditable.min.js"></script>
<script type='text/javascript' src="js/jquery-1.12.1.min.js"></script>
<script type='text/javascript' src="bootstrap/js/bootstrap.min.js"></script>

<script src="controller.js"></script>
<style type="text/css">

</style>
</head>

<body>
<?php  
	include_once 'runalyze_dal.php';
	$pw = $_GET['pw'];
	if(RunalyzeDAL::CheckPw($pw) != 1)
	{
		echo 'WRONG PW';
		exit(1);
	}
?>

<div ng-controller="Ctrl" id="main">
<div id="plans">
	<div class="btn-group" role="group" aria-label="...">
	  <button  ng-repeat="p in plans" ng-click="getPlan(p)" onClick="window.location.href='index.php<?php echo "?pw=".$pw ?>#plan'" type="button" class="btn btn-default">{{p.title}}</button>
	</div>
	<a data-container="body" data-toggle="popover" data-placement="right" id="new-plan-popover" popover><span class="glyphicon glyphicon-plus" aria-hidden="true"></span>
			<div id="popover-head-plan" class="hide">Titel des Plans</div>
			<div id="popover-content-plan" class="hide"> 
				<table><tr>
					<td><input type="text" name="name" ng-model="planTitle" size="70" /></td>
					<td>&nbsp;</td>
					<td><button ng-click="addPlan(plans)" class="btn btn-primary" id="close-popover" data-toggle="clickover"><span class="glyphicon glyphicon-ok" aria-hidden="true"></span></button></td>
				</tr></table>
			</div>
	</a>
</div>
<div id="plan_header">
	<h1 id="plan">{{ plan.title || '' }}</h1>
	<button class="btn btn-danger btn-xs" id="abs_right" ng-click="removePlan(plans, plan)"><span class="glyphicon glyphicon-minus" aria-hidden="true"></span></button>
	<ul class="nav nav-tabs">
		<li ng-repeat="week in plan.weeks" ng-class="{active: $index == 0}"><a data-toggle="tab" href="#week{{$index + 1}}">Woche {{ week.weeknumber || "empty" }}</a></li>
		<li>
			<a data-container="body" data-toggle="popover" data-placement="right" id="new-week-popover" popover><span class="glyphicon glyphicon-plus" aria-hidden="true"></span>
				<div id="popover-head-week" class="hide">Information zur Woche</div>
				<div id="popover-content-week" class="hide"> 
					<table><tr>
						<td><input type="text" name="name" ng-model="weekTitle" size="70" /></td>
						<td>&nbsp;</td>
						<td><button ng-click="addWeek(plan)" class="btn btn-primary" id="close-popover" data-toggle="clickover"><span class="glyphicon glyphicon-ok" aria-hidden="true"></span></button></td>
					</tr></table>
				</div>
			</a>
		</li>
	</ul>
</div>
<div class="tab-content" id="plan_content">
<div class="tab-pane fade in" id="week{{$index + 1}}" ng-repeat="week in plan.weeks" ng-class="{active: $index == 0}" >
<button class="btn btn-danger btn-xs"  id="abs_right" ng-click="removeWeek(plan, week)"><span class="glyphicon glyphicon-minus" aria-hidden="true"></span></button>
<div class="panel panel-info">
  <div class="panel-heading">
    <h3 class="panel-title">Information zur Woche</h3>
  </div>
  <div class="panel-body">
    {{ week.annotation || '' }}
 </div>
</div>
<div ng-repeat="d in range(7) track by $index" >
  <table class="table table-bordered table-hover table-condensed">
	<caption class="weekDay">{{ showDay($index + 1) }}</caption>
    <tr style="font-weight: bold">
      <td style="width:15%">Sport</td>
      <td style="width:15%">Typ</td>
      <td style="width:20%">Umfang</td>
      <td style="width:50%">Notizen</td>
    </tr>
    <tr ng-repeat="training in week.trainings | filter: { day: ($index + 1) }">
      <td>
        <!-- editable status (select-local) | {{ showSport(training) }} -->
        <span editable-select="training.sportandtype.id" e-name="sport" e-form="rowform" e-ng-options="i.id as i.sport for i in sportsandtypes" e-ng-change="changeData(training, $data)">
          {{ training.sportandtype.sport }}
        </span>
      </td>
      <td>
        <!-- editable group (select-remote)foos in elements | onshow="loadTypes(training)" {{ showType(training) }} i.id as i.type for i in (sportsandtypes | filter: filterTypes(training.sportandtype.id))[0].types -->
        <span editable-select="training.sportandtype.types[0].id" e-name="type" e-form="rowform" e-ng-options="i.id as i.type for i in (sportsandtypes | filter: {id : training.sportandtype.id})[0].types">
          {{ training.sportandtype.types[0].type || "" }}
        </span>
      </td>
	  <td>
		<table><tr>
			<td>
				<span editable-text="training.durationhours" e-name="durationhours" e-form="rowform">
				  {{ training.durationhours || "0" }}
				</span>
			</td>
			<td>h</td>
			<td>&nbsp;</td>
			<td>
				<span editable-text="training.durationminutes" e-name="durationminutes" e-form="rowform">
				  {{ training.durationminutes || "0" }}
				</span>
			</td>
			<td>min</td>
		</tr></table>
      </td>
	  <td>
        <span editable-text="training.annotation" e-name="annotation" e-form="rowform">
          {{ training.annotation || "" }}
        </span>
      </td>
      <td style="white-space: nowrap">
        <!-- form -->
        <form editable-form name="rowform" onbeforesave="editTraining(week, training, $data)" ng-show="rowform.$visible" class="form-buttons form-inline" shown="inserted == training">
          <button type="submit" ng-disabled="rowform.$waiting" class="btn btn-primary">
            <span class="glyphicon glyphicon-ok" aria-hidden="true"></span>
          </button>
          <button type="button" ng-disabled="rowform.$waiting" ng-click="rowform.$cancel()" class="btn btn-default">
            <span class="glyphicon glyphicon-remove" aria-hidden="true"></span>
          </button>
        </form>
        <div class="buttons" ng-show="!rowform.$visible">
          <button class="btn btn-primary" ng-click="rowform.$show()"><span class="glyphicon glyphicon-pencil" aria-hidden="true"></span></button>
          <button class="btn btn-danger" ng-click="removeTraining(week, training)"><span class="glyphicon glyphicon-minus" aria-hidden="true"></span></button>
        </div>  
      </td>
    </tr>
	<tr>
		<td class="btmtd" colspan="5"><button class="btn btn-default" ng-click="addTraining(week, $index + 1)"><span class="glyphicon glyphicon-plus" aria-hidden="true"></span>Training hinzufügen</button></td>
	</tr>
  </table>
  
</div>
</div>
</div>
</div>
</body>