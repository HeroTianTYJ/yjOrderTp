$(function () {
  let moduleName = '文件';
  let $tool = $('.tool');

  // 列表
  list(moduleName);

  // 合并文件
  $tool.find('.merge').on('click', function () {
    ajaxMessageLayer(CONFIG['MERGE'], '合并文件', {}, function (index) {
      $.ajax({
        type: 'POST',
        url: CONFIG['MERGE'] + (CONFIG['MERGE'].indexOf('?') > 0 ? '&' : '?') + 'action=do',
        data: $('form.merge').serialize()
      }).then(function (data) {
        let json = JSON.parse(data);
        showTip(json['message'], json['status']);
        if (json['status'] === 1) {
          layer.close(index);
          setTimeout(function () {
            window.location.reload(true);
          }, 3000);
        }
      });
    }, function () {
      layui.use(['form'], function () {
        layui.form.render('select');
      });
      iCheck();
      $('.layui-layer-content').animate({scrollTop: 0});
    });
  });

  // 删除
  remove(moduleName);

  // 批量删除
  multiRemove(moduleName);

  // 打包
  $tool.on('click', '.zip1', function () {
    commonAjax(CONFIG['ZIP'], {ids: $tool.find('input[name=ids]').val()});
  });
  $tool.on('click', '.zip2', function () {
    commonAjax(CONFIG['ZIP'], {ids: $tool.find('input[name=ids]').val(), is_delete: 1});
  });

  // 搜索
  // 关键词
  searchKeyword();
  layui.use(['form'], function () {
    // 类型
    layui.form.on('select(type)', function (data) {
      window.location.href = searchUrl('type=' + data.value);
    });
  });
});

function listItem (item) {
  let control = [];
  if (isPermission('download')) control.push('<a href="' + CONFIG['DOWNLOAD'] + '?id=' + item['id'] + '">下载</a>');
  if (isPermission('delete')) control.push('<a href="javascript:" class="delete">删除</a>');
  return '<tr class="item' + item['id'] + '"><td' + (isPermission('delete') || isPermission('output') ? '' : ' class="none"') + '><div class="check-box"><label><input type="checkbox" name="id" value="' + item['id'] + '"></label></div></td><td>' + item['name'] + '</td><td>' + item['type'] + '</td><td>' + item['size'] + '</td>' + (control.length ? '<td>' + control.join('/') + '</td>' : '') + '</tr>';
}
