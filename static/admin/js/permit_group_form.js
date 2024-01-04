$(function () {
  // 管理权限勾选
  let $permitManageCheckbox = $('.permit_manage input[type=checkbox]');
  let $permitManageRedCheckbox = $('.permit_manage .red input[type=checkbox]');
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
    $permitManageRedCheckbox.each(function () {
      $(this).iCheck('check');
    });
  });

  // 数据权限勾选
  let $permitDataCheckbox = $('.permit_data input[type=checkbox]');
  let $permitDataRedCheckbox = $('.permit_data .red input[type=checkbox]');
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
    $permitDataRedCheckbox.each(function () {
      $(this).iCheck('check');
    });
  });
});
