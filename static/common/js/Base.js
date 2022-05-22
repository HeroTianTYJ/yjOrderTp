$(function () {
  let $main = $('.main');
  let $tool = $main.find('.tool');
  let $list = $main.find('.list');
  let $window = $(window);
  let $left = $('.left');
  let $navigator = $left.find('ul.navigator');
  let $right = $('.right');

  // 屏幕自适应
  setTimeout(screenAuto, 10);
  $window.on({resize: screenAuto});

  // 左侧导航中的子导航的悬浮
  $navigator.find('li').on({
    mouseover: function () {
      if ($(this).find('.child').length) $navigator.find('.child').hide();
      $(this).find('.child').show();
    },
    mouseout: function () {
      $navigator.find('li .child').hide();
      $navigator.find('li.active .child').show();
    }
  });

  // 表格阴影
  boxShadow();
  $list.on({scroll: boxShadow});

  // 右侧栏收起和展开
  if ($right.length === 0) $main.width(ThinkPHP['FOLD_MAIN_WIDTH']);
  $right.find('span.icon-fold').on('click', function () {
    $main.width(ThinkPHP['FOLD_MAIN_WIDTH']);
    $right.addClass('fold');
    $right.find('span.icon-expand').show('slow');
    boxShadow();
    screenAuto();
  });
  $right.find('span.icon-expand').on('click', function () {
    $main.width(ThinkPHP['EXPAND_MAIN_WIDTH']);
    $right.removeClass('fold');
    $right.find('span.icon-expand').hide('slow');
    boxShadow();
    screenAuto();
  });

  // 单选和多选
  iCheck();

  // 多选
  $list.on('ifChecked', 'input.all', function () {
    $list.find('input[name=id]').each(function () {
      $(this).iCheck('check');
      $(this).parent().parent().parent().parent().parent('tr').addClass('checked');
    });
    checked();
  }).on('ifUnchecked', 'input.all', function () {
    $list.find('input[name=id]').each(function () {
      $(this).iCheck('uncheck');
    });
    checked();
    $(this).parent().parent().parent().parent().parent().removeClass('checked');
  }).on('ifChecked', 'input[name=id]', function () {
    check();
    checked();
    $(this).parent().parent().parent().parent().parent().addClass('checked');
  }).on('ifUnchecked', 'input[name=id]', function () {
    check();
    checked();
    $(this).parent().parent().parent().parent().parent().removeClass('checked');
  });
  function check () {
    let $all = $list.find('input.all');
    let $idChecked = $list.find('input[name=id]:checked');
    if ($idChecked.length === 0) {
      $all.iCheck('uncheck');
      $all.iCheck('determinate');
    } else if ($idChecked.length === $list.find('input[name=id]').length) {
      $all.iCheck('check');
    } else {
      $all.iCheck('indeterminate');
    }
  }
  function checked () {
    let ids = '';
    $list.find('input[name=id]:checked').each(function () {
      ids += $(this).val() + ',';
    });
    ids = ids.substring(0, ids.length - 1);
    $tool.find('input[name=ids]').val(ids);
    let $mustSelect = $tool.find('input.must_select');
    ids ? $mustSelect.removeClass('disabled').removeAttr('disabled') : $mustSelect.addClass('disabled').attr('disabled', '');
  }

  // 排序
  $list.find('input.sort').on('click', function () {
    let obj = {};
    let sort = {};
    $.each($list.find('tbody tr'), function (index, element) {
      let $sort = $(element).find('input[name=sort]');
      obj[$sort.val()] = $(element).html().replace(/<input type="text" name="sort" value="[\d]+" class="text">/, '<input type="text" name="sort" value="' + $sort.val() + '" class="text">');
      sort[$(element).find('input[name=id]').val()] = $sort.val();
    });
    $.ajax({
      type: 'POST',
      url: ThinkPHP['SORT'],
      data: {
        sort: sort
      }
    }).then(function (data) {
      let json = JSON.parse(data);
      showTip(json.content, json.state);
      if (json.state === 1) {
        let html = '';
        $.each(obj, function (index, value) {
          html += '<tr>' + value + '</tr>';
        });
        $list.find('tbody').html(html);
      }
    });
  });

  // 搜索
  // 关键词
  let $searchText = $('.search-text');
  let $keyword = $searchText.find('input[name=keyword]');
  $keyword.on({
    keyup: function (e) {
      if (e.key === 'Enter') keyword();
    }
  });
  $searchText.find('.icon-search').on({click: keyword});
  function keyword () {
    if ($keyword.val()) {
      window.location.href = searchUrl('keyword=' + $keyword.val());
    } else {
      $keyword.trigger('focus');
      showTip('请输入搜索关键词！', 0);
    }
  }
});

// 添加
function add (title, width = 800) {
  $('.tool .add').on('click', function () {
    ajaxMessageLayer(ThinkPHP['ADD'], title, {}, function (index) {
      $.ajax({
        type: 'POST',
        url: ThinkPHP['ADD'] + (ThinkPHP['ADD'].indexOf('?') > 0 ? '&' : '?') + 'action=do',
        data: $('form.add').serialize()
      }).then(function (data) {
        let json = JSON.parse(data);
        showTip(json.content, json.state);
        if (json.state === 1) {
          layer.close(index);
          setTimeout(function () {
            window.location.reload(true);
          }, 3000);
        }
      });
    }, function () {
      layui.use(['form'], function () {
        layui.form.render('select');
      });
      iCheck();
      $('.layui-layer-content').animate({scrollTop: 0});
    }, width);
  });
}

// 批量操作
function multi (element, title, url) {
  let $tool = $('.tool');
  $tool.find(element).on('click', function () {
    ajaxMessageLayer(url, title, {ids: $tool.find('input[name=ids]').val()}, function (index) {
      $.ajax({
        type: 'POST',
        url: url + (url.indexOf('?') > 0 ? '&' : '?') + 'action=do',
        data: $('form' + element).serialize()
      }).then(function (data) {
        let json = JSON.parse(data);
        showTip(json.content, json.state);
        if (json.state === 1) {
          layer.close(index);
          setTimeout(function () {
            window.location.reload(true);
          }, 3000);
        }
      });
    }, function () {
      layui.use(['form'], function () {
        layui.form.render('select');
      });
      iCheck();
      $('.layui-layer-content').animate({scrollTop: 0});
    }, 800, element.substring(1));
  });
}

// 修改
function update (title, width = 800) {
  let $list = $('.list');
  $list.on('click', 'a.update', function () {
    let id = $(this).parent().parent().find('input[name=id]').val();
    ajaxMessageLayer(ThinkPHP['UPDATE'], title, {id: id}, function (index) {
      $.ajax({
        type: 'POST',
        url: ThinkPHP['UPDATE'] + (ThinkPHP['UPDATE'].indexOf('?') > 0 ? '&' : '?') + 'action=do',
        data: $('form.update').serialize() + '&id=' + id
      }).then(function (data) {
        let json = JSON.parse(data);
        if (json.state === 0) {
          showTip(json.content, 0);
        } else if (json.state === 1) {
          showTip(json.content.msg);
          layer.close(index);
          $list.find('.item' + id).html(listItem(json.content.data).replace(/<tr class="[\w ]+">/, '').replace('</tr>', '').replace(/<li class="[\w ]+">/, '').replace('</li>', ''));
          boxShadow();
          iCheck();
          if (json.content.reload) {
            setTimeout(function () {
              window.location.reload();
            }, 3000);
          }
        }
      });
    }, function () {
      layui.use(['form'], function () {
        layui.form.render('select');
      });
      iCheck();
      $('.layui-layer-content').animate({scrollTop: 0});
    }, width);
  });
}

// 列表
function list (moduleName) {
  paging(1);
  $('.pagination ul').createPage({
    total: ThinkPHP['TOTAL'],
    pageSize: ThinkPHP['PAGE_SIZE'],
    pageCount: Math.ceil(ThinkPHP['TOTAL'] / ThinkPHP['PAGE_SIZE']),
    paging: paging
  });
  function paging (page) {
    $.ajax({
      type: 'POST',
      url: window.location,
      data: {
        page: page
      }
    }).then(function (data) {
      let $list = $('.list');
      if (data) {
        let html = '';
        $.each(JSON.parse(data), function (index, value) {
          html += listItem(value);
        });
        $list.find('.items').html(html);
        $list.animate({scrollTop: 0, scrollLeft: 0});

        boxShadow();
        iCheck();

        let width = 0;
        $.each($list.find('th'), function (index, element) {
          if ($(element).attr('style')) width += ($(element).attr('style').match(/\d+/)[0] * 1);
        });
        $list.find('table').width(width + 2);
      } else {
        $list.find('p.nothing').html((window.location.toString().indexOf('?') > 0 ? '没有找到您搜索的' : '暂无') + moduleName);
      }
    });
  }
}

// 删除
function remove (moduleName, recycle = false) {
  $('.list').on('click', 'a.delete', function () {
    let that = this;
    confirmLayer(
      ThinkPHP['DELETE'],
      {
        id: $(that).parent().parent().find('input[name=id]').val()
      },
      '<h3><span>？</span>确认要删除此' + moduleName + '吗？</h3><p>删除此' + moduleName + '之后，' + (recycle ? '可在' + moduleName + '回收站进行恢复' : '无法进行恢复，可以重新添加') + '。</p>',
      function (json, index) {
        if (json.state === 1) {
          $(that).parent().parent().remove();
          layer.close(index);
          setTimeout(function () {
            window.location.reload(true);
          }, 3000);
        }
      }
    );
  });
}

// 批量删除
function multiRemove (moduleName, recycle = false) {
  let $tool = $('.tool');
  $tool.find('.delete').on('click', function () {
    let $checked = $('.list tr.checked');
    $checked = $checked.length ? $checked : $('.list li.checked');
    confirmLayer(
      ThinkPHP['DELETE'],
      {
        ids: $tool.find('input[name=ids]').val()
      },
      '<h3><span>？</span>确认要删除这' + $checked.length + '个' + moduleName + '吗？</h3><p>删除这' + $checked.length + '个' + moduleName + '之后，' + (recycle ? '可在' + moduleName + '回收站进行恢复' : '无法进行恢复，可以重新添加') + '。</p>',
      function (json, index) {
        if (json.state === 1) {
          $checked.remove();
          layer.close(index);
          setTimeout(function () {
            window.location.reload(true);
          }, 3000);
        }
      }
    );
  });
}
