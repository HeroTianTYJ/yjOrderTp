/*
@Name：《昱杰后台UI框架》
@Author：风形火影
@Site：https://www.yjrj.top
*/
$(function () {
  let $tip = $('.tip');
  $tip.find('.iconfont').addClass('icon-tip-' + CONFIG['TYPE']);
  // $tip.find('.tip_content').html(CONFIG['TIP_CONTENT']);
  if (CONFIG['LOCATION_URL']) {
    $tip.find('.location a').attr({href: CONFIG['LOCATION_URL']});
    setTimeout(function () {
      window.location.href = CONFIG['LOCATION_URL'];
    }, CONFIG['LOCATION_SECOND'] * 1000);
    $tip.find('.location a').text(CONFIG['LOCATION_CONTENT']);
  } else if (CONFIG['TYPE'] === 'failed' && CONFIG['LOCATION_SECOND'] > 0) {
    $tip.find('.location a').attr({href: 'javascript:history.go(-1)'});
    setTimeout(function () {
      window.history.go(-1);
    }, CONFIG['LOCATION_SECOND'] * 1000);
    $tip.find('.location a').text(CONFIG['LOCATION_CONTENT']);
  }
});
