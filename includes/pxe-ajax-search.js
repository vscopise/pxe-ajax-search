jQuery(document).ready(function ($) {
    var loading = false;
    $('.pxe_ajax_search input').keyup(function(){
        if ( ! loading ) {
            var result_container = $(this).parents('.pxe_ajax_search').children('.ajax-results');
            result_container.html('');
            if($(this).val().length>0){
                $('.icon-container .close').addClass('typing');
            };
            if($(this).val().length>3){
                $('.icon-container .loader').addClass('loading');
                $('.icon-container .close').removeClass('typing');
                loading = true;
                $.ajax({
                    type: 'POST',
                    url:ajax_search_object.ajaxurl,
                    data: {
                        action: 'pxe_ajax_search',
                        input: $(this).val(),
                        nonce : ajax_search_object.nonce
                    },
                    success: function(response) {
                        result_container.html('');
                        $('.icon-container .loader').removeClass('loading');
                        $('.icon-container .close').addClass('typing');
                        loading = false;
                        if(response.results.length===0){
                            item = '<p>No hubo resultados...</p>'
                            result_container.append(item);
                        } else {
                            $.each(response.results,function(index,result){
                                var title = result.title;
                                var link = result.link;
                                var item = '<a href="'+link+'" title="'+title+'">'+title+'</a>';
                                result_container.append(item);
                            });
                        }
                    }
                });
            }
        }
    });
    $('.pxe_ajax_search .close').click(function(){
        $(this).parents('.pxe_ajax_search').children('.ajax-results').html('');
        $(this).parents('.pxe_ajax_search').children().find('input').val('');
    });
});