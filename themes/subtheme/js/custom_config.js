$ = jQuery;

console.log("custom js working");
$('.action-unflag').on('click', function() {
  ct = parseInt($('.rcp-main.saved-tally').html());
  console.log(ct);
  $('.rcp-main.saved-tally').html(ct-1);
});

$('.action-flag').on('click', function() {
  ct = parseInt($('.rcp-main.saved-tally').html());
  console.log(ct);
  $('.rcp-main.saved-tally').html(ct+1);
});