$(function () {
  let $window = $(window);
  let $login = $('.login');

  // 移动端内容区域上下居中
  setTimeout(screenAuto, 10);
  $window.on({resize: screenAuto});
  function screenAuto () {
    $login.css({marginTop: $window.width() <= 720 ? ($window.height() - $login.height() - 80) / 2 : 0});
  }

  // 登录
  $login.Validform({
    tiptype: function (msg) {
      tip(msg);
    },
    showAllError: false,
    dragonfly: true,
    tipSweep: true,
    ajaxPost: true,
    callback: function (data) {
      let json = JSON.parse(data);
      if (json['status'] === 0) {
        tip(json['message']);
      } else if (json['status'] === 1) {
        window.location.reload();
      } else if (json['status'] === 2) {
        window.location.href = json['message'];
      }
    }
  }).addRule([{
    ele: 'input[name=name]',
    datatype: /^[\w\W]{1,20}$/,
    nullmsg: '请填写账号！',
    errormsg: '账号不得大于20位！'
  }, {
    ele: 'input[name=pass]',
    datatype: '*',
    nullmsg: '请填写密码！'
  }]);

  function tip (tip) {
    if (tip === '通过信息验证！' || tip === '正在提交数据…') return;
    let $tip = $('.tip');
    $tip.html(tip);
    setTimeout(function () {
      $tip.html('');
    }, 3000);
  }
});
