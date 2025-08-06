$(function () {
  let $form = $('.form');
  if ($form.length) {
    // 提交修改
    $form.on('submit', function (e) {
      $.ajax({
        type: 'POST',
        async: false,
        data: $(this).serialize(),
        success: function (data) {
          let json = JSON.parse(data);
          showTip(json['message'], json['status']);
          if (json['status'] === 1) {
            if ($('input[name=wechat_open_id]:checked').val() === '1') {
              $form.find('.wechat_yes').hide();
              $form.find('.wechat_no').show();
            }
            if ($('input[name=qq_open_id]:checked').val() === '1') {
              $form.find('.qq_yes').hide();
              $form.find('.qq_no').show();
            }
          }
          e.preventDefault();
        }
      });
    });
  } else {
    let moduleName = '登录记录';
    // 列表
    list(moduleName);

    // 搜索
    // 关键词
    searchKeyword();
  }
});

function listItem (item) {
  return '<tr class="item' + item['id'] + '"><td>' + item['ip'] + '</td><td>' + item['create_time'] + '</td></tr>';
}
