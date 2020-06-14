;(function($) {
	
  $('.access-menu > a > span.active').css('background',$('.access-menu > a > span.active').data('color'));

  $('.access-menu span:not(".active")').mouseover(function() {
    $(this).css({
      color: '#fff',
      background: $(this).data('color')
    });
  }).mouseleave(function() {
    $(this).css({
      color: 'rgba(255,255,255,0.6)',
      background: 'rgba(0,0,0,0.15)'
    });
  });
  
  $('.nav-header-icons .apps').click(function() {
    var $this = $(this);
    if ($this.find('.access-menu').is(':visible')) {
      $this.removeClass('collapse');
      $('.access-menu').hide();
      $('.nav-header-icons .apps > i').tooltip();
    } else {
      $this.addClass('collapse');
      $('.access-menu').show();

      //$('.portal-access > .user-account').removeClass('collapse');
      //$('.account-menu').hide();
      $('.nav-header-icons .apps > i').tooltip('remove');
    }
  });
  
  // CLOSE NAVIGATION ICONS IF CLICKED OUTSIDE THE ELEMENT
  $(document).mouseup(function(e) 
  {
    var container = $('.nav-header-icons .apps');
    var notif_container = $('.nav-header-icons .notif');
    var account_container = $('.nav-header-icons .account');

    if (!container.is(e.target) && container.has(e.target).length === 0) 
        container.removeClass('collapse');
	
	if (!notif_container.is(e.target) && notif_container.has(e.target).length === 0) 
        notif_container.removeClass('collapse');
	
	if (!account_container.is(e.target) && account_container.has(e.target).length === 0) 
        account_container.removeClass('collapse');
  });
  
  
  // NOTIFICATION
  $('.nav-header-icons .notif').click(function() {
    var $this = $(this);
    if ($this.find('.notif-menu').is(':visible')) {
      $this.removeClass('collapse');
      $('.notif-menu').hide();
      $('.nav-header-icons .notif > i').tooltip();
    } else {
      $this.addClass('collapse');
      $('.notif-menu').show();

      //$('.portal-access > .user-account').removeClass('collapse');
      //$('.account-menu').hide();
      $('.nav-header-icons .notif > i').tooltip('remove');
    }
  });
  
  // ACCOUNT 
  $('.nav-header-icons .account').click(function() {
    var $this = $(this);
    if ($this.find('.account-menu').is(':visible')) {
      $this.removeClass('collapse');
      $('.account-menu').hide();
      $('.nav-header-icons .account > i').tooltip();
    } else {
      $this.addClass('collapse');
      $('.account-menu').show();

      //$('.portal-access > .user-account').removeClass('collapse');
      //$('.account-menu').hide();
      $('.nav-header-icons .account > i').tooltip('remove');
    }
  });
})(jQuery);
