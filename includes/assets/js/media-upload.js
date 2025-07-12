jQuery(document).ready(function($){
    var frame;
    $('#blockxpert-logo-upload-btn').on('click', function(e){
        e.preventDefault();
        if(frame){ frame.open(); return; }
        frame = wp.media({
            title: blockxpert_media.select_logo_title,
            button: {
                text: blockxpert_media.use_logo_text
            },
            multiple: false
        });
        frame.on('select', function(){
            var attachment = frame.state().get('selection').first().toJSON();
            $('#blockxpert_company_logo').val(attachment.url);
            $('#blockxpert-logo-preview').attr('src', attachment.url).show();
            $('#blockxpert-logo-remove-btn').show();
        });
        frame.open();
    });

    $('#blockxpert-logo-remove-btn').on('click', function(e){
        e.preventDefault();
        $('#blockxpert_company_logo').val('');
        $('#blockxpert-logo-preview').hide();
        $(this).hide();
    });
}); 