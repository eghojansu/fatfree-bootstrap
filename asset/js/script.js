$(document).ready(function() {
    var printing = false;

	$('.notifier').each(function() {
		var opt = $.extend({}, {
			'style': 'bootstrap',
		}, $(this).data());
		var message = $.trim($(this).html());
		$.notify(message, opt);
	})
    $('[data-transform="autonumeric"]').autoNumeric('init', {
        aSep: '.',
        aDec: ','
    });
	$('[data-toggle=tooltip]').tooltip();
	$('[data-toggle=datepicker]').datepicker({
		format: 'yyyy-mm-dd'
	});
	$('[data-confirm=delete]').on('click', function(event) {
		event.preventDefault();
		var target = $(this).prop('href');
		bootbox.confirm('Hapus data?', function(ya) {
			if (ya) {
				window.location.href = target;
			}
		});
	});
    $('[data-ready=print]').each(function() {
        if (!printing) {
            window.print();
        }

        printing = true;
    });
})
