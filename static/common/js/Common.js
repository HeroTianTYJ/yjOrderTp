// 单选和多选
function iCheck () {
  if ($().iCheck !== undefined) {
    $('.check-box input,.radio-box input').iCheck({
      checkboxClass: 'icheckbox-blue',
      radioClass: 'iradio-blue'
    });
  }
}

// 屏幕自适应
function screenAuto () {
  let $main = $('.main');
  let $window = $(window);
  let $navigator = $('.left .navigator');
  $main.css({minHeight: $window.height() - 69});
  $main.find('.right').css({minHeight: $window.height() - 54});
  $navigator.height($main.height() + 15);
  $navigator.find('.child').height($main.height() - 1);
  $main.find('.list').height($window.height() - $('.top').height() - $main.find('.tool').height() - $main.find('.pagination').height() - 32);
  $('.right').css({minHeight: $window.height() - 54});
  boxShadow();
}

// 表格阴影
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

// 信息提示
function showTip (content, state = 1) {
  if (state === 0) {
    state = 'failed';
  } else if (state === 1) {
    state = 'succeed';
  }
  let $tip = $('body > div.tip');
  $tip.show();
  $tip.find('div').css({display: 'inline'});
  $tip.find('span.iconfont').addClass('icon-tip-' + state);
  $tip.find('span.content').html(content);
  setTimeout(function () {
    $tip.hide();
    $tip.find('div').hide();
    $tip.find('span.iconfont').removeClass('icon-tip-' + state);
    $tip.find('span.content').html('');
  }, 3000);
}

// 搜索链接
function searchUrl (param) {
  let params = {};
  let url;
  if (window.location.toString().indexOf('?') > 0) {
    let temp = window.location.toString().split('?');
    let temp2 = temp[1].split('&');
    for (let i = 0; i < temp2.length; i++) {
      let temp3 = temp2[i].split('=');
      params[temp3[0]] = temp3[1];
    }
    let temp4 = param.split('=');
    params[temp4[0]] = temp4[1];
    let paramStr = '';
    for (let key in params) {
      paramStr += key + '=' + params[key] + '&';
    }
    url = temp[0] + '?' + paramStr.substr(0, paramStr.length - 1);
  } else {
    url = window.location.toString() + '?' + param;
  }
  return url;
}

// 判断是否是json
function isJSON (data) {
  if (typeof data === 'string') {
    try {
      JSON.parse(data);
      return true;
    } catch (e) {
      return false;
    }
  }
}

// 文件下载
function download (url, data = {}) {
  $.ajax({
    type: 'POST',
    url: url,
    data: data
  }).then(function (data) {
    let json = JSON.parse(data);
    let $a = $('<a href="' + URL.createObjectURL(new Blob(['\ufeff' + json.file], {type: 'text/' + json.extension})) + '" download="' + json.filename + '"></a>').appendTo('body');
    $a[0].click();
    $a.remove();
  });
}

// 通用ajax
function commonAjax (url, data = {}, reload = true) {
  $.ajax({
    type: 'POST',
    url: url,
    data: data
  }).then(function (data) {
    let json = JSON.parse(data);
    showTip(json.content, json.state);
    if (json.state === 1 && reload) {
      setTimeout(function () {
        window.location.reload(true);
      }, 3000);
    }
  });
}

// 确认框
function confirmLayer (url, data = {}, message = '', callback = function () {}) {
  layer.confirm(
    '<div class="confirm">' + message + '</div>',
    {
      title: false,
      closable: false,
      area: '500px',
      resizable: false
    },
    function (index) {
      $.ajax({
        type: 'POST',
        url: url,
        data: data
      }).then(function (data) {
        let json = JSON.parse(data);
        showTip(json.content, json.state);
        callback(json, index);
      });
    }
  );
}

// ajax消息框
function ajaxMessageLayer (url, title = '', data = {}, callback = function () {}, succeed = function () {}, width = 800, id = '', headerHeight = 0) {
  $.ajax({
    type: 'POST',
    url: url,
    data: data
  }).then(function (data) {
    if (isJSON(data)) {
      let json = JSON.parse(data);
      showTip(json.content, json.state);
    } else {
      layer.confirm(
        data,
        {
          title: title,
          area: width + 'px',
          resizable: false,
          id: id,
          headerHeight: headerHeight
        },
        function (index) {
          callback(index);
        }
      );
      succeed();
    }
  });
}

// 验证权限
function isPermission (action = '', controller = '') {
  if (ThinkPHP['SESSION_LEVEL'] === 1) return true;
  if (ThinkPHP['PERMIT_MANAGE'][controller || ThinkPHP['CONTROLLER']] === undefined) return false;
  return $.inArray(ThinkPHP['PERMIT_MANAGE'][controller || ThinkPHP['CONTROLLER']][action || ThinkPHP['ACTION']], ThinkPHP['SESSION_PERMIT_MANAGE']) >= 0;
}

// 生成随机字符串
function getKey (length) {
  let key = '';
  let charset = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
  for (let i = 0; i < length; i++) {
    key += charset.charAt(Math.ceil(Math.random() * 1000 % charset.length));
  }
  return key;
}

// 生成随机手机号
function getTel (count = 10) {
  let prefix = [130, 131, 132, 133, 134, 135, 136, 137, 138, 139, 144, 147, 150, 151, 152, 153, 155, 156, 157, 158, 159, 176, 177, 178, 180, 181, 182, 183, 184, 185, 186, 187, 188, 189];
  let result = [];
  for (let i = 0; i < count; i++) {
    let tel = prefix[getRandom(0, prefix.length - 1)] + '' + getRandom(10000000, 99999999);
    if (result.indexOf(tel) < 0) result.push(tel);
  }
  return result;
}

// 生成随机数
function getRandom (min, max) {
  return Math.floor(Math.random() * (min - max + 1)) + max;
}

// 获取耗时
function getTakeTime (time) {
  let day = Math.floor(time / 86400);
  let hour = Math.floor((time - day * 86400) / 3600);
  let minute = Math.floor((time - day * 86400 - hour * 3600) / 60);
  let second = Math.floor(time % 60);
  let takeTime = '';
  if (day > 0) {
    takeTime += (day < 10 ? '0' : '') + day + '天';
  }
  if (hour > 0) {
    takeTime += (hour < 10 ? '0' : '') + hour + '小时';
  }
  if (minute > 0) {
    takeTime += (minute < 10 ? '0' : '') + minute + '分钟';
  }
  if (second > 0) {
    takeTime += (second < 10 ? '0' : '') + second + '秒';
  }
  return takeTime;
}
