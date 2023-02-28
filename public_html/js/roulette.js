var option = {
	speed : 10,
	duration : 3,
	stopImageNumber : 0,
	startCallback : function() {
		console.log('start');
	},
	slowDownCallback : function() {
		console.log('slowDown');
	},
	stopCallback : function($stopElm) {
		console.log('stop');
	}
}
$('div.roulette').roulette(option);	

