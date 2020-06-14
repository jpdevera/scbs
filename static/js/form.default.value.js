;(function($) {
  
  $('select').each(function() {
    var $this = $(this);
    if ($this.data('default-value')) {
      var data = $this.data('default-value');
          data = data.toString().split('|');

      for (var x=0; x<data.length; x++) {
        $this.find(`option[value="${data[x]}"]`).attr('selected', 'selected');
      }
    }
  });

  $('.radio, .radios').each(function() {
    var $this = $(this);
    if ($this.data('default-value')) {
      var data = $this.data('default-value');
          data = data.toString().split('|');

      for (var x=0; x<data.length; x++) {
        $this.find(`input[value="${data[x]}"]`).attr('checked', 'checked');
      }
    }
  });

  $('.checkbox').each(function() {
    var $this = $(this);
    if ($this.data('default-value')) {
      var data = $this.data('default-value');
          data = data.toString().split('|');

      for (var x=0; x<data.length; x++) {
        $this.find(`input[value="${data[x]}"]`).attr('checked', 'checked');
      }
    }
  });

  $('.datepicker').each(function() {
    var $this = $(this);
    if ($this.data('default-value')) {
      var data = $this.data('default-value');
      $this.val(data);
    }
  });

  $('.datepicker-min').each(function() {
    var $this = $(this);
    if ($this.data('default-value')) {
      var data = $this.data('default-value');
      $this.val(data);
    }
  });
})(jQuery);
