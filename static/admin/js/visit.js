$(function () {
  let moduleName = '访问统计';

  // 列表
  list(moduleName);

  // 导出并清空
  $('.tool .output').on('click', function () {
    confirmLayer(
      CONFIG['OUTPUT'],
      {},
      '<h3><span>？</span>确定要将访问统计导出到文件并清空当前访问数据吗？</h3><p>导出成功后，可到<a href="' + CONFIG['FILE_CONTROLLER'] + '">文件管理</a>模块中进行下载。</p>',
      function (json, layerIndex) {
        if (json['state'] === 1) {
          layer.close(layerIndex);
          setTimeout(function () {
            window.location.reload(true);
          }, 3000);
        }
      }
    );
  });

  // 更新js
  $('.right .js').on('click', function () {
    commonAjax(CONFIG['JS'], {}, false);
  });

  // 搜索
  // 关键词
  searchKeyword();
});

function listItem (item) {
  return '<tr class="item' + item['id'] + '"><td>' + item['ip'] + '</td><td><a href="' + item['url'] + '" target="_blank" title="' + item['url'] + '">' + item['truncate_url'] + '</a></td><td>' + item['count'] + '</td><td>' + item['date1'] + '</td><td>' + item['date2'] + '</td></tr>';
}
