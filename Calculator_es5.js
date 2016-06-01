"use strict";

var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

var Calculator = function () {
	function Calculator() {
		_classCallCheck(this, Calculator);
	}

	_createClass(Calculator, null, [{
		key: "Init",
		value: function Init(plans, userRestHR, userMaxHR) {
			console.log(plans);
			Calculator.resetValues();
			Calculator.Plan = Calculator.initPlan(plans);
			Calculator.UserRestHR = userRestHR;
			Calculator.UserMaxHR = userMaxHR;
		}
	}, {
		key: "initPlan",
		value: function initPlan(plans) {
			//aus mehreren Plänen einen machen
			if (plans === null) {
				return null;
			}

			if (plans.length === 0) {
				return null;
			}

			var plan = plans[plans.length - 1];

			for (var i = 0; i < plans.length - 1; i++) {
				if (plans[i].weeks === undefined) {
					continue;
				}

				for (var j = plans[i].weeks.length - 1; j >= 0; j--) {
					if (plans[i].weeks[j].trainings === undefined || plans[i].weeks[j].trainings.length === 0) {
						continue;
					}

					plan.weeks.unshift(plans[i].weeks[j]);
				}
			}

			//Wochen neu durchnummerieren
			for (var _i = 0; _i < plan.weeks.length; _i++) {
				plan.weeks[_i].weeknumber = _i + 1;
			}

			return plan;
		}
	}, {
		key: "resetValues",
		value: function resetValues() {
			Calculator.LambdaCTL = 2 / (Calculator.CTLn + 1);
			Calculator.LambdaATL = 2 / (Calculator.ATLn + 1);
			Calculator.Monotony = 10;
			Calculator.Strain = 0;
			Calculator.ATL = 0;
			Calculator.CTL = 0;
			Calculator.RestDays = 0;
			Calculator.Trimps = [];
			Calculator.Trimps_Sum = 0;
			Calculator.Plan = null;
			Calculator.LastWeek = -1;
			Calculator.LastTraining = -1;
			Calculator.UserRestHR = 0;
			Calculator.UserMaxHR = 0;
			Calculator.LastWeeks = null;
			Calculator.LastDays = null;
		}
	}, {
		key: "isInitialized",
		value: function isInitialized() {
			if (Calculator.Plan === null || Calculator.Plan.weeks === undefined || Calculator.Plan.weeks.length === 0 || Calculator.UserMaxHR === 0 || Calculator.UserRestHR === 0) {
				return false;
			}

			return true;
		}
	}, {
		key: "createIndexes",
		value: function createIndexes() {
			if (!Calculator.isInitialized()) {
				return;
			}

			var trains = [];

			var _iteratorNormalCompletion = true;
			var _didIteratorError = false;
			var _iteratorError = undefined;

			try {
				for (var _iterator = Calculator.Plan.weeks[Symbol.iterator](), _step; !(_iteratorNormalCompletion = (_step = _iterator.next()).done); _iteratorNormalCompletion = true) {
					var w = _step.value;

					var hasTraining = false;
					var _iteratorNormalCompletion3 = true;
					var _didIteratorError3 = false;
					var _iteratorError3 = undefined;

					try {
						for (var _iterator3 = w.trainings[Symbol.iterator](), _step3; !(_iteratorNormalCompletion3 = (_step3 = _iterator3.next()).done); _iteratorNormalCompletion3 = true) {
							var t = _step3.value;

							if (t.planeddone) {
								hasTraining = true;
							}
						}
					} catch (err) {
						_didIteratorError3 = true;
						_iteratorError3 = err;
					} finally {
						try {
							if (!_iteratorNormalCompletion3 && _iterator3.return) {
								_iterator3.return();
							}
						} finally {
							if (_didIteratorError3) {
								throw _iteratorError3;
							}
						}
					}

					if (hasTraining && w.weeknumber > Calculator.LastWeek) {
						Calculator.LastWeek = w.weeknumber;
						trains = w.trainings;
					}
				}
			} catch (err) {
				_didIteratorError = true;
				_iteratorError = err;
			} finally {
				try {
					if (!_iteratorNormalCompletion && _iterator.return) {
						_iterator.return();
					}
				} finally {
					if (_didIteratorError) {
						throw _iteratorError;
					}
				}
			}

			if (Calculator.LastWeek == -1) {
				return;
			}

			if (trains === undefined || trains.length === 0) {
				return;
			}

			var _iteratorNormalCompletion2 = true;
			var _didIteratorError2 = false;
			var _iteratorError2 = undefined;

			try {
				for (var _iterator2 = trains[Symbol.iterator](), _step2; !(_iteratorNormalCompletion2 = (_step2 = _iterator2.next()).done); _iteratorNormalCompletion2 = true) {
					var _t = _step2.value;

					if (_t.planeddone && _t.day > Calculator.LastTraining) {
						Calculator.LastTraining = _t.day;
					}
				}
			} catch (err) {
				_didIteratorError2 = true;
				_iteratorError2 = err;
			} finally {
				try {
					if (!_iteratorNormalCompletion2 && _iterator2.return) {
						_iterator2.return();
					}
				} finally {
					if (_didIteratorError2) {
						throw _iteratorError2;
					}
				}
			}

			if (Calculator.LastTraining == -1) {
				return;
			}

			if (Calculator.WeekDay < Calculator.LastTraining) //dann liegt now in der nächsten Woche
				{
					Calculator.LastWeek++;
				}

			console.log("lastWeek:" + Calculator.LastWeek);
			console.log("lastTraining:" + Calculator.LastTraining);
			console.log("now:" + Calculator.Now);
		}
	}, {
		key: "createTrimps",
		value: function createTrimps() {
			if (!Calculator.isInitialized()) {
				return;
			}

			var lastWeek = Calculator.LastWeek;

			//von jetzt an Math.max(MONOn, ATLn, CTLn) Tage zurück

			var _loop = function _loop(i) {
				var weeks = Calculator.Plan.weeks.filter(function (o) {
					return o.weeknumber == lastWeek;
				});
				if (weeks === undefined || weeks.length === 0) {
					Calculator.Trimps.push(0);
				} else {
					var week = weeks[0];
					//alle trainings an dem tag
					var res = week.trainings.filter(function (o) {
						return o.day + (lastWeek - 1) * 7 == Calculator.Now - i && o.planeddone === true;
					});
					if (res === undefined || res.length === 0) //leere Trimps pushen
						{
							Calculator.Trimps.push(0);
						} else {
						var dayTrimp = 0;
						var _iteratorNormalCompletion4 = true;
						var _didIteratorError4 = false;
						var _iteratorError4 = undefined;

						try {
							for (var _iterator4 = res[Symbol.iterator](), _step4; !(_iteratorNormalCompletion4 = (_step4 = _iterator4.next()).done); _iteratorNormalCompletion4 = true) //mehrere Trainings
							{
								var k = _step4.value;

								dayTrimp += (k.durationminutes + k.durationhours * 60) * (k.avghr - Calculator.UserRestHR) / (Calculator.UserMaxHR - Calculator.UserRestHR) * 0.64 * Math.pow(Math.E, 1.92 * (k.avghr - Calculator.UserRestHR) / (Calculator.UserMaxHR - Calculator.UserRestHR));
							}
						} catch (err) {
							_didIteratorError4 = true;
							_iteratorError4 = err;
						} finally {
							try {
								if (!_iteratorNormalCompletion4 && _iterator4.return) {
									_iterator4.return();
								}
							} finally {
								if (_didIteratorError4) {
									throw _iteratorError4;
								}
							}
						}

						Calculator.Trimps.push(dayTrimp);
					}
				}

				if ((Calculator.Now - i) % 7 == 1) {
					lastWeek--;
				}

				if (lastWeek === 0) {
					return "break";
				}
			};

			for (var i = 0; i < Math.max(Calculator.MONOn, Calculator.ATLn, Calculator.CTLn); i++) {
				var _ret = _loop(i);

				if (_ret === "break") break;
			}

			//Monotonie und Strain
			for (var _i2 = 0; _i2 < Math.min(Calculator.MONOn, Calculator.Trimps.length); _i2++) {
				Calculator.Trimps_Sum += Calculator.Trimps[_i2];
			}

			console.log('trimps:' + Calculator.Trimps);
			console.log('trimps_sum:' + Calculator.Trimps_Sum);
		}
	}, {
		key: "calcPerformance",
		value: function calcPerformance() {
			if (!Calculator.isInitialized()) {
				return;
			}

			Calculator.createIndexes();
			Calculator.createTrimps();

			//benötigte Arrays
			var fatigue = [];
			var fitness = [];
			var performance = [];

			var avg = Calculator.Trimps_Sum / Math.min(Calculator.MONOn, Calculator.Trimps.length);
			var variance = 0;

			for (var _i3 = 0; _i3 < Math.min(Calculator.MONOn, Calculator.Trimps.length); _i3++) {
				variance += Math.pow(Calculator.Trimps[_i3] - avg, 2);
			}

			variance /= Math.min(Calculator.MONOn, Calculator.Trimps.length);
			console.log(variance);

			if (variance !== 0) {
				Calculator.Monotony = (avg / Math.sqrt(variance)).toFixed(2);
			}

			Calculator.Strain = (Calculator.Trimps_Sum * Calculator.Monotony).toFixed(2);

			//TSB, ATL und CTL
			for (var i = 1; i <= Calculator.Trimps.length; i++) {
				var lastFitness = void 0;
				if (fitness[i - 1] === undefined) {
					lastFitness = 0;
				} else {
					lastFitness = fitness[i - 1];
				}
				var lastFatigue = void 0;
				if (fatigue[i - 1] === undefined) {
					lastFatigue = 0;
				} else {
					lastFatigue = fatigue[i - 1];
				}

				fitness[i] = Calculator.Trimps[Calculator.Trimps.length - i] * Calculator.LambdaCTL + (1 - Calculator.LambdaCTL) * lastFitness;

				fatigue[i] = Calculator.Trimps[Calculator.Trimps.length - i] * Calculator.LambdaATL + (1 - Calculator.LambdaATL) * lastFatigue;

				performance[i] = fitness[i] - fatigue[i];
			}

			if (fitness[fitness.length - 1] === undefined) {
				Calculator.CTL = 0;
			} else {
				Calculator.CTL = fitness[fitness.length - 1].toFixed(2);
			}

			if (fatigue[fatigue.length - 1] === undefined) {
				Calculator.ATL = 0;
			} else {
				Calculator.ATL = fatigue[fatigue.length - 1].toFixed(2);
			}

			if (performance[performance.length - 1] === undefined) {
				Calculator.TSB = 0;
			} else {
				Calculator.TSB = performance[performance.length - 1].toFixed(2);
			}

			var restDays = 0;
			if (Calculator.ATL === 0) {
				return;
			}
			if (Calculator.CTL === 0) {
				Calculator.CTL = 1;
			}

			restDays = Math.log(Calculator.CTL / Calculator.ATL) / Math.log((1 - Calculator.LambdaATL) / (1 - Calculator.LambdaCTL));
			if (Calculator.CTL < 15) //Fallback für sehr niedrige CTLs
				{
					restDays = 4 + restDays / -5;
				}

			Calculator.RestDays = Math.max(0, restDays).toFixed(2);

			console.log(Calculator.CTL);
			console.log(Calculator.ATL);
			console.log(Calculator.TSB);
			console.log(Calculator.RestDays);
		}
	}, {
		key: "createChartData",
		value: function createChartData() {
			var now = Calculator.Now;
			var week = 0;
			var dataDays = [];
			var dataWeeks = [];
			var sumWeekTrimp = 0;
			for (var i = 0; i < Calculator.Trimps.length; i++) {
				if (i < 7) {
					var dataDay = {
						x: Calculator.getDay(now),
						y: [Calculator.Trimps[i]]
					};
					dataDays.push(dataDay);
				}

				//Wochen Trimp aufsummieren
				sumWeekTrimp += Calculator.Trimps[i];

				now--;
				if (now % 7 == 0) //Woche zu Ende -> von vorne
					{
						//Wochendaten setzen
						var dataWeek = {
							x: Calculator.getWeekName(week),
							y: [sumWeekTrimp]
						};
						dataWeeks.push(dataWeek);

						//Werte zurücksetzen
						sumWeekTrimp = 0;

						now = 7;
						week--;
					}

				if (week == -4) {
					break;
				}
			}

			dataDays.reverse();
			dataWeeks.reverse();

			Calculator.LastDays = {
				series: ['Trimp'],
				data: dataDays
			};

			Calculator.LastWeeks = {
				series: ['Trimp'],
				data: dataWeeks
			};

			console.log(Calculator.LastDays);
			console.log(Calculator.LastWeeks);
		}
	}, {
		key: "getWeekName",
		value: function getWeekName(index) {
			switch (index) {
				case 0:
					return 'Diese';
				case -1:
					return 'Letzte';
				case -2:
					return 'Vorletzte';
				case -3:
					return 'Vorvorletzte';
				default:
					return '';
			}
		}
	}, {
		key: "getDay",
		value: function getDay(index) {
			switch (index % 7) {
				case 1:
					return 'Mo';
				case 2:
					return 'Di';
				case 3:
					return 'Mi';
				case 4:
					return 'Do';
				case 5:
					return 'Fr';
				case 6:
					return 'Sa';
				case 7:
				case 0:
					return 'So';
				default:
					return '';
			}
		}
	}, {
		key: "MONOn",
		get: function get() {
			return 7;
		}
	}, {
		key: "ATLn",
		get: function get() {
			return 7;
		}
	}, {
		key: "CTLn",
		get: function get() {
			return 42;
		}
	}, {
		key: "LambdaCTL",
		set: function set(value) {
			Calculator._LambdaCTL = value;
		},
		get: function get() {
			return Calculator._LambdaCTL;
		}
	}, {
		key: "LambdaATL",
		set: function set(value) {
			Calculator._LambdaATL = value;
		},
		get: function get() {
			return Calculator._LambdaATL;
		}
	}, {
		key: "Monotony",
		set: function set(value) {
			Calculator._Monotony = value;
		},
		get: function get() {
			return Calculator._Monotony;
		}
	}, {
		key: "Strain",
		set: function set(value) {
			Calculator._Strain = value;
		},
		get: function get() {
			return Calculator._Strain;
		}
	}, {
		key: "ATL",
		set: function set(value) {
			Calculator._ATL = value;
		},
		get: function get() {
			return Calculator._ATL;
		}
	}, {
		key: "CTL",
		set: function set(value) {
			Calculator._CTL = value;
		},
		get: function get() {
			return Calculator._CTL;
		}
	}, {
		key: "Trimps",
		set: function set(value) {
			Calculator._Trimps = value;
		},
		get: function get() {
			return Calculator._Trimps;
		}
	}, {
		key: "Trimps_Sum",
		set: function set(value) {
			Calculator._Trimps_Sum = value;
		},
		get: function get() {
			return Calculator._Trimps_Sum;
		}
	}, {
		key: "Plan",
		set: function set(value) {
			Calculator._Plan = value;
		},
		get: function get() {
			return Calculator._Plan;
		}
	}, {
		key: "LastWeek",
		set: function set(value) {
			Calculator._LastWeek = value;
		},
		get: function get() {
			return Calculator._LastWeek;
		}
	}, {
		key: "LastTraining",
		set: function set(value) {
			Calculator._LastTraining = value;
		},
		get: function get() {
			return Calculator._LastTraining;
		}
	}, {
		key: "WeekDay",
		get: function get() {
			var day = new Date().getDay();
			if (day === 0) {
				day = 7;
			}
			return day;
		}
	}, {
		key: "Now",
		get: function get() {
			var now = Calculator.WeekDay;

			if (Calculator.LastWeek != -1) {
				now += (Calculator.LastWeek - 1) * 7;
			}

			return now;
		}
	}, {
		key: "UserRestHR",
		set: function set(value) {
			Calculator._UserRestHR = value;
		},
		get: function get() {
			return Calculator._UserRestHR;
		}
	}, {
		key: "UserMaxHR",
		set: function set(value) {
			Calculator._UserMaxHR = value;
		},
		get: function get() {
			return Calculator._UserMaxHR;
		}
	}, {
		key: "TSB",
		set: function set(value) {
			Calculator._TSB = value;
		},
		get: function get() {
			return Calculator._TSB;
		}
	}, {
		key: "RestDays",
		set: function set(value) {
			Calculator._RestDays = value;
		},
		get: function get() {
			return Calculator._RestDays;
		}
	}, {
		key: "LastWeeks",
		set: function set(value) {
			Calculator._LastWeeks = value;
		},
		get: function get() {
			return Calculator._LastWeeks;
		}
	}, {
		key: "LastDays",
		set: function set(value) {
			Calculator._LastDays = value;
		},
		get: function get() {
			return Calculator._LastDays;
		}
	}]);

	return Calculator;
}();