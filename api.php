<?php
/**
 * search.php 
 * Libertyblog 搜索项目入口文件
 * 
 * 该文件由 liberalman
 * 创建时间：2018-02-23 08:57:29
 * 默认编码：UTF-8
 */
// 加载 XS 入口文件
require_once '/usr/local/xunsearch/sdk/php/lib/XS.php';
error_reporting(E_ALL ^ E_NOTICE);

//$xs = new XS('/usr/local/xunsearch/sdk/php/app/article.ini');

$xs = new XS('libertyblog'); // 建立 XS 对象，项目名称为：article
$INDEX = $xs->index; // 创建索引对象

$method = $_SERVER['REQUEST_METHOD'];
try {
    if ($method == 'GET')
    {
        $cmd = $_SERVER['QUERY_STRING'];
        switch ($cmd)
        {
        case 'query':
            break;
        default:
            echo 'not found this cmd : '.$cmd;
        }
    }
    else if ($method == 'POST')
    {
        // curl -l -H "Content-type: application/json" -X POST -d '{"cmd":"update"}' localhost:8080/libertyblog/api.php
        // curl -X POST -d 'cmd=add&id=123456&title=not mydark&content=this is not mydark&digest=aaaa' localhost:8080/libertyblog/api.php
        // curl -X POST -d 'cmd=update&id=123456&title=mydark&content=this is mydark&digest=dddd' localhost:8080/libertyblog/api.php
        $cmd = $_POST['cmd'];
        $data = array(
            'id' => $_POST['id'], // 此字段为主键，必须指定
            'title' => $_POST['title'],
            'content' => $_POST['content'],
            'digest' => $_POST['digest'],
            'chrono' => time()
        );
        switch ($cmd)
        {
        case 'add':
            $doc = new XSDocument; // 创建文档对象
            $doc->setFields($data);
            $INDEX->add($doc); // 添加到索引数据库中
            echo '{"result":0}';
            break;
        case 'update':
            $doc = new XSDocument; // 创建文档对象
            $doc->setFields($data);
            $INDEX->update($doc); // 添加到索引数据库中
            echo '{"result":0}';
            break;
        default:
            echo 'not found this cmd '.$cmd;
        }
    }
    else if ($method == 'DELETE')
    {
        // curl -X DELETE localhost:8080/libertyblog/api.php/123456
        $id = pathinfo($_SERVER['REQUEST_URI'], PATHINFO_BASENAME) ;
        $INDEX->del($id);
        echo '{"result":0,"id":'.$id.'}';
    }
    else
    {
        $logger->error('unknown http method. url: ' . $_SERVER['REQUEST_URI']);
    }
} catch (XSException $e) {
    $error = strval($e);
    echo '{"result":"'.$error.'"}';
}

function update($data)
{
}

function add($data)
{
}

function delete($id)
{
}

//获取域名或主机地址 
//echo $_SERVER['HTTP_HOST']."<br>"; #localhost

//获取网页地址 
//echo $_SERVER['PHP_SELF']."<br>"; #/blog/testurl.php

//获取网址参数 
//echo $_SERVER["QUERY_STRING"]."<br>"; #id=5

//获取用户代理 
//echo $_SERVER['HTTP_REFERER']."<br>"; 

//获取完整的url
//echo 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']."<br/>";
//echo 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING']."<br/>";


//包含端口号的完整url
//echo 'http://'.$_SERVER['SERVER_NAME'].':'.$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"]."<br/>"; 


//只取路径
//$url='http://'.$_SERVER['SERVER_NAME'].$_SERVER["REQUEST_URI"]."<br/>"; 
//echo dirname($url);

