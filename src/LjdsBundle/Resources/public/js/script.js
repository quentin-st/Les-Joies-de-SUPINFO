$(document).ready(function() {
    // Avoid the hash (#) in the URL
    $('a[href="#"]').on('click', function(e){ e.preventDefault() });

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
});

function report() {
	$('.report').click(function(){

		// Ajax call to abuse.php
		$.ajax({
			url : '/abuse',
			type : 'POST',
			data : 'id=' + $(this).attr('data-id'),
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
