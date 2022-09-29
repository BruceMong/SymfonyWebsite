var btn_to_top = $('#button_to_top');

$(window).scroll(function() {
    if ($(window).scrollTop() > 300) {
        btn_to_top.addClass('show');
    } else {
        btn_to_top.removeClass('show');
    }
});

btn_to_top.on('click', function(e) {
    e.preventDefault();
    $('html, body').animate({ scrollTop: 0 }, '300');
});