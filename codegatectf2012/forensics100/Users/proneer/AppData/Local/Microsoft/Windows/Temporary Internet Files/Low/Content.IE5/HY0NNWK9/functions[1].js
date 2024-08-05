//Hover
	$(document).ready(function() {

		$('.overbutton').append('<span class="hover"></span>').each(function () {
			
			var $span = $('> span.hover', this).css('opacity', 0);
			
			$(this).hover(function () {
				$span.stop().fadeTo(300, 1);
			}, function () {
				$span.stop().fadeTo(300, 0);
			});
		});

		// handle contact form submit
		contactSubmit();
		// handle captcha reload
		captchaReload();
		
	});

// fixPNG();
function fixPNG(element)
{
	if (/MSIE (5\.5|6).+Win/.test(navigator.userAgent))
	{
		var src;
		
		if (element.tagName=='IMG')
		{
			if (/\.png$/.test(element.src))
			{
				src = element.src;
				element.src = "/img/blank.gif";
			}
		}
		else
		{
			src = element.currentStyle.backgroundImage.match(/url\("(.+\.png)"\)/i)
			if (src)
			{
				src = src[1];
				element.runtimeStyle.backgroundImage="none";
			}
		}
		
		if (src) element.runtimeStyle.filter = "progid:DXImageTransform.Microsoft.AlphaImageLoader(src='" + src + "',sizingMethod='scale')";
	}
}

function contactSubmit() {

	$('#send').click(function() {
		
		var formValid = true;

		// empty all previous errors
		$('input[name="subject"]').removeClass('error');
		$('#subjectError').hide();
		$('input[name="message"]').removeClass('error');
		$('#messageError').hide();
		$('input[name="email"]').removeClass('error');
		$('#emailError').hide();
		$('input[name="captcha"]').removeClass('error');
		$('#captchaError').hide();

		// valildate subject
		if( $('input[name="subject"]').val() == '' ) {

			formValid = false;
			$('input[name="subject"]').addClass('error');
			$('#subjectError').show();
		}
		
		// valildate message
		if( $('textarea[name="message"]').val() == '' ) {

			formValid = false;
			$('textarea[name="message"]').addClass('error');
			$('#messageError').show();
		}

		// valildate email
		if( /[a-zA-Z_]+@[a-zA-Z_]+(\.[a-zA-Z_]+)+/.test( $('input[name="email"]').val() ) == false ) {

			formValid = false;
			$('input[name="email"]').addClass('error');
			$('#emailError').show();
		}

		// validate kaptcha
		if( $('input[name="captcha"]').val() == '' ) {

			formValid = false;
			$('input[name="captcha"]').addClass('error');
			$('#captchaError').show();
		}
		
		var captcha = $('input[name="captcha"]').val();

		if( $('input[name="captcha"]').val() != '' ) {
			
			// captcha ajax check
			$.post('/securimage/ajax_check.php', {captcha : captcha}, function(data){

				var captchaError = data.captchaError;

				if( captchaError == true ) {

					formValid = false;
					$('input[name="captcha"]').addClass('error');
					$('#captchaError').show();
				}
				else {
					$('.keySequence img').attr('src', '/securimage/securimage_show.php?sid=' + Math.random());
					$('input[name="captcha"]').val('');
				}

				if( formValid )
					$('#contact_form form').submit();

			}, 'json');
		}
	});
}

function captchaReload() {

	$('.keySequence img').click(function() {

		$(this).attr('src', '/securimage/securimage_show.php?' + Math.random());
	});
}