$(function () {
  // 商品价格
  let $price = $('input[name=price]');
  let $productId = $('.product_id');
  if ($price.val() === '') {
    $price.val($productId.find('option:selected').attr('price'));
  }
  layui.use(['form'], function () {
    layui.form.on('select(product_id)', function (data) {
      $price.val($(data.elem).find('option:selected').attr('price'));
    });
  });

  // 所在地区类型切换
  let $district1 = $('.district1');
  let $district2 = $('.district2');
  districtType($('input[name=district_type]:checked').val());
  $('input[name=district_type]').on('ifChecked', function () {
    districtType($(this).val());
  });
  function districtType (val) {
    switch (val) {
      case '0':
        $district1.show();
        $district2.hide();
        break;
      case '1':
        $district1.hide();
        $district2.show();
        break;
    }
  }
});
