$(function () {
  let $list = $('.list');
  let moduleName = '模板';

  // 列表
  list(moduleName);

  // 添加
  add('添加' + moduleName);

  // 修改
  update('修改' + moduleName);

  // 设置默认
  $list.on('click', 'a.is_default', function () {
    let that = this;
    $.ajax({
      type: 'POST',
      url: CONFIG['IS_DEFAULT'],
      data: {
        id: $(that).parent().parent().find('input[name=id]').val()
      }
    }).then(function (data) {
      let json = JSON.parse(data);
      showTip(json['content'], json['state']);
      if (json['state'] === 1) {
        let $parent = $(that).parent();
        $list.find('td.is_default').html('<a href="javascript:" class="is_default">设为默认</a>');
        $parent.html('<span class="red">是</span>');
      }
    });
  });

  // 获取代码
  $list.on('click', 'a.code', function () {
    ajaxMessageLayer(CONFIG['CODE'], '获取代码', {id: $(this).parent().parent().find('input[name=id]').val()}, function (index) {
      layer.close(index);
    });
  });

  // 删除
  remove(moduleName);

  // 批量删除
  multiRemove(moduleName);

  // 搜索
  // 关键词
  searchKeyword();
});

function listItem (item) {
  let control = [];
  control.push('<a href="' + item['url'] + '" target="_blank">访问</a>');
  if (isPermission('code')) control.push('<a href="javascript:" class="code">调用代码</a>');
  if (isPermission('update')) control.push('<a href="javascript:" class="update">修改</a>');
  if (isPermission('delete')) control.push('<a href="javascript:" class="delete">删除</a>');
  return '<tr class="item' + item['id'] + '"><td' + (isPermission('delete') ? '' : ' class="none"') + '><div class="check-box"><label><input type="checkbox" name="id" value="' + item['id'] + '"></label></div></td><td>' + item['name'] + '</td><td>' + item['template'] + '</td><td>' + item['template_style_id'] + '号皮肤</td><td>' + item['is_show_search'] + '</td><td>' + item['is_show_send'] + '</td><td>' + item['is_captcha'] + '</td>' + (isPermission('isDefault') ? '<td class="is_default">' + (item['is_default'] ? '<span class="red">是</span>' : '<a href="javascript:" class="is_default">设为默认</a>') + '</td>' : '') + '<td>' + item['date'] + '</td>' + (control.length ? '<td>' + control.join('/') + '</td>' : '') + '</tr>';
}
