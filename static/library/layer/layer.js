/**
 @Name：layer v3.1.1 Web弹层组件
 @Author：贤心
 @Site：http://layer.layui.com
 @License：MIT
*/

(function (window) {
  'use strict';

  let $;
  let $window;
  let ready = {};
  let layer = {
    index: 0,
    confirm: function (content, options, yes, cancel, close) {
      return layer.open($.extend({content: content, yes: yes, btn2: cancel, close: close}, options));
    }
  };
  let Class = function (settings) {
    let that = this;
    that.index = ++layer.index;
    that.config = $.extend({}, that.config, settings);
    document.body ? that.creat() : setTimeout(function () {
      that.creat();
    }, 30);
  };

  Class.pt = Class.prototype;
  Class.pt.config = {
    shade: 0.3,
    move: '.layui-layer-title',
    title: '信息',
    area: 'auto',
    time: 0,
    zIndex: 19891014,
    maxWidth: 360,
    icon: -1,
    closable: true,
    shadeClosable: false,
    resizable: true,
    buttons: true,
    close: function () {},
    headerHeight: 0
  };
  Class.pt.vessel = function (callback = function () {}) {
    let that = this;
    let times = that.index;
    let config = that.config;
    let zIndex = config.zIndex + times;
    let titleHtml = config.title ? '<div class="layui-layer-title">' + config.title + '</div>' : '';
    config.zIndex = zIndex;
    callback([
      config.shade ? '<div class="layui-layer-shade" id="layui-layer-shade' + times + '" times="' + times + '" style="' + ('z-index:' + (zIndex - 1) + '; ') + '"></div>' : '',
      '<div class="layui-layer layui-layer-dialog" id="layui-layer' + times + '" type="dialog" times="' + times + '" showtime="' + config.time + '" conType="string" style="z-index: ' + zIndex + '; width:' + config.area[0] + ';height:' + config.area[1] + ';">' + titleHtml + '<div id="' + (config.id || '') + '" class="layui-layer-content' + (config.icon !== -1 ? ' layui-layer-padding' : '') + '">' + (config.icon !== -1 ? '<i class="layui-layer-ico layui-layer-ico' + config.icon + '"></i>' : '') + config.content + '</div><span class="layui-layer-set-win">' + (config.closable ? '<a class="layui-layer-ico layui-layer-close" href="javascript:"></a>' : '') + '</span>' + (config.buttons ? (function () {
        let buttons = ['确定', '取消'];
        let button = '';
        for (let i = 0; i < buttons.length; i++) {
          button += '<a class="layui-layer-btn' + i + '">' + buttons[i] + '</a>';
        }
        return '<div class="layui-layer-btn">' + button + '</div>';
      }()) : '') + (config.resizable ? '<span class="layui-layer-resize"></span>' : '') + '</div>'
    ], titleHtml, $('<div class="layui-layer-move"></div>'));
    return that;
  };
  Class.pt.creat = function () {
    let that = this;
    let config = that.config;
    let times = that.index;
    let $body = $('body');
    if (config.id && $('#' + config.id)[0]) return;
    if (typeof config.area === 'string') config.area = config.area === 'auto' ? ['', ''] : [config.area, ''];
    that.vessel(function (html, titleHTML, moveElem) {
      $body.append(html[0]);
      $body.append(html[1]);
      $('.layui-layer-move')[0] || $body.append(ready.moveElem = moveElem);
      that.$layer = $('#layui-layer' + times);
      that.$shade = $('#layui-layer-shade' + times);
    }).auto(times);
    $('#layui-layer-shade' + that.index).css({background: '#000', opacity: config.shade});
    that.offset();
    $window.on('resize', function () {
      that.offset();
      (/^\d+%$/.test(config.area[0]) || /^\d+%$/.test(config.area[1])) && that.auto(times);
    });
    config.time <= 0 || setTimeout(function () {
      layer.close(that.index);
    }, config.time);
    that.move().callback();
  };
  Class.pt.auto = function (index) {
    let config = this.config;
    let $layer = $('#layui-layer' + index);
    if (config.area[0] === '' && config.maxWidth > 0) $layer.outerWidth() > config.maxWidth && $layer.width(config.maxWidth);
    let area = [$layer.innerWidth(), $layer.innerHeight()];
    let setHeight = function (elem) {
      elem = $layer.find(elem);
      elem.height(area[1] - ($layer.find('.layui-layer-title').outerHeight() || 0) - ($layer.find('.layui-layer-btn').outerHeight() || 0) - 2 * (parseFloat(elem.css('padding-top')) || 0) - config.headerHeight * 2);
    };
    if (config.area[1] === '') {
      if (config.maxHeight > 0 && $layer.outerHeight() > config.maxHeight) {
        area[1] = config.maxHeight;
        setHeight('.layui-layer-content');
      } else if (area[1] >= $window.height()) {
        area[1] = $window.height();
        setHeight('.layui-layer-content');
      }
    } else {
      setHeight('.layui-layer-content');
    }
    return this;
  };
  Class.pt.offset = function () {
    let $layer = this.$layer;
    $layer.css({top: ($window.height() - $layer.outerHeight() + this.config.headerHeight) / 2, left: ($window.width() - $layer.outerWidth()) / 2});
  };
  Class.pt.move = function () {
    let config = this.config;
    let $layer = this.$layer;
    let $move = $layer.find(config.move);
    let dict = {};
    if (config.move) $move.css('cursor', 'move');
    $move.on('mousedown', function (e) {
      e.preventDefault();
      if (config.move) {
        dict.moveStart = true;
        dict.offset = [e.clientX - parseFloat($layer.css('left')), e.clientY - parseFloat($layer.css('top')) + config.headerHeight];
        ready.moveElem.css({cursor: 'move'}).show();
      }
    });
    $layer.find('.layui-layer-resize').on('mousedown', function (e) {
      e.preventDefault();
      dict.resizeStart = true;
      dict.offset = [e.clientX, e.clientY];
      dict.area = [$layer.outerWidth(), $layer.outerHeight()];
      ready.moveElem.css({cursor: 'se-resize'}).show();
    });
    $(document).on({
      mousemove: function (e) {
        if (dict.moveStart) {
          e.preventDefault();
          let X = e.clientX - dict.offset[0];
          let Y = e.clientY - dict.offset[1];
          dict.stX = 0;
          dict.stY = 0;
          let setRig = $window.width() - $layer.outerWidth() + dict.stX;
          let setBot = $window.height() - $layer.outerHeight() - config.headerHeight + dict.stY;
          X < dict.stX && (X = dict.stX);
          X > setRig && (X = setRig);
          Y < dict.stY && (Y = dict.stY);
          Y > setBot && (Y = setBot);
          $layer.css({left: X, top: Y + config.headerHeight});
        }
        if (config.resizable && dict.resizeStart) dict.isResize = true;
      },
      mouseup: function () {
        if (dict.moveStart) delete dict.moveStart;
        if (dict.resizeStart) delete dict.resizeStart;
        ready.moveElem.hide();
      }
    });
    return this;
  };
  Class.pt.callback = function () {
    let that = this;
    let $layer = that.$layer;
    let config = that.config;
    if (config.success) config.success($layer, that.index);
    $layer.find('.layui-layer-btn a').on('click', function () {
      let $index = $(this).index();
      if ($index === 0) {
        config.yes(that.index, $layer);
      } else if ($index === 1) {
        layer.close(that.index);
      }
    });
    $layer.find('.layui-layer-close').on('click', function () {
      (config.cancel && config.cancel(that.index, $layer)) || layer.close(that.index);
    });
    if (config.shadeClosable) {
      that.$shade.on('click', function () {
        config.close();
        layer.close(that.index);
      });
    }
  };
  layer.close = function (index) {
    $('#layui-layer-moves,#layui-layer-shade' + index + ',#layui-layer' + index).remove();
  };
  window.layer = layer;
  ready.run = function (_$) {
    $ = _$;
    $window = $(window);
    layer.open = function (deliver) {
      return new Class(deliver).index;
    };
  };
  (function () {
    ready.run(window.jQuery);
  }());
}(window));
