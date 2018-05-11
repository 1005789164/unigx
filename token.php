<?php

$mysql = new SaeMysql ();
$appid = "wx383e5b3c57fca9da";
$secret = "e23befb9fb7efe7f5ca14274cdffddcf";
$readJson = file_get_contents ( "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid={$appid}&secret={$secret}" );
$tokenJson = json_decode ( $readJson );
$sql = "select * from `token`";
if(!empty($mysql->getData($sql))){
    $sql = "update `token` set `token` = '{$tokenJson->access_token}'";
	$mysql -> runSql ($sql);
}else {
    $sql = "insert into `token` (`token`) values ('{$tokenJson->access_token}')";
    $mysql -> runSql ($sql);
}

?>
