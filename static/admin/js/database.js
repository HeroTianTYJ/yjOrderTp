$(function () {
  let moduleName = '数据表';
  let $tool = $('.tool');

  // 列表
  list(moduleName);

  // 优化表
  $tool.find('.optimize').on('click', function () {
    commonAjax(CONFIG['OPTIMIZE']);
  });

  // 修复AutoIncrement
  $tool.find('.repair_auto_increment').on('click', function () {
    commonAjax(CONFIG['REPAIR_AUTO_INCREMENT']);
  });

  // 更新表缓存
  $tool.find('.schema').on('click', function () {
    commonAjax(CONFIG['SCHEMA'], {}, false);
  });

  // 搜索
  // 关键词
  searchKeyword();
});

function listItem (item) {
  return '<tr class="item' + item['id'] + '"><td>' + item['Name'] + '</td><td>' + item['Rows'] + '</td><td>' + item['Auto_increment'] + '</td><td>' + item['Size'] + '</td><td>' + item['Data_free'] + ' 字节</td></tr>';
}
