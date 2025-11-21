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
        preg_match('/^\d+.\d+.\d+/', $row[0], $version);
        exit($version[0] >= 5.5 ?
            '<span class="green">支持</span>' : '最低版本要求为5.5.0，当前版本为' . $version[0] . '，<span class="red">不支持</span>');
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
<script type="text/javascript" src="static/library/jquery/jquery.js"></script>
<link rel="stylesheet" type="text/css" href="static/yj.admin.ui/css/basic.css">
<script type="text/javascript">
$(function () {
  $('.button').on('click', function () {
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
<style>
body {
    font-size: 16px;
    background: #FFF;
}
.form table {
    width: 800px;
    margin: 0 auto;
}
.form table tr td:nth-child(1) {
    width: 135px;
}
.form table tr td input.text {
    width: 194px;
}
</style>
</head>

<body>
<form method="post" action="" class="form">
  <table>
    <tr>
      <td>PHP版本：</td>
      <td><?php echo version_compare(PHP_VERSION, '8.0.0', '>=') ?
              '<span class="green">支持</span>' : '最低版本要求为8.0.0，当前版本为' . phpversion() . '，<span class="red">不支持' .
              '</span>，建议您升级PHP版本，使运行环境更安全。如确实不能升级，请' .
              '<a href="https://pan.baidu.com/s/14NtpvNxD-S-_a7LZAjCU7g?pwd=5q9w" target="_blank">点击此处</a>下载本系统的PHP' .
              '7.4版支持包，下载后，删除本系统根目录中的vendor目录及run.inc.php文件，并将支持包解压到本系统根目录即可安装本系统（如已上传，此页面不会更新，请直接访问安装页面进行安装）。';?></td>
    </tr>
    <tr>
      <td>curl扩展：</td>
      <td><?php echo extension_loaded('curl') ? '<span class="green">支持</span>' : '<span class="red">不支持</span>';?></td>
    </tr>
    <tr>
      <td>gd2扩展：</td>
      <td><?php echo extension_loaded('gd') ? '<span class="green">支持</span>' : '<span class="red">不支持</span>';?></td>
    </tr>
    <tr>
      <td>mbstring扩展：</td>
      <td>
        <?php echo extension_loaded('mbstring') ? '<span class="green">支持</span>' : '<span class="red">不支持</span>';?>
      </td>
    </tr>
    <tr>
      <td>openssl扩展：</td>
      <td>
          <?php echo extension_loaded('openssl') ? '<span class="green">支持</span>' : '<span class="red">不支持</span>';?>
      </td>
    </tr>
    <tr>
      <td>pdo_mysql扩展：</td>
      <td>
        <?php echo extension_loaded('pdo_mysql') ? '<span class="green">支持</span>' : '<span class="red">不支持</span>';?>
      </td>
    </tr>
    <tr>
      <td>MySQL版本：</td>
      <td>
        <?php
        if (version_compare(PHP_VERSION, '8.0.0', '<')) {
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
          <input type="text" name="host" class="text" placeholder="MySQL服务器地址">
          <input type="text" name="user" class="text" placeholder="MySQL用户名">
          <input type="text" name="password" class="text" placeholder="MySQL密码">
          <input type="button" value="查看" class="button">
            <?php
        }
        ?>
      </td>
    </tr>
  </table>
</form>
</body>
</html>