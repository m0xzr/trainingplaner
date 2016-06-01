class Calculator {

	static get MONOn() {
		return 7;
	}

	static get ATLn() {
		return 7;
	}

	static get CTLn() {
		return 42;
	}

	static set LambdaCTL(value) {
		Calculator._LambdaCTL = value;
	}

	static get LambdaCTL() {
		return Calculator._LambdaCTL;
	}

	static set LambdaATL(value) {
		Calculator._LambdaATL = value;
	}

	static get LambdaATL() {
		return Calculator._LambdaATL;
	}

	static set Monotony(value) {
		Calculator._Monotony = value;
	}

	static get Monotony() {
		return Calculator._Monotony;
	}

	static set Strain(value) {
		Calculator._Strain = value;
	}

	static get Strain() {
		return Calculator._Strain;
	}

	static set ATL(value) {
		Calculator._ATL = value;
	}

	static get ATL() {
		return Calculator._ATL;
	}

	static set CTL(value) {
		Calculator._CTL = value;
	}

	static get CTL() {
		return Calculator._CTL;
	}

	static set Trimps(value) {
		Calculator._Trimps = value;
	}

	static get Trimps() {
		return Calculator._Trimps;
	}

	static set Trimps_Sum(value) {
		Calculator._Trimps_Sum = value;
	}

	static get Trimps_Sum() {
		return Calculator._Trimps_Sum;
	}

	static set Plan(value) {
		Calculator._Plan = value;
	}

	static get Plan() {
		return Calculator._Plan;
	}

	static set LastWeek(value) {
		Calculator._LastWeek = value;
	}

	static get LastWeek() {
		return Calculator._LastWeek;
	}

	static set LastTraining(value) {
		Calculator._LastTraining = value;
	}

	static get LastTraining() {
		return Calculator._LastTraining;
	}

	static get WeekDay() {
		let day = new Date().getDay();
		if (day === 0) {
			day = 7;
		}
		return day;
	}

	static get Now() {
		let now = Calculator.WeekDay;

		if (Calculator.LastWeek != -1) {
			now += (Calculator.LastWeek - 1) * 7;
		}

		return now;
	}

	static set UserRestHR(value) {
		Calculator._UserRestHR = value;
	}

	static get UserRestHR() {
		return Calculator._UserRestHR;
	}

	static set UserMaxHR(value) {
		Calculator._UserMaxHR = value;
	}

	static get UserMaxHR() {
		return Calculator._UserMaxHR;
	}

	static set TSB(value) {
		Calculator._TSB = value;
	}

	static get TSB() {
		return Calculator._TSB;
	}

	static set RestDays(value) {
		Calculator._RestDays = value;
	}

	static get RestDays() {
		return Calculator._RestDays;
	}

	static set LastWeeks(value) {
		Calculator._LastWeeks = value;
	}

	static get LastWeeks() {
		return Calculator._LastWeeks;
	}

	static set LastDays(value) {
		Calculator._LastDays = value;
	}

	static get LastDays() {
		return Calculator._LastDays;
	}

	static Init(plans, userRestHR, userMaxHR) {
		console.log(plans);
		Calculator.resetValues();
		Calculator.Plan = Calculator.initPlan(plans);
		Calculator.UserRestHR = userRestHR;
		Calculator.UserMaxHR = userMaxHR;
	}
	
	static initPlan(plans) {				//aus mehreren Plänen einen machen
		if(plans === null) {
			return null;
		}
		
		if(plans.length === 0) {
			return null;
		}
		
		let plan = plans[plans.length - 1];
		
		for(let i = 0; i < plans.length - 1; i++) {
			if(plans[i].weeks === undefined) {
				continue;
			}
			
			for(let j = plans[i].weeks.length - 1; j >= 0; j--) {
				if(plans[i].weeks[j].trainings === undefined || plans[i].weeks[j].trainings.length === 0) {
					continue;
				}
				
				plan.weeks.unshift(plans[i].weeks[j]);
			}
		}
		
		//Wochen neu durchnummerieren
		for(let i = 0; i < plan.weeks.length; i++) {
			plan.weeks[i].weeknumber = i + 1;
		}
		
		return plan;
	}

	static resetValues() {
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

	static isInitialized() {
		if (Calculator.Plan === null || Calculator.Plan.weeks === undefined || Calculator.Plan.weeks.length === 0 || Calculator.UserMaxHR === 0 || Calculator.UserRestHR === 0) {
			return false;
		}

		return true;
	}

	static createIndexes() {
		if (!Calculator.isInitialized()) {
			return;
		}

		let trains = [];

		for (const w of Calculator.Plan.weeks) {
			let hasTraining = false;
			for (const t of w.trainings) {
				if (t.planeddone) {
					hasTraining = true;
				}
			}

			if (hasTraining && w.weeknumber > Calculator.LastWeek) {
				Calculator.LastWeek = w.weeknumber;
				trains = w.trainings;
			}
		}

		if (Calculator.LastWeek == -1) {
			return;
		}

		if (trains === undefined || trains.length === 0) {
			return;
		}

		for (const t of trains) {
			if (t.planeddone && t.day > Calculator.LastTraining) {
				Calculator.LastTraining = t.day;
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

	static createTrimps() {
		if (!Calculator.isInitialized()) {
			return;
		}

		let lastWeek = Calculator.LastWeek;

		//von jetzt an Math.max(MONOn, ATLn, CTLn) Tage zurück
		for (let i = 0; i < Math.max(Calculator.MONOn, Calculator.ATLn, Calculator.CTLn); i++) {
			let weeks = Calculator.Plan.weeks.filter(function (o) {
					return o.weeknumber == lastWeek;
				});
			if (weeks === undefined || weeks.length === 0) {
				Calculator.Trimps.push(0);
			} else {
				let week = weeks[0];
				//alle trainings an dem tag
				let res = week.trainings.filter(function (o) {
						return ((o.day + ((lastWeek - 1) * 7)) == (Calculator.Now - i)) && (o.planeddone === true);
					});
				if (res === undefined || res.length === 0) //leere Trimps pushen
				{
					Calculator.Trimps.push(0);
				} else {
					let dayTrimp = 0;
					for (const k of res) //mehrere Trainings
					{
						dayTrimp += (k.durationminutes + k.durationhours * 60) * (k.avghr - Calculator.UserRestHR) / (Calculator.UserMaxHR - Calculator.UserRestHR) * 0.64 * Math.pow(Math.E, (1.92 * (k.avghr - Calculator.UserRestHR) / (Calculator.UserMaxHR - Calculator.UserRestHR)));
					}
					Calculator.Trimps.push(dayTrimp);
				}
			}

			if ((Calculator.Now - i) % 7 == 1) {
				lastWeek--;
			}

			if (lastWeek === 0) {
				break;
			}
		}

		//Monotonie und Strain
		for (let i = 0; i < Math.min(Calculator.MONOn, Calculator.Trimps.length); i++) {
			Calculator.Trimps_Sum += Calculator.Trimps[i];
		}

		console.log('trimps:' + Calculator.Trimps);
		console.log('trimps_sum:' + Calculator.Trimps_Sum);
	}

	static calcPerformance() {
		if (!Calculator.isInitialized()) {
			return;
		}

		Calculator.createIndexes();
		Calculator.createTrimps();

		//benötigte Arrays
		let fatigue = [];
		let fitness = [];
		let performance = [];

		let avg = Calculator.Trimps_Sum / Math.min(Calculator.MONOn, Calculator.Trimps.length);
		let variance = 0;

		for (let i = 0; i < Math.min(Calculator.MONOn, Calculator.Trimps.length); i++) {
			variance += Math.pow(Calculator.Trimps[i] - avg, 2);
		}

		variance /= Math.min(Calculator.MONOn, Calculator.Trimps.length);
		console.log(variance);

		if (variance !== 0) {
			Calculator.Monotony = (avg / Math.sqrt(variance)).toFixed(2);
		}

		Calculator.Strain = (Calculator.Trimps_Sum * Calculator.Monotony).toFixed(2);

		//TSB, ATL und CTL
		for (var i = 1; i <= Calculator.Trimps.length; i++) {
			let lastFitness;
			if (fitness[i - 1] === undefined) {
				lastFitness = 0;
			} else {
				lastFitness = fitness[i - 1];
			}
			let lastFatigue;
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

		let restDays = 0;
		if (Calculator.ATL === 0) {
			return;
		}
		if (Calculator.CTL === 0) {
			Calculator.CTL = 1;
		}

		restDays = Math.log(Calculator.CTL / Calculator.ATL) / (Math.log((1 - Calculator.LambdaATL) / (1 - Calculator.LambdaCTL)));
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

	static createChartData() {
		let now = Calculator.Now;
		let week = 0;
		let dataDays = [];
		let dataWeeks = [];
		let sumWeekTrimp = 0;
		for (let i = 0; i < Calculator.Trimps.length; i++) {
			if (i < 7) {
				let dataDay = {
					x : Calculator.getDay(now),
					y : [Calculator.Trimps[i]],
				};
				dataDays.push(dataDay);
			}

			//Wochen Trimp aufsummieren
			sumWeekTrimp += Calculator.Trimps[i];

			now--;
			if (now % 7 == 0) //Woche zu Ende -> von vorne
			{
				//Wochendaten setzen
				let dataWeek = {
					x : Calculator.getWeekName(week),
					y : [sumWeekTrimp],
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
			series : ['Trimp'],
			data : dataDays
		};

		Calculator.LastWeeks = {
			series : ['Trimp'],
			data : dataWeeks
		};

		console.log(Calculator.LastDays);
		console.log(Calculator.LastWeeks);
	}

	static getWeekName(index) {
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

	static getDay(index) {
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
}
