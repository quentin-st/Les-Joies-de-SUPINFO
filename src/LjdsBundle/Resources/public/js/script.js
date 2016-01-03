$(document).ready(function() {
	// Avoid the hash (#) in the URL
	$('a[href="#"]').on('click', function(e){ e.preventDefault() });

	// Report action
	report();

	// Countdown until upcoming gif
	var countdown = $('.countdown');
	if (countdown.length) {
		var container = countdown.find('ul');

		container.countdown(container.data('datetime'), function(event) {
			$(this).html(
				event.strftime('<li>%H</li><li class="sep">:</li><li>%M</li><li class="sep">:</li><li>%S</li>')
			);
		});
	}

	// Overflow buttons
	$('.gif-overflow-actions')
		.appendTo($('#dropdowns'))
		.on('show', function(event, dropdownData) {
			dropdownData.trigger.addClass('force-visible');
		}).on('hide', function(event, dropdownData) {
			$('.overflow-actions').removeClass('force-visible');
		});

	/* SOCIAL BUTTONS */
	// Twitter buttons
	window.twttr = (function(d, s, id) {
		var js, fjs = d.getElementsByTagName(s)[0],
			t = window.twttr || {};
		if (d.getElementById(id)) return t;
		js = d.createElement(s);
		js.id = id;
		js.src = "https://platform.twitter.com/widgets.js";
		fjs.parentNode.insertBefore(js, fjs);

		t._e = [];
		t.ready = function(f) {
			t._e.push(f);
		};

		return t;
	}(document, "script", "twitter-wjs"));

	// Facebook buttons
	(function (d, s, id) {
		var js, fjs = d.getElementsByTagName(s)[0];
		if (d.getElementById(id)) return;
		js = d.createElement(s);
		js.id = id;
		js.src = "//connect.facebook.net/en_US/sdk.js#xfbml=1&version=v2.3&appId=125396707609867";
		fjs.parentNode.insertBefore(js, fjs);
	}(document, 'script', 'facebook-jssdk'));
});

function report() {
	$('.report').click(function(){
		// Ajax call to /abuse
		$.ajax({
			url : '/abuse',
			type : 'POST',
			data : 'id=' + $(this).data('id'),
			context: this,
			success : function(data){
				$(this).parent().append('<div class="alert ' + data.class + ' reported">' + data.message + '</div>');
				setTimeout(function(){ $('.reported').remove(); }, 10000);
			}
		});

		// Disable all "Report" link for 10 seconds to avoid spam
		$('.report').unbind();
		setTimeout(function(){ report(); }, 10000);
	});
}
