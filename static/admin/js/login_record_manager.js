$(function () {
  let moduleName = '登录记录';

  // 列表
  list(moduleName);

  // 搜索
  layui.use(['form'], function () {
    // 管理员
    layui.form.on('select(manager_id)', function (data) {
      window.location.href = searchUrl('manager_id=' + data.value);
    });
  });

  // 导出并清空
  $('.tool .output').on('click', function () {
    confirmLayer(
      CONFIG['OUTPUT'],
      {},
      '<h3><span>？</span>确定要将登录记录导出到文件并清空当前登录数据吗？</h3><p>导出成功后，可到<a href="' + CONFIG['FILE_CONTROLLER'] + '">文件管理</a>模块中进行下载。</p>',
      function (json, layerIndex) {
        if (json['status'] === 1) {
          layer.close(layerIndex);
          setTimeout(function () {
            window.location.reload(true);
          }, 3000);
        }
      }
    );
  });

  // 搜索
  // 关键词
  searchKeyword();
});

function listItem (item) {
  return '<tr class="item' + item['id'] + '"><td>' + item['manager'] + '</td><td>' + item['ip'] + '</td><td>' + item['create_time'] + '</td></tr>';
}
