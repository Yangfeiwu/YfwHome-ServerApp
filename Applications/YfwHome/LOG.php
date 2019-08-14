<?php
require_once __DIR__ . '/../../vendor/autoload.php';

/**
 * LOG class
 *
 * weChat服务消息接口.
 */
class LOG
{
//需要使用绝对地址,log文件路径

	/**
	 * @param string $label 消息标签
	 * @param string $outdata 要输出的消息
	 */
	public static function OutLog($label, $outdata)
	{
	 date_default_timezone_set("Asia/Shanghai");
	 file_put_contents(__DIR__ ."/logs/".date("Y-m-d").".txt",date("h:i:sa").$label.$outdata."\r\n", FILE_APPEND);

		//$tsr=file_exists(self::filepath);
		//return array($tsr, null);
	}

}


?>
