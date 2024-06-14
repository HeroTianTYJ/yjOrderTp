/*
@Name：《昱杰后台UI框架》
@Author：风形火影
@Site：https://www.yjrj.cn
*/
;(function ($) {
  $.fn.extend({
    'number': function (args) {
      args = args || {};
      args.width = args.width ? args.width : 140;
      args.callback = args.callback ? args.callback : function () {};
      args.top = args.top ? args.top : 2;
      args.min = typeof args.min !== 'undefined' ? args.min : 1;
      args.max = args.max ? args.max : 1000000000000;

      let that = this;
      let $that = $(that);
      for (let i = 0; i < $that.length; i++) {
        $that.eq(i).html('<input type="text" name="' + $that.eq(i).attr('name') + '" value="' + $that.eq(i).attr('value') + '" style="width:' + (args.width - 19) + 'px;border:none;outline:none;text-align:center;line-height:29px;float:left;padding:0 5px;box-sizing:border-box;"><div style="float:left;text-align:center;"><div class="increase" style="cursor:pointer;background:#F2F2F2;width:15px;line-height:14px;border-bottom:1px solid #D0D2D7;border-left:1px solid #D0D2D7;">+</div><div class="decrease" style="cursor:pointer;background:#F2F2F2;width:15px;line-height:14px;border-left:1px solid #D0D2D7;">-</div></div>').css({width: args.width, top: args.top, border: '1px solid #D0D2D7', position: 'relative', display: 'inline-block', fontSize: '14px', boxSizing: 'border-box', lineHeight: 'normal'});
        $(that).eq(i).find('.decrease').on('click', function (e) {
          e.stopPropagation();
          let $this = $(this);
          let val = $this.parent().parent().find('input').val();
          if (val === '') val = args.min + 1;
          val = parseInt(val);
          if (val <= args.min) return;
          $this.parent().parent().find('input').val(val - 1).trigger('focus');
          args.callback(val - 1);
        });
        $(that).eq(i).find('.increase').on('click', function (e) {
          e.stopPropagation();
          let $this = $(this);
          let val = $this.parent().parent().find('input').val();
          if (val === '') val = '0';
          val = parseInt(val.toString());
          if (val >= args.max) return;
          $this.parent().parent().find('input').val(val + 1).trigger('focus');
          args.callback(val + 1);
        });
        $(that).eq(i).find('input').on('keyup blur', function () {
          let $this = $(this);
          $this.val($this.val().replace(/\D/g, ''));
          if ($this.val() === '' || parseInt($this.val().toString()) < args.min) $this.val(args.min);
          if (parseInt($this.val().toString()) > args.max) $this.val(args.max);
          args.callback($this.val());
        });
      }
      return this;
    }
  });
})(jQuery);
