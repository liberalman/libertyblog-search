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


///////////////////////// search /////////////////////////
//
// 支持的 GET 参数列表
// q: 查询语句
// m: 开启模糊搜索，其值为 yes/no
// f: 只搜索某个字段，其值为字段名称，要求该字段的索引方式为 self/both
// s: 排序字段名称及方式，其值形式为：xxx_ASC 或 xxx_DESC
// p: 显示第几页，每页数量为 XSSearch::PAGE_SIZE 即 10 条
// ie: 查询语句编码，默认为 UTF-8
// oe: 输出编码，默认为 UTF-8
// xml: 是否将搜索结果以 XML 格式输出，其值为 yes/no
//
// variables
$eu = '';
$__ = array('q', 'm', 'f', 's', 'p', 'ie', 'oe', 'syn', 'xml');
foreach ($__ as $_) {
        $$_ = isset($_GET[$_]) ? $_GET[$_] : '';
}

// input encoding
if (!empty($ie) && !empty($q) && strcasecmp($ie, 'UTF-8')) {
        $q = XS::convert($q, $cs, $ie);
        $eu .= '&ie=' . $ie;
}

// output encoding
if (!empty($oe) && strcasecmp($oe, 'UTF-8')) {

        function xs_output_encoding($buf)
        {
                return XS::convert($buf, $GLOBALS['oe'], 'UTF-8');
        }
        ob_start('xs_output_encoding');
        $eu .= '&oe=' . $oe;
} else {
        $oe = 'UTF-8';
}

// recheck request parameters
$q = get_magic_quotes_gpc() ? stripslashes($q) : $q;
$f = empty($f) ? '_all' : $f;
${'m_check'} = ($m == 'yes' ? ' checked' : '');
${'syn_check'} = ($syn == 'yes' ? ' checked' : '');
${'f_' . $f} = ' checked';
${'s_' . $s} = ' selected';

// base url
$bu = $_SERVER['SCRIPT_NAME'] . '?q=' . urlencode($q) . '&m=' . $m . '&f=' . $f . '&s=' . $s . $eu;

// other variable maybe used in tpl
$count = $total = $search_cost = 0;
$docs = $related = $corrected = $hot = array();
$error = $pager = '';
$total_begin = microtime(true);
///////////////////////// search /////////////////////////

$xs = new XS('libertyblog'); // 建立 XS 对象，项目名称为：libertyblog
$INDEX = $xs->index; // 创建索引对象

$method = $_SERVER['REQUEST_METHOD'];
try {
    if ($method == 'GET')
    {
        $cmd = $_GET['cmd'];
        switch ($cmd)
        {
        case 'search':
            header('Content-type:text/json');
            $q = $_GET['key'];
            $current_page = max(1, intval($_GET['current_page']));
            if (empty($_GET['page_size'])) {
              $page_size = XSSearch::PAGE_SIZE;
            } else {
              $page_size = max(0, intval($_GET['page_size']));
            }
            
            $search = $xs->search;
            $search->setCharset('UTF-8');

            if (empty($q)) {
                // just show hot query
                $hot = $search->getHotQuery();
                echo '{}';
            } else {
                // fuzzy search
                $search->setFuzzy($m === 'yes');

                // synonym search
                $search->setAutoSynonyms($syn === 'yes');

                // set query
                if (!empty($f) && $f != '_all') {
                    $search->setQuery($f . ':(' . $q . ')');
                } else {
                    $search->setQuery($q);
                }

                // set sort
                if (($pos = strrpos($s, '_')) !== false) {
                    $sf = substr($s, 0, $pos);
                    $st = substr($s, $pos + 1);
                    $search->setSort($sf, $st === 'ASC');
                }

                // set offset, limit
                //$p = max(1, intval($p));
                $p = $current_page;
                //$n = XSSearch::PAGE_SIZE;
                $n = $page_size;
                $search->setLimit($n, ($p - 1) * $n);

                // get the result
                $search_begin = microtime(true);
                $docs = $search->search();
                $search_cost = microtime(true) - $search_begin;

                // get other result
                $count = $search->getLastCount();
                $total = $search->getDbTotal();

                if ($xml !== 'yes') {
                    // try to corrected, if resul too few
                    if ($count < 1 || $count < ceil(0.001 * $total)) {
                        $corrected = $search->getCorrectedQuery();
                    }
                    // get related query
                    $related = $search->getRelatedQuery();
                }

                // gen pager
                if ($count > $n) {
                    $pb = max($p - 5, 1);
                    $pe = min($pb + 10, ceil($count / $n) + 1);
                    $pager = '';
                    do {
                        $pager .= ($pb == $p) ? '<li class="disabled"><a>' . $p . '</a></li>' : '<li><a href="' . $bu . '&p=' . $pb . '">' . $pb . '</a></li>';
                    } while (++$pb < $pe);
                }
                $total_cost = microtime(true) - $total_begin;
                $array_2 = array(); // 多维数组
                foreach ($docs as $doc) {
                    $array = array(); //一维数组
                    $array['_id'] = $doc->_id;
                    //$array['url'] = 'http://www.hicool.top/#/article/'.$doc->_id; 
                    $array['rank'] = $doc->rank(); 
                    //$array['title']=$search->highlight(htmlspecialchars($doc->title)); 
                    $array['title'] = $doc->title;
                    $array['percent']=$doc->percent(); 
                    //$array['description']=htmlspecialchars($doc->description);
                    $array['description'] = $doc->description;
                    //$array['contentt']=$search->highlight(htmlspecialchars($doc->content));
                    //$array['contentt'] = $doc->content;
                    array_push($array_2, $array);
                }
                echo '{"list": '.json_encode($array_2).', "count":'.$count.', "search_cost":'.$search_cost.',"total_cost":'.$total_cost.'}';
            }
            break;
        case 'reindex': // 由于PHP中安装mongodb.so后因为新版本认证算法连接不上去，所以此项暂时不能用。使用脚本 /data/libertyblog-web/scripts/mysql2mongo/reindex_search.js
            // 清空索引，慎用
            $INDEX->clean();

            // http://php.net/manual/en/mongodb-driver-manager.executequery.php
            $manager = new MongoDB\Driver\Manager("mongodb://username:password@host:port/dbname"); // PHP7
            var_dump($manager);
            $page_size = 10;
            $total = 0;
            
            // 查询记录总的数量
            $filter = array();
            $commands = [ 'count' => 'articles', 'query' => $filter]; // collection名称是 articles
            $command = new \MongoDB\Driver\Command($commands);
            $cursor = $manager->executeCommand($dbname, $command);
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
                $cursor = $manager->executeQuery('articles', $query);
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
            echo 'reindex end';
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
    echo '{"result":"-1","message::"'.$error.'"}';
} catch (Exception $e) {
    $error = $e->getMessage();
    echo '{"result":"-2","message::"'.$error.'"}';
}
?>
