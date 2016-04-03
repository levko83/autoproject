<?php

class StatgaExtension extends Orm {
	
	public static function stat($u='',$p='',$id=''){

		if ($id && $u && $p) {
			
		require_once('statga3.1/gapi.class.php');
		require_once('statga3.1/config.php');
		
		$GA_last_update = file_get_contents($path.'GA_last_update.txt');
		if ((int)$GA_last_update <= mktime())
			require_once('statga3.1/stat.php');
		
		file_put_contents($path.'GA_last_update.txt',strtotime("+4 hours",mktime()));
		
		$mktime = mktime();
			
$OEM =<<<OEM


<script type="text/javascript" src="/staffcp/models/extensions/statga3.1/swfobject.js"></script>

<div id="visitors" align="center" style="padding-bottom:80px"></div>
<script type="text/javascript">
// <![CDATA[
var so = new SWFObject("/staffcp/models/extensions/statga3.1/amline.swf", "amline_chart", "630", "500", "8", "#FFFFFF");
so.addVariable("path", "./amline/");
so.addVariable("settings_file", escape("/staffcp/models/extensions/statga3.1/visitors_settings.xml?{$mktime}"));
so.addVariable("data_file", escape("/cache/visitors.csv?{$mktime}"));
so.addVariable("preloader_color", "#BBBBBB");
so.write("visitors");
// ]]>
</script>

<div id="visitors_3" align="center" style="padding-bottom:80px"></div>
<script type="text/javascript">
// <![CDATA[
var so = new SWFObject("/staffcp/models/extensions/statga3.1/amline.swf", "amline_chart", "600", "400", "8", "#FFFFFF");
so.addVariable("path", "./amline/");
so.addVariable("settings_file", escape("/staffcp/models/extensions/statga3.1/visitors_3_settings.xml?{$mktime}"));
so.addVariable("data_file", escape("/cache/visitors_3.csv?{$mktime}"));
so.addVariable("preloader_color", "#BBBBBB");
so.write("visitors_3");
// ]]>
</script>

<div id="country" align="center" style="padding-bottom:80px"></div>
<script type="text/javascript">
// <![CDATA[
var so = new SWFObject("/staffcp/models/extensions/statga3.1/ampie.swf", "ampie_chart", "550", "350", "8", "#FFFFFF");
so.addVariable("path", "./ampie/");
so.addVariable("settings_file", escape("/staffcp/models/extensions/statga3.1/country_settings.xml?{$mktime}"));
so.addVariable("data_file", escape("/cache/country.csv?{$mktime}"));
so.addVariable("preloader_color", "#BBBBBB");
so.write("country");
// ]]>
</script>

<div id="city" align="center" style="padding-bottom:80px"></div>
<script type="text/javascript">
// <![CDATA[
var so = new SWFObject("/staffcp/models/extensions/statga3.1/ampie.swf", "ampie_chart", "550", "350", "8", "#FFFFFF");
so.addVariable("path", "./ampie/");
so.addVariable("settings_file", escape("/staffcp/models/extensions/statga3.1/country_settings.xml?{$mktime}"));
so.addVariable("data_file", escape("/cache/city.csv?{$mktime}"));
so.addVariable("preloader_color", "#BBBBBB");
so.write("city");
// ]]>
</script>


OEM;
			
			return $OEM;
		}
		else {
			
			return "<p><b>Нет данных. Неправильный логин/пароль/индификатор аккаунта.</b></p>";
		}
	}
}

?>