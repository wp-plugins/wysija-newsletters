jQuery(function($){
    /* send ajax request */
    function WYSIJA_SAVE(){
        
        if($(this).validationEngine('validate')){
            wysijaAJAX.task='save';
            wysijaAJAX.controller='subscribers';
            var dataarray=$(this).serializeArray();
            //wysijaAJAX.data=dataarray;
            $.each(dataarray,function(index,value){
                wysijaAJAX['data['+index+'][name]']=value.name;
                wysijaAJAX['data['+index+'][value]']=value.value;
            });

            wysijaAJAX.formid=$(this).attr('id');
            jQuery.WYSIJA_SEND();
        }

        return false;
    }
    
    $(document).ready(function(){
        $(".form-valid-sub").validationEngine('attach',{promptPosition : "centerRight", scroll: false, validationEventTrigger: 'submit'});
        $(".form-valid-sub").submit(WYSIJA_SAVE);
        $('input[name="wysija[user][email]"]').blur(function(){
            $(this).val(trim($(this).val()));
        });
    });
   
    function trim(myString){
        return myString.replace(/^\s+/g,'').replace(/\s+$/g,'')
    }
    
    
    jQuery.WYSIJA_SEND=function(){
        $('#msg-'+wysijaAJAX.formid).html('<div class="allmsgs"><blink>'+wysijaAJAX.loadingTrans+'</blink></div>');
        $('#'+wysijaAJAX.formid).fadeOut();
        wysijaAJAX._wpnonce=$('#wysijax').val();
        
        
        $.ajax({
          type: 'POST',
          url: wysijaAJAX.ajaxurl,
          data: wysijaAJAX,
          success: function(response) {
                        $('#msg-'+wysijaAJAX.formid).html('<div class="allmsgs"></div>');

                        if(response['result']){
                            $('#msg-'+wysijaAJAX.formid+' .allmsgs').html('<div class="updated">'+$('#'+wysijaAJAX.formid+' input[name="message_success"]').val()+'</div>');
                        }else{
                            $('#'+wysijaAJAX.formid).fadeIn();
                            $.each(response['msgs'], function(level, messages) {   

                                  if(!$('#msg-'+wysijaAJAX.formid+' .allmsgs .'+level+' ul').length) $('#msg-'+wysijaAJAX.formid+' .allmsgs').append('<div class="'+level+'"><ul></ul></div>');
                                  $.each(messages,function(key,val){
                                      $('#msg-'+wysijaAJAX.formid+' .allmsgs .'+level+' ul').append("<li>"+val+"</li>");
                                  });
                            });
                        }
                        

                    },
          error: function(XMLHttpRequest, textStatus, errorThrown) {
                        $('#msg-'+wysijaAJAX.formid).html('<div class="allmsgs"></div>');
                        $('#msg-'+wysijaAJAX.formid+' .allmsgs').html('<div class="error"><ul><li>textStatus:'+textStatus+'</li><li>errorThrown:'+errorThrown+'</li></ul></div>');
                    },

          dataType: "json"
        });
    }
    

    jQuery('.showerrors').live('click',function(){
        jQuery('.xdetailed-errors').toggle();
    });
    jQuery('.shownotices').live('click',function(){
        jQuery('.xdetailed-updated').toggle();
    });
});