<?php
/*
**********************************************
	Author:	quincyqiang
	Date:	2017/6/22

	usage:
			weibo.php?act=add&content=xxx	添加一条
				返回：{error:0, id: 新添加内容的ID, time: 添加时间}
			
			weibo.php?act=get_page_count	获取页数
				返回：{count:页数}
			
			weibo.php?act=get&page=1		获取一页数据
				返回：[{id: ID, content: "内容", time: 时间戳, acc: 顶次数, ref: 踩次数}, {...}, ...]
			
			weibo.php?act=acc&id=12			顶某一条数据
				返回：{error:0}
			
			weibo.php?act=ref&id=12			踩某一条数据
				返回：{error:0}
	
	注意：	服务器所返回的时间戳都是秒（JS是毫秒）
**********************************************
*/
$conn=mysqli_connect('localhost','root','','test','3306');
mysqli_query($conn,"set names 'utf8'");
$sql= "CREATE TABLE  weibo (
ID INT(6) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
content TEXT NOT NULL ,
time VARCHAR(30) NOT NULL ,
acc INT(8) NOT NULL ,
ref INT(8) NOT NULL
) CHARACTER SET utf8 COLLATE utf8_general_ci";

mysqli_query($conn,$sql);
//接口开始
$act=$_GET['act'];
$PAGE_SIZE=3;
switch ($act){
	case 'add':
		$content=urldecode($_GET['content']);//将编码的中文还原
		$time=date("Y-m-d H:i:s",time());//将时间戳变换成所需格式
		$content=str_replace("\n", "", $content);
		//将消息插入数据库
		$sql="INSERT INTO weibo (ID, content, time, acc, ref) VALUES(0, '{$content}', '{$time}', 0, 0)";
		mysqli_query($conn,$sql);
		//返回插入的数据
		$res=mysqli_query($conn,'SELECT LAST_INSERT_ID()');
		$row=mysqli_fetch_array($res,MYSQLI_BOTH);
		$id=(int)$row[0];
		$data = array('error'=>0,'id'=>$id,'time'=>$time); //【关联数组】
		echo json_encode($data);
		break;
	case 'get':
		$page=(int)$_GET['page'];
		if($page<1)$page=1;
		$s=($page-1)*$PAGE_SIZE;
		$sql="SELECT ID, content, time, acc, ref FROM weibo ORDER BY time DESC LIMIT {$s}, {$PAGE_SIZE}";
		$res=mysqli_query($conn,$sql);
		$aResult=array();//存放数据
		while($row=mysqli_fetch_array($res,MYSQLI_BOTH))
		{
			$arr=array();
			array_push($arr, '"id":'.$row[0]);
			array_push($arr, '"content":"'.$row[1].'"');
			array_push($arr, '"time":"'.$row[2].'"');
			array_push($arr, '"acc":'.$row[3]);
			array_push($arr, '"ref":'.$row[4]);
			array_push($aResult, implode(',', $arr));//implode() 函数返回由数组元素组合成的字符串。
		}
		if(count($aResult)>0)
		{
			echo '[{'.implode('},{', $aResult).'}]';
		}
		else
		{
			echo '[]';
		}
		break;
	case 'acc':
		$id=(int)$_GET['id'];
		$res=mysqli_query($conn,"SELECT acc FROM weibo WHERE ID={$id}");
		$row=mysqli_fetch_array($res,MYSQLI_BOTH);
		$old=(int)$row[0]+1;
		$sql="UPDATE weibo SET acc={$old} WHERE ID={$id}";
		mysqli_query($conn,$sql);
		echo '{"error":0}';
		break;
	case 'ref':
		$id=(int)$_GET['id'];
		$res=mysqli_query($conn,"SELECT ref FROM weibo WHERE ID={$id}");
		$row=mysqli_fetch_array($res,MYSQLI_BOTH);
		$old=(int)$row[0]+1;
		$sql="UPDATE weibo SET ref={$old} WHERE ID={$id}";
		mysqli_query($conn,$sql);
		echo '{"error":0}';
		break;
	case 'del':
		$id=(int)$_GET['id'];
		$sql="DELETE FROM weibo WHERE ID={$id}";
		mysqli_query($conn,$sql);
		echo '{"error":0}';
		break;
}
		
?>
