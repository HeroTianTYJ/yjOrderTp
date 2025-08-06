$(function () {
  // 身份切换
  let $permit = $('.permit');
  level($('input[name=level_id]:checked').val());
  $('input[name=level_id]').on('ifChecked', function () {
    level($(this).val());
  });
  function level (val) {
    switch (val) {
      case '1':
        $permit.hide();
        break;
      case '2':
        $permit.show();
        break;
    }
  }
});
