/*
@Name：《昱杰后台UI框架》
@Author：风形火影
@Site：https://www.yjrj.cn
*/
$(function () {
  // 屏幕自适应
  screenAuto();
  setTimeout(screenAuto, 10);
  $(window).on({resize: screenAuto});

  // 左侧导航中的子导航的悬浮
  leftNavigator();

  // 右侧栏收起和展开
  rightSidebar();

  // 单选框和多选框
  iCheck();
});

// 左侧导航中的子导航的悬浮
function leftNavigator () {
  let $navigator = $('.left ul.navigator');
  $navigator.find('li').on({
    mouseover: function () {
      let $this = $(this);
      if ($this.find('.child').length) $navigator.find('.child').hide();
      $this.find('.child').show();
    },
    mouseout: function () {
      $navigator.find('li .child').hide();
      $navigator.find('li.active .child').show();
    }
  });
}

// 屏幕自适应
function screenAuto () {
  let $main = $('.main');
  let $window = $(window);
  let $navigator = $('.left ul.navigator');
  $main.css({minHeight: $window.height() - 69});
  $main.find('.list').height($window.height() - $('.top').height() - $main.find('.tool').height() - $main.find('.pagination').height() - 32);
  boxShadow();
  $navigator.css({minHeight: $main.height() + 15});
  $navigator.find('.child').css({minHeight: $navigator.height()});
  $('.right').css({minHeight: $navigator.height()});
}

// 列表阴影
function boxShadow () {
  setTimeout(function () {
    let $list = $('.list');
    let $tr = $list.find('tr');
    if ($list.scrollLeft() > 0) {
      $tr.find('th:first-child').addClass('box-shadow1');
      $tr.find('td:first-child').addClass('box-shadow1');
    } else {
      $tr.find('th:first-child').removeClass('box-shadow1');
      $tr.find('td:first-child').removeClass('box-shadow1');
    }
    if ($list.width() + $list.scrollLeft() >= $list.find('table').width()) {
      $tr.find('th:last-child').removeClass('box-shadow2');
      $tr.find('td:last-child').removeClass('box-shadow2');
    } else {
      $tr.find('th:last-child').addClass('box-shadow2');
      $tr.find('td:last-child').addClass('box-shadow2');
    }
  }, 200);
}

// 添加
function add (title, width = 800, succeed = function () {}) {
  $('.tool .add').on('click', function () {
    ajaxMessageLayer(CONFIG['ADD'], title, {}, function (index) {
      $.ajax({
        type: 'POST',
        url: CONFIG['ADD_DO'],
        data: $('form.add').serialize()
      }).then(function (data) {
        let json = JSON.parse(data);
        showTip(json['message'], json['status']);
        if (json['status'] === 1) {
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
      succeed();
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
        showTip(json['message'], json['status']);
        if (json['status'] === 1) {
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
function update (title, width = 800, succeed = function () {}) {
  let $list = $('.list');
  $list.on('click', 'a.update', function () {
    let id = $(this).parent().parent().find('input[name=id]').val();
    ajaxMessageLayer(CONFIG['UPDATE'], title, {id: id}, function (index) {
      $.ajax({
        type: 'POST',
        url: CONFIG['UPDATE_DO'],
        data: $('form.update').serialize() + '&id=' + id
      }).then(function (data) {
        let json = JSON.parse(data);
        showTip(json['message'], json['status']);
        if (json['status'] === 1) {
          layer.close(index);
          $list.find('.item' + id).html(listItem(json['data']).replace(/null/g, '').replace(/<tr class="[\w ]+">/, '').replace('</tr>', '').replace(/<li class="[\w ]+">/, '').replace('</li>', ''));
          boxShadow();
          iCheck();
          if (json['data']['reload']) {
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
      succeed();
    }, width);
  });
}

// 列表
function list (moduleName) {
  let $list = $('.list');
  paging(1);
  $('.pagination ul').createPage({
    total: CONFIG['TOTAL'],
    pageSize: CONFIG['PAGE_SIZE'],
    pageCount: Math.ceil(CONFIG['TOTAL'] / CONFIG['PAGE_SIZE']),
    paging: paging
  });
  function paging (page) {
    $.ajax({
      type: 'POST',
      url: CONFIG['LIST'],
      data: {
        page: page
      }
    }).then(function (data) {
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
    // 列表阴影
    boxShadow();
    $list.on({scroll: boxShadow});
    // 列表多选
    listCheck();
  }
}

// 列表多选
function listCheck () {
  let $main = $('.main');
  let $tool = $main.find('.tool');
  let $list = $main.find('.list');
  $list.on('ifChecked', 'input.all', function () {
    $list.find('input[name=id]').each(function () {
      let $this = $(this);
      $this.iCheck('check');
      $this.parent().parent().parent().parent().parent('tr').addClass('checked');
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
}

// 列表排序
function sort () {
  let $list = $('.list');
  $list.find('input.sort').on('click', function () {
    let obj = {};
    let sort = {};
    $.each($list.find('tbody tr'), function (index, element) {
      let sortVal = $(element).find('input[name=sort]').val();
      obj[sortVal] = $(element).html().replace(/<input type="text" name="sort" value="\d+" class="text">/, '<input type="text" name="sort" value="' + sortVal + '" class="text">');
      sort[$(element).find('input[name=id]').val()] = sortVal;
    });
    $.ajax({
      type: 'POST',
      url: CONFIG['SORT'],
      data: {
        sort: sort
      }
    }).then(function (data) {
      let json = JSON.parse(data);
      showTip(json['message'], json['status']);
      if (json['status'] === 1) {
        let html = '';
        $.each(obj, function (index, value) {
          html += '<tr>' + value + '</tr>';
        });
        $list.find('tbody').html(html);
      }
    });
  });
}

// 关键词搜索
function searchKeyword () {
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
}

// 右侧栏收起和展开
function rightSidebar () {
  let $main = $('.main');
  let $right = $('.right');
  $main.width($right.length === 0 ? CONFIG['FOLD_MAIN_WIDTH'] : CONFIG['EXPAND_MAIN_WIDTH']);
  $right.find('span.icon-fold').on('click', function () {
    $main.width(CONFIG['FOLD_MAIN_WIDTH']);
    $right.addClass('fold');
    $right.find('span.icon-expand').show('slow');
    boxShadow();
    screenAuto();
  });
  $right.find('span.icon-expand').on('click', function () {
    $main.width(CONFIG['EXPAND_MAIN_WIDTH']);
    $right.removeClass('fold');
    $right.find('span.icon-expand').hide('slow');
    boxShadow();
    screenAuto();
  });
}

// 删除
function remove (moduleName, recycle = false) {
  $('.list').on('click', 'a.delete', function () {
    let that = this;
    confirmLayer(
      CONFIG['DELETE'],
      {
        id: $(that).parent().parent().find('input[name=id]').val()
      },
      '<h3><span>？</span>确认要删除此' + moduleName + '吗？</h3><p>删除此' + moduleName + '之后，' + (recycle ? '可在' + moduleName + '回收站进行恢复' : '无法进行恢复，可以重新添加') + '。</p>',
      function (json, index) {
        if (json['status'] === 1) {
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
      CONFIG['DELETE'],
      {
        ids: $tool.find('input[name=ids]').val()
      },
      '<h3><span>？</span>确认要删除这' + $checked.length + '个' + moduleName + '吗？</h3><p>删除这' + $checked.length + '个' + moduleName + '之后，' + (recycle ? '可在' + moduleName + '回收站进行恢复' : '无法进行恢复，可以重新添加') + '。</p>',
      function (json, index) {
        if (json['status'] === 1) {
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

// tab切换
function tabSwitch (succeed = function () {}) {
  let $tabLi = $('.tab li');
  let $column = $('.form .column');
  $tabLi.on('click', function () {
    let $this = $(this);
    $tabLi.removeClass('active');
    $this.addClass('active');
    $column.addClass('none');
    $column.eq($this.index()).removeClass('none');
    screenAuto();
    succeed($this.index(), $tabLi.length);
  });
}
