// global namespace
var OAUTH = OAUTH || {};

// displays a short-lived notification message at the top of the screen:
OAUTH.notify = function(msg) {
	jQuery(".login-message").remove();
	var h = "";
	h += "<div class='login-message'><span>" + msg + "</span></div>";
	jQuery("body").prepend(h);
	jQuery(".login-message").delay(10000).fadeOut(2000);
}

// unlink provider account (by row id)
OAUTH.unlink_account = function(btn,oauth_identity_row){
	var post_data = {
		action: "oauth_unlink_account",
		oauth_identity_row: oauth_identity_row,
	}
	jQuery.ajax({
		type: "POST",
		url: oauth_vars.ajaxurl,
		data: post_data,
		success: function(response) {
			var oresponse = JSON.parse(response);
			if (oresponse["result"] == 1) {
				btn.parent().fadeOut(1000, function() {
					btn.parent().remove();
				});
			}
		}
	});

}

jQuery(document).ready(function() {

	// attach unlink button click events:
	jQuery(".oauth-unlink-account").click(function(event) {
		event.preventDefault();
		var btn = jQuery(this);
		var oauth_identity_row = btn.data("oauth-identity-row");
		btn.hide();
		btn.after("<span> Please wait...</span>");
		// Unlink Account
		var response = OAUTH.unlink_account(btn,oauth_identity_row);
	});

	// hide the login form if the admin enabled this setting:
	if (oauth_vars.hide_login_form == 'no') {
		jQuery("#login #loginform").hide();
		jQuery("#login #nav").hide();
		jQuery("#login #backtoblog").hide();
	}
	// TODO: This doesn't prevent login through form, it simply hides it, need to prevent login with php

});