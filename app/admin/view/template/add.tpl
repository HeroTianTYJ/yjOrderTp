{extend name="../../common/view/base/form" /}

{block name="title"}模板管理{/block}
{block name="head"}<script type="text/javascript" src="public/admin/js/{:request()->controller()}.js?{:staticCache()}"></script>
<script type="text/javascript" src="public/base/xm-select.js?{:staticCache()}"></script>
<link rel="stylesheet" type="text/css" href="public/admin/styles/{:request()->controller()}.css?{:staticCache()}">
<script type="text/javascript">
let ThinkPHP = {
	AJAX : '{:url("/".parse_name(request()->controller())."/ajaxProduct")}',
	AJAX2 : '{:url("/".parse_name(request()->controller())."/ajaxProduct2")}'
};
</script>{/block}

{block name="nav"}
{if condition="session(config('system.session_key').'.level')==1||in_array(config('permit_manage.'.request()->controller().'.index'),session(config('system.session_key').'.permit_manage'))"}<li><a href="{:url('/'.parse_name(request()->controller()).'/index')}">列表</a></li>{/if}
<li class="current"><a href="{:url('/'.parse_name(request()->controller()).'/add')}">添加</a></li>
{/block}

{block name="form"}
<form method="post" action="" class="form layui-form">
  <table>
    <tr><td>模 板 名：</td><td><input type="text" name="name" class="input-text"></td><td>用于区分其它模板</td></tr>
    <tr><td>指定代理：</td><td><select name="manager_id" lay-search><option value="0">不指定</option>{$Manager}</select></td></tr>
    <tr><td>使用模板：</td><td><select name="template" lay-filter="template" lay-search>{$Template}</select></td><td><span class="view"></span>　　经典版不支持皮肤样式的定义</td></tr>
    <tr class="style"><td>皮肤样式：</td><td><select name="template_style_id" lay-search>{$TemplateStyle}</select></td><td>手机版2和手机版3推荐使用11号皮肤样式，手机版4推荐使用12号皮肤样式</td></tr>
    <tr><td>产品分类：</td><td><div class="radio-box"><label><input type="radio" name="product_type" value="0" checked>单分类</label></div><div class="radio-box"><label><input type="radio" name="product_type" value="1">多分类</label></div></td></tr>
    <tr class="pro1"><td>选择分类：</td><td><select name="sort1" lay-search>{$ProductSort}</select></td></tr>
    <tr class="pro1 product_ids1"><td>选择产品：</td><td><input type="hidden" name="product_ids1"><div class="product_select1 xm-select"></div></td><td>生成的下单页只显示勾选且在<a href="javascript:" onclick="window.parent.addTab('{:url('/product/index')}','产品管理')">产品管理</a>模块中设置了前台显示的产品</td></tr>
    <tr class="pro1"><td>默认产品：</td><td><select name="selected1" lay-search></select></td></tr>
    <tr class="pro2 product_ids2"><td>选择产品：</td><td><input type="hidden" name="sort2"><input type="hidden" name="product_ids2"><div class="product_select2 xm-select"></div></td><td>生成的下单页只显示勾选且在<a href="javascript:" onclick="window.parent.addTab('{:url('/product/index')}','产品管理')">产品管理</a>模块中设置了前台显示的产品</td></tr>
    <tr class="pro2"><td>默认产品：</td><td><select name="selected2" lay-search></select></td></tr>
    <tr><td>产品显示：</td><td><div class="radio-box"><label><input type="radio" name="view_type" value="1" checked>单选按钮</label></div><div class="radio-box"><label><input type="radio" name="view_type" value="0">下拉框</label></div></td></tr>
    <tr><td>下单字段：</td><td class="field">{$Field}<p style="margin:10px 0 0 0;"><input type="button" value="全选" class="btn btn-primary radius all"> <input type="button" value="全部不选" class="btn btn-primary radius no"> <input type="button" value="默认字段" class="btn btn-primary radius selected"></p></td><td>红色部分为默认字段</td></tr>
    <tr><td>支付方式：</td><td>{$Pay}</td></tr>
    <tr><td>默认方式：</td><td><select name="selectedPay" lay-search>{$Pay2}</select></td></tr>
    <tr><td>订单状态：</td><td><select name="order_state_id" lay-search>{$OrderState}</select></td><td>客户下单后，该订单在<a href="javascript:" onclick="window.parent.addTab('{:url('/order/index')}','订单管理')">订单管理</a>模块中显示的默认订单状态</td></tr>
    <tr><td>订单查询：</td><td><div class="radio-box"><label><input type="radio" name="is_show_search" value="0" checked>关闭</label></div><div class="radio-box"><label><input type="radio" name="is_show_search" value="1">开启</label></div></td><td>在<a href="javascript:" onclick="window.parent.addTab('{:url('/system/index')}','系统设置')">系统设置</a>模块中开启“订单查询”后，此设置才可生效</td></tr>
    <tr><td>发货通知：</td><td><div class="radio-box"><label><input type="radio" name="is_show_send" value="0" checked>关闭</label></div><div class="radio-box"><label><input type="radio" name="is_show_send" value="1">开启</label></div></td><td>为模拟发货通知，并非从数据库里提取的数据（不支持经典版模板）</td></tr>
    <tr><td>验 证 码：</td><td><div class="radio-box"><label><input type="radio" name="is_captcha" value="0" checked>关闭</label></div><div class="radio-box"><label><input type="radio" name="is_captcha" value="1">开启</label></div></td></tr>
    <tr><td>QQ登录：</td><td><div class="radio-box"><label><input type="radio" name="is_qq" value="0" checked>关闭</label></div><div class="radio-box"><label><input type="radio" name="is_qq" value="1">开启</label></div></td><td>开启后，使用QQ登录后才可下单</td></tr>
    <tr><td>提示信息　</td></tr>
    <tr><td>提交成功：</td><td><textarea name="success" class="textarea">订单提交成功，订单号为：{oid}，可用于后续的查单操作。请保持您的联系电话畅通，以便和您取得联系！</textarea></td><td>{oid}表示订单号</td></tr>
    <tr><td>支付成功：</td><td><textarea name="success2" class="textarea">订单支付成功，订单号为：{oid}，可用于后续的查单操作。</textarea></td><td>{oid}表示订单号</td></tr>
    <tr><td>频繁提交：</td><td><textarea name="often" class="textarea">请勿频繁提交订单！</textarea></td></tr>
    <tr><td colspan="2" class="left"><input type="submit" value="确认添加" class="btn btn-primary radius"></td></tr>
  </table>
</form>
{/block}