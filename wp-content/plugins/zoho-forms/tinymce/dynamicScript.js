
function loadScript(url,callback)
{
    var script = document.createElement("script")
    script.type = "text/javascript";
    if (script.readyState)
    {  
	//IE

	script.onreadystatechange = function(){ 		
            if (script.readyState == "loaded" || script.readyState == "complete")
	    {
                script.onreadystatechange = null;
                callback();
            }
        };
    } 
    else 
    {
	//Others
       script.onload = function(){
            callback();
        };
        script.onerror = function(){
    		callback();
    	};
    }	
    script.src = url;
    document.getElementsByTagName("head")[0].appendChild(script);	
}


this.loadScript("https://forms.zoho.com/api/getforms?type=plugin",reloadDiv);

function reloadDiv()
{
	getForms();
	selectForm();
}
