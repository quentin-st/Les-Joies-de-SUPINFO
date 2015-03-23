$(document).ready(function() {
    // Avoid the hash (#) in the URL
    $('a[href="#"]').on('click', function(e){ e.preventDefault() });
    
    function click() {
        $('.report').click(function(){
            // Ajax call to abuse.php

            $.ajax({
                url : 'abuse.php',
                type : 'POST',
                data : 'id=' + $(this).attr('id'),
                context: this,
                success : function(msg){
                    $(this).parent().append('<div class="alert alert-danger">Ce gif a bien été signalé, nous allons le vérifier d\'un peu plus près.</div>');
                }
            });

            // Disable all "Report" link for 10 seconds to avoid spam
            $('.report').unbind();

            setTimeout(function(){
                click();
            }, 10000);

        });
    }
    click();
});