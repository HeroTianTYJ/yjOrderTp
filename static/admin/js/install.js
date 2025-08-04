$(function () {
  let $openid = $('input[name=openid]');
  let timer = setInterval(function () {
    $.ajax({
      type: 'POST',
      url: CONFIG['OPENID'],
      success: function (data) {
        let json = JSON.parse(data);
        if (json['status'] === 1 && json['message']) {
          $openid.val(json['message']);
          clearInterval(timer);
        }
      }
    });
  }, 3000);
});
