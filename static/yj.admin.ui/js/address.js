$(function () {
  let $provinceSelect = $('select.province');
  let $citySelect = $('select.city');
  let $countySelect = $('select.county');
  let $townSelect = $('select.town');
  let $province = $('input[name=province]');
  let $city = $('input[name=city]');
  let $county = $('input[name=county]');
  let $town = $('input[name=town]');

  if ($provinceSelect.length) {
    $.ajax({
      type: 'POST',
      url: CONFIG['DISTRICT'],
      data: {
        parent_id: 0
      }
    }).then(function (data) {
      let html = '<option value="">请选择省份</option>';
      $.each(JSON.parse(data), function (index, value) {
        html += '<option value="' + value.id + '">' + value.name + '</option>';
      });
      $provinceSelect.html(html);
      layui.form.render();
    });
  }

  layui.use(['form'], function () {
    layui.form.on('select(province)', function (data) {
      $countySelect.html('<option value="">请选择区/县</option>');
      $townSelect.html('<option value="">若不清楚，可不选</option>');
      $city.val('');
      $county.val('');
      $town.val('');
      if (data.value === '') {
        $province.val('');
        $citySelect.html('<option value="">请选择城市</option>');
        layui.form.render();
      } else {
        $province.val(data.elem[data.value].innerText);
        $.ajax({
          type: 'POST',
          url: CONFIG['DISTRICT'],
          data: {
            parent_id: data.value
          }
        }).then(function (data) {
          let html = '<option value="">请选择城市</option>';
          $.each(JSON.parse(data), function (index, value) {
            html += '<option value="' + value.id + '">' + value.name + '</option>';
          });
          $citySelect.html(html);
          layui.form.render();
        });
      }
    });

    layui.form.on('select(city)', function (data) {
      $townSelect.html('<option value="">若不清楚，可不选</option>');
      $county.val('');
      $town.val('');
      if (data.value === '') {
        $city.val('');
        $countySelect.html('<option value="">请选择区/县</option>');
        layui.form.render();
      } else {
        $city.val(data.elem[data.elem.selectedIndex].text);
        $.ajax({
          type: 'POST',
          url: CONFIG['DISTRICT'],
          data: {
            parent_id: data.value
          }
        }).then(function (data) {
          let html = '<option value="">请选择区/县</option>';
          $.each(JSON.parse(data), function (index, value) {
            html += '<option value="' + value.id + '">' + value.name + '</option>';
          });
          $countySelect.html(html);
          layui.form.render();
        });
      }
    });

    layui.form.on('select(county)', function (data) {
      $town.val('');
      if (data.value === '') {
        $county.val('');
        $townSelect.html('<option value="">若不清楚，可不选</option>');
        layui.form.render();
      } else {
        $county.val(data.elem[data.elem.selectedIndex].text);
        $.ajax({
          type: 'POST',
          url: CONFIG['DISTRICT'],
          data: {
            parent_id: data.value
          }
        }).then(function (data) {
          let html = '<option value="">若不清楚，可不选</option>';
          $.each(JSON.parse(data), function (index, value) {
            html += '<option value="' + value.id + '">' + value.name + '</option>';
          });
          $townSelect.html(html);
          layui.form.render();
        });
      }
    });

    layui.form.on('select(town)', function (data) {
      $town.val(data.value === '' ? '' : data.elem[data.elem.selectedIndex].text);
    });
  });
});
