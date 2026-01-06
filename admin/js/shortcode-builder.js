jQuery(document).ready(function($) {
    
    function refreshPreview() {
        var posts = $('#n2n-posts').val();
        var category = $('#n2n-category').val();
        var tag = $('#n2n-tag').val(); // New
        var orderby = $('#n2n-order').val();
        var layout = $('#n2n-layout').val();
        var length = $('#n2n-excerpt-length').val();
        var showImage = $('#n2n-show-image').is(':checked');

        // Update Shortcode Text
        var shortcode = '[n2n_news posts="' + posts + '"';
        if(category) shortcode += ' category="' + category + '"';
        if(tag) shortcode += ' tag="' + tag + '"'; // New
        if(layout !== 'grid') shortcode += ' layout="' + layout + '"';
        if(orderby !== 'date') shortcode += ' order="' + orderby + '"';
        
        shortcode += ']';
        $('#n2n-generated-code').val(shortcode);

        // Fetch Preview
        $('#n2n-preview-canvas').css('opacity', '0.5');
        
        $.post(n2n_ajax.url, {
            action: 'n2n_preview_news',
            nonce: n2n_ajax.nonce,
            posts: posts,
            category: category,
            tag: tag, // New
            orderby: orderby,
            layout: layout,
            excerpt_length: length,
            show_image: showImage
        }, function(response) {
            $('#n2n-preview-canvas').html(response).css('opacity', '1');
        });
    }

    // Bind events - Added #n2n-tag
    $('#n2n-posts, #n2n-category, #n2n-tag, #n2n-order, #n2n-layout, #n2n-excerpt-length, #n2n-show-image').on('change input', function() {
        // Debounce slightly
        clearTimeout(window.n2nTimer);
        window.n2nTimer = setTimeout(refreshPreview, 300);
    });

    // Copy Button
    $('#n2n-copy-btn').click(function(e) {
        e.preventDefault();
        var copyText = document.getElementById("n2n-generated-code");
        copyText.select();
        document.execCommand("copy");
        
        var $btn = $(this);
        var original = $btn.text();
        $btn.text('Copied!');
        setTimeout(function() { $btn.text(original); }, 1500);
    });

    // Init
    refreshPreview();
});
