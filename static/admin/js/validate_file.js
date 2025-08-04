$(function () {
  // 提交修改
  $('.form').on('submit', function (e) {
    $.ajax({
      type: 'POST',
      async: false,
      data: $(this).serialize(),
      success: function (data) {
        let json = JSON.parse(data);
        showTip(json['message'], json['status']);
        e.preventDefault();
      }
    });
  });
});
