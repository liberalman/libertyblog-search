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
require_once 'sdk/php/lib/XS.php';
error_reporting(E_ALL ^ E_NOTICE);

$xs = new XS('libertyblog'); // 建立 XS 对象，项目名称为：libertyblog
$INDEX = $xs->index; // 创建索引对象

$method = $_SERVER['REQUEST_METHOD'];
try {
    if ($method == 'GET')
    {
        $cmd = $_GET['cmd'];
        switch ($cmd)
        {
        case 'reindex':
            // 清空索引，慎用
            $INDEX->clean();

            // http://php.net/manual/en/mongodb-driver-manager.executequery.php
            $manager = new MongoDB\Driver\Manager("mongodb://api.hicool.top:27017");
            $page_size = 10;
            $total = 0;
            
            // 查询记录总的数量
            $filter = array();
            $commands = [ 'count' => 'articles', 'query' => $filter]; // collection名称是 articles
            $command = new \MongoDB\Driver\Command($commands);
            $cursor = $manager->executeCommand('libertyblog-dev', $command);
            $info = $cursor->toArray();
            $total = $info[0]->n;
            echo 'total:'.$total.'</br>';

            // 轮询分页数据
            for ($page = 0; $page < $total;) {
                $options = array(
                    /* Only return the following fields in the matching documents */
                    "projection" => array("title" => 1,"description" => 1, "content" => 1),
                    "skip" => $page,
                    "limit" => $page_size,
                );
                $query = new MongoDB\Driver\Query($filter, $options);
                $cursor = $manager->executeQuery('libertyblog-dev.articles', $query);
                $i = 0;
                foreach ($cursor as $document) {
                    //print_r($document);
                    $doc = (array)$document;
                    $data = array(
                        '_id' => $doc['_id'], // 此字段为主键，必须指定
                        'title' => $doc['title'],
                        'content' => $doc['content'],
                        'description' => $doc['description'],
                        'chrono' => time()
                    );
                    $doc = new XSDocument; // 创建文档对象
                    $doc->setFields($data);
                    $INDEX->add($doc); // 添加到索引数据库中
                    echo 'success '.($page + $i).': '.$doc['_id'].' '.$doc['title'].'</br>';
                    $i++;
                }

                $page = $page + $page_size;
            }
            break;
        default:
            echo 'not found this cmd : '.$cmd;
        }
    }
    else if ($method == 'POST')
    {
        header('Access-Control-Allow-Origin:*');//注意！跨域要加这个头
        $cmd = $_POST['cmd'];
        $data = array(
            '_id' => $_POST['_id'], // 此字段为主键，必须指定
            'title' => $_POST['title'],
            'content' => $_POST['content'],
            'description' => $_POST['description'],
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
        $id = pathinfo($_SERVER['REQUEST_URI'], PATHINFO_BASENAME) ;
        $INDEX->del($id);
        echo '{"result":0,"_id":'.$id.'}';
    }
    else
    {
        $logger->error('unknown http method. url: ' . $_SERVER['REQUEST_URI']);
    }
} catch (XSException $e) {
    $error = strval($e);
    echo '{"result":"'.$error.'"}';
}

