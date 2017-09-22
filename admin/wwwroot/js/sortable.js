$.fn.sortImg = function(opts = null) {
	if (opts == null) {
		opts = {
			opacity: 0.6,
			handle: 'img',
			axis: false, //x or y
			distance: 1 //px
		};
	} else {
		if (opts.opacity == undefined) {
			opts.opacity = 0.6;
		}
		if (opts.handle == undefined) {
			opts.handle = 'img';
		}
		if (opts.axis == undefined) {
			opts.axis = false;
		}
		if (opts.distance == undefined) {
			opts.distance = 1;
		}
	}
	var $$ = $(this);
	var data = new Array();

	$$.sortable({
	    opacity: opts.opacity,
	    handle: opts.handle,
	    axis: opts.axis,
	    distance: opts.distance,
	    start: function (event, ui) {
	    	//
	    },
	    update: function (event, ui) {
	        $$.find('li').each(function (key, value) {
	        	data.push({id: $(this).data('id'), sort: $$.find('li').length - key});
	        });

	        if (opts.url != undefined) {
	        	$$.ajaxAuto({
	        	    url: opts.url,
	        	    type: 'POST',
	        	    data: {data: data}
	        	});
	        }
	  }
	});
};