$(document).ready(function() {
    // Avoid the hash (#) in the URL
    $('a[href="#"]').on('click', function(e){ e.preventDefault() });

    report();

	// Countdown until upcoming gif
	var countdown = $('.countdown');
	if (countdown.length) {
		var container = countdown.find('ul');
		var d = new Date(container.data('datetime'));
		container.countdown({
			until: d,
			format: 'HMs',
			layout: '<li>{hn}</li><li class="sep">:</li><li>{mnn}</li><li class="sep">:</li><li>{snn}</li>'
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
