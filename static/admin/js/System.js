$(function () {
  // tab切换
  let $tabLi = $('.tab li');
  let $column = $('.form .column');
  $tabLi.on('click', function () {
    $tabLi.removeClass('active');
    $(this).addClass('active');
    $column.addClass('none');
    $column.eq($(this).index()).removeClass('none');
    screenAuto();

    if ($(this).index() === $tabLi.length - 1) {
      uploader.refresh();
    }
  });

  // 更新IP数据库
  let uploader = WebUploader.create({
    auto: true,
    server: ThinkPHP['UPLOAD'],
    pick: {
      id: '.qqwry_picker',
      label: '更新IP数据库',
      multiple: false
    },
    fileSingleSizeLimit: 20480000,
    accept: {
      extensions: 'dat',
      mimeTypes: '.dat'
    },
    compress: false,
    resize: false,
    duplicate: true
  });
  uploader.on('uploadSuccess', function (file, response) {
    $('.qqwry').html('更新成功，当前IP数据库更新日期为：' + response._raw);
  });
  uploader.on('error', uploadValidate);

  // 提交修改
  $('.form').on('submit', function (e) {
    $.ajax({
      type: 'POST',
      async: false,
      data: $(this).serialize(),
      success: function (data) {
        let json = JSON.parse(data);
        if (json.state === 0) {
          showTip(json.content, 0);
        } else if (json.state === 1) {
          if (json.content.url) {
            showTip(json.content.msg);
            setTimeout(function () {
              window.location.href = json.content.url;
            }, 3000);
          } else {
            showTip(json.content);
          }
        }
        e.preventDefault();
      }
    });
  });
});
