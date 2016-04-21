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
	
	$scope.$watch('plan', function(newVal, oldVal){
		if(newVal === undefined)
		{
			return;
		}
		
        $scope.calcPerformance(newVal);
    }, true);
	
	

	//initiale Daten laden
	$scope.init = function (username) 
	{
		console.log(username);
		
		var request = $http({
			method: "post",
			url: requestUrl,
			data: {
				action: 'GetUserData',
				obj: JSON.parse('{"username" : "'+username+'"}')
			}
		});
		request.success(function (response) {
			console.log(JSON.stringify(response));
			$scope.userdata = response;
			$scope.userweight = $scope.userdata.weight;
			$scope.userhrmax = $scope.userdata.hrmax;
			$scope.userhrrest = $scope.userdata.hrrest;
		}).error(function(response) {
			alert("error");
		});
		
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
				//$scope.calcPerformance(response[0]);
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
	
	$scope.range = function(n) {
        return new Array(n);
    };
  
	$scope.insertUserData = function() {
		console.log(JSON.parse('{"userid" : '+$scope.userdata.userid+', "weight" : '+$scope.userweight+', "hrmax" : '+$scope.userhrmax+', "hrrest" : '+$scope.userhrrest+'}'));
		
		var request = $http({
			method: "post",
			url: requestUrl,
			data: {
				action: 'InsertUserData',
				obj: JSON.parse('{"userid" : '+$scope.userdata.userid+', "weight" : '+$scope.userweight+', "hrmax" : '+$scope.userhrmax+', "hrrest" : '+$scope.userhrrest+'}')
			}
		});
		request.success(function (response) {
			console.log(JSON.stringify(response));
			$scope.userdata = response;
		}).		error(function(response) {
			alert("error");
		});
		
		$('#new-userdata-popover').popover('hide');
		$scope.userweight = $scope.userdata.weight;
		$scope.userhrmax = $scope.userdata.hrmax;
		$scope.userhrrest = $scope.userdata.hrrest;
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
	  console.log(JSON.parse('{"week" : '+week.id+', "day" : '+day+', "sport" : 1, "type" : 1, "annotation" : "", "durationhours" : 0, "durationminutes" : 0, "planeddone"  : '+planedDone+', "avghr" : 0}'));
	 var request = $http({
			method: "post",
			url: requestUrl,
			data: {
				action: 'AddTraining',
				obj: JSON.parse('{"week" : '+week.id+', "day" : '+day+', "sport" : 1, "type" : 1, "annotation" : "", "durationhours" : 0, "durationminutes" : 0, "planeddone"  : '+planedDone+', "avghr" : 0}')
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
	  if(data.avghr === undefined)
	  {
		  angular.extend(data, {avghr: 0});
	  }
	  
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
	/*if (confirm("Training wirklich löschen?") == false) {
		return;
	}*/
  
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
	if (confirm("Plan wirklich löschen?") == false) {
		return;
	}   
  
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
	if (confirm("Woche wirklich löschen?") == false) {
		return;
	} 
  
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
	var planedKeys = [];
	var doneKeys = [];
	var planedTotal = 0;
	var doneTotal = 0;
	var trimp = 0;
	
	//Maps mit den Trainingsminuten und Sportarten befüllen
	for(var i = 0; i< week.trainings.length; i++)  
	{
		if(week.trainings[i].planeddone == 0)
		{
			var mins = planedVolumeMap.get(week.trainings[i].sportandtype.sport);
			if(mins === undefined)
			{
				mins = 0;
				planedKeys.push(week.trainings[i].sportandtype.sport);
			}
			mins += week.trainings[i].durationhours * 60 + week.trainings[i].durationminutes;
			planedTotal += week.trainings[i].durationhours * 60 + week.trainings[i].durationminutes;
			
			planedVolumeMap.set(week.trainings[i].sportandtype.sport, mins);
		}
		else if(week.trainings[i].planeddone == 1)
		{
			var mins = doneVolumeMap.get(week.trainings[i].sportandtype.sport);
			if(mins === undefined)
			{
				mins = 0;
				doneKeys.push(week.trainings[i].sportandtype.sport);
			}
			mins += week.trainings[i].durationhours * 60 + week.trainings[i].durationminutes;
			doneTotal += week.trainings[i].durationhours * 60 + week.trainings[i].durationminutes;

			trimp += (week.trainings[i].durationminutes + week.trainings[i].durationhours * 60) * (week.trainings[i].avghr - $scope.userdata.hrrest)/($scope.userdata.hrmax - $scope.userdata.hrrest) * 0.64 * Math.pow(Math.E, (1.92 * (week.trainings[i].avghr - $scope.userdata.hrrest)/($scope.userdata.hrmax - $scope.userdata.hrrest)));
			
			doneVolumeMap.set(week.trainings[i].sportandtype.sport, mins);
		}
	}
	
	//Maps sortieren
	planedKeys.sort();
	doneKeys.sort();
	
	//ausgeben
	var retVal = week.annotation + '<hr /><h3>Geplantes Training:</h3><dl class="inline">';
	for (var i = 0; i < planedKeys.length; i++) 
	{
		var key = planedKeys[i];
		var value = planedVolumeMap.get(key);
		var hours = Math.floor(value / 60);
		var mins = value % 60;
		retVal += '<dt>' + key + '</dt><dd>' + hours + 'h ' + mins + 'min' + '</dd>';
	}
	retVal += '<dt class="sum">Komplett</dt><dd class="sum">' + Math.floor(planedTotal / 60) + 'h ' + planedTotal % 60 + 'min</dd></dl><hr /><h3>Absolviertes Training:</h3><dl class="inline">';
	for (var i = 0; i < doneKeys.length; i++) 
	{
		var key = doneKeys[i];
		var value = doneVolumeMap.get(key);
		var hours = Math.floor(value / 60);
		var mins = value % 60;
		retVal += '<dt>' + key + '</dt><dd>' + hours + 'h ' + mins + 'min' + '</dd>';
	}
	retVal += '<dt class="sum">Komplett</dt><dd class="sum">' + Math.floor(doneTotal / 60) + 'h ' + doneTotal % 60 + 'min</dd>';
	retVal += '<dt class="sum">Trimp</dt><dd class="sum">' + trimp.toFixed(2) + '</dd></dl>';
	
	return retVal;
  }
  
  $scope.calcPerformance = function(plan) {
	var trimps_sum = 0;
	
	//Anzahl der Berechnungstage
	var MONOn = 7;
	var ATLn = 7;
	var CTLn = 42;
	
	var lambdaCTL = 2 / (CTLn + 1);
	var lambdaATL = 2 / (ATLn + 1);
	
	//benötigte Arrays
	var trimps = [];
	var fatigue = [];
	var fitness = [];
	var performance = [];
	  
	if(plan.weeks === undefined || plan.weeks.length == 0)
	{
		return;
	}

	var now = new Date().getDay();
	if(now == 0)
	{
		now = 7;
	}

	var lastWeek = -1;
	for (const w of plan.weeks) 
	{
		var hasTraining = false;
		for(const t of w.trainings)
		{
			if(t.planeddone)
			{
				hasTraining = true;
			}
		}
		
		if(hasTraining && w.weeknumber > lastWeek)
		{
			lastWeek = w.weeknumber;
		}
	}
	
	if(lastWeek == -1)
	{
		return;
	}
	
	var lastTraining = -1;
	for(const t of plan.weeks.filter(function(o){return o.weeknumber == lastWeek;})[0].trainings)
	{
		if(t.planeddone && t.day > lastTraining)
		{
			lastTraining = t.day;
		}
	}
	
	if(lastTraining == -1)
	{
		return;
	}


	if(now < lastTraining)		//dann liegt now in der nächsten Woche
	{
		now += lastWeek * 7;
	}
	else
	{
		now += (lastWeek - 1) * 7;
	}
	  
	console.log("lastWeek:"+lastWeek);
	console.log("lastTraining:"+lastTraining);
	console.log("now:"+now);
	
	//von jetzt an days Tage zurück
	for(var i = 0; i < Math.max(MONOn, ATLn, CTLn); i++)
	{
		var weeks = plan.weeks.filter(function(o){return o.weeknumber == lastWeek;} );
		if(weeks === undefined || weeks.length == 0)
		{
			trimps.push(0);
		}
		else
		{
			var week = weeks[0];
			//alle trainings an dem tag
			var res = week.trainings.filter(function(o){return (o.day + ((lastWeek - 1) * 7)) == (now - i) && (o.planeddone == true);} );
			
			if(res === undefined || res.length == 0)		//leere Trimps pushen
			{
				trimps.push(0);
			}
			else
			{
				var dayTrimp = 0;
				for(var k = 0; k < res.length; k++)		//mehrere Trainings
				{
					dayTrimp += (res[k].durationminutes + res[k].durationhours * 60) * (res[k].avghr - $scope.userdata.hrrest)/($scope.userdata.hrmax - $scope.userdata.hrrest) * 0.64 * Math.pow(Math.E, (1.92 * (res[k].avghr - $scope.userdata.hrrest)/($scope.userdata.hrmax - $scope.userdata.hrrest)));
				}
				trimps.push(dayTrimp);
			}
		}
		
		if((now - i) % 7 == 1)
		{
			lastWeek--;
		}
		
		if(lastWeek == 0)
		{
			break;
		}
	}

	//Monotonie und Strain
	for(var i = 0; i < Math.min(MONOn, trimps.length); i++)
	{
		trimps_sum += trimps[i];
	}
		  
	  
	var avg = trimps_sum / Math.min(MONOn, trimps.length);
	console.log('trimps:'+trimps);
	console.log('trimps_sum:'+trimps_sum);
	console.log('avg:'+avg);
	var variance = 0;
	
	for (var i = 0; i < Math.min(MONOn, trimps.length); i++)
	{
		variance += Math.pow(trimps[i] - avg, 2);
	}
	
	variance /= Math.min(MONOn, trimps.length);
	console.log(variance);
	$scope.monotony = 10;
	
	if(variance != 0)
	{
		$scope.monotony = (avg/Math.sqrt(variance)).toFixed(2);
	}
	
	$scope.strain = (trimps_sum * $scope.monotony).toFixed(2);
	
	
	//TSB, ATL und CTL
	var CTLmax = 0;
	var ATLmax = 0;
	for (var i = 1; i <= trimps.length; i++) 
	{
		var lastFitness;
		if(fitness[i - 1] === undefined)
		{
			lastFitness = 0;
		}
		else
		{
			lastFitness = fitness[i - 1];
		}
		var lastFatigue;
		if(fatigue[i - 1] === undefined)
		{
			lastFatigue = 0;
		}
		else
		{
			lastFatigue = fatigue[i - 1];
		}
	
		fitness[i] = trimps[trimps.length - i] * lambdaCTL + (1 - lambdaCTL) * lastFitness;
		if(fitness[i] > CTLmax)
		{
			CTLmax = fitness[i];
		}
		fatigue[i] = trimps[trimps.length - i] * lambdaATL + (1 - lambdaATL) * lastFatigue;
		if(fatigue[i] > ATLmax)
		{
			ATLmax = fatigue[i];
		}
		performance[i] = fitness[i] - fatigue[i];
	}
	
	if(fitness[fitness.length - 1] === undefined)
	{
		$scope.CTL = 0;
	}
	else
	{
		$scope.CTL = (fitness[fitness.length - 1] / CTLmax).toFixed(2);
	}
	
	if(fatigue[fatigue.length - 1] === undefined)
	{
		$scope.ATL = 0;
	}
	else
	{
		$scope.ATL = (fatigue[fatigue.length - 1] / ATLmax).toFixed(2);
	}
	
	if(performance[performance.length - 1] === undefined)
	{
		$scope.TSB = 0;
	}
	else
	{
		$scope.TSB = performance[performance.length - 1].toFixed(2);
	}
	
	var restDays = 0;
	if($scope.ATL !== undefined)
	{
		if ($scope.CTL  === undefined) 
		{
			$scope.CTL = 1;
		}
		console.log("log"+Math.log((1 - lambdaATL) / (1 - lambdaCTL)));
		console.log("log"+Math.log(($scope.CTL * CTLmax) / ($scope.ATL * ATLmax)));
		restDays = Math.log(($scope.CTL * CTLmax) / ($scope.ATL * ATLmax)) / (Math.log((1 - lambdaATL) / (1 - lambdaCTL)));
		if ($scope.CTL * CTLmax < 15) 	//Fallback für sehr niedrige CTLs
		{
			restDays = 4 + $scope.restDays / -5;
		}
	}
	$scope.restDays = Math.max(0, restDays).toFixed(2);
	
	console.log($scope.CTL);
	console.log($scope.ATL);
	console.log($scope.TSB);
	console.log($scope.restDays);	
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
		else if(attrs.id=="new-userdata-popover"){
			var content = $("#popover-content-userdata").html();
			var title = $("#popover-head-userdata").html();
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