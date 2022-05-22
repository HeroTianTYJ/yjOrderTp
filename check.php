<?php
$webUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] .
    substr($_SERVER['SCRIPT_NAME'], 0, strrpos($_SERVER['SCRIPT_NAME'], '/') + 1);

if ($_POST) {
    try {
        $PDO = new PDO('mysql:host=' . $_POST['host'] . ';charset=UTF8', $_POST['user'], $_POST['password']);
        $prepare = $PDO->prepare('SELECT VERSION();');
        $prepare->execute();
        $row = $prepare->fetch();
        $PDO = $prepare = null;
        preg_match('/^[\d]+.[\d]+.[\d]+/', $row[0], $version);
        exit('最低版本要求为5.5.0，当前版本为' . $version[0] . '，' . ($version[0] >= 5.5 ?
                '<span class="green">支持</span>' : '<span class="red">不支持</span>'));
    } catch (Exception $e) {
        exit($e->getMessage());
    }
}
?>
<!doctype html>
<html lang="zh-cn">
<head>
<meta charset="utf-8">
<title>《昱杰订单管理系统（ThinkPHP版）》运行环境检测</title>
<base href="<?php echo $webUrl; ?>">
<script type="text/javascript" src="public/base/jquery.js"></script>
<script type="text/javascript" src="public/base/H-ui/H-ui.min.js"></script>
<script type="text/javascript" src="public/base/Common.js"></script>
<link rel="stylesheet" type="text/css" href="public/base/H-ui/H-ui.min.css">
<link rel="stylesheet" type="text/css" href="public/base/styles/Basic.css">
<script type="text/javascript">
  $(function () {
    $('.btn').on('click', function () {
      $.ajax({
        type: 'POST',
        url: '<?php echo $webUrl;?>check.php',
        data: {
          host: $('input[name=host]').val(),
          user: $('input[name=user]').val(),
          password: $('input[name=password]').val()
        },
        success: function (data) {
          $('.mysql_version').html(data + '<br>');
        }
      });
    });
  });
</script>
<style type="text/css">
    .form table {
        width: 707px;
        margin: 0 auto;
    }

    .form table tr td input.input-text {
        width: 180px;
    }
</style>
</head>

<body>
<form method="post" action="" class="form">
  <table>
    <tr><td>PHP版本：</td><td>最低版本要求为7.4.0，当前版本为<?php echo phpversion();?>，<?php echo
            version_compare(PHP_VERSION, '7.4.0', '>=') ?
                '<span class="green">支持</span>' : '<span class="red">不支持</span>';?></td></tr>
    <tr><td>MySQL版本：</td><td>
    <?php
    if (version_compare(PHP_VERSION, '7.4.0', '<')) {
        ?>
        请先解决PHP版本过低的问题
        <?php
    } elseif (!extension_loaded('pdo_mysql')) {
        ?>
        请先开启pdo_mysql扩展
        <?php
    } else {
        ?>
      <span class="mysql_version"></span>
      <input type="text" name="host" class="input-text" placeholder="MySQL服务器地址">
      <input type="text" name="user" class="input-text" placeholder="MySQL用户名">
      <input type="text" name="password" class="input-text" placeholder="MySQL密码">
      <input type="button" value="查看" class="btn btn-primary radius">
        <?php
    }
    ?>
    </td></tr>
    <tr><td>curl扩展：</td><td><?php echo extension_loaded('curl') ?
                '<span class="green">支持</span>' : '<span class="red">不支持</span>';?></td></tr>
    <tr><td>gd2扩展：</td><td><?php echo extension_loaded('gd') ?
                '<span class="green">支持</span>' : '<span class="red">不支持</span>';?></td></tr>
    <tr><td>mbstring扩展：</td><td><?php echo extension_loaded('mbstring') ?
                '<span class="green">支持</span>' : '<span class="red">不支持</span>';?></td></tr>
    <tr><td>openssl扩展：</td><td><?php echo extension_loaded('openssl') ?
                '<span class="green">支持</span>' : '<span class="red">不支持</span>';?></td></tr>
    <tr><td>pdo_mysql扩展：</td><td><?php echo extension_loaded('pdo_mysql') ?
                '<span class="green">支持</span>' : '<span class="red">不支持</span>';?></td></tr>
  </table>
</form>
</body>
</html>