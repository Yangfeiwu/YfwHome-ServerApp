<?php
/*
  设备与服务器数据包协议的业务逻辑
*/

use \GatewayWorker\Lib\Gateway;
use \Workerman\Lib\Timer;
require_once __DIR__ . '/../../vendor/autoload.php';

class DevPackageHandler
{
	public static function handlePackage($client_id, $package_data, $db)
	{

		//对设备的数据包进行分析并作出相应的动作
    	switch ($package_data['type']) {
            case 'Utils::PING':
                if(!empty($_SESSION['PID'])){
                    //debug
				 	LOG::OutLog("[DevPackHandler_Msg]:","Dev". $_SESSION['PID'] ." ,ping...\n"); //
    
                }else{
                    //debug               
				  LOG::OutLog("[DevPackHandler_Msg]:","Dev[unknown]: ,ping...\n"); //
                }
            break;
    		case Utils::CONNECT:
			   LOG::OutLog("[DevPackHandler_Msg]:","connect...\n"); //
    			self::checkConnect($client_id, $package_data);
				
    		break;
    		case Utils::DISCONNECT:
			 LOG::OutLog("[DevPackHandler_Msg]:","disconnect...\n"); //
      			if(Gateway::isOnline($client_id)){
       				if(!empty($_SESSION['PID'])){                        
                        //info
						LOG::OutLog("[DevPackHandler_Msg]:", "Dev[". $_SESSION['PID'] ."]: disconnecting...\n"); //                    
						
       				}
        			Gateway::closeClient($client_id);
      			}
     		break;
     		case Utils::DEVSTAT:
      			self::SendDevStat($client_id, $package_data);
					 LOG::OutLog("[DevPackHandler_Msg]:","devstat...\n"); //
      		break;
      		case Utils::DONE:
      			self::sendDone($client_id, $package_data, $db);
				 LOG::OutLog("[DevPackHandler_Msg]:","done...\n"); //
      		break;
      		case Utils::DEV_ERROR:
      			//self::sendUndone($client_id, $package_data);
				 LOG::OutLog("[DevPackHandler_Msg]:","error...\n"); //
      		break;
    	}
    }
	
	 /**
   	* 检查设备是否连接成功，并发送连接结果
   	* @param string $package_data
   	* @return bool
   	*/
	private static function checkConnect($client_id, $package_data)
 	{
        //info
     
	  LOG::OutLog("[DevPackHandler_Msg]:","checkConnect...\n"); //
    
    	if (!empty($_SESSION['PID'])) {
            //error
           
		   LOG::OutLog("[DevPackHandler_Msg]:","Server: PID is existence....\n"); //
            return false;
        }
     	//检查PID
    	if(empty($package_data['PID'])){               
        	//PID+password为空，创建登录失败反馈信息rejected
     		$new_package = array('length' => 1, 'type' => Utils::SERVER_FEEDBACK_FAIL);
      		Gateway::sendToCurrentClient($new_package);
            //error
           
			  LOG::OutLog("[DevPackHandler_Msg]:","Dev[unknown]: connect failed! PID is null.....\n"); //
     		Gateway::closeCurrentClient();
     		return false;
   		}
   		if (strlen($package_data['PID']) != 6){

   			$new_package = array('length' => 1, 'type' => Utils::SERVER_FEEDBACK_FAIL);
      		Gateway::sendToCurrentClient($new_package);
            //error
          
		    LOG::OutLog("[DevPackHandler_Msg]:","Dev[unknown]: Length of PID is not 6 byte.....\n"); //
     		return false;
   		}

     	//设备PID
   		$PID = trim($package_data['PID']);

      	//设备集session
   		$dev_sessions = Gateway::getAllClientSessions();
      	
      	//检查PID是否重复登录
   		foreach ($dev_sessions as $temp_client_id => $temp_sessions) 
   		{

    		if(!empty($temp_sessions['PID']) && $temp_sessions['PID'] == $PID){
          		//用户名重复，创建登录失败反馈信息
      			$new_package = array('length' => 1, 'type' => Utils::SERVER_FEEDBACK_FAIL);
      			Gateway::sendToCurrentClient($new_package);
                //error
            
			  LOG::OutLog("[DevPackHandler_Msg]:","Dev[". $PID ."]:connect failed! PID is repeated.....\n"); //
      			//Gateway::closeCurrentClient();

                //为防止设备1s多次重连
                Timer::add(5, array('\GatewayWorker\Lib\Gateway', 'closeClient'), array($client_id), false);
      			return false;
    		}
  		}

		//没有发现重名
  		//把PID、password到session中
  		$_SESSION['PID'] = $PID;
      	//TODO: 检查密码
  		//$_SESSION['password'] = $password;

  		//创建连接成功反馈信息
  		$new_package = array('length' => 1, 'type' => Utils::SERVER_FEEDBACK_SUCCESS);
      	Gateway::sendToCurrentClient($new_package);
  	
        //info
       
	     LOG::OutLog("[DevPackHandler_Msg]:","Dev[". $PID ."]:connect successful!.....\n"); //
		 
				//通知用户设备已上线
					$new_message = array('type' => 'UP_LINE', 'from' => 'SERVER', 'content' => $PID);					
    				Gateway::sendToUid($_SESSION['PID'], json_encode($new_message));
					
	   
		 
  		return true;
	}
	
	
	
	 /**
     * 向用户发送设备状态消息
     */
	private static function SendDevStat($client_id, $package_data)
	{
        //info
       // LoggerServer::log(Utils::INFO, "Bed[". $_SESSION['PID'] ."]:send posture info to users...\n");  
	     LOG::OutLog("[DevPackHandler_Msg]:","Dev[". $_SESSION['PID'] ."]:send devstat info to users!.....\n"); //
  		if(empty($_SESSION['PID'])){
            //error
          //  LoggerServer::log(Utils::ERROR, "Server: Bed session[PID] lost!\n");
		   LOG::OutLog("[DevPackHandler_Msg]:","Error:session[PID] lost!.....\n"); //
    		Gateway::closeClient($client_id);
    		return false;
  		}else{
		//	 LOG::OutLog("[SendDevStat]:","Dev[". $PID ."]:数据内容：".$package_data['data'].".....\n"); //
			
          	//向绑定的用户发送姿态信息
          	$new_message = array('type' => 'DevMsg', 'from' => 'SERVER', 'data' =>$package_data['data']);
    		Gateway::sendToUid($_SESSION['PID'], json_encode($new_message,JSON_UNESCAPED_UNICODE));
    		return true;
  		}
	}
	 /**
     * 向用户发送完成消息
     */
	private static function sendDone($client_id, $package_data)
	{
        //info
       // LoggerServer::log(Utils::INFO, "Bed[". $_SESSION['PID'] ."]:send posture info to users...\n");  
	     LOG::OutLog("[DevPackHandler_Msg]:","Dev[". $_SESSION['PID'] ."]:send devstat info to users!.....\n"); //
  		if(empty($_SESSION['PID'])){
            //error
          //  LoggerServer::log(Utils::ERROR, "Server: Bed session[PID] lost!\n");
		   LOG::OutLog("[DevPackHandler_Msg]:","Error:session[PID] lost!.....\n"); //
    		Gateway::closeClient($client_id);
    		return false;
  		}else{
		//	 LOG::OutLog("[SendDevStat]:","Dev[". $PID ."]:数据内容：".$package_data['data'].".....\n"); //
			
          	//向绑定的用户发送姿态信息
          	$new_message = array('type' => 'DONE', 'from' => 'DEV', 'data' =>$package_data['info']);
    		Gateway::sendToUid($_SESSION['PID'], json_encode($new_message,JSON_UNESCAPED_UNICODE));
    		return true;
  		}
	}
	

}


?>