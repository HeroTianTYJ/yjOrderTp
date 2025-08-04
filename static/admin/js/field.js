$(function () {
  let moduleName = '下单字段';

  // 列表
  list(moduleName);

  // 设置/取消默认
  $('.list').on('click', 'a.is_default', function () {
    let that = this;
    $.ajax({
      type: 'POST',
      url: CONFIG['IS_DEFAULT'],
      data: {
        id: $(that).parent().parent().find('input[name=id]').val()
      }
    }).then(function (data) {
      let json = JSON.parse(data);
      showTip(json['message'], json['status']);
      if (json['status'] === 1) {
        let isDefault1 = '<span class="red">是</span> | <a href="javascript:" class="is_default">取消默认</a>';
        let isDefault2 = '<span class="green">否</span> | <a href="javascript:" class="is_default">设置默认</a>';
        $(that).parent().html($(that).parent().html() === isDefault1 ? isDefault2 : isDefault1);
      }
    });
  });

  // 搜索
  // 关键词
  searchKeyword();
});

function listItem (item) {
  return '<tr class="item' + item['id'] + '"><td class="none"><div class="check-box"><label><input type="checkbox" name="id" value="' + item['id'] + '"></label></div></td><td>' + item['name'] + '</td>' + (isPermission('isDefault') ? '<td class="is_default">' + (item['is_default'] ? '<span class="red">是</span> | <a href="javascript:" class="is_default">取消默认</a>' : '<span class="green">否</span> | <a href="javascript:" class="is_default">设为默认</a>') + '</td>' : '') + '</tr>';
}
