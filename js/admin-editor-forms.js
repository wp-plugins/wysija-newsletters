jQuery(function(c){c(".wysija_toolbar_tabs a").click(function(){c(".wysija_toolbar_tabs a").removeClass("selected");c(this).addClass("selected");c("#wysija_toolbar .wj-tab-inner").hide();if(c(c(this).attr("rel")).length>0){c(c(this).attr("rel")).show()}c(this).blur();return false});c("#wj-create-new-form").click(function(){var g=jQuery.extend(true,{},wysijaForms["default-form"]);g.name="New Form";g.id="new-form";g.blocks.unshift({type:"instruction",params:{label:"Hello Welcome to our wonderful website, please subscribe here."}});if(wysijaForms[g.id]!=undefined){return alert("already new form exists")}wysijaForms[g.id]=g;c("#list-forms").append('<option value="'+g.id+'">'+g.name+"</option>");c("#list-forms").val(g.id).change()});c(document).on("change","#list-forms",function(){if(c(this).val()===""){c("#wj-forms-editor").hide()}else{c("#wj-forms-editor").show();d(c(this).val())}});c("#forms-save").click(function(){wysijaAJAX.task="form_save";wysijaAJAX.formid=c("#wj-form-id-value").val();wysijaAJAX.data=JSON.stringify(a());wysijaAJAX.popTitle=wysijatrans.testemail;wysijaAJAX.dataType="json";c.WYSIJA_SEND();b()});c("#form-delete").click(function(){wysijaAJAX.task="form_delete";wysijaAJAX.formid=c("#wj-form-id-value").val();wysijaAJAX.popTitle=wysijatrans.testemail;wysijaAJAX.dataType="json";c.WYSIJA_SEND()});c("#wj-form-name-label").click(function(){wysijaAJAX.task="form_delete";wysijaAJAX.formid=c("#wj-form-id-value").val()});function a(){var g=jQuery.extend(true,{},wysijaForms[c("#wj-form-id-value").val()]);g.name=c("#wj-form-name-value").val();g.name=c("#wj-form-name-value").val();g.id=e(g.name);return g}function e(g){return g.toLowerCase().replace(/\W/g,"")}function b(){delete wysijaForms[c("#wj-form-id-value").val()];wysijaForms[c("#wj-form-id-value").val()]}function d(h){var g=wysijaForms[h];c("#wj-form-name-label").html(g.name);c("#wj-form-name-value").val(g.name);c("#wj-form-id-value").val(g.id);f(g)}function f(h){var g="";for(i in h.blocks){g+=JSON.stringify(h.blocks[i])+"\n"}c("#wj-currentform").html("<pre>"+g+"</pre>")}});