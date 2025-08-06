$(function () {
  let $list = $('.list');
  let moduleName = '商品';

  // 列表
  list(moduleName);

  // 排序
  sort();

  // 添加
  add('添加' + moduleName);

  // 修改
  update('修改' + moduleName);

  // 上下架
  $list.on('click', 'a.is_view', function () {
    let that = this;
    $.ajax({
      type: 'POST',
      url: CONFIG['IS_VIEW'],
      data: {
        id: $(that).parent().parent().find('input[name=id]').val()
      }
    }).then(function (data) {
      let json = JSON.parse(data);
      showTip(json['message'], json['status']);
      if (json['status'] === 1) {
        if ($(that).attr('attr-is-view') === '0') {
          $(that).parent().html('<span class="green">否</span> | <a href="javascript:" class="is_view" attr-is-view="1">上架</a>');
        } else if ($(that).attr('attr-is-view') === '1') {
          $(that).parent().html('<span class="red">是</span> | <a href="javascript:" class="is_view" attr-is-view="0">下架</a>');
        }
      }
    });
  });

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
      showTip(json['message'], json['status']);
      if (json['status'] === 1) {
        let $parent = $(that).parent();
        $list.find('td.is_default').html('<a href="javascript:" class="is_default">设为默认</a>');
        $parent.html('<span class="red">是</span>');
      }
    });
  });

  // 删除
  remove(moduleName);

  // 批量删除
  multiRemove(moduleName);

  // 搜索
  // 关键词
  searchKeyword();
  layui.use(['form'], function () {
    // 分类
    layui.form.on('select(product_sort_id)', function (data) {
      window.location.href = searchUrl('product_sort_id=' + data.value);
    });
  });
});

function listItem (item) {
  let control = [];
  if (isPermission('update')) control.push('<a href="javascript:" class="update">修改</a>');
  if (isPermission('delete')) control.push('<a href="javascript:" class="delete">删除</a>');
  return '<tr class="item' + item['id'] + '"><td' + (isPermission('delete') ? '' : ' class="none"') + '><div class="check-box"><label><input type="checkbox" name="id" value="' + item['id'] + '"></label></div></td><td style="color:' + item['color'] + ';">' + item['name'] + '</td><td>' + item['product_sort'] + '</td><td>' + item['price'] + '</td>' + (isPermission('sort') ? '<td><input type="text" name="sort" value="' + item['sort'] + '" class="text"></td>' : '') + (isPermission('isView') ? '<td>' + (parseInt(item['is_view']) === 0 ? '<span class="green">否</span> | <a href="javascript:" class="is_view" attr-is-view="1">上架</a>' : '<span class="red">是</span> | <a href="javascript:" class="is_view" attr-is-view="0">下架</a>') + '</td>' : '') + (isPermission('isDefault') ? '<td class="is_default">' + (item['is_default'] ? '<span class="red">是</span>' : '<a href="javascript:" class="is_default">设为默认</a>') + '</td>' : '') + '<td>' + item['create_time'] + '</td>' + (control.length ? '<td>' + control.join('/') + '</td>' : '') + '</tr>';
}
