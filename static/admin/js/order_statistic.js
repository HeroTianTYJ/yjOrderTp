$(function () {
  let moduleName = '订单统计';
  let $tool = $('.tool');

  // 列表
  list(moduleName);

  // 导出统计结果
  $tool.find('.output').on('click', function () {
    download(CONFIG['OUTPUT'], {type: CONFIG['TYPE']});
  });

  // 搜索
  layui.use(['form', 'date'], function () {
    // 下单时间
    layui.date.render({
      elem: 'input[name=date1]'
    });
    layui.date.render({
      elem: 'input[name=date2]'
    });
    // 支付时间
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
});

function listItem (item) {
  if (CONFIG['TYPE'] === 'index') {
    return '<tr class="item' + item['id'] + '"><td>' + item['time'] + '</td><td>' + item['data']['count1'] + '</td><td>' + item['data']['count2'] + '</td><td>' + item['data']['count3'] + '</td><td>' + item['data']['count4'] + '</td><td>' + item['data']['count5'] + '</td><td>' + item['data']['count6'] + '</td><td>' + item['data']['sum1'] + '</td><td>' + item['data']['sum2'] + '</td><td>' + item['data']['sum3'] + '</td><td>' + item['data']['sum4'] + '</td><td>' + item['data']['sum5'] + '</td><td>' + item['data']['sum6'] + '</td><td>' + item['data']['count7'] + '</td><td>' + item['data']['count8'] + '</td><td>' + item['data']['sum7'] + '</td><td>' + item['data']['sum8'] + '</td></tr>';
  } else {
    return '<tr class="item' + item['id'] + (item['time'] === '合计' ? ' footer' : '') + '"><td>' + item['time'] + '</td><td>' + item['count1'] + '</td><td>' + item['count2'] + '</td><td>' + item['count3'] + '</td><td>' + item['count4'] + '</td><td>' + item['count5'] + '</td><td>' + item['count6'] + '</td><td>' + item['sum1'] + '</td><td>' + item['sum2'] + '</td><td>' + item['sum3'] + '</td><td>' + item['sum4'] + '</td><td>' + item['sum5'] + '</td><td>' + item['sum6'] + '</td><td>' + item['count7'] + '</td><td>' + item['count8'] + '</td><td>' + item['sum7'] + '</td><td>' + item['sum8'] + '</td></tr>';
  }
}
