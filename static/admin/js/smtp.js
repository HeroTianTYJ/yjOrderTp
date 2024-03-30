$(function () {
  let moduleName = 'SMTP服务器';

  // 列表
  list(moduleName);

  if (CONFIG['TYPE'] === 'index') {
    // 添加
    add('添加' + moduleName);

    // 修改
    update('修改' + moduleName);

    // 删除
    remove(moduleName);

    // 批量删除
    multiRemove(moduleName);

    // 搜索
    // 关键词
    searchKeyword();
  }
});

function listItem (item) {
  if (CONFIG['TYPE'] === 'index') {
    let control = [];
    if (isPermission('update')) control.push('<a href="javascript:" class="update">修改</a>');
    if (isPermission('delete')) control.push('<a href="javascript:" class="delete">删除</a>');
    return '<tr class="item' + item['id'] + '"><td' + (isPermission('delete') ? '' : ' class="none"') + '><div class="check-box"><label><input type="checkbox" name="id" value="' + item['id'] + '"></label></div></td><td>' + item['smtp'] + '</td><td>' + item['port'] + '</td><td>' + item['email'] + '</td><td>' + item['from_name'] + '</td>' + (control.length ? '<td>' + control.join('/') + '</td>' : '') + '</tr>';
  } else {
    return '<tr class="item' + item['id'] + (item['current'] ? ' red' : '') + '">' + '<td>' + item['hour'] + '</td><td>' + item['smtp'] + '</td><td>' + item['port'] + '</td><td>' + item['email'] + '</td><td>' + item['from_name'] + '</td></tr>';
  }
}
