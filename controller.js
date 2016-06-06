var app = angular.module("app", ["xeditable", "ngSanitize", "angularCharts"]);

app.run(function (editableOptions) {
	editableOptions.theme = 'bs3';
});

app.filter("sanitize", ['$sce', function ($sce) {
			return function (htmlCode) {
				return $sce.trustAsHtml(htmlCode);
			}
		}
	]);

app.controller('Ctrl', function ($scope, $filter, $http) {
	var requestUrl = "http://lerche.dyndns.info:4980/trainingplaner/db_interaction.php";
	$http.defaults.headers.post["Content-Type"] = "application/x-www-form-urlencoded";

	$scope.weekTitle = '';
	$scope.planTitle = '';

	$scope.$watch('plan', function (newVal, oldVal) {
		if (newVal === undefined) {
			return;
		}
		
		var request = $http({
				method : "post",
				url : requestUrl,
				data : {
					action : 'GetPlans',
					obj : JSON.parse('{"" : ""}')
				}
			});
		request.success(function (response) {
			//alert(JSON.stringify(response))
			$scope.plans = response;
			$scope.updateCalculations();
		}).
		error(function (response) {
			alert(response + "error");
		});
	}, true);

	//initiale Daten laden
	$scope.init = function (username) {
		//console.log(username);

		var request = $http({
				method : "post",
				url : requestUrl,
				data : {
					action : 'GetUserData',
					obj : JSON.parse('{"username" : "' + username + '"}')
				}
			});
		request.success(function (response) {
			//console.log(JSON.stringify(response));
			$scope.userdata = response;
			$scope.userweight = $scope.userdata.weight;
			$scope.userhrmax = $scope.userdata.hrmax;
			$scope.userhrrest = $scope.userdata.hrrest;
		}).error(function (response) {
			alert("error");
		});

		var request = $http({
				method : "post",
				url : requestUrl,
				data : {
					action : 'GetPlans',
					obj : JSON.parse('{"" : ""}')
				}
			});
		request.success(function (response) {
			//alert(JSON.stringify(response))
			$scope.plans = response;
			if (response.length > 0) {
				for(i = 0; i < response.length; i++)		//aktiven Plan setzen
				{
					if(response[i].active == 1)
					{
						$scope.plan = response[i];
						break;
					}
				}
			}
		}).
		error(function (response) {
			alert(response + "error");
		});

		var request = $http({
				method : "post",
				url : requestUrl,
				data : {
					action : 'GetSportsAndTypes',
					obj : JSON.parse('{"" : ""}')
				}
			});
		request.success(function (response) {
			//console.log(JSON.stringify(response));
			$scope.sportsandtypes = response;
		}).
		error(function (response) {
			alert("error");
		});
	};

	$scope.range = function (n) {
		return new Array(n);
	};

	$scope.insertUserData = function () {
		//console.log(JSON.parse('{"userid" : ' + $scope.userdata.userid + ', "weight" : ' + $scope.userweight + ', "hrmax" : ' + $scope.userhrmax + ', "hrrest" : ' + $scope.userhrrest + '}'));

		var request = $http({
				method : "post",
				url : requestUrl,
				data : {
					action : 'InsertUserData',
					obj : JSON.parse('{"userid" : ' + $scope.userdata.userid + ', "weight" : ' + $scope.userweight + ', "hrmax" : ' + $scope.userhrmax + ', "hrrest" : ' + $scope.userhrrest + '}')
				}
			});
		request.success(function (response) {
			//console.log(JSON.stringify(response));
			$scope.userdata = response;
		}).error(function (response) {
			alert("error");
		});

		$('#new-userdata-popover').popover('hide');
		$scope.userweight = $scope.userdata.weight;
		$scope.userhrmax = $scope.userdata.hrmax;
		$scope.userhrrest = $scope.userdata.hrrest;
	};

	$scope.getPlan = function (plan) {
		var request = $http({
				method : "post",
				url : requestUrl,
				data : {
					action : 'GetPlan',
					obj : JSON.parse('{"id" : ' + plan.id + '}')
				}
			});
		request.success(function (response) {
			$scope.plan = response;
			if (response.weeks.length > 0) {
				$scope.trainings = response.weeks[0].trainings;
			} else {
				$scope.trainings = null;
			}
		}).error(function (response) {
			alert("error");
		});
	};

	$scope.getPlanByTitle = function (plan) {
		var request = $http({
				method : "post",
				url : requestUrl,
				data : {
					action : 'GetPlanByTitle',
					obj : JSON.parse('{"title" : "' + plan.title + '"}')
				}
			});
		request.success(function (response) {
			$scope.plan = response;
			$scope.trainings = response.weeks[0].trainings;
		}).
		error(function (response) {
			alert("error");
		});
	};
	
	$scope.activatePlan = function (plan) {
		var request = $http({
				method : "post",
				url : requestUrl,
				data : {
					action : 'ActivatePlan',
					obj : JSON.parse('{"id" : "' + plan.id + '"}')
				}
			});
		request.success(function (response) {
			$scope.plan = plan;
		}).
		error(function (response) {
			alert("error");
		});
	};

	$scope.addPlan = function (plans) {
		var request = $http({
				method : "post",
				url : requestUrl,
				data : {
					action : 'CreatePlan',
					obj : JSON.parse('{"title" : "' + $scope.planTitle + '"}')
				}
			});
		request.success(function (response) {
			plans.push(response);
			$scope.plan = response;
		}).
		error(function (response) {
			alert("error");
		});

		$('#new-plan-popover').popover('hide');
		$scope.planTitle = '';
	};

	$scope.addWeek = function (plan) {
		var request = $http({
				method : "post",
				url : requestUrl,
				data : {
					action : 'AddWeek',
					obj : JSON.parse('{"id" : ' + plan.id + ', "annotation" : "' + $scope.weekTitle + '"}')
				}
			});
		request.success(function (response) {
			plan.weeks.push(response);
		}).
		error(function (response) {
			alert("error");
		});

		$('#new-week-popover').popover('hide');
		$scope.weekTitle = '';
	};

	$scope.addTraining = function (week, day, planedDone) {
		//console.log(JSON.parse('{"week" : ' + week.id + ', "day" : ' + day + ', "sport" : 1, "type" : 1, "annotation" : "", "durationhours" : 0, "durationminutes" : 0, "planeddone"  : ' + planedDone + ', "avghr" : 0}'));
		var request = $http({
				method : "post",
				url : requestUrl,
				data : {
					action : 'AddTraining',
					obj : JSON.parse('{"week" : ' + week.id + ', "day" : ' + day + ', "sport" : 1, "type" : 1, "annotation" : "", "durationhours" : 0, "durationminutes" : 0, "planeddone"  : ' + planedDone + ', "avghr" : 0}')
				}
			});
		request.success(function (response) {
			//console.log(response);
			//alert(response);
			week.trainings.push(response);
		}).
		error(function (response) {
			alert("error");
		});
	};

	$scope.editTraining = function (week, training, data, planedDone) {
		angular.extend(data, {
			id : training.id
		});
		if (data.avghr === undefined) {
			angular.extend(data, {
				avghr : 0
			});
		}

		//console.log(data);
		var request = $http({
				method : "post",
				url : requestUrl,
				data : {
					action : 'EditTraining',
					obj : data
				}
			});
		request.success(function (response) {
			//console.log(response);
			var index = week.trainings.indexOf(training);
			if (index > -1) {
				if (data.avghr === 0 && planedDone === true) {
					//Keine HR Daten eingetragen -> versuchen welche zu finden
					$scope.avgHrOfSimilarTrainings(data, training, response, week, index);
				} else {
					week.trainings.splice(index, 1);
					week.trainings.splice(index, 0, response);
				}
			}
		}).
		error(function (response) {
			alert("error");
		});
	};

	$scope.removeTraining = function (week, training) {
		/*if (confirm("Training wirklich löschen?") == false) {
		return;
		}*/

		var request = $http({
				method : "post",
				url : requestUrl,
				data : {
					action : 'RemoveTraining',
					obj : JSON.parse('{"id" : ' + training.id + '}')
				}
			});

		var index = week.trainings.indexOf(training);
		if (index > -1) {
			week.trainings.splice(index, 1);
		}
	};

	$scope.avgHrOfSimilarTrainings = function (data, oldTraining, newTraining, week, index) {
		//console.log("Search AvgHr For:");
		//console.log(data);
		var request = $http({
				method : "post",
				url : requestUrl,
				data : {
					action : 'AvgHrOfSimilarTrainings',
					obj : data
				}
			});
		request.success(function (response) {
			if (response > 0 && confirm("Es wurde " + parseInt(response) + " als Durchschnittspuls bei ähnlichen Trainings gefunden. Soll dieser eingetragen werden?") == true) {
				data.avghr = parseInt(response);
				$scope.editTraining(week, oldTraining, data);		//nochmal updaten
			} else {
				week.trainings.splice(index, 1);
				week.trainings.splice(index, 0, newTraining);
			}
		}).
		error(function (response) {
			alert("error");
		});
	};

	$scope.removePlan = function (plans, plan) {
		if (confirm("Plan wirklich löschen?") == false) {
			return;
		}

		var request = $http({
				method : "post",
				url : requestUrl,
				data : {
					action : 'RemovePlan',
					obj : JSON.parse('{"id" : ' + plan.id + '}')
				}
			});
		request.success(function (response) {
			var index = -1;
			for (var i = 0; i < plans.length; i++) {
				if (plans[i].id == plan.id) {
					index = i;
					break;
				}
			}

			if (index > -1) {
				plans.splice(index, 1);
			}
			$scope.plan = null;
		}).
		error(function (response) {
			alert("error");
		});

	};

	$scope.removeWeek = function (plan, week) {
		if (confirm("Woche wirklich löschen?") == false) {
			return;
		}

		var request = $http({
				method : "post",
				url : requestUrl,
				data : {
					action : 'RemoveWeek',
					obj : JSON.parse('{"id" : ' + week.id + '}')
				}
			});

		var index = plan.weeks.indexOf(week);
		if (index > -1) {
			plan.weeks.splice(index, 1);
		}
	};

	$scope.changeData = function (training, data) {
		for (var i = 0; i < $scope.sportsandtypes.length; i++) {
			if ($scope.sportsandtypes[i].id == data) {
				training.sportandtype.id = $scope.sportsandtypes[i].id;
				training.sportandtype.sport = $scope.sportsandtypes[i].sport;
				if ($scope.sportsandtypes[i].types.length) {
					training.sportandtype.types[0] = $scope.sportsandtypes[i].types[0];
				} else {
					training.sportandtype.types[0] = null;
				}
				break;
			}
		}
	}
	
	$scope.editThought = function (training) {
		var request = $http({
			method : "post",
			url : requestUrl,
			data : {
				action : 'EditThought',
				obj : { trainingid :  training.id , thought : training.thought }
			}
		});
		
		request.success(function (response) {
		}).
		error(function (response) {
			alert("error");
		});

		$('#new-thought-popover').popover('hide');
	};
	
	$scope.removeThought = function (training) {
		var request = $http({
			method : "post",
			url : requestUrl,
			data : {
				action : 'RemoveThought',
				obj : JSON.parse('{"trainingid" : ' + training.id + '}')
			}
		});
		
		request.success(function (response) {
			training.thought = "";
		}).
		error(function (response) {
			alert("error");
		});

		$('#new-thought-popover').popover('hide');
	};

	$scope.getWeekInfos = function (week) {
		var planedVolumeMap = new Map();
		var doneVolumeMap = new Map();
		var planedKeys = [];
		var doneKeys = [];
		var planedTotal = 0;
		var doneTotal = 0;
		var trimp = 0;

		//Maps mit den Trainingsminuten und Sportarten befüllen
		for (var i = 0; i < week.trainings.length; i++) {
			if (week.trainings[i].planeddone == 0) {
				var mins = planedVolumeMap.get(week.trainings[i].sportandtype.sport);
				if (mins === undefined) {
					mins = 0;
					planedKeys.push(week.trainings[i].sportandtype.sport);
				}
				mins += week.trainings[i].durationhours * 60 + week.trainings[i].durationminutes;
				planedTotal += week.trainings[i].durationhours * 60 + week.trainings[i].durationminutes;

				planedVolumeMap.set(week.trainings[i].sportandtype.sport, mins);
			} else if (week.trainings[i].planeddone == 1) {
				var mins = doneVolumeMap.get(week.trainings[i].sportandtype.sport);
				if (mins === undefined) {
					mins = 0;
					doneKeys.push(week.trainings[i].sportandtype.sport);
				}
				mins += week.trainings[i].durationhours * 60 + week.trainings[i].durationminutes;
				doneTotal += week.trainings[i].durationhours * 60 + week.trainings[i].durationminutes;

				trimp += (week.trainings[i].durationminutes + week.trainings[i].durationhours * 60) * (week.trainings[i].avghr - $scope.userdata.hrrest) / ($scope.userdata.hrmax - $scope.userdata.hrrest) * 0.64 * Math.pow(Math.E, (1.92 * (week.trainings[i].avghr - $scope.userdata.hrrest) / ($scope.userdata.hrmax - $scope.userdata.hrrest)));

				doneVolumeMap.set(week.trainings[i].sportandtype.sport, mins);
			}
		}

		//Maps sortieren
		planedKeys.sort();
		doneKeys.sort();

		//ausgeben
		var retVal = week.annotation + '<hr /><h3>Geplantes Training:</h3><dl class="inline">';
		for (var i = 0; i < planedKeys.length; i++) {
			var key = planedKeys[i];
			var value = planedVolumeMap.get(key);
			var hours = Math.floor(value / 60);
			var mins = value % 60;
			retVal += '<dt>' + key + '</dt><dd>' + hours + 'h ' + mins + 'min' + '</dd>';
		}
		retVal += '<dt class="sum">Komplett</dt><dd class="sum">' + Math.floor(planedTotal / 60) + 'h ' + planedTotal % 60 + 'min</dd></dl><hr /><h3>Absolviertes Training:</h3><dl class="inline">';
		for (var i = 0; i < doneKeys.length; i++) {
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

	$scope.updateCalculations = function () {
		Calculator.Init($scope.plans, $scope.userdata.hrrest, $scope.userdata.hrmax);
		Calculator.calcPerformance();

		$scope.monotony = Calculator.Monotony;
		$scope.strain = Calculator.Strain;
		$scope.CTL = Calculator.CTL;
		$scope.ATL = Calculator.ATL;
		$scope.TSB = Calculator.TSB;
		$scope.restDays = Calculator.RestDays;

		Calculator.createChartData();
		$scope.chartData = Calculator.LastDays;
		return;
	}

	$scope.showLastDays = function () {
		$scope.chartData = Calculator.LastDays;
	};

	$scope.showLastWeekss = function () {
		$scope.chartData = Calculator.LastWeeks;
	};

	$scope.chartCfg = {
		labels : false,
		title : '',
		legend : {
			display : false,
			position : 'left'
		},
		colors : [{
				fillColor : 'rgba(47, 132, 71, 0.8)',
				strokeColor : 'rgba(47, 132, 71, 0.8)',
				highlightFill : 'rgba(47, 132, 71, 0.8)',
				highlightStroke : 'rgba(47, 132, 71, 0.8)'
			}
		],
		isAnimate : false,
		innerRadius : 0
	};

}).directive('popover', function ($compile) {
	return {
		restrict : 'A',
		link : function (scope, elem, attrs) {
			if (attrs.id == "new-plan-popover") {
				var content = $("#popover-content-plan").html();
				var title = $("#popover-head-plan").html();
			} else if (attrs.id == "new-week-popover") {
				var content = $("#popover-content-week").html();
				var title = $("#popover-head-week").html();
			} else if (attrs.id == "new-userdata-popover") {
				var content = $("#popover-content-userdata").html();
				var title = $("#popover-head-userdata").html();
			} else if (attrs.id == "new-thought-popover") {
				var content = $("#popover-content-thought").html();
				var title = $("#popover-head-thought").html();
			}

			var compileContent = $compile(content)(scope);
			var options = {
				content : compileContent,
				html : true,
				title : title
			};

			$(elem).popover(options);
		}
	}
});
