jQuery(function(e){e(".action-send-spam-test").click(function(){tb_show(wysijatrans.processqueue,e(this).attr("href")+"&KeepThis=true&TB_iframe=true&height=618&width=1000",null);tb_showIframe();return false});function c(){wysijaAJAX.popTitle=wysijatrans.previewemail;wysijaAJAX.dataType="json";wysijaAJAX.task="send_preview";if(jQuery("#campaignstep3").length>0){wysijaAJAX.data=jQuery("#campaignstep3").serializeArray();wysijaAJAX.id=jQuery("#email_id").val()}wysijaAJAX.receiver=jQuery("#preview-receiver").val();jQuery.WYSIJA_SEND()}function a(){if(typeof(saveWYSIJA)=="function"){saveWYSIJA(function(){c()})}else{c()}return false}e("#wj-send-preview").click(a);function f(){var i=getListUsers();var h=0;var j="";i.each(function(k,l){h+=parseInt(l.total);j+=l.title+", "});j=j.substr(0,j.length-2);if(wysijatrans.alertsend!=undefined){var g=wysijatrans.alertsend;g=g.replace("[#]",h);g=g.replace("[#nms]",j);if(confirm(g)){return true}return false}else{return true}}e("#submit-send").click(f);e(document).ready(function(){if(typeof(saveWYSIJA)!="function"){e("#datepicker-day").datepicker({minDate:0,showOn:"focus",dateFormat:"yy-mm-dd"});g();e("#scheduleit").change(function(){g()})}function g(){if(e("#scheduleit").attr("checked")){e(".schedule-row").show();e("#submit-send").val(wysijatrans.schedule)}else{e(".schedule-row").hide();e("#submit-send").val(wysijatrans.send)}}});e("#wysija-send-spamtest").click(d);function d(){if(e("#wysija-send-spamtest").hasClass("disabled")){return false}WysijaPopup.showLoading();WysijaPopup.showOverlay();saveWYSIJA(function(){b()})}e("#link-back-step2").click(function(){e("#hid-redir").attr("value","savelastback");e("#campaignstep3").submit();return false});function b(){wysijaAJAX.popTitle=wysijatrans.previewemail;wysijaAJAX.dataType="json";wysijaAJAX.task="send_spamtest";e.ajax({type:"POST",url:wysijaAJAX.ajaxurl,data:wysijaAJAX,success:function(h){WysijaPopup.hideLoading();if(!h.result["result"]){if(h.result["notriesleft"]){alert(h.result["notriesleft"])}WysijaPopup.hideOverlay()}else{WysijaPopup.setSize(990,500).open(wysijatrans.spamtestresult,h.result["urlredirect"]);var g=parseInt(e("#counttriesleft").html())-1;e("#counttriesleft").html(g);if(g<=0){e("#wysija-send-spamtest").addClass("disabled")}}},error:function(h,g){alert("Request error not JSON:"+h.responseText);wysijaAJAXcallback.onSuccess=""},dataType:wysijaAJAX.dataType})}});