jQuery(function(c){var a=window.parent.document.getElementById("bodyBgColorInput").value;if(a===undefined||a.length!==6){a="FFFFFF"}c("ul.icons").css("backgroundColor","#"+a);c(".bookmarks .sizes a").live("click",function(d){c(".bookmarks .sizes a").removeClass("selected");c(this).addClass("selected");b(c(this).attr("rel"));c("#bookmarks-size").val(c(this).attr("rel"));c("#bookmarks-iconset").val("");return false});c(".bookmarks .icons a").live("click",function(d){c(".bookmarks .icons a").removeClass("selected");c(this).addClass("selected");c("#bookmarks-iconset").val(c(this).attr("rel"));return false});c("#bookmarks-submit").click(function(){wysijaAJAX.task="generate_social_bookmarks";wysijaAJAX.wysijaData=c("#bookmarks-form").serializeArray();jQuery.ajax({type:"POST",url:wysijaAJAX.ajaxurl,data:wysijaAJAX,success:function(d){if(d.result==false){}else{window.parent.WysijaPopup.getInstance().callback(d.result);window.parent.WysijaPopup.close()}}});return false});function b(d,e){wysijaAJAX.task="get_social_bookmarks";wysijaAJAX.wysijaData={size:d,theme:e};jQuery.ajax({type:"POST",url:wysijaAJAX.ajaxurl,data:wysijaAJAX,success:function(j){if(j.result!=undefined){var l=JSON.parse(j.result),h="",g=0,k='class="selected"',f;c.each(l.icons,function(i,m){h+='<li class="clearfix"><a href="javascript:;" '+k+' rel="'+i+'">';f=m;if(g==0){k="";c("#bookmarks-iconset").val(i);g++}c.each(["facebook","twitter","google","linkedin"],function(n,o){h+='<img src="'+f[o]+'" alt="'+o+'" />'})});h+="</a></li>";c("ul.icons").html(h)}},dataType:"json"})}c(function(){b(c("#bookmarks-size").val(),c("#bookmarks-theme").val())})});