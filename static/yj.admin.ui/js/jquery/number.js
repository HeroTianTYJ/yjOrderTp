/*
@Name：《昱杰后台UI框架》
@Author：风形火影
@Site：https://www.yjrj.top
*/
;(function ($) {
  $.fn.extend({
    'number': function (args, blur = function () {}) {
      args = args || {};
      args.width = args.width ? args.width : 140;
      args.height = args.height ? args.height : 36;
      args.fontSize = args.fontSize ? args.fontSize : 14;
      args.callback = args.callback ? args.callback : function () {};
      args.top = args.top ? args.top : 2;
      args.min = typeof args.min !== 'undefined' ? args.min : 1;
      args.max = args.max ? args.max : 1000000000000;

      let that = this;
      let $that = $(that);
      for (let i = 0; i < $that.length; i++) {
        $that.eq(i).html('<div class="decrease" style="padding:0 5px;cursor:pointer;float:left;background:#F2F2F2;border-right:1px solid #DCDCDC;">-</div><input type="text" name="' + $that.eq(i).attr('name') + '" value="' + $that.eq(i).attr('value') + '" style="float:left;width:' + (args.width - 39) + 'px;height:' + args.height + 'px;line-height:' + args.height + 'px;border:none;text-align:center;font-size:' + args.fontSize + 'px;outline:none;"><div class="increase" style="padding:0 5px;cursor:pointer;float:right;background:#F2F2F2;border-left:1px solid #DCDCDC;">+</div>').css({width: args.width, height: args.height, lineHeight: args.height + 'px', border: '1px solid #DCDCDC', position: 'relative', top: args.top, borderRadius: 6, display: 'inline-block'});
        $(that).eq(i).find('.decrease').on('click', function (e) {
          e.stopPropagation();
          let $this = $(this);
          let val = $this.next('input').val();
          if (val === '') val = args.min + 1;
          val = parseInt(val);
          if (val <= args.min) return;
          $this.next('input').val(val - 1).trigger('focus');
          args.callback();
        });
        $(that).eq(i).find('.increase').on('click', function (e) {
          e.stopPropagation();
          let $this = $(this);
          let val = $this.prev('input').val();
          if (val === '') val = '0';
          val = parseInt(val.toString());
          if (val >= args.max) return;
          $this.prev('input').val(val + 1).trigger('focus');
          args.callback();
        });
        $(that).eq(i).find('input').on('keyup blur', function () {
          let $this = $(this);
          $this.val($this.val().replace(/\D/g, ''));
          if ($this.val() === '' || parseInt($this.val().toString()) < args.min) $this.val(args.min);
          if (parseInt($this.val().toString()) > args.max) $this.val(args.max);
          args.callback();
        });
        $(that).eq(i).find('input').on('blur', function () {
          blur($(this).val());
        });
      }
      return this;
    }
  });
})(jQuery);
