<!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo $oe; ?>" />
<meta name="googlebot" content="index,noarchive,nofollow,noodp" />
<meta name="robots" content="index,nofollow,noarchive,noodp" />
<title><?php if (!empty($q)) echo "搜索：" . strip_tags($q) . " - "; ?>Libertyblog 搜索 - Powered by liberalman</title>
<meta http-equiv="keywords" content="Fulltext Search Engine Libertyblog" />
<meta http-equiv="description" content="Fulltext Search for Libertyblog, Powered by liberalman " />
<link rel="stylesheet" type="text/css" href="https://apps.bdimg.com/libs/bootstrap/2.3.1/css/bootstrap.min.css"/>
<link rel="stylesheet" type="text/css" href="css/style.css"/>
<link rel="stylesheet" href="https://apps.bdimg.com/libs/jqueryui/1.8.16/themes/base/jquery-ui.css" type="text/css" media="all" />
<!--[if lt IE 9]>
<script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script>
<![endif]-->
<!--[if lte IE 6]>
<link rel="stylesheet" type="text/css" href="css/bootstrap-ie6.css" />
<link rel="stylesheet" type="text/css" href="css/ie.css" />
<![endif]-->
</head>
<!-- search.tpl Libertyblog 搜索模板 -->	
<body>
<div class="container">
  <div class="row">
	<!-- search form -->
    <div class="span12">
      <h1><a href="<?php echo $_SERVER['SCRIPT_NAME']; ?>"><img src="img/logo.png" /></a></h1>
      <form class="form-search" id="q-form" method="get">
        <div class="input-append" id="q-input">
          <input type="text" class="span6 search-query" name="q" title="输入任意关键词皆可搜索" value="<?php echo htmlspecialchars($q); ?>">
          <button type="submit" class="btn">搜索</button>
        </div>
        <div class="condition" id="q-options">
          <label class="radio inline"><input type="radio" name="f" value="title" <?php echo $f_title; ?> />Title</label>
          <label class="radio inline">
            <input type="radio" name="f" value="_all" <?php echo $f__all; ?> />全文
          </label>
          <label class="checkbox inline">
            <input type="checkbox" name="m" value="yes" <?php echo $m_check; ?> />模糊搜索 
          </label>
          <label class="checkbox inline">
            <input type="checkbox" name="syn" value="yes" <?php echo $syn_check; ?> />同义词
          </label>
          按
          <select name="s" size="1">
            <option value="relevance">相关性</option>
			
          </select>
          排序
		</div>
      </form>
    </div>

    <!-- begin search result -->
    <?php if (!empty($q)): ?>
    <div class="span12">
      <!-- neck bar -->
      <?php if (!empty($error)): ?>
      <p class="text-error"><strong>错误：</strong><?php echo $error; ?></p>
	  <?php else: ?>
	  <p class="result">大约有<b><?php echo number_format($count); ?></b>项符合查询结果，库内数据总量为<b><?php echo number_format($total); ?></b>项。（搜索耗时：<?php printf('%.4f', $search_cost); ?>秒） [<a href="<?php echo "$bu&o=$o&n=$n&xml=yes"; ?>" target="_blank">XML</a>]</p>
      <?php endif; ?>
      
      <!-- fixed query -->
      <?php if (count($corrected) > 0): ?>
      <div class="link corrected">
        <h4>您是不是要找：</h4>
        <p>
          <?php foreach ($corrected as $word): ?>
          <span><a href="<?php echo $_SERVER['SCRIPT_NAME'] . '?q=' . urlencode($word); ?>" class="text-error"><?php echo $word; ?></a></span>
          <?php endforeach; ?>
        </p>
      </div>
      <?php endif; ?>
      
      <!-- empty result -->
      <?php if ($count === 0 && empty($error)): ?>
      <div class="demo-error">
        <p class="text-error">找不到和 <em><?php echo htmlspecialchars($q); ?></em> 相符的内容或信息。</p>
        <h5>建议您：</h5>
        <ul>
          <li>1.请检查输入字词有无错误。</li>
          <li>2.请换用另外的查询字词。</li>
          <li>3.请改用较短、较为常见的字词。</li>
        </ul>
      </div>
      <?php endif; ?>
      
      <!-- result doc list -->
      <dl class="result-list">
        <?php foreach ($docs as $doc): ?>
        <dt>
          <a target="_blank" href="https://www.hicool.top/article/<?php echo $doc->_id; ?>"><h4><?php echo $doc->rank(); ?>. <?php echo $search->highlight(htmlspecialchars($doc->title)); ?> <small>[<?php echo $doc->percent(); ?>%]</small></h4></a>          
        </dt>
        <dd>
          <p class="field-info text-error">
            <span><strong>Description:</strong><?php echo htmlspecialchars($doc->description); ?></span>
          </p>
          <p><?php echo $search->highlight(htmlspecialchars($doc->content)); ?></p>
        </dd>
        <?php endforeach; ?>
      </dl>
      
      <!-- pager -->
      <?php if (!empty($pager)): ?>
      <div class="pagination pagination-centered">
        <ul>
          <!--<li><a href="#">Prev</a></li>-->
          <?php echo $pager; ?>
          <!--<li><a href="#">Next</a></li>-->
        </ul>
      </div>
      <?php endif; ?>

    </div>
    <?php endif; ?>
    <!-- end search result -->
  </div>
</div>

<!-- hot search -->
<?php if (count($hot) > 0): ?>
<section class="link">
  <div class="container">
    <h4>热门搜索:</h4>
    <p>
      <?php foreach($hot as $word => $freq): ?>
      <span><a href="<?php echo $_SERVER['SCRIPT_NAME'] . '?q=' . urlencode($word); ?>"><?php echo $word; ?></a></span>
      <?php endforeach; ?>	
    </p>
  </div>
</section>
<?php endif; ?>

<!-- related query -->
<?php if (count($related) > 0): ?>
<section class="link">
  <div class="container">
    <h4>相关搜索:</h4>
    <p>
      <?php foreach ($related as $word): ?>
      <span><a href="<?php echo $_SERVER['SCRIPT_NAME'] . '?q=' . urlencode($word); ?>"><?php echo $word; ?></a></span>
      <?php endforeach; ?>	
	</p>
  </div>
</section>
<?php endif; ?>

<!-- footer -->
<footer>
  <div class="container">
    <p>(C)opyright 2016 - Libertyblog search - 页面处理总时间：<?php printf('%.4f', $total_cost); ?>秒<br>
      Powered by <a href="https://www.hicool.top/" target="_blank" title="Libertyblog 博客">libertyblog</a></p>
  </div>
</footer>
<script type="text/javascript" src="https://apps.bdimg.com/libs/jquery/1.6.2/jquery.min.js"></script>
<script type="text/javascript" src="https://apps.bdimg.com/libs/jqueryui/1.8.16/jquery-ui.min.js"></script>
<script type="text/javascript">
$(function(){
	// input tips
	$('#q-input .search-query').focus(function(){
		if ($(this).val() == $(this).attr('title')) {
			$(this).val('').removeClass('tips');
		}
	}).blur(function(){
		if ($(this).val() == '' || $(this).val() == $(this).attr('title')) {
			$(this).addClass('tips').val($(this).attr('title'));
		}
	}).blur().autocomplete({
		'source':'suggest.php',
		'select':function(ev,ui) {
			$('#q-input .search-query').val(ui.item.label);
			$('#q-form').submit();
		}
	});
	// submit check
	$('#q-form').submit(function(){
		var $input = $('#q-input .search-query');
		if ($input.val() == $input.attr('title')) {
			alert('请先输入关键词');
			$input.focus();
			return false;
		}
	});	
});	
</script>
</body>
</html>
