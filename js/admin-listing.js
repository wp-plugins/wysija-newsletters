jQuery(function($){
    /*snippet for listing bulk actions*/
    var $_wpnonce = $("#_wpnonce").attr( 'disabled', 'disabled' ),
        $bulk_action = $('.global-action');

    $bulk_action.change(function (e){
        var value = $bulk_action.val();
        if(value=="delete" || value=="bulk_delete"){
            $_wpnonce.prop( 'disabled', null );
        }else{
            $_wpnonce.attr( 'disabled','disabled' );
        }
    });


    $('.orderlink').click(function(){
        $(this).parent("th.sortable , th.sorted").click();
        return false;
    });

    /*posts-filter form submit disabled bulk action except when bulkaction submitted */
    $('#posts-filter').submit(function(){
        if($('#wysija-pagination').length && (parseInt($('#wysija-pagination').val()) > parseInt($('#wysija-pagination-max').val()))){
            $('#wysija-pagination').val($('#wysija-pagination-max').val());
        }
    });


    /*snippet bulkaction check*/
    $('.bulksubmit').click(function(){
        /* activate the bulk action*/
        var locale = $bulk_action.data('locale'), // Grab from Data Attr the l18n strings
            action = $bulk_action.val(),
            $selected = $('#posts-filter .check-column input:checked');

        if ($selected.length===0){
            alert(wysijatrans.selecmiss);
            return false;
        }

        switch(action){
            // Delete confirmation messages
            case 'deleteusers':
                if (!confirm( (($selected.length === 1) ? locale.delete : locale.delete_bulk) ))
                    return false
                break;

            // There is not default action yet
            default:
                break;
        }

        $("<input/>", {
            'type': 'hidden',
            'name': 'action',
            'value': $bulk_action.val(),
        }).insertAfter($(this));

        $_wpnonce.prop( 'disabled', null );
        $("#_wpnonce").val( $( ".global-action option:selected").data("nonce") );
        return true;
    });

    $('.check-column input[type="checkbox"]').click(function(){
        /*if(is_one_checkbox_selected() === false){
            $('#bulksubmit-area').hide();
        }else{
            $('#bulksubmit-area').show();
        }*/
    });

    function is_one_checkbox_selected(){
        if($('#posts-filter .check-column input:checked').length === 0){
            return false;
        }else{
            return $('#posts-filter .check-column input:checked').length;
        }
    }

    function fixForceSelectAllWrapper()
    {
        flag = false;
        $('.batch-select td').children().each(function(){
            if($(this).hasClass('display')){
                flag = true;
            }
        });
        if(!flag){
            $('.batch-select').hide();
        }
        else{
            $('.batch-select').show();
        }
    }

    function batchSelect()
    {
        if($('#force_select_all').is(':checked')){
            $('.checkboxselec, #user-id input, #force_select_all').attr('checked',false);
        }
        subscriberCount = $('#posts-filter input.checkboxselec:checked').length;
        $('.batch-select div.clear_select_all').removeClass('display').hide();
        if (subscriberCount > 0){
            $('.batch-select div.force_to_select_all_link').removeClass('display').addClass('display').show();
        }else{
            $('.batch-select div.force_to_select_all_link').removeClass('display').hide();
        }

        fixForceSelectAllWrapper();
    }

    $('#user-id').click(batchSelect);
    $('.checkboxselec').click(function(){
        if(!$(this).is(':checked')){
            $('#user-id input, #force_select_all').attr('checked',false);
            $('.batch-select div.force_to_select_all_link').removeClass('display').hide();
            $('.batch-select div.clear_select_all').removeClass('display').hide();
        }
        fixForceSelectAllWrapper();
    });
    $('.force_to_select_all_link a').click(function(_event){
        _event.preventDefault();
        $('.checkboxselec, #user-id input, #force_select_all').attr('checked','checked');
        $('.batch-select div.force_to_select_all_link').removeClass('display').hide();
        $('.batch-select div.clear_select_all').removeClass('display').addClass('display').show();
        //batchSelect();
        fixForceSelectAllWrapper();
    });

    $('.clear_select_all a').click(function(_event){
        $('.batch-select div.force_to_select_all_link').removeClass('display').hide();
        $('.batch-select div.clear_select_all').removeClass('display').hide();
        $('.checkboxselec, #user-id input, #force_select_all').attr('checked',false);
        fixForceSelectAllWrapper();
    });


    /* snippet for listing ordering*/
    $('th.sortable , th.sorted').click(function(){
        var valorder='';
        if($(this).hasClass('sorted')){
            if($(this).hasClass('asc')) valorder="desc";
            else valorder="asc";
        }else{
            valorder="desc";
        }
        var idheader=$(this).attr("id");

        if($('#wysija-orderby').length){
            $('#wysija-orderby').val(idheader);
            $('#wysija-ordert').val(valorder);
        }else{
            $('#posts-filter').append('<input id="wysija-ordert" type="hidden" name="ordert" value="'+valorder+'" />');
            $('#posts-filter').append('<input id="wysija-orderby" type="hidden" name="orderby" value="'+idheader+'" />');
        }

        $('#posts-filter').submit();
    });

    /* snippet for pagination submit */
    $('a.page-numbers').click(function(){
        var valpagi=$(this).attr('alt');

        if($('#wysija-pagination').length){
            $('#wysija-pagination').val(valpagi);
        }else{
            $('#posts-filter').append('<input id="wysija-pagination" type="hidden" name="pagi" value="'+valpagi+'" />');
        }

        $('#posts-filter').submit();
        return false;
    });

    $('a.page-limit').click(function(){
        var valpagi=$(this).html();

        if($('#wysija-pagelimit').length){
            $('#wysija-pagelimit').val(valpagi);
        }else{
            $('#posts-filter').append('<input id="wysija-pagelimit" type="hidden" name="limit_pp" value="'+valpagi+'" />');
        }

        $('#posts-filter').submit();
        return false;
    });

    /*snippet for launching file download after an export*/
    $(document).ready(function() {
        if($('a.exported-file').length){
            window.open($('a.exported-file').attr('href'),'Download');
        }
    });

    $('.searchbox').blur(function(){
        $(this).val(trim($(this).val()));
    });

    fixForceSelectAllWrapper();
});