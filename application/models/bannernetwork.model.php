<?php

class BannernetworkModel extends Orm {
	
	var $catch = '';
	
	public function __construct()
	{
		parent::__construct(DB_PREFIX.'bannernetwork');
	}
	
	public static function getById($id)
	{
		$model = new BannernetworkModel();
		return $model->select()->where("id=?",(int)$id)->fetchOne();
	}
	
	function getBanner($zone=0){
		$id = (isset($_SESSION['banner_showed_id_'.$zone]))?$_SESSION['banner_showed_id_'.$zone]:0;
		
		$fetch = '';
		if (isset($id) && !empty($id))
			$fetch .= "AND bb.id>".$id;
		else
			$fetch .= "ORDER BY bb.id ASC";
			
		$db = Register::get('db');
		
		$sql = "SELECT 
					bb.*,bp.width,bp.height 
				FROM ".DB_PREFIX."bannernetwork bb 
				JOIN ".DB_PREFIX."bannernetwork__places bp ON (bb.zone=bp.id) 
				WHERE 
					(
						(bb.zone='".(int)$zone."' AND bb.view_from <= '".time()."') OR
						(bb.view_by_url LIKE '".mysql_real_escape_string("http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'])."')
					) AND bb.is_active='1'
					$fetch
				LIMIT 0,1;";
		$data = $db->get($sql);
		$this->catch = $data;
		
		$cc = "	SELECT 
					COUNT(*) as cc 
				FROM ".DB_PREFIX."bannernetwork bb 
				JOIN ".DB_PREFIX."bannernetwork__places bp ON (bb.zone=bp.id) 
				WHERE bb.zone='".(int)$zone."' AND bb.is_active='1';";
		$cc = $db->get($cc);
		
		if (count($data)<=0) {
			$_SESSION['banner_showed_id_'.$zone] = null;
			if ($cc['cc']>0) {
				$this->getBanner($zone);
			}
		}
		else {
			//type_view: 1 - counter
			//type_view: 2 - date from to
			//type_view: 3 - nolimit
			if ($data['type_view']==1) {
				if ($data['view_count_minus']<=0) {
					$db->query("UPDATE ".DB_PREFIX."bannernetwork SET is_active=0 WHERE id='".$data['id']."';");
				}
				else {
					$db->query("UPDATE ".DB_PREFIX."bannernetwork SET view_count_minus=view_count_minus-1 WHERE id='".$data['id']."';");
				}
			}
			elseif ($data['type_view']==2) {
				if ($data['view_to'] <= mktime()) {
					$db->query("UPDATE ".DB_PREFIX."bannernetwork SET is_active=0 WHERE id='".$data['id']."';");
				}
			}
			
			//showed
			$db->query("UPDATE ".DB_PREFIX."bannernetwork SET showed=showed+1 WHERE id='".$data['id']."';");
		}
	}
	
	public static function viewBanner($zone) {
		
		$banner = new BannernetworkModel();
		$banner->getBanner($zone);
		$data = $banner->catch;
		$_SESSION['banner_showed_id_'.$zone] = $data['id'];
		
		return $data;		
	}
	
	public static function click($id) {
		
		$banner = BannernetworkModel::getById($id);
		if (count($banner)>0) {
			$db = Register::get('db');
			$db->query("UPDATE ".DB_PREFIX."bannernetwork SET click=click+1 WHERE id='".(int)$banner['id']."';");
			if (empty($banner['url'])) {
				header("location: http://".$_SERVER['SERVER_NAME']);
			}
			else {
				header("location: http://".str_replace(array("http://"),"",$banner['url']));
			}
		}
		else 
			header("location: http://".$_SERVER['SERVER_NAME']);
	}
}