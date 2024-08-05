$(document).ready(function() {
	$('#ecLink > a').mouseenter(function() {
		$('#ecLinkBG').show().stop().animate({
			width: '28em'
		}, 400, 'easeOutBounce', function() {
			$(this).css("border-top","0");
			$('#ecBG').show().stop().animate({
				height: '9.5em'
			}, 400, 'easeOutBounce', function() {
				$('#ecBox').fadeIn(100, function() {$('#ecSeeOurWork').fadeIn(1000);});
			});
		});
	});
	
	$('#ecCredit').mouseleave(function() {
		$('#ecBox, #ecSeeOurWork').stop().fadeOut(100);
		$('#ecBG').stop().animate({
			height: '0'
		}, 200, function() {
			$(this).hide();
			$('#ecLinkBG').stop().animate({
				width: '0'
			}, 200, function() {
				$(this).hide();
				$(this).css("border-top","1px solid #555759");
			});
		});
	});
	
});

jQuery.extend(
	jQuery.easing, {
		easeOutBounce: function (x, t, b, c, d) {
			if ((t/=d) < (1/2.75)) {
				return c*(7.5625*t*t) + b;
			} else if (t < (2/2.75)) {
				return c*(7.5625*(t-=(1.5/2.75))*t + .75) + b;
			} else if (t < (2.5/2.75)) {
				return c*(7.5625*(t-=(2.25/2.75))*t + .9375) + b;
			} else {
				return c*(7.5625*(t-=(2.625/2.75))*t + .984375) + b;
			}
		}
	}
);
