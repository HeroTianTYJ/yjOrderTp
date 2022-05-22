/**
jQuery插件：颜色拾取器
@author  Karson
@url     http://blog.iplaybus.com
@name    jquery.colorpicker.js
*/
(function ($) {
  let colorHex = ['0', '3', '6', '9', 'C', 'F'];
  let spColorHex = ['F00', '0F0', '00F', 'FF0', '0FF', 'F0F'];
  $.fn.colorpicker = function (options) {
    let extend = jQuery.extend({}, jQuery.fn.colorpicker.defaults, options);
    init();
    return this.each(function () {
      let $this = $(this);
      let $colorPanel = $('table.color_panel');
      $this.on(extend.event, function (e) {
        e.stopPropagation();
        $colorPanel.css({top: $(this).offset().top + $(this).height() + 5, left: $(this).offset().left}).show();
        let $target = extend.target ? $(extend.target) : $this;
        if ($target.data('color') === null) $target.data('color', $target.css('color'));
        if ($target.data('value') === null) $target.data('value', $target.val());
        $colorPanel.find('tbody.header a.reset').on('click', function () {
          $target.css('color', $target.data('color')).val($target.data('value'));
          $colorPanel.hide();
        });
        $colorPanel.find('tbody.panel tr td').on('mouseover', function () {
          let color = '#' + $(this).attr('rel');
          $colorPanel.find('tbody.header tr td span.dis_color').css('background', color);
          $colorPanel.find('tbody.header tr td input.hex_color').val(color);
        }).on('click', function () {
          let color = '#' + $(this).attr('rel');
          if (extend.fill_color) $target.val(color);
          // $target.css('color', color);
          $colorPanel.hide();
        });
        $colorPanel.find('tbody.header a.close').on('click', function () {
          $colorPanel.hide();
        });
        $(document).on('click', function () {
          $colorPanel.hide();
        });
      });
    });
    function init () {
      let colorPicker = '<style type="text/css">table.color_panel{position:absolute;display:none;border-collapse:collapse;z-index:9999999999;}table.color_panel tr td{border:1px solid #000}table.color_panel tbody.header tr td{background:#ccc;padding:0 3px;height:30px;font-size:12px}table.color_panel tbody.header tr td span.dis_color{border:solid 1px #000;background:#FF0;margin:0 20px 0 0;width:60px;height:16px;float:left}table.color_panel tbody.header tr td input.hex_color{border:1px solid #000;width:32px;margin:0 20px 0 0}table.color_panel tbody.panel tr{height:12px}table.color_panel tbody.panel tr td{cursor:pointer;width:11px}</style><table class="color_panel"><tbody class="header"><tr><td colspan="21"><span class="dis_color"></span><input type="text" class="hex_color" value="#FF0"><a href="javascript:" class="close">关闭</a> <a href="javascript:" class="reset">清除</a></td></tr></tbody><tbody class="panel">';
      let color = '';
      for (let i = 0; i < 2; i++) {
        for (let j = 0; j < 6; j++) {
          colorPicker += '<tr><td rel="000" style="background:#000">';
          color = i === 0 ? colorHex[j] + colorHex[j] + colorHex[j] : spColorHex[j];
          colorPicker += '<td rel="' + color + '" style="background:#' + color + '"><td rel="000" style="background:#000">';
          for (let k = 0; k < 3; k++) {
            for (let l = 0; l < 6; l++) {
              color = colorHex[k + i * 3] + colorHex[l] + colorHex[j];
              colorPicker += '<td rel="' + color + '" style="background:#' + color + '">';
            }
          }
        }
      }
      colorPicker += '</tbody></table>';
      $('body').append(colorPicker);
    }
  };
  jQuery.fn.colorpicker.defaults = {
    fill_color: true, // 是否将颜色值填充至对象的val中
    target: null, // 目标对象
    event: 'click' // 颜色框显示的事件
  };
})(jQuery);
