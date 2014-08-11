<div align="center" class="cadre1">
      <h1>BDoBrowser</h1>
          {ACTBROWSER}
          <br />
     
       
                  <div align="center">
                      <!-- BEGIN NavBlock -->
                      <a href="{URLLETTER}">{LETTER}&nbsp;</a>
                      <!-- END NavBlock -->         
                  </div>
             
              <script type="text/javascript">
                function go()
                {
                 	document.browser.pageNum.value='0';
                 	go1();

               		var div=getObj("livesearch2");
                	if (document.browser.let.value.length > 2)
                	{
						go2();
                	}
                	else
                	{
                		div.style.display = "none";
                	}
                	
                }
                function go1()
                {

               		SubmitXhr(document.browser,"livesearch1","browser_search.php",false);
                }
                function go2()
                {
               		var div=getObj("livesearch2");
					div.style.display = "block";
               		SubmitXhr(document.browser,"livesearch2","browser_search2.php",false);
                	
                }
                
			</script>
                    <form name="browser" method="get" onsubmit='return false;'>
                        <div align="center">
                            ... commence par : 
                            <input type="text" id="searchtext" onKeyUp='chrono=setTimeout("go()", 1000);' onKeyDown="clearTimeout(chrono)" autocomplete="off" name="let" value="{VALUELET}"/> ( recherche '...contient' à partir de 3 caractères )
                            <input type=hidden name="rb_browse" value="{TYPBROWSE}" /> 
                            <input type=hidden name="pageNum" value="" />
                        </div>
                    </form>
            
 
  <br />
    <div id="livesearch1"></div>
    <div id="livesearch2"></div>
      <script type="text/javascript">go()</script>
</div>