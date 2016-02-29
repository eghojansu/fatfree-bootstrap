$(document).ready(function(){
  $.fn.triggerNotif = function() {
    return this.each(function(){
      var $el = $(this);
      $.notify({
          icon: 'pe-7s-'+$el.data('icon'),
          message: $el.html()
        }, $el.data());
    });
  };

  $('.trigger-notif').triggerNotif();
  $('.confirm-delete').on('click', function(event){
    event.preventDefault();
    var target = $(this).attr('href');
    bootbox.confirm("Hapus data?", function(result) {
      if (result)
        document.location.href = target;
    });
  });
  if (typeof $.fn.datetimepicker !== 'undefined') {
    $('.use-datepicker').each(function(){
      $(this).datetimepicker({
        format: 'YYYY-MM-DD'
      });
    });
  }
  if (typeof $.fn.bootstrapSwitch !== 'undefined') {
    $('.use-switch').bootstrapSwitch({
      size: 'small'
    });
  }
  if (typeof $.fn.selectpicker !== 'undefined') {
    $('.use-selectpicker').each(function(){
      var option = $.extend({}, $(this).data(), {size: 5});
      $(this).selectpicker(option).on('changed.bs.select', function(event){
        $(this).blur();
      });
    });
  }
});