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
<script type="text/javascript" src="Calculator_es5.js"></script>		<!--Aus Calculator.js erstellt mit https://babeljs.io/repl/ da Safari es6 nicht wirklich unterstützt (Calculator.js bearbeiten und Calculator_es5.js generieren lassen) -->
<script type="text/javascript" src="js/angular.min.js"></script>
<script type="text/javascript" src="js/angular-sanitize.min.js"></script>
<script src='js/d3.min.js' type='text/javascript'></script>
<script src='js/angular-charts.min.js' type='text/javascript'></script>
<script type="text/javascript" src="js/xeditable.min.js"></script>
<script type='text/javascript' src="js/jquery-1.12.1.min.js"></script>
<script type='text/javascript' src="bootstrap/js/bootstrap.min.js"></script>
<script src="controller.js"></script>
<style type="text/css">

</style>
</head>

<body>
<?php  
	include_once 'user_dal.php';
	$pw = $_GET['pw'];
	$username = $_GET['user'];
	if(!UserDAL::CheckCredentials($username, $pw))
	{
		echo 'WRONG PW';
		exit(1);
	}
?>

<div ng-controller="Ctrl" ng-init="init('<?php echo $_GET['user'] ?>')" id="main">
<div id="info">
	<div id="user">
		<h3>Benutzerdaten</h3>
		<dl class="inline">
			<dt>Gewicht</dt><dd>{{ userdata.weight || '' }}</dd>
			<dt>Maximaler Puls</dt><dd>{{ userdata.hrmax || '' }}</dd>
			<dt>Ruhepuls</dt><dd>{{ userdata.hrrest || '' }}</dd>
		</dl>
		<a data-container="body" data-toggle="popover" data-placement="right" id="new-userdata-popover" popover><span class="glyphicon glyphicon-plus" aria-hidden="true"></span>
				<div id="popover-head-userdata" class="hide">Benutzerdaten</div>
				<div id="popover-content-userdata" class="hide"> 
					<table>
					<tr>
						<td>Gewicht</td>
						<td><input type="text" name="userweight" ng-model="userweight" size="30" /></td>
					</tr>
					<tr>
						<td>Maximaler Puls</td>
						<td><input type="text" name="userhrmax" ng-model="userhrmax" size="30" /></td>
					</tr>
					<tr>
						<td>Ruhepuls</td>
						<td><input type="text" name="userhrrest" ng-model="userhrrest" size="30" /></td>
					</tr>
					<tr>
						<td><button ng-click="insertUserData()" class="btn btn-primary" id="close-popover" data-toggle="clickover"><span class="glyphicon glyphicon-ok" aria-hidden="true"></span></button></td>
					</tr>
					</table>
				</div>
		</a>
	</div>
	<div id="calculations">
		<h3>Trainingswerte der letzten 7 Tage</h3>
		<dl class="inline">
			<dt>Monotonie</dt><dd>{{ monotony || '' }}</dd>
			<dt>Trainingsbelastung</dt><dd>{{ strain || '' }}</dd>
			<dt>Müdigkeit (ATL)</dt><dd>{{ ATL || '' }}</dd>
			<dt>Fitnessgrad (CTL)</dt><dd>{{ CTL || '' }}</dd>
			<dt>Stress Balance (TSB)</dt><dd>{{ TSB || '' }}</dd>
			<dt>Ruhetage</dt><dd>{{ restDays || '' }}</dd>
		</dl>
	</div>
	<div id="chart_container">
		<input type="radio" name="chart" value="daysChart" checked="checked" ng-model="value" ng-change='showLastDays()' />Trimps der letzten Tage<input type="radio" name="chart" value="weeksChart" ng-model="value" ng-change='showLastWeekss()' style="margin-left: 20px;" />Trimps der letzten Wochen
		<div id="chart" ac-data="chartData" ac-chart="'bar'" ac-config="chartCfg">
		</div>
	</div>
	<div id="infofix"></div>
</div>
<div id="plans">
	<div class="btn-group" role="group" aria-label="...">
	  <button  ng-repeat="p in plans" ng-click="getPlan(p)" onClick="window.location.href='index.php<?php echo "?pw=".$pw."&user=".$username ?>#plan'" type="button" class="btn btn-default">{{p.title}}</button>
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
		<li ng-repeat="week in plan.weeks" ng-class="{'active':$last}"><a data-toggle="tab" href="#week{{$index + 1}}">Woche {{ week.weeknumber || "empty" }}</a></li>
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
<div class="tab-pane fade in" id="week{{$index + 1}}" ng-repeat="week in plan.weeks" ng-class="{'active':$last}" >
<button class="btn btn-danger btn-xs"  id="abs_right" ng-click="removeWeek(plan, week)"><span class="glyphicon glyphicon-minus" aria-hidden="true"></span></button>
<div class="panel panel-info">
  <div class="panel-heading">
    <h3 class="panel-title">Information zur Woche</h3>
  </div>
  <div class="panel-body" ng-bind-html="getWeekInfos(week) | sanitize">
 </div>
</div>
<div ng-repeat="d in range(7) track by $index" >
  <table class="table table-bordered table-hover table-condensed">
	<caption class="weekDay">Tag {{ $index + 1 }}</caption>
    <tr style="font-weight: bold">
      <td style="width:15%">Sport</td>
      <td style="width:15%">Typ</td>
      <td style="width:20%">Umfang</td>
      <td style="width:50%">Notizen</td>
    </tr>
	<tr style="background-color: #efefef; height: 10px;">
		<td colspan="5" class="planedDone">Soll</td>
	</tr>
    <tr ng-repeat="training in week.trainings | filter: { day: ($index + 1), planeddone: false }">
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
		<td class="btmtd" colspan="5"><button class="btn btn-default" ng-click="addTraining(week, $index + 1, false)"><span class="glyphicon glyphicon-plus" aria-hidden="true"></span>geplantes Training hinzufügen</button></td>
	</tr>
	<!-- darüber geplant -->
	<tr style="background-color: #efefef; height: 10px;">
		<td colspan="5"  class="planedDone">Ist</td>
	</tr>
	<!-- darunter erfüllt -->
	<tr ng-repeat="training in week.trainings | filter: { day: ($index + 1), planeddone: true }">
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
		<table>
			<tr>
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
			</tr>
			<tr><td colspan="4" style="text-align: right"><span editable-text="training.avghr" e-name="avghr" e-form="rowform">{{ training.avghr || "0" }}</span></td><td>bpm(&empty;)</td></tr>
		</table>
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
		<td class="btmtd" colspan="5"><button class="btn btn-default" ng-click="addTraining(week, $index + 1, true)"><span class="glyphicon glyphicon-plus" aria-hidden="true"></span>absolviertes Training hinzufügen</button></td>
	</tr>
  </table>
  
</div>
</div>
</div>
</div>
</body>