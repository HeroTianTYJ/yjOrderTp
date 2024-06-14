/*
@Name：《昱杰后台UI框架》
@Author：风形火影
@Site：https://www.yjrj.cn
*/
(function ($) {
  $.fn.createPage = function (options) {
    let page = {
      init: function (element, args) {
        let that = this;
        return (function () {
          that.fillHtml(element, args);
          that.bindEvent(element, args);
        })();
      },
      // 填充html
      fillHtml: function (element, args) {
        return (function () {
          let html = '';
          // 上一页
          html += '<li class="' + (args.active > 1 ? 'prev' : 'disabled') + '"><a href="javascript:">上一页</a></li>';
          // 中间页码
          if (args.active !== 1 && args.active >= 4 && args.pageCount !== 4) html += '<li class="number"><a href="javascript:">' + 1 + '</a></li>';
          if (args.active - 2 > 2 && args.active <= args.pageCount && args.pageCount > 5) html += '<li class="ellipsis"><a href="javascript:">...</a></li>';
          let start = args.active - 2;
          let end = args.active + 2;
          if ((start > 1 && args.active < 4) || args.active === 1) end++;
          if (args.active > args.pageCount - 4 && args.active >= args.pageCount) start--;
          for (; start <= end; start++) {
            if (start <= args.pageCount && start >= 1) html += '<li class="' + (start === args.active ? 'active' : 'number') + '' + '' + '"><a href="javascript:">' + start + '</a></li>';
          }
          if (args.active + 2 < args.pageCount - 1 && args.active >= 1 && args.pageCount > 5) html += '<li class="ellipsis"><a href="javascript:">...</a></li>';
          if (args.active !== args.pageCount && args.active < args.pageCount - 2 && args.pageCount !== 4) html += '<li class="number"><a href="javascript:">' + args.pageCount + '</a></li>';
          // 下一页
          html += '<li class="' + (args.active < args.pageCount ? 'next' : 'disabled') + '"><a href="javascript:">下一页</a></li>';
          // 统计
          html += '<li class="statistics">每页' + args.pageSize + '条/共' + args.total + '条</li>';
          // 输入框
          html += '<li class="text">跳至 <input type="text" name="page" value="' + args.active + '" class="text"> 页</li>';

          element.html(html);
        })();
      },
      // 绑定事件
      bindEvent: function (element, args) {
        let that = this;
        return (function () {
          // 上一页
          element.on('click', '.prev', function () {
            args.active -= 1;
            that.fillHtml(element, args);
            if (typeof (args.paging) === 'function') args.paging(args.active);
          });
          // 中间页码
          element.on('click', '.number', function () {
            args.active = parseInt($(this).text());
            that.fillHtml(element, args);
            if (typeof (args.paging) === 'function') args.paging(args.active);
          });
          // 下一页
          element.on('click', '.next', function () {
            args.active += 1;
            that.fillHtml(element, args);
            if (typeof (args.paging) === 'function') args.paging(args.active);
          });
          // 输入框
          element.on('keyup', 'input', function (e) {
            let $this = $(this);
            $this.val($this.val().replace(/\D/g, ''));
            if (e.key === 'Enter') {
              args.active = parseInt($this.val());
              if (args.active < 1) {
                showTip('页码不得小于1！', 0);
                $this.trigger('focus');
                return false;
              }
              if (args.active > args.pageCount) {
                showTip('页码不得大于' + args.pageCount + '！', 0);
                $this.trigger('focus');
                return false;
              }
              that.fillHtml(element, args);
              if (typeof (args.paging) === 'function') args.paging(args.active);
            }
          });
        })();
      }
    };
    page.init(this, $.extend({
      pageCount: 10,
      active: 1,
      paging: function () {}
    }, options));
  };
})(jQuery);
