/**
 * GDPR Data Checker plugin for Craft CMS
 *
 * GDPR Data Checker JS
 *
 * @author    Matt Shearing
 * @copyright Copyright (c) 2018 Matt Shearing
 * @link      https://adigital.agency
 * @package   GdprDataChecker
 * @since     1.0.0
 */

function validateEmail($email) {
	var emailReg = /^([\w-\.]+@([\w-]+\.)+[\w-]{2,4})?$/;
	return emailReg.test( $email );
}

$(document).ready(function(){
	$("#gdprsubmitemail").click(function(e){
		var errors = 0;
		var msg = "";
		if ($("#emailAddress").val().length == 0) {
			msg = "Please enter an email address.";
			errors++;
		}
		if(!validateEmail($("#emailAddress").val())) {
			msg = "Please enter a valid email address.";
			errors++;
		}
		if (errors > 0) {
			$("#emailAddress").parent().addClass("errors");
			if ($("#emailAddress").parent().find(".errormessage").length) {
				$("#emailAddress").parent().find(".errormessage").text(msg);
			} else {
				$("#emailAddress").parent().append("<span class=\"errormessage\">"+msg+"</span>");
			}
			e.preventDefault();
		}
	});
	
	$("#emailAddress").on("change keyup", function(){
		$(this).parent().removeClass("errors");
		$(this).parent().find(".errormessage").remove();
	});
	
	if ($(".blockcontent:first").length && $(".blockheading").length) {
		$(".blockcontent:first").parent().find(".blockheading").addClass("expanded");
	}
	
	$(".blockheading").click(function(){
		$(".subcontent").slideUp();
		$(".expanded").removeClass("expanded");
		if ($(this).next(".blockcontent").is(":visible")) {
			$(this).next(".blockcontent").slideUp();
		} else {
			$(this).addClass("expanded");
			$(".blockcontent").slideUp();
			$(this).next(".blockcontent").slideDown();
		}
	});
	
	$(".subheading").click(function(){
		$(this).parent().find(".expanded").removeClass("expanded");
		if ($(this).next(".subcontent").is(":visible")) {
			$(this).next(".subcontent").slideUp();
		} else {
			$(this).addClass("expanded");
			$(this).parent().find(".subcontent").slideUp();
			$(this).next(".subcontent").slideDown();
		}
	});
});