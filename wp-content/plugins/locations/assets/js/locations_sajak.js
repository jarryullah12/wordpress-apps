var gp_setup_ajax_forms = function() {
	$ = jQuery;
	var forms = $('div[data-gp-ajax-form="1"]');
	if (forms.length > 0) {
		forms.each(function () {
			var f = this;
			var btns = $(this).find('.button[type="submit"]').on('click', 
				function () {
					gp_submit_ajax_form(f);
					return false;
				} 
			);
		});
	}
};

var gp_submit_ajax_form = function (f) {
	var msg = jQuery('<p><span class="fa fa-refresh fa-spin"></span><em> One moment..</em></p>');	
	var f = jQuery(f).after(msg).detach();
	var enc = f.attr('enctype');
	var act = f.attr('action');
	var meth = f.attr('method');
	var submit_with_ajax = ( f.data('ajax-submit') == 1 );
	
	var wrap = f.wrap('<form></form>').parent();
	wrap.attr('enctype', f.attr('enctype'));
	wrap.attr('action', f.attr('action'));
	wrap.attr('method', f.attr('method'));
	wrap.find('#submit').attr('id', '#notsubmit');

	if ( !submit_with_ajax ) {
		jQuery('body').append(wrap);
		setTimeout(function () {
			wrap.submit();
		}, 500);	
		return false;
	}
	
	data = wrap.serialize();
	
	$.ajax(act,
	{
		crossDomain: true,
		method: 'post',
		data: data,
		dataType: "json",
		success: function (ret) {
			var r = jQuery(ret)[0];
			msg.html('<p class="ajax_response_message">' + r.msg + '</p>');
		}
	});		
};

jQuery(function () {
	gp_setup_ajax_forms();
});

if ( location.hash && location.hash.indexOf('tab-') == 1 ) {               // do the test straight away
    window.scrollTo(0, 0);         // execute it straight away
	setTimeout(function() {
		window.scrollTo(0, 0);     // run it a bit later also for browser compatibility
	}, 1);
}