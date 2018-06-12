// Requires jquery.js

var Tourneys = {
	UPDATEDELAYSECONDS: 1,

	info: [
		// 0 - Expert
		{
			name: "Expert",
			div: "expert",
			openBefore: (10 * 60),
			length: (60 * 60),
			timeUntil: 0
		},
		
		// 1 - Beat Rush
		{
			name: "Beat Rush",
			div: "beatrush",
			openBefore: (10 * 60),
			length: (60 * 60),
			timeUntil: 0
		},
		
		// 2 - Couple
		{
			name: "Couple",
			div: "couple",
			openBefore: (10 * 60),
			length: (60 * 60),
			timeUntil: 0
		},
		
		// 3 - Team
		{
			name: "Team",
			div: "team",
			openBefore: (10 * 60),
			length: (60 * 60),
			timeUntil: 0
		},
		
		// 4 - Beat Up
		{
			name: "Beat Up",
			div: "beatup",
			openBefore: (10 * 60),
			length: (60 * 60),
			timeUntil: 0
		},
		
		// 5 - Ballroom
		{
			name: "Ballroom",
			div: "ballroom",
			openBefore: (10 * 60),
			length: (60 * 60),
			timeUntil: 0
		},
		
		// 6 - Guitar
		{
			name: "Guitar",
			div: "guitar",
			openBefore: (10 * 60),
			length: (60 * 60),
			timeUntil: 0
		},
		
		// 7 - Beginner
		{
			name: "Beginner",
			div: "beginner",
			openBefore: (10 * 60),
			length: (60 * 60),
			timeUntil: 0
		},
		
		// 8 - Intermediate
		{
			name: "Intermediate",
			div: "intermediate",
			openBefore: (10 * 60),
			length: (60 * 60),
			timeUntil: 0
		}
	],
	
	divs: [], // Containers: 0 - In Progress, 1 - Open, 2 - List
	
	intrvl: null,

	init: function() {
		Tourneys.intrvl = setInterval(Tourneys.updateAll, Tourneys.UPDATEDELAYSECONDS * 1000);
	},
	
	// Called every second to update times.
	updateAll: function() {
		for (i in Tourneys.info) {
			var t = Tourneys.info[i];
			
			if (t.timeUntil <= -t.length) // Reset
				t.timeUntil = (60 * 60 * 24) - t.length;
				
			if (t.timeUntil <= 0) { // In Progress
				$("#tourneystatus_" + t.div + ", #tourneyname_" + t.div).css({
					color: "#FFFFAA"
				});
				$("#tourneystatus_" + t.div).html("In Progress - " + 
					Tourneys.convertSeconds(t.timeUntil));
			}
			else if (t.timeUntil <= t.openBefore) { // Open
				$("#tourneystatus_" + t.div + ", #tourneyname_" + t.div).css({
					color: "#AAFFAA"
				});
				$("#tourneystatus_" + t.div).html("Open! Starts in " + 
					Tourneys.convertSeconds(t.timeUntil));
			}
			else {
				$("#tourneystatus_" + t.div + ", #tourneyname_" + t.div).css({
					color: "#FFFFFF"
				});
				$("#tourneystatus_" + t.div).html("Opens in " + 
					Tourneys.convertSeconds(t.timeUntil - t.openBefore));
			}
				
				
			
			t.timeUntil--; // Substract a second
		}
	},
	
	convertSeconds: function(s) {
		var totalSeconds = Math.abs(s);
		var hours = Math.floor(totalSeconds / (60 * 60));
		var minutes = Math.floor((totalSeconds - (hours * 3600)) / 60);
		var seconds = totalSeconds - (hours * 3600) - (minutes * 60);
				
		minutes = minutes < 10 ? "0" + minutes : minutes;
		seconds = seconds < 10 ? "0" + seconds : seconds;
		
		return hours + ":" + minutes + ":" + seconds;
	}
};