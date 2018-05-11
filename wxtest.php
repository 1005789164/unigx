<?php
$checkTest = new CheckTest ();
if (! isset ( $_GET ["echostr"] ))
	$checkTest->sendMsg ();
else
	$checkTest->valid ();
class CheckTest {
	public function valid() {
		echo $_GET ["echostr"];
	}
	public function getToken() {
		// $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=wx383e5b3c57fca9da&secret=e23befb9fb7efe7f5ca14274cdffddcf";
		// $readJson = file_get_contents ( $url );
		// $tokenJson = json_decode ( $readJson );
		// $tokenJson->access_token;
		$gettoken = new SaeMysql ();
		$sql = "select token from `token` where `id` = 1";
		$data = $gettoken->getData ( $sql );
		return $data [0] [token];
	}
	public function GET_Api($tmp) {
		$url = $tmp [0];
		for($i = 0; $i < count ( $tmp ) - 1; $i ++) {
			if (i == 0)
				$url = $url . "&" . $tmp [$i + 1];
			else
				$url = $url . "&";
		}
		$readJson = file_get_contents ( $url );
		$json = json_decode ( $readJson );
		return $json;
	}
	public function POST_Api($url, $post_data) {
		$ch = curl_init ();
		curl_setopt ( $ch, CURLOPT_URL, $url );
		curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
		// 设置请求为post类型
		curl_setopt ( $ch, CURLOPT_POST, 1 );
		// 添加post数据到请求中
		curl_setopt ( $ch, CURLOPT_POSTFIELDS, $post_data );
		// 执行post请求，获得回复
		$response = curl_exec ( $ch );
		curl_close ( $ch );
		return $response;
	}
	public function sendtext($fromUsername, $toUsername, $time, $contentStr) {
		$textTpl = "<xml>
                            <ToUserName><![CDATA[%s]]></ToUserName>
                            <FromUserName><![CDATA[%s]]></FromUserName>
                            <CreateTime>%s</CreateTime>
                            <MsgType>text</MsgType>
                            <Content><![CDATA[%s]]></Content>
                            </xml>";
		$resultStr = sprintf ( $textTpl, $fromUsername, $toUsername, $time, $contentStr ); // 将XML格式中的变量分别赋值。注意sprintf函数
		echo $resultStr;
	}
	public function sendimage($fromUsername, $toUsername, $time, $mediaId) {
		$textTpl = "<xml>
                            <ToUserName><![CDATA[%s]]></ToUserName>
                            <FromUserName><![CDATA[%s]]></FromUserName>
                            <CreateTime>%s</CreateTime>
                            <MsgType>image</MsgType>
                            <Image><MediaId><![CDATA[%s]]></MediaId></Image>
                            </xml>";
		$resultStr = sprintf ( $textTpl, $fromUsername, $toUsername, $time, $mediaId ); // 将XML格式中的变量分别赋值。注意sprintf函数
		echo $resultStr;
	}
	public function sendvoice($fromUsername, $toUsername, $time, $mediaId) {
		$textTpl = "<xml>
                            <ToUserName><![CDATA[%s]]></ToUserName>
                            <FromUserName><![CDATA[%s]]></FromUserName>
                            <CreateTime>%s</CreateTime>
                            <MsgType>voice</MsgType>
                            <Voice><MediaId><![CDATA[%s]]></MediaId></Voice>
                            </xml>";
		$resultStr = sprintf ( $textTpl, $fromUsername, $toUsername, $time, $mediaId ); // 将XML格式中的变量分别赋值。注意sprintf函数
		echo $resultStr;
	}
	public function sendMsg() {
		$postStr = $GLOBALS ["HTTP_RAW_POST_DATA"];
		
		if (! empty ( $postStr )) {
			$postObj = simplexml_load_string ( $postStr, 'SimpleXMLElement', LIBXML_NOCDATA ); // 将postStr变量进行解析并赋予变量postObj。simplexml_load_string（）函数是php中一个解析XML的函数，SimpleXMLElement为新对象的类，LIBXML_NOCDATA表示将CDATA设置为文本节点，CDATA标签中的文本XML不进行解析
			$msgType = $postObj->MsgType;
			$fromUsername = $postObj->FromUserName;
			$toUsername = $postObj->ToUserName;
			$time = time ();
			$mysql = new SaeMysql ();
			if (! empty ( $postObj )) {
				switch ($msgType) {
					case "event" :
						$event = $postObj->Event;
						if ($event == "subscribe") {
							$sql = "insert into `user`(`openid`,`menu`) values('{$fromUsername}',0)";
							$mysql->runSql ( $sql );
							$this->sendtext ( $fromUsername, $toUsername, $time, "欢迎关注！" );
						} else if ($event == "unsubscribe") {
							$sql = "delete from `user` where `openid`='{$fromUsername}'";
							$mysql->runSql ( $sql );
						} else if ($event == "CLICK" && $postObj->EventKey == "admin") {
							$sql = "select * from `admin` where `openid` = '{$fromUsername}'";
							$data = $mysql->getData ( $sql );
							if (! empty ( $data )) {
								$sql = "select `openid` from `user`";
								$data1 = $mysql->getData ( $sql );
								switch ($data [0] [类型]) {
									case 图文消息 :
										for($i = 0; $i < count ( $data1 ); $i ++) {
											$this->POST_Api ( "https://api.weixin.qq.com/cgi-bin/message/mass/preview?access_token={$this->getToken()}", "{
   											\"touser\":\"{$data1[$i][openid]}\",
   											\"mpnews\":{\"media_id\":\"{$data[0][内容]}\"},
   											\"msgtype\":\"mpnews\"
											}" );
										}
										break;
									case 文本 :
										for($i = 0; $i < count ( $data1 ); $i ++) {
											$this->POST_Api ( "https://api.weixin.qq.com/cgi-bin/message/mass/preview?access_token={$this->getToken()}", "{
											\"touser\":\"{$data1[$i][openid]}\",
											\"text\":{\"content\":\"{$data[0][内容]}\"},
											\"msgtype\":\"text\"
											}" );
										}
										break;
									case 语音 :
										for($i = 0; $i < count ( $data1 ); $i ++) {
											$this->POST_Api ( "https://api.weixin.qq.com/cgi-bin/message/mass/preview?access_token={$this->getToken()}", "{
											\"touser\":\"{$data1[$i][openid]}\",
											\"voice\":{\"media_id\":\"{$data[0][内容]}\"},
											\"msgtype\":\"voice\"
											}" );
										}
										break;
									case 图片 :
										for($i = 0; $i < count ( $data1 ); $i ++) {
											$this->POST_Api ( "https://api.weixin.qq.com/cgi-bin/message/mass/preview?access_token={$this->getToken()}", "{
											\"touser\":\"{$data1[$i][openid]}\",
											\"image\":{\"media_id\":\"{$data[0][内容]}\"},
											\"msgtype\":\"image\"
											}" );
										}
										break;
									case 视频 :
										for($i = 0; $i < count ( $data1 ); $i ++) {
											$this->POST_Api ( "https://api.weixin.qq.com/cgi-bin/message/mass/preview?access_token={$this->getToken()}", "{
											\"touser\":\"{$data1[$i][openid]}\",
											\"mpvideo\":{\"media_id\":\"{$data[0][内容]}\"},
											\"msgtype\":\"mpvideo\"
											}" );
										}
										break;
								}
								$sql = "delete from `admin` where `openid`='{$fromUsername}'";
								$mysql->runSql ( $sql );
							}
						}
						break;
					case "image" :
						$this->sendimage ( $fromUsername, $toUsername, $time, $postObj->MediaId );
						break;
					case "text" :
						$content = $postObj->Content;
						$arr_str = explode ( "@", $postObj->Content );
						if ($arr_str [0] == "群发" && $arr_str [2] == "majunlaila") {
							$sql = "delete from `admin` where `openid` = '{$fromUsername}'";
							$mysql->runSql ( $sql );
							$sql = "insert into `admin`(`类型`,`openid`,`内容`) values('{$arr_str[1]}','{$fromUsername}','{$arr_str[3]}')";
							$mysql->runSql ( $sql );
							$content = "添加管理成功";
						}
						$this->sendtext ( $fromUsername, $toUsername, $time, $content );
						break;
					case "voice" :
						$this->sendvoice ( $fromUsername, $toUsername, $time, $postObj->MediaId );
						break;
					case "location" :
						$tmp = $this->GET_Api ( array (
								"http://apis.map.qq.com/ws/geocoder/v1/?",
								"location=" . $postObj->Location_X . "," . $postObj->Location_Y,
								"poi_options=page_size=10",
								"key=RCBBZ-JM7W6-I6KSN-EEVUZ-SCVYT-UTFIQ" 
						) );
						$contentStr = "您所查询地区的经度和纬度分别为：" . $postObj->Location_Y . "，" . $postObj->Location_X . "\n位置：" . $tmp->result->formatted_addresses->recommend . "\n知名区域：" . $tmp->result->address_reference->famous_area->title . "\n乡镇街道：" . $tmp->result->address_reference->town->title . "\n一级地标：" . $tmp->result->address_reference->landmark_l1->title . "\n二级地标：" . $tmp->result->address_reference->landmark_l2->title;
						$this->sendtext ( $fromUsername, $toUsername, $time, $contentStr );
						break;
				}
			} else {
				exit ();
			}
		} else {
			exit ();
		}
	}
}

?>
