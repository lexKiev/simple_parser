<?php
//update articles set h1 = null,content = null, dt_parsed = null, tmp_uniq = null where dt_parsed is not null limit 100;
error_reporting(E_ALL);
define('BASE_URL', 'http://tehnoblog.org.ua');
define('IMG_FOLDER', __DIR__ . DIRECTORY_SEPARATOR . 'images');

require_once "db.class.php";
require_once "simple_html_dom.php";

$db = new DB('localhost', 'root', '', 'tehnoblog.org.ua');

$url = 'http://tehnoblog.org.ua/blogs';

if (isset($argv[1]) && $argv[1] == 'catalog') {
	parseCatalog($url);
} else {
	while ($article = $db->query('SELECT * FROM articles WHERE dt_parsed IS NULL AND tmp_uniq IS NULL ')) {
		$hash = $db->escape(md5(time() . rand(1, 50000) . time()));
		$sql = "update articles
	set tmp_uniq = '{$hash}'
	where dt_parsed is null
	and tmp_uniq is null
	limit 10";
		$db->query($sql);
		
		while ($article = $db->query("SELECT * FROM articles WHERE dt_parsed IS NULL AND tmp_uniq = '{$hash}' LIMIT 1")) {
			
			$id = $article[0]['id'];
			echo 'Parsing link #' . $id . ' URL: ' . $article[0]['article_url'];
			
			$parsedArticle = parseArticle($article[0]['article_url']);
			
			$articleTitle = $db->escape($parsedArticle['articleTitle']);
			$articleContent = $db->escape($parsedArticle['articleContent']);
			
			
			$sql = "update articles
				set  h1 = '{$articleTitle}',
				content = '{$articleContent}',
				dt_parsed = NOW()
				where id = {$id}
				limit 1";
			
			$db->query($sql);
			
		}
	}
	exit("all data parsed");
}

function parseCatalog($url)
{
	global $db;
	
	$html = file_get_html($url);
	
	foreach ($html->find('.hkm-readmore a') as $item) {
		if ($item->innertext == "Далее...") {
			echo BASE_URL . $item->href . PHP_EOL;
			$articleUrl = BASE_URL . $item->href . PHP_EOL;
			
			$db->escape($articleUrl);
			
			$sql = "insert ignore into articles
              set article_url = '{$articleUrl}'";
			$db->query($sql);
		}
	}
	
	foreach ($html->find('ul.pagination a') as $item) {
		if ($item->title == "Следующая") {
			echo "next " . BASE_URL . $item->href . PHP_EOL;
			parseCatalog(BASE_URL . $item->href);
		} else {
			exit('done');
		}
	}
}

function parseArticle($url)
{
	$article = file_get_html($url);
	$articleTitle = $article->find('div.hkm_contentpaneopen h2 a', 0)->innertext;
	$articleContent = $article->find('div.hkm_article-content', 0)->innertext;
	$result = compact("articleTitle", "articleContent");
	parseImages($article, $url);
	return $result;
}

function parseImages($article, $url)
{
	global $db;
	foreach ($article->find('div.hkm_article-content img') as $item) {
		$prefix = '';
		$imgName = explode('/', $item->src);
		if ($imgName[0] != 'http:' && $imgName[0] != 'https:') {
			$prefix = BASE_URL;
		}
		
		$imgName = end($imgName);
		$imgNewName = time() . rand(1, 65536) . $imgName;
		$imgLink = $item->src;
		file_put_contents(IMG_FOLDER . DIRECTORY_SEPARATOR . $imgNewName, file_get_contents($prefix . $imgLink));
		
		$db->escape($url);
		$db->escape($imgName);
		$db->escape($imgNewName);
		$sql = "insert ignore into images
				set article_url = '{$url}',
				img_origin_name = '{$imgName}',
				img_new_name = '{$imgNewName}'";
		$db->query($sql);
		
	}
	
}