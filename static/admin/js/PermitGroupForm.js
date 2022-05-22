$(function () {
  let $permitManageCheckbox = $('.permit_manage input[type=checkbox]');
  let $permitDataCheckbox = $('.permit_data input[type=checkbox]');

  $('.all').on('click', function () {
    $permitManageCheckbox.each(function () {
      $(this).iCheck('check');
    });
  });
  $('.no').on('click', function () {
    $permitManageCheckbox.each(function () {
      $(this).iCheck('uncheck');
    });
  });
  $('.default').on('click', function () {
    $permitManageCheckbox.each(function () {
      $(this).iCheck('uncheck');
    });
    $('.permit_manage label.red input[type=checkbox]').each(function () {
      $(this).iCheck('check');
    });
  });

  $('.all2').on('click', function () {
    $permitDataCheckbox.each(function () {
      $(this).iCheck('check');
    });
  });
  $('.no2').on('click', function () {
    $permitDataCheckbox.each(function () {
      $(this).iCheck('uncheck');
    });
  });
  $('.default2').on('click', function () {
    $permitDataCheckbox.each(function () {
      $(this).iCheck('uncheck');
    });
    $('.permit_data label.red input[type=checkbox]').each(function () {
      $(this).iCheck('check');
    });
  });
});
