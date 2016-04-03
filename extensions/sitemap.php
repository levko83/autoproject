<?php

class YmlController {
	
	var $yml;
	var $db;
	
	function __construct($db){
		$this->db = $db;
	}
	
	public function getLevelsBack($id) {
		$ids = $this->getCatName($id);
		if ($ids['id']){
			$this->tree []= (int)$ids['id'];
			if (!empty($ids['parent'])) {
				YmlController::getLevelsBack($ids['parent']);
			}
		}
	}
	
	public function getCatName($id){
		$sql = "select id,name,parent from ".DB_PREFIX."cat where id='".(int)$id."' AND is_active='1';";
		return $this->db->get($sql);
	}
	
	
}
?>
<?php define('URL','http://'.$_SERVER['HTTP_HOST']);?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title></title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
</head>
<body>
<?php
$time = explode(" ",microtime());
$time = $time[1];
// include class
include 'sitemap-generator/SitemapGenerator.php';
// create object
$sitemap = new SitemapGenerator(URL."/", "./../");
// will create also compressed (gzipped) sitemap
$sitemap->createGZipFile = true;
// determine how many urls should be put into one file
$sitemap->maxURLsPerSitemap = 50000;
// sitemap file name
$sitemap->sitemapFileName = "sitemap.xml";
// sitemap index file name
$sitemap->sitemapIndexFileName = "sitemap.xml";
// robots file name
$sitemap->robotsFileName = "robots.txt";        

require('../core/classes/register.class.php');
require('../application/config/db.cfg.php');
require('../application/config/inc.cfg.php');
require('../application/library/helpers/alias_view.helper.php');
require('../core/classes/orm_condition.class.php');
require('../core/classes/collection.class.php');
require('../core/classes/db.class.php');

$db = new Db();
$YmlController = new YmlController($db);

$sitemap->addUrl(URL."/", date('c'), 'weekly');

$sitemap->addUrl(URL."/catalog/", date('c'), 'weekly');

/* *********************** */

$sql = "SELECT * FROM ".DB_PREFIX."manufacturers WHERE MY_ACTIVE=1;";
$data = $db->query($sql);
foreach ($data as $dd){
	$sitemap->addUrl(URL."/auto/".AliasViewHelper::doTraslit($dd['MFA_BRAND'])."-".$dd['MFA_ID']."/", date('c'), 'weekly');
}


$sql = "SELECT code FROM ".DB_PREFIX."page;";
$data = $db->query($sql);
foreach ($data as $dd){
	$sitemap->addUrl(URL."/page/".$dd['code'], date('c'), 'weekly');
}


$sitemap->addUrl(URL."/contacts/", date('c'), 'weekly');

		

try {

	//create sitemap
	$sitemap->createSitemap();
	//write sitemap as file
	$sitemap->writeSitemap();
	//update robots.txt file
	$sitemap->updateRobots();

	echo('<pre>');
	echo('Generation sitemap successfuly done!');
	echo('</pre>');
			
} catch (Exception $exc){
	echo $exc->getTraceAsString();
}

echo "Memory peak usage: ".number_format(@memory_get_peak_usage()/(1024*1024),2)."MB";
$time2 = explode(" ",microtime());
$time2 = $time2[1];
echo "<br>Execution time: ".number_format($time2-$time)."s";
exit();

?>
</body>
</html>
