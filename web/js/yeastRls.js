$(document).ready(function() {
  $('#filterMore').click(function() {
    $('#filterMore').hide();
    $("#filterMoreDiv").fadeIn(500);
    return false;
  });

  $('.expandable').expander({
    slicePoint:       0,
    expandText:       '\u00BB',
    userCollapseText: '\u00AB',
    expandPrefix:     ''
  });
  
  $('#checkAll').click(function() {
    var checkState = $(this).attr('checked');
    $('#exportForm :checkbox').attr('checked', checkState);
  });

  $('.dataTable tr').mouseover(function() {
    $(this).addClass('highlight');
  });

  $('.dataTable tr').mouseout(function() {
    $(this).removeClass('highlight');
  });

});

