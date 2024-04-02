$(function () {
  let $openid = $('input[name=openid]');
  let timer = setInterval(function () {
    $.ajax({
      type: 'POST',
      url: CONFIG['OPENID'],
      success: function (data) {
        let json = JSON.parse(data);
        if (json['state'] === 1 && json['content']) {
          $openid.val(json['content']);
          clearInterval(timer);
        }
      }
    });
  }, 3000);
});
