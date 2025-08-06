$(function () {
  let moduleName = '模板样式';

  // 列表
  list(moduleName);

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
});

function listItem (item) {
  let control = [];
  if (isPermission('update')) control.push('<a href="javascript:" class="update">修改</a>');
  if (isPermission('delete')) control.push('<a href="javascript:" class="delete">删除</a>');
  return '<tr class="item' + item['id'] + '"><td' + (isPermission('delete') ? '' : ' class="none"') + '><div class="check-box"><label><input type="checkbox" name="id" value="' + item['id'] + '"></label></div></td><td>' + item['id'] + '</td><td>' + item['keyword_bg_color'] + ' <span class="bg" style="background:' + item['bg_color'] + ';"></span></td><td>' + item['keyword_border_color'] + ' <span class="bg" style="background:' + item['border_color'] + '"></span></td><td>' + item['keyword_button_color'] + ' <span class="bg" style="background:' + item['button_color'] + '"></span></td><td>' + item['keyword_select_current_bg_color'] + ' <span class="bg" style="background:' + item['select_current_bg_color'] + ';"></span></td><td>' + item['create_time'] + '</td>' + (control.length ? '<td>' + control.join('/') + '</td>' : '') + '</tr>';
}
