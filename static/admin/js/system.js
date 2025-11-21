$(function () {
  // tab切换
  tabSwitch(function (index, length) {
    if (index === length - 1) {
      uploader.refresh();
      uploader2.refresh();
    }
  });

  // 更新IP数据库
  let uploadConfig = {
    auto: true,
    server: CONFIG['UPLOAD_SERVER'],
    fileSingleSizeLimit: 51200000,
    accept: {
      extensions: 'czdb',
      mimeTypes: '.czdb'
    },
    compress: false,
    resize: false,
    duplicate: true
  };
  uploadConfig.pick = {
    id: '.czdb_v4_picker',
    label: '更新',
    multiple: false
  };
  uploadConfig.formData = {czdb_version: 0};
  let uploader = WebUploader.create(uploadConfig);
  uploader.on('uploadSuccess', function (file, response) {
    let json = JSON.parse(response._raw);
    json['status'] === 1 ? $('.czdb_v4_version').html(json['message']) : showTip(json['message'], 0);
  });
  uploader.on('error', uploadValidate);
  uploadConfig.pick = {
    id: '.czdb_v6_picker',
    label: '更新',
    multiple: false
  };
  uploadConfig.formData = {czdb_version: 1};
  let uploader2 = WebUploader.create(uploadConfig);
  uploader2.on('uploadSuccess', function (file, response) {
    let json = JSON.parse(response._raw);
    json['status'] === 1 ? $('.czdb_v6_version').html(json['message']) : showTip(json['message'], 0);
  });
  uploader2.on('error', uploadValidate);

  // 提交修改
  $('.form').on('submit', function (e) {
    $.ajax({
      type: 'POST',
      async: false,
      data: $(this).serialize(),
      success: function (data) {
        let json = JSON.parse(data);
        showTip(json['message'], json['status']);
        if (json['status'] === 1 && json['data']['url']) {
          setTimeout(function () {
            window.location.href = json['data']['url'];
          }, 3000);
        }
        e.preventDefault();
      }
    });
  });
});
