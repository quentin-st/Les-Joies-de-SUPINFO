$(document).ready(function() {

    // Avoid the hash (#) in the URL
    $('a[href="#"]').on('click', function(e){ e.preventDefault() });

    function report() {
        $('.report').click(function(){

            // Ajax call to abuse.php
            $.ajax({
                url : 'abuse.php',
                type : 'POST',
                data : 'id=' + $(this).attr('data-id'),
                context: this,
                success : function(msg){
                    $(this).parent().append(msg);
                    setTimeout(function(){ $('.reported').remove(); }, 10000);
                }
            });

            // Disable all "Report" link for 10 seconds to avoid spam
            $('.report').unbind();
            setTimeout(function(){ report(); }, 10000);
        });
    }
    report();
});