<?php

/**
 * CSV操作类
 * @author    1049347793@qq.com
 * @version  
 */
namespace Org\Util;
class Csv{

	public function __construct(){

	}

	public function import_csv(){
		$filename = $_FILES['file']['tmp_name'];
		if (empty ($filename)) {
			echo '请选择要导入的CSV文件！';
			exit;
		}
		$handle = fopen($filename, 'r');
		$result = input_csv($handle); //解析csv
		$len_result = count($result);
		if($len_result==0){
			echo '没有任何数据！';
			exit;
		}
		for ($i = 1; $i < $len_result; $i++) { //循环获取各字段值
			$name = iconv('gb2312', 'utf-8', $result[$i][0]); //中文转码
			$sex = iconv('gb2312', 'utf-8', $result[$i][1]);
			$age = $result[$i][2];
			$data_values .= "('$name','$sex','$age'),";
		}
		$data_values = substr($data_values,0,-1); //去掉最后一个逗号
		fclose($handle); //关闭指针
		return $data_values;
	}

	public function export_csv($catalog,$ori_data){
		$str = $catalog."\n";
	    $str = iconv('utf-8','gb2312',$str);
	    while($row=mysql_fetch_array($ori_data)){
	        $name = iconv('utf-8','gb2312',$row['name']);
	        $sex = iconv('utf-8','gb2312',$row['sex']);
	    	$str .= $name.",".$sex.",".$row['age']."\n";
	    }
	    $filename = date('Ymd').'.csv';
	    export_csv($filename,$str);
	}

	protected function input_csv($handle) {
		$out = array ();
		$n = 0;
		while ($data = fgetcsv($handle, 10000)) {
			$num = count($data);
			for ($i = 0; $i < $num; $i++) {
				$out[$n][$i] = $data[$i];
			}
			$n++;
		}
		return $out;
	}

	protected function export_csv($filename,$data) {
	    header("Content-type:text/csv");
	    header("Content-Disposition:attachment;filename=".$filename);
	    header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
	    header('Expires:0');
	    header('Pragma:public');
	    echo $data;
	}
}	
	