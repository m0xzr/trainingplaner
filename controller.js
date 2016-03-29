var app = angular.module("app", ["xeditable", "ngSanitize"]);

app.run(function(editableOptions) {
  editableOptions.theme = 'bs3';
});

app.filter("sanitize", ['$sce', function($sce) {
  return function(htmlCode){
    return $sce.trustAsHtml(htmlCode);
  }
}]);

app.controller('Ctrl', function($scope, $filter, $http) {
	var requestUrl = "http://lerche.dyndns.info:4980/runalyze/trainingplaner_dev/trainingplaner/db_interaction.php";
	$http.defaults.headers.post["Content-Type"] = "application/x-www-form-urlencoded";

	$scope.weekTitle = '';
	$scope.planTitle = '';
	
	//initiale Daten laden
	$scope.init = function () 
	{
		var request = $http({
			method: "post",
			url: requestUrl,
			data: {
				action: 'GetPlans',
				obj: JSON.parse('{"" : ""}')
			}
		});
		request.success(function (response) {
			//alert(JSON.stringify(response))
			$scope.plans = response;
			if(response.length > 0)
			{
				$scope.plan = response[0];
			}
		}).
		error(function(response) {
			alert(response+"error");
		});
		
		var request = $http({
			method: "post",
			url: requestUrl,
			data: {
				action: 'GetSportsAndTypes',
				obj: JSON.parse('{"" : ""}')
			}
		});
		request.success(function (response) {
			console.log(JSON.stringify(response));
			$scope.sportsandtypes = response;
		}).
		error(function(response) {
			alert("error");
		});
	};
	
	$scope.init();
	$scope.range = function(n) {
        return new Array(n);
    };
  
  $scope.getPlan = function(plan) {
		var request = $http({
			method: "post",
			url: requestUrl,
			data: {
				action: 'GetPlan',
				obj: JSON.parse('{"id" : '+plan.id+'}')
			}
		});
		request.success(function (response) {
			$scope.plan = response;
			if(response.weeks.length > 0)
			{
				$scope.trainings = response.weeks[0].trainings;
			}
			else
			{
				$scope.trainings = null;
			}
		}).		error(function(response) {
			alert("error");
		});
  };
  
  $scope.getPlanByTitle = function(plan) {
		var request = $http({
			method: "post",
			url: requestUrl,
			data: {
				action: 'GetPlanByTitle',
				obj: JSON.parse('{"title" : "'+plan.title+'"}')
			}
		});
		request.success(function (response) {
			$scope.plan = response;
			$scope.trainings = response.weeks[0].trainings;
		}).
		error(function(response) {
			alert("error");
		});
  };
  
  $scope.addPlan = function(plans) {
	 var request = $http({
			method: "post",
			url: requestUrl,
			data: {
				action: 'CreatePlan',
				obj: JSON.parse('{"title" : "'+$scope.planTitle+'"}')
			}
		});
		request.success(function (response) {
			plans.push(response);
			$scope.plan = response;
		}).
		error(function(response) {
			alert("error");
		});
		
		$('#new-plan-popover').popover('hide');
		$scope.planTitle = '';
  };
  
  $scope.addWeek = function(plan) {
	 var request = $http({
			method: "post",
			url: requestUrl,
			data: {
				action: 'AddWeek',
				obj: JSON.parse('{"id" : '+plan.id+', "annotation" : "'+$scope.weekTitle+'"}')
			}
		});
		request.success(function (response) {
			plan.weeks.push(response);
		}).
		error(function(response) {
			alert("error");
		});
		
		$('#new-week-popover').popover('hide');
		$scope.weekTitle = '';
  };

  $scope.addTraining = function(week, day, planedDone) {
	  console.log(JSON.parse('{"week" : '+week.id+', "day" : '+day+', "sport" : 6, "type" : 9, "annotation" : "", "durationhours" : 0, "durationminutes" : 0, "planeddone"  : '+planedDone+'}'));
	 var request = $http({
			method: "post",
			url: requestUrl,
			data: {
				action: 'AddTraining',
				obj: JSON.parse('{"week" : '+week.id+', "day" : '+day+', "sport" : 6, "type" : 9, "annotation" : "", "durationhours" : 0, "durationminutes" : 0, "planeddone"  : '+planedDone+'}')
			}
		});
		request.success(function (response) {
			console.log(response);
			//alert(response);
			week.trainings.push(response);
		}).
		error(function(response) {
			alert("error");
		});
  };
  
  $scope.editTraining = function(week, training, data) {
	  angular.extend(data, {id: training.id});
	  console.log(data);
	 var request = $http({
			method: "post",
			url: requestUrl,
			data: {
				action: 'EditTraining',
				obj: data
			}
		});
		request.success(function (response) {
			var index = week.trainings.indexOf(training);
			if (index > -1) 
			{
				week.trainings.splice(index, 1);
				week.trainings.splice(index, 0, response);
			}
		}).
		error(function(response) {
			alert("error");
		});
  };
  
  $scope.removeTraining = function(week, training) { 
	var request = $http({
		method: "post",
		url: requestUrl,
		data: {
			action: 'RemoveTraining',
			obj: JSON.parse('{"id" : '+training.id+'}')
		}
	});
	  
	var index = week.trainings.indexOf(training);
	if (index > -1) 
	{
		week.trainings.splice(index, 1);
	}
  };
  
  $scope.removePlan = function(plans, plan) { 
    var request = $http({
    	method: "post",
		url: requestUrl,
		data: {
			action: 'RemovePlan',
			obj: JSON.parse('{"id" : '+plan.id+'}')
		}
	});
	request.success(function (response) {
		var index = -1;
		for(var i=0; i< plans.length; i++)
		{
			if(plans[i].id == plan.id)
			{
				index = i;
				break;
			}
		}

		if (index > -1) 
		{
			plans.splice(index, 1);
		}
		$scope.plan = null;
	}).
	error(function(response) {
		alert("error");
	});

  };
  
  $scope.removeWeek = function(plan, week) { 
    var request = $http({
		method: "post",
		url: requestUrl,
		data: {
			action: 'RemoveWeek',
			obj: JSON.parse('{"id" : '+week.id+'}')
		}
	});
	  
	var index = plan.weeks.indexOf(week);
	if (index > -1) 
	{
		plan.weeks.splice(index, 1);
	}
  };
  
  $scope.changeData = function(training, data) {
	for(var i=0; i< $scope.sportsandtypes.length; i++) 
	{
		if($scope.sportsandtypes[i].id == data)
		{
			training.sportandtype.id = $scope.sportsandtypes[i].id;
			training.sportandtype.sport = $scope.sportsandtypes[i].sport;
			if($scope.sportsandtypes[i].types.length)
			{
				training.sportandtype.types[0] = $scope.sportsandtypes[i].types[0];
			}
			else
			{
				training.sportandtype.types[0] = null;
			}
			break;
		}
	}
  }
  
  $scope.getWeekInfos = function(week) {
	var planedVolumeMap = new Map();
	var doneVolumeMap = new Map();
	var runMins = 0;
	var runHours = 0;
	var otherMins = 0;
	var otherHours = 0;
	for(var i=0; i< week.trainings.length; i++)  
	{
		if(week.trainings[i].planeddone == 0)
		{
			var mins = planedVolumeMap.get(week.trainings[i].sportandtype.sport);
			if(mins === undefined)
			{
				mins = 0;
			}
			mins += week.trainings[i].durationhours * 60 + week.trainings[i].durationminutes;
			
			planedVolumeMap.set(week.trainings[i].sportandtype.sport, mins);
		}
		else if(week.trainings[i].planeddone == 1)
		{
			var mins = doneVolumeMap.get(week.trainings[i].sportandtype.sport);
			if(mins === undefined)
			{
				mins = 0;
			}
			mins += week.trainings[i].durationhours * 60 + week.trainings[i].durationminutes;
			
			doneVolumeMap.set(week.trainings[i].sportandtype.sport, mins);
		}
		
		if(week.trainings[i].sportandtype.sport == 'Laufen')
		{
			runMins += week.trainings[i].durationminutes;
			runHours += week.trainings[i].durationhours;
			if(runMins >= 59)
			{
				runHours++;
				runMins %= 60;
			}
		}
		else
		{
			otherMins += week.trainings[i].durationminutes;
			otherHours += week.trainings[i].durationhours;
			if(otherMins >= 59)
			{
				otherHours++;
				otherMins %= 60;
			}
		}
	}
	
	var retVal = week.annotation + '<hr />Geplantes Training:<ul>';
	for (var [key, value] of planedVolumeMap) 
	{
		var hours = Math.floor(value / 60);
		var mins = value % 60;
		retVal += '<li>' + key + ': ' + hours + 'h ' + mins + 'min' + '</li>';
	}
	retVal += '</ul><hr />Absolviertes Training:<ul>';
	for (var [key, value] of doneVolumeMap) 
	{
		var hours = Math.floor(value / 60);
		var mins = value % 60;
		retVal += '<li>' + key + ': ' + hours + 'h ' + mins + 'min' + '</li>';
	}
	retVal += '</ul>';
	
	return retVal;
  }
  
}).directive('popover', function($compile) {
    return {
      restrict: 'A',
      link: function(scope, elem, attrs) {
		if (attrs.id=="new-plan-popover"){
			var content = $("#popover-content-plan").html();
			var title = $("#popover-head-plan").html();
		}
		else if(attrs.id=="new-week-popover"){
			var content = $("#popover-content-week").html();
			var title = $("#popover-head-week").html();
		}
        
        var compileContent = $compile(content)(scope);
        var options = {
          content: compileContent,
          html: true,
          title: title
        };

        $(elem).popover(options);
      }
    }
  });