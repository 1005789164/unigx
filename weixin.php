<?php 
/** 
  * wechat php test 
  */  
  
//define your token  
define("TOKEN", "mj123");  
$wechatObj = new wechatCallbackapiTest();//将11行的class类实例化  
$wechatObj->valid();//responseMsg();//使用-》访问类中valid方法，用来验证开发模式  
//11--23行代码为签名及接口验证。  
class wechatCallbackapiTest  
{  
    public function valid()//验证接口的方法  
    {  
        $echoStr = $_GET["echostr"];//从微信用户端获取一个随机字符赋予变量echostr  
  
        //valid signature , option访问地61行的checkSignature签名验证方法，如果签名一致，输出变量echostr，完整验证配置接口的操作  
        if($this->checkSignature()){  
            echo $echoStr;  
            exit;  
        }  
    }  
    //公有的responseMsg的方法，是我们回复微信的关键。以后的章节修改代码就是修改这个。  
    public function responseMsg()  
    {  
        //get post data, May be due to the different environments  
        $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];//将用户端放松的数据保存到变量postStr中，由于微信端发送的都是xml，使用postStr无法解析，故使用$GLOBALS["HTTP_RAW_POST_DATA"]获取  
  
        //extract post data如果用户端数据不为空，执行30-55否则56-58  
        if (!empty($postStr)){  
                  
                $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);//将postStr变量进行解析并赋予变量postObj。simplexml_load_string（）函数是php中一个解析XML的函数，SimpleXMLElement为新对象的类，LIBXML_NOCDATA表示将CDATA设置为文本节点，CDATA标签中的文本XML不进行解析  
                $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=wx6e585333cb37b6d7&secret=a7e1469ed486172888e42eea02252ed9";
            	$readJson = file_get_contents($url);
            	$tokenJson = json_decode($readJson);
            	$msgType = $postObj->MsgType;
            	$fromUsername = $postObj->FromUserName;//将微信用户端的用户名赋予变量FromUserName  
                $toUsername = $postObj->ToUserName;//将你的微信公众账号ID赋予变量ToUserName  
                //$keyword = trim($postObj->Content);//将用户微信发来的文本内容去掉空格后赋予变量keyword  
            	$keyword = $postObj->MsgId;
                $time = time();//将系统时间赋予变量time  
                //构建XML格式的文本赋予变量textTpl，注意XML格式为微信内容固定格式，详见文档  
                
                if(!empty( $keyword ))//如果用户端微信发来的文本内容不为空，执行46--51否则52--53  
                {  
                    if($msgType=="image")//如果用户端微信发来的文本内容不为空，执行46--51否则52--53  
                	{ 
                        $textTpl = "<xml>  
                            <ToUserName><![CDATA[%s]]></ToUserName>  
                            <FromUserName><![CDATA[%s]]></FromUserName>  
                            <CreateTime>%s</CreateTime>  
                            <MsgType><![CDATA[%s]]></MsgType>  
                            <Image>
                            <MediaId><![CDATA[%s]]>
                            </MediaId>
                            </Image>
                            <FuncFlag>0</FuncFlag> 
                            </xml>";
                        //$msgType = "text";//回复文本信息类型为text型，变量类型为msgType  
                        //$contentStr = "Welcome to wechat world!";//我们进行文本输入的内容，变量名为contentStr，如果你要更改回复信息，就在这儿  
                        $contentStr = $postObj->MediaId;
                        $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType,$contentStr);//将XML格式中的变量分别赋值。注意sprintf函数  
                        echo $resultStr;//输出回复信息，即发送微信  
                    }
                }else{  
                    echo "Input something...";//不发送到微信端，只是测试使用  
                }  
  
        }else {  
            echo "";//回复为空，无意义，调试用  
            exit;  
        }  
    }  
    //签名验证程序    ，checkSignature被18行调用。官方加密、校验流程：将token，timestamp，nonce这三个参数进行字典序排序，然后将这三个参数字符串拼接成一个字符串惊喜shal加密，开发者获得加密后的字符串可以与signature对比，表示该请求来源于微信。  
    private function checkSignature()  
    {  
        $signature = $_GET["signature"];//从用户端获取签名赋予变量signature  
        $timestamp = $_GET["timestamp"];//从用户端获取时间戳赋予变量timestamp  
        $nonce = $_GET["nonce"];    //从用户端获取随机数赋予变量nonce  
                  
        $token = TOKEN;//将常量token赋予变量token  
        $tmpArr = array($token, $timestamp, $nonce);//简历数组变量tmpArr  
        sort($tmpArr, SORT_STRING);//新建排序  
        $tmpStr = implode( $tmpArr );//字典排序  
        $tmpStr = sha1( $tmpStr );//shal加密  
        //tmpStr与signature值相同，返回真，否则返回假  
        if( $tmpStr == $signature ){  
            return true;  
        }else{  
            return false;  
        }  
    }  
}  

?>
