$ = jQuery;

$( document ).ajaxComplete(function() {
  $('.like-icon .action-flag').on('click', function() {
    ct = parseInt($('.rcp-main.saved-tally').html());
    $('.rcp-main.saved-tally').html(ct+1);
  });
});

$( document ).ajaxComplete(function() {
  $('.like-icon .action-unflag').on('click', function() {
    ct = parseInt($('.rcp-main.saved-tally').html());
    $('.rcp-main.saved-tally').html(ct-1);
  });
});

$( document ).ajaxComplete(function() {
  $('.save-icon .action-flag').on('click', function() {
    ct = parseInt($('.rcp-main.saved-tally').html());
    $('.rcp-main.saved-tally').html(ct+1);
  });
});

$( document ).ajaxComplete(function() {
  $('.save-icon .action-unflag').on('click', function() {
    ct = parseInt($('.rcp-main.saved-tally').html());
    $('.rcp-main.saved-tally').html(ct-1);
  });
});