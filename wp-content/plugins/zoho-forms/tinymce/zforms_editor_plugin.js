(function() {  
    tinymce.create('tinymce.plugins.zohoForms', {  

	init : function(ed, url) {  
	    ed.addCommand('zforms_embed_window',function(){
		ed.windowManager.open({
			file : url+'/zforms_dialog.php',
			//title : 'Zoho Forms',
			width : 650, 
			height : 570,
			inline :1,
		},
		{plugin_url : url});
		});
		  
            ed.addButton('zohoForms', {  
                title : 'Zoho Forms',
		cmd : 'zforms_embed_window',  
                image : url+'/zohoforms.png', 				
            });
			return false;
        }
	
    
  	  
         
    });  
    tinymce.PluginManager.add('zohoForms', tinymce.plugins.zohoForms);  
	
    
})();  

