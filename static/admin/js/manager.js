$(function () {
  let $list = $('.list');
  let moduleName = '管理员';

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

  // 解绑微信
  $list.on('click', 'a.wechat_open_id', function () {
    let that = this;
    confirmLayer(
      CONFIG['WECHAT_OPEN_ID'],
      {id: $(that).parent().parent().find('input[name=id]').val()},
      '<h3><span>？</span>确定要为此用户解绑微信吗？</h3><p>解绑后，此用户需要重新绑定微信。</p>',
      function (json, layerIndex) {
        if (json['status'] === 1) {
          layer.close(layerIndex);
          $(that).parent().html('<span class="green">否</span>');
        }
      }
    );
  });

  // 解绑QQ
  $list.on('click', 'a.qq_open_id', function () {
    let that = this;
    confirmLayer(
      CONFIG['QQ_OPEN_ID'],
      {id: $(that).parent().parent().find('input[name=id]').val()},
      '<h3><span>？</span>确定要为此用户解绑QQ吗？</h3><p>解绑后，此用户需要重新绑定QQ。</p>',
      function (json, layerIndex) {
        if (json['status'] === 1) {
          layer.close(layerIndex);
          $(that).parent().html('<span class="green">否</span>');
        }
      }
    );
  });

  // 激活/取消激活
  $list.on('click', 'a.is_activation', function () {
    let that = this;
    $.ajax({
      type: 'POST',
      url: CONFIG['IS_ACTIVATION'],
      data: {
        id: $(that).parent().parent().find('input[name=id]').val()
      }
    }).then(function (data) {
      let json = JSON.parse(data);
      showTip(json['message'], json['status']);
      if (json['status'] === 1) {
        let isActivation1 = '<span class="red">是</span> | <a href="javascript:" class="is_activation">取消激活</a>';
        let isActivation2 = '<span class="green">否</span> | <a href="javascript:" class="is_activation">帮他激活</a>';
        $(that).parent().html($(that).parent().html() === isActivation1 ? isActivation2 : isActivation1);
      }
    });
  });

  // 搜索
  // 关键词
  searchKeyword();
  layui.use(['form', 'date'], function () {
    // 身份
    layui.form.on('select(level)', function (data) {
      window.location.href = searchUrl('level=' + data.value);
    });
    // 权限组
    layui.form.on('select(permit_group_id)', function (data) {
      window.location.href = searchUrl('permit_group_id=' + data.value);
    });
    // 订单权限
    layui.form.on('select(order_permit)', function (data) {
      window.location.href = searchUrl('order_permit=' + data.value);
    });
    // 微信绑定
    layui.form.on('select(wechat)', function (data) {
      window.location.href = searchUrl('wechat=' + data.value);
    });
    // QQ绑定
    layui.form.on('select(qq)', function (data) {
      window.location.href = searchUrl('qq=' + data.value);
    });
    // 是否激活
    layui.form.on('select(is_activation)', function (data) {
      window.location.href = searchUrl('is_activation=' + data.value);
    });
    // 注册时间
    layui.date.render({
      elem: 'input[name=date1]',
      done: function (value) {
        window.location.href = searchUrl('date1=' + value);
      }
    });
    layui.date.render({
      elem: 'input[name=date2]',
      done: function (value) {
        window.location.href = searchUrl('date2=' + value);
      }
    });
  });
});

function listItem (item) {
  let control = [];
  if (isPermission('update')) control.push('<a href="javascript:" class="update">修改</a>');
  if (isPermission('delete')) control.push('<a href="javascript:" class="delete">删除</a>');
  return '<tr class="item' + item['id'] + '"><td' + (isPermission('delete') ? '' : ' class="none"') + '><div class="check-box"><label><input type="checkbox" name="id" value="' + item['id'] + '"></label></div></td><td>' + item['name'] + '</td><td>' + item['level_name'] + '</td><td>' + item['permit_group'] + '</td><td>' + item['order_permit'] + '</td><td>' + item['date'] + '</td>' + (isPermission('wechatOpenId') ? '<td>' + (item['wechat_open_id'] || item['wechat_union_id'] ? '<span class="red">是</span> | <a href="javascript:" class="wechat_open_id">解绑</a>' : '<span class="green">否</span>') + '</td>' : '') + (isPermission('qqOpenId') ? '<td>' + (item['qq_open_id'] ? '<span class="red">是</span> | <a href="javascript:" class="qq_open_id">解绑</a>' : '<span class="green">否</span>') + '</td>' : '') + (isPermission('isActivation') ? '<td>' + (item['is_activation'] ? '<span class="red">是</span> | <a href="javascript:" class="is_activation">取消激活</a>' : '<span class="green">否</span> | <a href="javascript:" class="is_activation">帮他激活</a>') + '</td>' : '') + (control.length ? '<td>' + control.join('/') + '</td>' : '') + '</tr>';
}
