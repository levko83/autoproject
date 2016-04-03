var foFileManager = {
	tinyMCE: null,
	init: function(tinyMCE) {
		foFileManager.tinyMCE = tinyMCE;
	},
	open: function(field_name, url, type, win) {
	    if (!foFileManager.tinyMCE.selectedInstance.fileBrowserAlreadyOpen) {
	       tinyMCE.activeEditor.windowManager.open({
	            file : '/staffcp/media/js/tiny_mce/filemanager/fm.php',
	            title : "File Browser",
	            width : 840,
	            height : 500,
	            close_previous : "no",
	            resizable : "yes",
	            inline : "yes"
	        }, {
	            window : win,
	            input : field_name
	        });
	    }
	return false;
	}
};

var foFileManagerPopup = {
	init: function(){
	},
	select: function(file)
	{
        var win = tinyMCEPopup.getWindowArg("window");
        win.document.getElementById(tinyMCEPopup.getWindowArg("input")).value = file;
        if (typeof(win.ImageDialog) != "undefined") {
            if (win.ImageDialog.getImageData) win.ImageDialog.getImageData();
            if (win.ImageDialog.showPreviewImage) win.ImageDialog.showPreviewImage(file);
        }
        win.tinyMCE.selectedInstance.fileBrowserAlreadyOpen = false;
        tinyMCEPopup.close();
	}
}