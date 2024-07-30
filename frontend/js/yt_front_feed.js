jQuery(document).ready(function ($) {
	
    // Load more posts
	$(document).on("click", "#load_more_button", function () {
		var nextPageToken = this.dataset.next_page_token;

		var channelId = $('[name="channel_id"]').val(); // Reemplaza con tu channel ID
		var googleKey = $('[name="google_key"]').val(); // Reemplaza con tu Google API Key

		$.ajax({
			type: "POST",
			url: solicitudAjax.url, // Cambia esta URL si es necesario
			data: {
				action: "load_more_posts",
				channel_id: channelId,
				google_key: googleKey,
				next_page_token: nextPageToken,
				nonce: solicitudAjax.security,
			},
			success: function (response) {
                if (response.success) {
                    // console.log(response);
                    $(".frm_yt_feed .frm_yt_grid").append(response.data.content);
                    
                    // Eliminar el botón solo si no hay más posts
                    if (!response.data.has_more_posts) {
                        $(".frm_yt_feed-load").remove();
                        $(".frm_yt_feed").addClass('no_more_posts');
                    } else {
                        // Actualizar el botón con el nuevo nextPageToken
                        $(".frm_yt_feed-load").remove();
                        initLoadMoreButton(response.data.next_page_token);
                    }
                } else {
                    console.log(response.data); // Muestra el mensaje de error en la consola
                }
			},
            error: function (jqXHR, textStatus, errorThrown) {
                console.log('Error:', textStatus, errorThrown);
            }
		});
	});

    // Inicializar el botón "Load More" al cargar la página si hay más posts
    function initLoadMoreButton(nextPageToken) {
        var buttonHtml = `<div class="frm_yt_feed-load">
            <button type="button" id="load_more_button" data-next_page_token="${nextPageToken}" title="Load More">Load More</button>
        </div>`;
        
        // Asegúrate de que el contenedor exista antes de agregar el botón
        if ($(".frm_yt_feed .frm_yt_grid").length) {
            $(".frm_yt_feed").append(buttonHtml);
        }
    }

});