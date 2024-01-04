$(function () {
  // 备份
  $('.form').on('submit', function (e) {
    $.ajax({
      type: 'POST',
      async: false,
      data: $(this).serialize(),
      success: function (data) {
        let json = JSON.parse(data);
        showTip(json['content'], json['state']);
        e.preventDefault();
      }
    });
  });
});
