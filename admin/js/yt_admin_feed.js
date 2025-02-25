jQuery(document).ready(function($){

    $('#add_new_feed').click(function(){
        $('#adding_feed').modal('show');
    });

    $('#close_popup').click(function(){
        $('#adding_feed').modal('hide');
    });

    // Delete post
    $(document).on('click', 'a[data-id]', function(){
        var id = this.dataset.id;

        $.ajax({
            type: "POST",
            url: solicitudAjax.url,
            data: {
                action: 'deleteFeedPetition',
                nonce: solicitudAjax.security,
                id: id,
            },
            success: function(){
                location.reload();
            }
        });
    });

});