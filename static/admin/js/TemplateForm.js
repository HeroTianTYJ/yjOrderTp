$(function () {
  template($('select[name=template]'));
  layui.use(['form'], function () {
    layui.form.on('select(template)', function (data) {
      template($(data.elem));
    });
  });
  function template (element) {
    $('.view_url').html('<a href="' + element.find('option:selected').attr('view') + '" target="_blank">预览</a>');
  }

  let $productDefault1 = $('select[name=product_default1]');
  productType($('input[name=product_type]:checked').val());
  $('input[name=product_type]').on('ifChecked', function () {
    productType($(this).val());
  });
  function productType (val) {
    let $productSort1 = $('.product_sort1');
    let $product1 = $('.product1');
    let $productDefault1 = $('.product_default1');
    let $productSort2 = $('.product_sort2');
    let $product2 = $('.product2');
    let $productDefault2 = $('.product_default2');
    switch (val) {
      case '0':
        $productSort1.show();
        $product1.show();
        $productDefault1.show();
        $productSort2.hide();
        $product2.hide();
        $productDefault2.hide();
        break;
      case '1':
        $productSort1.hide();
        $product1.hide();
        $productDefault1.hide();
        $productSort2.show();
        $product2.show();
        $productDefault2.show();
        break;
    }
  }

  product();
  layui.use(['form'], function () {
    layui.form.on('select(product_sort_id)', function () {
      product();
      $productDefault1.html('');
      layui.form.render();
    });
  });
  let productIds1 = xmSelect.render({
    el: '.product_ids1',
    toolbar: {
      show: true
    },
    theme: {
      color: '#0059FF',
      hover: '#E4EBFF'
    },
    filterable: true,
    autoRow: true,
    on: function (data) {
      let html = '';
      let productIds = [];
      $.each(data.arr, function (index, value) {
        html += '<option value="' + value['value'] + '" style="color:' + value['color'] + ';">' + value['name'] + '</option>';
        productIds.push(value['value']);
      });
      $productDefault1.html(html);
      layui.use(['form'], function () {
        layui.form.render();
      });
      productIds.sort((num1, num2) => num1 - num2);
      $('input[name=product_ids1]').val(productIds.join(','));
    }
  });
  function product () {
    $.ajax({
      type: 'POST',
      url: ThinkPHP['AJAX'],
      data: {
        product_sort_id: $('select[name=product_sort_id] option:selected').val(),
        product_ids1: $('input[name=product_ids1]').val()
      },
      success: function (data) {
        productIds1.update({
          data: JSON.parse(data)
        });
        default1();
      }
    });
  }
  function default1 () {
    let html = '';
    $.each(productIds1.getValue(), function (index, value) {
      if ($.inArray(value['value'] + '', $('input[name=product_ids1]').val().split(',')) !== -1 && $('input[name=product_type]:checked').val() === '0') html += '<option value="' + value['value'] + '"' + (value['value'] + '' === $('.product_default').val() ? 'selected' : '') + ' style="color:' + value['color'] + ';">' + value['name'] + '</option>';
    });
    $productDefault1.html(html);
    layui.use(['form'], function () {
      layui.form.render();
    });
  }

  product2();
  let $productDefault2 = $('select[name=product_default2]');
  let productIds2 = xmSelect.render({
    el: '.product_ids2',
    toolbar: {
      show: true
    },
    theme: {
      color: '#0059FF',
      hover: '#E4EBFF'
    },
    filterable: true,
    autoRow: true,
    on: function (data) {
      let html = '';
      let productSortId = '';
      let productIds = [];
      $.each(data.arr, function (index, value) {
        if (!new RegExp('<optgroup label="' + value['parent_name'] + '" style="color:' + value['parent_color'] + ';">').test(html)) {
          html += '<optgroup label="' + value['parent_name'] + '" style="color:' + value['parent_color'] + ';">';
          productSortId += value['parent_value'] + ',';
        }
        html += '<option value="' + value['value'] + '" style="color:' + value['color'] + ';"' + (value['value'] + '' === $('.product_default').val() ? ' selected' : '') + '>' + value['name'] + '</option>';
        productIds.push(value['value']);
      });
      $productDefault2.html(html);
      layui.use(['form'], function () {
        layui.form.render();
      });
      $('input[name=product_sort_ids]').val(productSortId.substring(0, productSortId.length - 1));
      productIds.sort((num1, num2) => num1 - num2);
      $('input[name=product_ids2]').val(productIds.join(','));
    }
  });
  function product2 () {
    $.ajax({
      type: 'POST',
      url: ThinkPHP['AJAX2'],
      data: {
        product_ids2: $('input[name=product_ids2]').val()
      },
      success: function (data) {
        productIds2.update({
          data: JSON.parse(data)
        });
        default2();
      }
    });
  }
  function default2 () {
    let html = '';
    let productSortId = '';
    $.each(productIds2.getValue(), function (index, value) {
      if (!new RegExp('<optgroup label="' + value['parent_name'] + '" style="color:' + value['parent_color'] + ';">').test(html)) {
        html += '<optgroup label="' + value['parent_name'] + '" style="color:' + value['parent_color'] + ';">';
        productSortId += value['parent_value'] + ',';
      }
      html += '<option value="' + value['value'] + '" style="color:' + value['color'] + ';"' + (value['value'] + '' === $('.product_default').val() ? ' selected' : '') + '>' + value['name'] + '</option>';
    });
    $productDefault2.html(html);
    $('input[name=product_sort_ids]').val(productSortId.substring(0, productSortId.length - 1));
    layui.use(['form'], function () {
      layui.form.render();
    });
  }

  $('.all').on('click', function () {
    $('.field input[type=checkbox]').each(function () {
      $(this).iCheck('check');
    });
  });
  $('.no').on('click', function () {
    $('.field input[type=checkbox]').each(function () {
      $(this).iCheck('uncheck');
    });
  });
  $('.selected').on('click', function () {
    $('.field input[type=checkbox]').each(function () {
      $(this).iCheck('uncheck');
    });
    $('.field label.red input[type=checkbox]').each(function () {
      $(this).iCheck('check');
    });
  });
});
