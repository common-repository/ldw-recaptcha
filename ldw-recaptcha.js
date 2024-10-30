function recaptcha_callback() {
    var buttons = ['#commentform #submit'];
    for(var i=0; i<=buttons.length; i++) {
        if(jQuery(buttons[i]).length > 0) {
            jQuery(buttons[i]).show();
			jQuery(buttons[i]).after("<br /><br />");
        }
    }
}
