$(function () {
  let moduleName = '订单';
  let $main = $('.main');
  let $tool = $main.find('.tool');
  let $list = $main.find('.list');

  // 列表
  list(moduleName);

  // 修改
  update('修改' + moduleName);

  // 删除
  remove(moduleName);

  // 批量删除
  multiRemove(moduleName);

  // 导出当前订单
  $tool.find('.output1').on('click', function () {
    download(CONFIG['OUTPUT'] + '?' + window.location.toString().split('?')[1], {type: 0, siwu: $tool.find('input[name=siwu]:checked').val()});
  });

  // 导出选定订单
  $tool.find('.output2').on('click', function () {
    download(CONFIG['OUTPUT'], {type: 1, ids: $tool.find('input[name=ids]').val(), siwu: $tool.find('input[name=siwu]:checked').val()});
  });

  // 搜索
  layui.use(['form', 'date'], function () {
    // 时间
    layui.date.render({
      elem: 'input[name=date1]'
    });
    layui.date.render({
      elem: 'input[name=date2]'
    });
    layui.date.render({
      elem: 'input[name=pay_date1]'
    });
    layui.date.render({
      elem: 'input[name=pay_date2]'
    });
    // 支付方式
    layui.form.on('select(payment_id)', function (data) {
      payment(data.value);
    });
  });
  // 数字调节器
  $('div.number').number({
    width: 86,
    top: 11,
    min: 0
  });
  // 支付方式
  let $alipayScene = $tool.find('.alipay_scene');
  let $wechatPayScene = $tool.find('.wechat_pay_scene');
  payment($tool.find('select[name=payment_id] option:selected').val());
  function payment (val) {
    switch (val) {
      case '2':
        $alipayScene.show();
        $wechatPayScene.hide();
        break;
      case '3':
        $alipayScene.hide();
        $wechatPayScene.show();
        break;
      default:
        $alipayScene.hide();
        $wechatPayScene.hide();
    }
  }

  // 详情
  $list.on('click', 'a.detail', function () {
    ajaxMessageLayer(CONFIG['DETAIL'], '订单详情', {id: $(this).parent().parent().find('input[name=id]').val()}, function (index) {
      layer.close(index);
    });
  });

  // 还原
  $list.on('click', 'a.recover', function () {
    commonAjax(CONFIG['RECOVER'], {id: $(this).parent().parent().find('input[name=id]').val()});
    $(this).parent().parent().remove();
  });

  // 批量还原
  $tool.find('.recover').on('click', function () {
    commonAjax(CONFIG['RECOVER'], {ids: $tool.find('input[name=ids]').val()});
  });
});

function listItem (item) {
  let control = [];
  if (isPermission('detail')) control.push('<a href="javascript:" class="detail">详情</a>');
  if (isPermission('update')) control.push('<a href="javascript:" class="update">修改</a>');
  if (CONFIG['CONTROLLER'] === 'OrderRecycle' && isPermission('recover')) control.push('<a href="javascript:" class="recover">还原</a>');
  if (isPermission('delete')) control.push('<a href="javascript:" class="delete">删除</a>');
  return '<tr class="item' + item['id'] + '"><td' + (isPermission('delete') ? '' : ' class="none"') + '><div class="check-box"><label><input type="checkbox" name="id" value="' + item['id'] + '"></label></div></td><td>' + item['order_id'] + '</td><td>' + item['manager'] + '</td><td>' + item['template'] + '</td><td>' + item['name'] + '</td><td>' + item['product'] + '</td><td>' + item['price'] + '元</td><td>' + item['count'] + '</td><td>' + item['total'] + '元</td><td>' + item['tel'] + '</td><td title="' + item['address'] + '">' + item['address_truncate'] + '</td><td>' + item['email'] + '</td><td>' + item['ip'] + '</td><td><a href="' + item['referrer'] + '" target="_blank" title="' + item['referrer'] + '">访问</a></td><td>' + item['date'] + '</td><td>' + item['payment'] + '</td><td>' + item['pay_id'] + '</td><td>' + item['pay_scene'] + '</td><td>' + item['pay_date'] + '</td><td>' + item['order_state'] + '</td><td>' + item['express'] + '</td>' + (control.length ? '<td>' + control.join('/') + '</td>' : '') + '</tr>';
}
