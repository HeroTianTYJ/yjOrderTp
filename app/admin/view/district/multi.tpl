{extend name="../../common/view/base/form" /}

{block name="title"}行政区划{/block}

{block name="nav"}
{if condition="session(config('system.session_key').'.level')==1||in_array(config('permit_manage.'.request()->controller().'.index'),session(config('system.session_key').'.permit_manage'))"}<li><a href="{:url('/'.parse_name(request()->controller()).'/index',['parent_id'=>input('get.parent_id',0)])}">{if condition="$Map"}{$Map['name']}{else/}一级区划{/if}</a></li>{/if}
{if condition="session(config('system.session_key').'.level')==1||in_array(config('permit_manage.'.request()->controller().'.add'),session(config('system.session_key').'.permit_manage'))"}<li><a href="{:url('/'.parse_name(request()->controller()).'/add',['parent_id'=>input('get.parent_id',0)])}">添加</a></li>{/if}
<li class="current"><a href="{:url('/'.parse_name(request()->controller()).'/multi',['parent_id'=>input('get.parent_id',0)])}">批量添加</a></li>
{/block}

{block name="form"}
<form method="post" action="" class="form layui-form">
  <table>
    <tr><td>区划名称：</td><td><textarea name="multi" class="textarea"></textarea></td><td>每一行代表一个行政区划</td></tr>
    <tr><td colspan="2" class="left"><input type="submit" value="确认添加" class="btn btn-primary radius"></td></tr>
  </table>
</form>
{/block}