$(function () {
  // 重置密码验证
  $('.form').Validform({
    tiptype: function (msg) {
      if (msg === '通过信息验证！' || msg === '正在提交数据…') return;
      showTip(msg, 0);
    },
    showAllError: false,
    dragonfly: true,
    tipSweep: true
  }).addRule([{
    ele: 'input[name=pass]',
    datatype: '*',
    nullmsg: '请填写新密码！'
  }, {
    ele: 'input[name=repass]',
    datatype: '*',
    recheck: 'pass',
    nullmsg: '请重复新密码！',
    errormsg: '两次输入的密码不相同！'
  }]);
});
