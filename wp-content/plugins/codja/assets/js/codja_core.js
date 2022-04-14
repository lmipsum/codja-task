(function ($) {
	$(document).on('click', 'span, li', function () {
		var columnName = 'id';
		var nextPage = 1;
		var $this = $(this);
		var codja = $('#codja');
		var sort = $('#sort');
		if ($(this).prop('tagName') == 'LI') {
			if ($(this).parents('#codja_roles').length) {
				// click happened on role filter
				$(this).toggleClass('active');
				// default order and sorting
				columnName = 'id';
				sort.val("ASC");
			} else {
				// click happened on paginator
				nextPage = $this.attr('p');
				// maintain sorting
				columnName = $('span.active').attr('id');
			}
		} else {
			// click happened on column header
			columnName = $(this).attr('id');
			if (sort.val() == "ASC") {
				sort.val("DESC");
			} else {
				sort.val("ASC");
			}
		}
		// get active role groups
		var filter = $('#codja_roles .active').map(function () {
			return $(this).text();
		}).get();
		var data = {
			'action': 'codja_action',
			'columnName': columnName,
			'sort': sort.val(),
			'page': nextPage,
			'filter': filter
		};
		$.post(ajax.ajaxurl, data, function (response) {
			codja.empty();

			// updates the table and the paginatior
			codja.append(response);
		})
	});
})(jQuery);
