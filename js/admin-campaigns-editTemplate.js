var modalLbox=null;
var wysijaIMG = $H();
var WYSIJAhtml="";
var ajaxOver=true;
document.observe("dom:loaded", function() {
    //code copied from wordpress.com formfield and window.send_to_editor maybe useless or not need to test ...
    $('wysija-upload-browse').observe('click', function() {
        tb_show($(this).innerHTML, $(this).readAttribute('href2')+"&KeepThis=true&TB_iframe=true&height=650&width=800", null);
        return false;
    });
    
    $('wysija-themes-browse').observe('click', function() {
        tb_show($(this).innerHTML, $(this).readAttribute('href2')+"&KeepThis=true&TB_iframe=true&height=600&width=200", null,"themes");
        return false;
    });

    $('wysija_divider_settings').observe('click', function() {
        tb_show(wysijatrans.dividerSelectionTitle, $(this).readAttribute('href2')+"&KeepThis=true&TB_iframe=true&height=500&width=200", null,"dividers");
        return false;
    });
    
    // toggle delete button on images list
    handleRemoveImage();
    // toggle delete button on themes list
    handleRemoveTheme();
    // trigger switchTheme on theme click
    handleSwitchTheme();
});

function handleRemoveImage() {
    $$('.wj_images li').invoke('stopObserving', 'mouseover');
    $$('.wj_images li').invoke('stopObserving', 'mouseout');
    
    $$('.wj_images li').invoke('observe', 'mouseover', function() {
        $(this).select('span.delete-wrap').first().show();
    });
    $$('.wj_images li').invoke('observe', 'mouseout', function() {
        $(this).select('span.delete-wrap').first().hide();
    });
    // delete image
    $$('.wj_images li .del-attachment').invoke('observe', 'click', function(event) {
        removeImage($(this).innerHTML);
        $(this).stopObserving('click');
    });
}

function handleRemoveTheme() {
    $$('.wj_themes li').invoke('stopObserving', 'mouseover');
    $$('.wj_themes li').invoke('stopObserving', 'mouseout');
    
    $$('.wj_themes li').invoke('observe', 'mouseover', function() {
        $(this).select('span.delete-wrap').first().show();
    });
    
    $$('.wj_themes li').invoke('observe', 'mouseout', function() {
        $(this).select('span.delete-wrap').first().hide();
    });
    // delete theme
    $$('.wj_themes li .del-attachment').invoke('observe', 'click', function(event) {
        removeTheme($(this).innerHTML);
        $(this).stopObserving('click');
    });
}

function handleSwitchTheme() {
    $$('a.wysija_theme').invoke('observe', 'click', function(event) {
        switchThemeWYSIJA(event);
    });
}

function removeTheme(key){
    if(confirm(wysijatrans.abouttodeletetheme.replace("%1$s",key))){
        $('wysija-theme-'+key).remove();
    
        // remove theme
        wysijaAJAX.task = 'deleteTheme';
        wysijaAJAX.themekey = key;
        WYSIJA_AJAX_POST();
    }
   
    return true;
}

function removeImage(key){
    // remove from images list
    var value = wysijaIMG.unset(key);
    
    if(value !== undefined) {
        // remove image from html list
        $('wysija-img-'+key).remove();
    }
    
    // save images
    saveIQS();
    return true;
}

function addImage(values) {
    // format data
    var img = new Element('img', { 'wysija_height': values['height'],'wysija_width': values['width'], 'wysija_src':  values['url'], 'src': values['thumb_url'] });
    var a = new Element('a', { 'wysija_type': 'image', 'class': 'wysija_item' }).update(img);
    var li = new Element('li', { 'id': 'wysija-img-'+values['identifier'], 'class': 'new'}).update(a);
    li.insert('<span class="delete-wrap" style="display:none;"><span class="delete del-attachment">'+values['identifier']+'</span></span>');
    
    // append new image to html list
    $('wj-images-quick').insert( li, 'before');
    
    // toggle delete button on images list
    handleRemoveImage();
    
    // add to images list
    wysijaIMG.set(values['identifier'], values);
    
    // save images
    saveIQS();
    
    return true;
}