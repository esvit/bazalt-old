$(function() {
    $('.thumbnails .thumbnail, .news-article-detail .image-container a[itemprop="image"], .news-article-body a.preview, #am-container a, .fancybox, [data-fancybox-group]').fancybox({
        'titleShow'     : false,
        'openEffect'  : 'elastic',
        'closeEffect' : 'elastic',
        'margin'      : [0, 0, 0, 0],
        'padding'     : [0, 0, 0, 0],
        'nextClick'   : true,
        'type'        : 'image',
        helpers	: {
            overlay : null,
            title	: {
                type: 'over'
            },
            thumbs	: {
                width	: 50,
                height	: 50
            }
        }
    });

    $('time.timeago').timeago();
});