<?php
/**
 * Pager
 *
 */
class CalendarViewHelper {

	public static function calendar()
	{
		$arr=<<<OEM

		<link type="text/css" rel="stylesheet" href="{HTTP_ROOT}/media/helpers/jscal/src/css/jscal2.css" />
		<link type="text/css" rel="stylesheet" href="{HTTP_ROOT}/media/helpers/jscal/src/css/border-radius.css" />
		<link id="skinhelper-compact" type="text/css" rel="alternate stylesheet" href="{HTTP_ROOT}/media/helpers/jscal/src/css/reduce-spacing.css" />
		
		<script src="{HTTP_ROOT}/media/helpers/jscal/src/js/jscal2.js"></script>
		<script src="{HTTP_ROOT}/media/helpers/jscal/src/js/lang/ru.js"></script>
    
		<div id="cont"></div>
		<script type="text/javascript">
		var LEFT_CAL = Calendar.setup({
		        cont: "cont",
		        weekNumbers: false,
		        selectionType: Calendar.SEL_MULTIPLE,
		        //showTime: 12
		        // titleFormat: "%B %Y"
		})
		</script>
OEM;

	return $arr;
	}
	
	public static function chooseDate() {
		
		$arr=<<<OEM

		<link type="text/css" rel="stylesheet" href="/media/helpers/jscal/src/css/jscal2.css" />
		<link type="text/css" rel="stylesheet" href="/media/helpers/jscal/src/css/border-radius.css" />
		<link id="skinhelper-compact" type="text/css" rel="alternate stylesheet" href="/media/helpers/jscal/src/css/reduce-spacing.css" />
		
		<script src="/media/helpers/jscal/src/js/jscal2.js"></script>
		<script src="/media/helpers/jscal/src/js/lang/ru.js"></script>

		<input class="info-input" type="text" name="form[date_delivery]" id="date_delivery"/>
        <button id="f_rangeStart_trigger">...</button>
        <script type="text/javascript">
          new Calendar({
                  inputField: "date_delivery",
                  dateFormat: "%Y-%m-%d",
                  trigger: "f_rangeStart_trigger",
                  bottomBar: false,
                  onSelect: function() {
                      var date = Calendar.intToDate(this.selection.get());
                      LEFT_CAL.args.min = date;
                      LEFT_CAL.redraw();
                      this.hide();
                  }
          });
        </script>
OEM;

	return $arr;
	}
	
	public static function chooseInputDate($field='form',$fieldName="date") {
		
		$arr=<<<OEM
		
		<script src="/media/helpers/calendar/calendar_ru.js"></script>
		<input type="button" name="{$field}[{$fieldName}]" id="{$fieldName}" value="&raquo;" onfocus="this.select();lcs(this)" onclick="event.cancelBubble=true;this.select();lcs(this)">

OEM;

	return $arr;
	}
}