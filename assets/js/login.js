$('.switchLogin').click(function() {
    $('.form-register').animate({ height: "toggle", opacity: "toggle" }, "slow");
    $('.form-login').animate({ height: "toggle", opacity: "toggle" }, "slow");
});