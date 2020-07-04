jQuery(document).ready(function($){
"use strict";
  
    /*
    * HIde user comment when click body
    */
   jQuery(document.body).on('click', 'span.userHideSticky', function(){
        jQuery('ul.user-button-ul').hide();
   });

   /*
   * Add local store for new comment
   */
  jQuery(document).on('click', 'li.note-new-comment a, li#wp-admin-bar-note_new_comment a', function(e){
    //e.preventDefault();
    jQuery(this).closest('ul').hide();
    localStorage.setItem("sticky_comment", 'active');
  });

    //add class
    jQuery(document.body).on('click', 'li#wp-admin-bar-add_sticky_notes a', function(){
        jQuery('body').toggleClass('note_additable');

    });
    //add class end
 
    function htmlDecode(input){
        var e = document.createElement('div');
        e.innerHTML = input;
        return e.childNodes[0].nodeValue;
    }




    jQuery(document.body).one('click','.supper_sticky_note p', function(evt){
        
        //e.preventDefault()
        //jQuery('p, span, li, ...').disableSelection();
        
        //$('p, span, li, ...').not('input').disableSelection();
 
        var status = localStorage.getItem("sticky_comment");
        
        if(jQuery(this).find('sub.new').length == 0 && notesAjax.status == 'active'){
        
        var content = jQuery(this);
        var parentClass = $(this).parent().attr('class');
        var currentClass = $(this).attr('class');
        let position = window.getSelection().focusOffset;
        // console.log('position1: ' + position);

        var myAnchorNodeValue = window.getSelection().anchorNode.nodeValue;
        var myAnchorOffset = window.getSelection().anchorOffset;
        var myFocusOffset =  window.getSelection().focusOffset;
        var d = document.createDocumentFragment(),
        myFocusNodeLength = window.getSelection().focusNode.nodeValue.length;
        var newCount = myAnchorNodeValue.slice(0, myAnchorOffset);
        position = newCount.length;

        // console.log('myFocusNodeLength: ' + myFocusNodeLength);
        // console.log('position2: ' + position);
        window.getSelection().anchorNode.nodeValue = myAnchorNodeValue.slice(0, myAnchorOffset) + '<sub class="new stickyQuestion" data-parent="'+parentClass+'" data-current="'+currentClass+'" data-position="'+position+'" class="note-question"><span class="note-question-icon-button"></span></sub>' + myAnchorNodeValue.slice(myAnchorOffset);
        
        var myFocusNodeValue = window.getSelection().focusNode.nodeValue;
    
        if(window.getSelection().focusNode.nodeValue.length - myFocusNodeLength > 0) {
            myFocusOffset += window.getSelection().focusNode.nodeValue.length - myFocusNodeLength;
        }
     
        
        position = myFocusOffset;
        window.getSelection().focusNode.nodeValue = myFocusNodeValue.slice(0, myFocusOffset) + myFocusNodeValue.slice(myFocusOffset);
        
        // window.getSelection().focusNode.nodeValue = myFocusNodeValue.slice(0, myFocusOffset) + '</span></sub>' + myFocusNodeValue.slice(myFocusOffset);
        // console.log(myFocusNodeValue);
        var thishtml = jQuery(this).html();
        
        var selection = window.getSelection();
            var result = thishtml;
            result = decodeHtml(result);

            content.html(result);
            jQuery(content).find('.note-question-icon-button').each(function(k, v){
                addQtip(jQuery(v));
            });
        }
    });
    
    function decodeHtml(html) {
        var txt = document.createElement("textarea");
        txt.innerHTML = html;
        return txt.value;
    }
    

    function ajaxfuncton(parntclass, currentClass, text_content, position, data_id, current_page_url, priv){

        
        var current_page_id = notesAjax['current_page_id'];
        var user_id = notesAjax['user_id'];
        var title = notesAjax['title'];
       
        // console.log('priv: ' + priv);

        // if(typeof data_id == 'undefined') data_id = '';
        
        var formdata = {
            'position'                : position,
            'current_page_id'         : current_page_id,
            'parentClass'             : parntclass,
            'user_id'                 : user_id,
            'text_content'            : text_content,
            'currentClass'            : currentClass,
            'title'                   : title,
            'priv'                    : priv,
            'action'                  : 'sendtonotesajax' 
        };

        // console.log(formdata);
        if(priv){
            jQuery('#successMsgSticky').find('h5').find('span').text(notesAjax.priv_message);
        }

        jQuery.ajax({
           type : 'post',
           dataType: 'json',
            data : formdata,
            url : notesAjax.ajax,
            success:function(data){
                if(data.message){
                    localStorage.removeItem("sticky_comment");
                    jQuery('#successMsgSticky').fadeIn('slow', function(){
                        setTimeout(function(){
                            jQuery('#successMsgSticky').fadeOut('show');
                            window.location.href = current_page_url;
                        }, 4000);
                    });
                    
                }
            }, 
            error:function(){
                console.log('error');
            }
            });    

    }
    // Apply tooltip on all <a/> elements with title attributes. Mousing over
    // these elements will the show tooltip as expected, but mousing onto the
    // tooltip is now possible for interaction with it's contents.
    // setTimeout(function(){
    // jQuery(document.body).on('click', 'i.fas.fa-question', function(){
    jQuery('.note-question-icon-button').each(function () {
        addQtip(jQuery(this));
    });
    


    function addQtip(element){
        var parntclass = element.closest('sub').data('parent');
        var position = element.closest('sub').data('position');
        var currentClass = element.closest('sub').data('current');
        var data_id = element.closest('sub').data('id');
        
        data_id = (typeof data_id != 'undefined') ? data_id.toString().split(',') : [];
        var status = (element.hasClass('old')) ? 'old' : 'new';
        var current_page_url = notesAjax.current_page_url;
        // var note_admin_avatar_url = notesAjax.note_admin_avatar_url;
        //console.log('single priv: ' +note_user_avatar_url);
        // notesAjax.submitoreply[data_id] == 1
        console.log(data_id);
        var yourcomment = '', 
        current_user_data = '',
        submitorreply = 'SUBMIT';
        data_id.forEach(function(single_id){           
            //console.log(notesAjax.note_user_avatar_url);

            if(notesAjax.notes[single_id] != undefined){

                var comment_user_like = notesAjax.notes[single_id].comment_user_like;
                var comment_user_unlike = notesAjax.notes[single_id].comment_user_unlike;
                var comment_admin_like = notesAjax.notes[single_id].comment_admin_like;
                var comment_admin_unlike = notesAjax.notes[single_id].comment_admin_unlike;

                var comment_user_likes = comment_user_like.split(",");
                var comment_user_unlikes = comment_user_unlike.split(",");
                var comment_admin_likes = comment_admin_like.split(",");
                var comment_admin_unlikes = comment_admin_unlike.split(",");

                var user_active_like = (comment_user_likes.includes(notesAjax.user_id)) ? 'like-h' : '';
                var user_active_unlike = (comment_user_unlikes.includes(notesAjax.user_id)) ? 'unlike-h' : '';
                var admin_active_like = (comment_admin_likes.includes(notesAjax.user_id)) ? 'like-h' : '';
                var admin_active_unlike = (comment_admin_unlikes.includes(notesAjax.user_id)) ? 'unlike-h' : '';

                
                var top_note_id = notesAjax.notes[single_id].id;
                var top_user_like_id = notesAjax.top_user_like_id;
                var top_user_unlike_id = notesAjax.top_user_unlike_id;
                var top_admin_like_id = notesAjax.top_admin_like_id;
                var top_admin_unlike_id = notesAjax.top_admin_unlike_id;

                var top_user_like = (top_user_like_id.includes(top_note_id)) ? '' : '';
                var top_user_unlike = (top_user_unlike_id.includes(top_note_id)) ? '' : '';
                var top_admin_like = (top_admin_like_id.includes(top_note_id)) ? '' : '';
                var top_admin_unlike = (top_admin_unlike_id.includes(top_note_id)) ? '' : '';
                
                yourcomment += ( notesAjax.notes[single_id].note_values != '' && notesAjax.notes[single_id].priv == 0) ? '<div class="your-comment" style="background-color:'+notesAjax.notetextbg+'"><div class="wp-ssn-user-avater"><img src="'+notesAjax.note_user_avatar_url[notesAjax.notes[single_id].user_id]+'" /></div><div class="wp-ssn-user-comment"><strong>'+notesAjax.notes[single_id].user_nicename+' wrote on '+notesAjax.notes[single_id].insert_time+' :</strong> '+notesAjax.notes[single_id].note_values+'</div><div data-id="'+notesAjax.notes[single_id].id+'" data-reply="user" data-like="'+notesAjax.comment_user_like[notesAjax.notes[single_id].id]+'" data-unlike="'+notesAjax.comment_user_unlike[notesAjax.notes[single_id].id]+'" class="wp-ssn-user-comment-like"><ul class="wp-ssn-user-comment-like"><li><div class="wp-ssn-user-like '+user_active_like+'"></div><div class="wp-ssn-user-like-cunter '+top_user_like+'" >'+notesAjax.comment_user_like[notesAjax.notes[single_id].id]+'</div></li><li><div class="wp-ssn-user-unlike '+user_active_unlike+'"></div><div class="wp-ssn-user-unlike-cunter '+top_user_unlike+'" >'+notesAjax.comment_user_unlike[notesAjax.notes[single_id].id]+'</div></li></ul></div></div>' : '';
                yourcomment += ( notesAjax.notes[single_id].note_reply != '' ) ? '<div class="admin-reply" style="background-color:'+notesAjax.notetextbg+'"><div class="wp-ssn-user-avater"><img src="'+notesAjax.note_admin_avatar_url+'" /></div><div class="wp-ssn-user-comment"><strong>Admin reply on '+notesAjax.notes[single_id].note_repliedOn+' :</strong> '+notesAjax.notes[single_id].note_reply+'</div><div data-id="'+notesAjax.notes[single_id].id+'" data-reply="admin" data-like="'+notesAjax.comment_admin_like[notesAjax.notes[single_id].id]+'" data-unlike="'+notesAjax.comment_admin_unlike[notesAjax.notes[single_id].id]+'" class="wp-ssn-user-comment-like"><ul class="wp-ssn-user-comment-like"><li><div class="wp-ssn-user-like  '+admin_active_like+'"></div><div class="wp-ssn-user-like-cunter '+top_admin_like+'" >'+notesAjax.comment_admin_like[notesAjax.notes[single_id].id]+'</div></li><li><div class="wp-ssn-user-unlike  '+admin_active_unlike+'"></div><div class="wp-ssn-user-unlike-cunter '+top_admin_unlike+'" >'+notesAjax.comment_admin_unlike[notesAjax.notes[single_id].id]+'</div></li></ul></div></div>' : '';
                yourcomment += ( notesAjax.notes[single_id].priv == 1 ) ? '<div class="user-priv" style="background-color:'+notesAjax.notetextbg+'"><strong>Private Note on '+notesAjax.notes[single_id].insert_time+' :</strong> '+notesAjax.notes[single_id].note_values+'</div>' : '';
                if(notesAjax.notes[single_id].user_id == notesAjax.user_id && notesAjax.notes[single_id].priv == 0) current_user_data = single_id;
            }
        });
        
        // console.log(current_user_data);
        if(notesAjax.notes[current_user_data] != undefined && notesAjax.notes[current_user_data].user_id == notesAjax.user_id && notesAjax.notes[current_user_data].next_conv_allowed == 1) submitorreply = 'REPLY';
        if(notesAjax.notes[current_user_data] != undefined && notesAjax.notes[current_user_data].user_id == notesAjax.user_id && notesAjax.notes[current_user_data].next_conv_allowed == 0) submitorreply = '';
        var thisElement = element;
        // if(status == 'old' && submitorreply == 'SUBMIT' ) submitorreply = '';
        var loginAllert = '';
        if(notesAjax.login_status == 'logout'){
            submitorreply = '';
            loginAllert = notesAjax.login_alert;
        } 
        
        element.qtip({
            content: function() 
                {

                    var text = '<div style="background-color:'+notesAjax.notetextbg+'" data-parent="'+parntclass+'" data-current="'+currentClass+'" data-id="'+data_id+'" data-position="'+position+'" class="sticky-note-theme">'
                    +'<div class="note-top-option" style="background-color:'+notesAjax.nottopcolor+'">'
                    +'<div class="note-plus-button"><div class="note-plus-icon-button"></div></div>'
                    +'<div class="note-color-button"><div class="note-color-icon-button"></div></div>'
                    +'<div class="note-exest-button"><div class="note-exest-icon-button"></div></div></div>'
                    +'<div class="note-top-option-all" style="display:none;">'
                    +'<div class="note-top-option-color">'
                    +'<div class="color-1 color-option"></div><div class="color-2 color-option"></div>'
                    +'<div class="color-3 color-option"></div><div class="color-4 color-option"></div>'
                    +'<div class="color-5 color-option"></div><div class="color-6 color-option"></div>'
                    +'<div class="color-7 color-option"></div>'
                    +'<div class="note-top-option-comment-list"><p class="note-go-to-comment">Go to your comments list</p></div>'
                    +'<div class="note-top-option-delete-comment" data-position="'+position+'" data-id="'+data_id+'"><p class="note-top-option-delete-all-comment">Delete your comment</p></div>'
                    +'</div>'
                    +'</div>'
                    +yourcomment;
                    if(loginAllert == ''){
                        if(notesAjax.user_restrict_alert == 'your_restricted'){
                            text+='<div style="background-color:'+notesAjax.notetextbg+'" class="login_alert active"><div class="alert_inside">'+notesAjax.restrict_alert+'</div></div>';
                        }else{
                            if(submitorreply !=''){
                                if(notesAjax.private_comment == 1){
                                    text+='<textarea name="textarea" style="background-color:'+notesAjax.notetextbg+'" id="sticky_note_text_editor" class="sticky-note-text-editor" placeholder="Ask Questions.."></textarea>';
                                    
                                }
                            }
                        }
                    }else{
                        text+='<div style="background-color:'+notesAjax.notetextbg+'" class="login_alert active"><div class="alert_inside">'+loginAllert+'</div></div>';
                    }   
                    if(notesAjax.user_restrict_alert != 'your_restricted'){
                        if(submitorreply !=''){
                            if(notesAjax.private_comment == 1){
                                text+='<label class="priv_label"><input type="checkbox" value="1" name="priv" class="prev_input"/>'+notesAjax.priv+'</label>';
                            }
                                text+='<span class="wpssn_rchars">'+notesAjax.wssn_restrict_number+'</span><button class="note-reply" style="background-color:'+notesAjax.nottopcolor+'">'+submitorreply+'</button>';
                        }
                    } 
                    text+='</div>';
                    jQuery(document.body).on('click', 'button.note-reply, div.note-exest-button', function(){
                        jQuery(this).closest('div.sticky-note-theme').qtip().hide();
                        // $(text).remove();
                    });

                    
                    return text;
 
                },
            show   : 'click',
            hide   : 'click',
            position: {
                viewport: $(window),
            },
            events: {
                hide: function(event, api) {

                },
                show: function(event, api){
                    console.log('show event');
                }
            }
        });
    }

    jQuery(document.body).on('click', 'button.note-reply', function(){  
        
        var parntclass = jQuery(this).closest('div.sticky-note-theme').data('parent');
        var position = jQuery(this).closest('div.sticky-note-theme').data('position');
        var currentClass = jQuery(this).closest('div.sticky-note-theme').data('current');
        var data_id = jQuery(this).closest('div.sticky-note-theme').data('id');
        var text_content = jQuery(this).closest('div.sticky-note-theme').find('textarea').val();
        var priv = (jQuery(this).closest('div.sticky-note-theme').find('input[name="priv"]').is(':checked')) ? 1 : 0;
        var current_page_url = notesAjax.current_page_url;
        
        //console.log(parntclass, currentClass, text_content, position, data_id, current_page_url, priv);
        if(notesAjax.login_status != 'logout' && text_content != ''){  
            ajaxfuncton(parntclass, currentClass, text_content, position, data_id, current_page_url, priv);
        }
    });


    //css color 
        jQuery(document.body).on('click', ".note-color-icon-button", function(){
          $(".note-top-option").attr("style", "display:none");
          $(".note-top-option-all").attr("style", "display:block");
        });

        jQuery(document.body).on('click', ".sticky-notes-user", function(){
            $("ul.user-button-ul").show();
          });


        jQuery(document.body).on('click', ".color-option", function(){
            $(".note-top-option").attr("style", "display:block");
            $(".note-top-option-all").attr("style", "display:none");

            var classname = jQuery(this).attr('class');
            switch(classname){
                case 'color-1 color-option':
                        $(".note-top-option").attr("style", "background-color:#F0B30C");
                        $(".sticky-note-theme").attr("style", "background-color:#FFF7C3");
                        $(".sticky-note-theme .login_alert").attr("style", "background-color:#FFF7C3");
                        $(".sticky-note-theme .your-comment").attr("style", "background-color:#FFF7C3");
                        $(".sticky-note-theme .admin-reply").attr("style", "background-color:#FFF7C3");
                        $("textarea.sticky-note-text-editor").attr("style", "background-color:#FFF7C3");
                        $("button.note-reply").attr("style", "background-color:#F0B30C");
                break;
                case 'color-2 color-option':
                        $(".note-top-option").attr("style", "background-color:#CEE9FD");
                        $(".sticky-note-theme").attr("style", "background-color:#E2F0FF");
                        $(".sticky-note-theme .login_alert").attr("style", "background-color:#E2F0FF");
                        $(".sticky-note-theme .your-comment").attr("style", "background-color:#E2F0FF");
                        $(".sticky-note-theme .admin-reply").attr("style", "background-color:#E2F0FF");
                        $("textarea.sticky-note-text-editor").attr("style", "background-color:#E2F0FF");
                        $("button.note-reply").attr("style", "background-color:#CEE9FD");
                break;
                case 'color-3 color-option':
                        $(".note-top-option").attr("style", "background-color:#B45FF6");
                        $(".sticky-note-theme").attr("style", "background-color:#EFC9FF");
                        $(".sticky-note-theme .login_alert").attr("style", "background-color:#EFC9FF");
                        $(".sticky-note-theme .your-comment").attr("style", "background-color:#EFC9FF");
                        $(".sticky-note-theme .admin-reply").attr("style", "background-color:#EFC9FF");
                        $("textarea.sticky-note-text-editor").attr("style", "background-color:#EFC9FF");
                        $("button.note-reply").attr("style", "background-color:#B45FF6");
                break;
                case 'color-4 color-option':
                        $(".note-top-option").attr("style", "background-color:#F87098");
                        $(".sticky-note-theme").attr("style", "background-color:#FFC9DD");
                        $(".sticky-note-theme .login_alert").attr("style", "background-color:#FFC9DD");
                        $(".sticky-note-theme .your-comment").attr("style", "background-color:#FFC9DD");
                        $(".sticky-note-theme .admin-reply").attr("style", "background-color:#FFC9DD");
                        $("textarea.sticky-note-text-editor").attr("style", "background-color:#FFC9DD");
                        $("button.note-reply").attr("style", "background-color:#F87098");
                break;
                case 'color-5 color-option':
                        $(".note-top-option").attr("style", "background-color:#3FD981");
                        $(".sticky-note-theme").attr("style", "background-color:#DEF6D9");
                        $(".sticky-note-theme .login_alert").attr("style", "background-color:#DEF6D9");
                        $(".sticky-note-theme .your-comment").attr("style", "background-color:#DEF6D9");
                        $(".sticky-note-theme .admin-reply").attr("style", "background-color:#DEF6D9");
                        $("textarea.sticky-note-text-editor").attr("style", "background-color:#DEF6D9");
                        $("button.note-reply").attr("style", "background-color:#3FD981");
                break;
                case 'color-6 color-option':
                        $(".note-top-option").attr("style", "background-color:#AEAEAE");
                        $(".sticky-note-theme").attr("style", "background-color:#EBEBEB");
                        $(".sticky-note-theme .login_alert").attr("style", "background-color:#EBEBEB");
                        $(".sticky-note-theme .your-comment").attr("style", "background-color:#EBEBEB");
                        $(".sticky-note-theme .admin-reply").attr("style", "background-color:#EBEBEB");
                        $("textarea.sticky-note-text-editor").attr("style", "background-color:#EBEBEB");
                        $("button.note-reply").attr("style", "background-color:#AEAEAE");
                break;
                case 'color-7 color-option':
                        $(".note-top-option").attr("style", "background-color:#686868");
                        $(".sticky-note-theme").attr("style", "background-color:#5D5D5D;color:#ffff");
                        $(".sticky-note-theme .login_alert").attr("style", "background-color:#5D5D5D;color:#ffff");
                        $(".sticky-note-theme .your-comment").attr("style", "background-color:#5D5D5D;color:#ffff");
                        $(".sticky-note-theme .admin-reply").attr("style", "background-color:#5D5D5D;color:#ffff");
                        $("textarea.sticky-note-text-editor").attr("style", "background-color:#5D5D5D;color:#ffff");
                        $("button.note-reply").attr("style", "background-color:#686868");
                break;

            }

            var topOption = jQuery('.note-top-option').css('backgroundColor'),
            textEditorBG = jQuery('textarea.sticky-note-text-editor').css('backgroundColor'),
            noteOptions = {
                topoption:topOption,
                texteditorbg:textEditorBG
            }
            noteOptions = JSON.stringify(noteOptions);
            setCookie('noteoptions', noteOptions, 365);

        });
        

        jQuery('.reply').click(function(){
            jQuery(this).next('.modal-overlay').addClass('active');
            jQuery(this).next('.modal-overlay').find('.modal').addClass('active');
        });
    
        jQuery('.close-modal').click(function(){
            jQuery(this).closest('.modal-overlay').removeClass('active');
            jQuery(this).closest('.modal').removeClass('active');
        });
        

        // onclick submit button qtip hide 
        function hideQtip(event){
            
        }

        // Set Local storage if click new commet  wp-admin-bar-note_new_comment
        // jQuery(document.body).on('click', 'li#wp-admin-bar-note_new_comment a', function(e){
        //     e.preventDefault();
        //     localStorage.setItem("notestatus", 'active');
        //     var status = localStorage.getItem("notestatus");
        //     if(status == 'active'){
        //         jQuery('sub.note-question').removeClass('d-none');
        //     }
        // });

        jQuery(document.body).on('click', ".note-go-to-comment", function(){

            jQuery.ajax({
                type : 'post',
                dataType: 'json',
                data : {
                    'action'                  : 'allcommentajax' 
                },
                url : notesAjax.ajax,
                success:function(data){
                    console.log(data);
                    if(data.message == 'success'){
                        var win = window.open(data.page_url , '_blank');
                        win.focus();
                    }
                }
            });
        });

        jQuery(document.body).on('click', ".note-top-option-delete-comment", function(){

            var position = jQuery(this).data('position');

            jQuery.ajax({
                type : 'post',
                dataType: 'json',
                data : {
                    'position'                : position,
                    'action'                  : 'deletecommentajax' 
                },
                url : notesAjax.ajax,
                success:function(data){
                    console.log(data);
                    if(data.message == 'success'){
                        window.location.reload();
                    }
                }
            });
        });

        jQuery(document.body).on('click', "#wp-admin-bar-admin_bar_custom_menu > a", function(){
            event.preventDefault();
            console.log('data');
        });

        jQuery(document.body).on('click', "#wp-admin-bar-note_old_comments a", function(){
            jQuery.ajax({
                type : 'post',
                dataType: 'json',
                data : {
                    'action'                  : 'allcommentajax' 
                },
                url : notesAjax.ajax,
                success:function(data){
                    console.log(data);
                    if(data.message == 'success'){
                        var win = window.open(data.page_url , '_blank');
                        win.focus();
                    }
                }
            });
        });

        //likeAndDislike start

        jQuery(document.body).on('click', ".wp-ssn-user-like", function(){
            jQuery(this).closest('.wp-ssn-user-comment-like').find('.wp-ssn-user-unlike').removeClass('unlike-h');
            jQuery(this).toggleClass('like-h');
            

            var parntid = jQuery(this).closest('div.wp-ssn-user-comment-like').data('id');
            var parntreply = jQuery(this).closest('div.wp-ssn-user-comment-like').data('reply');
            var user_id = notesAjax['user_id'];
            
            var parntlike = jQuery(this).next('.wp-ssn-user-like-cunter').text();
            var parntlike = Number(parntlike);
            var parntunlike = jQuery(this).closest('li').next('li').find('.wp-ssn-user-unlike-cunter').text();
            var parntunlike = Number(parntunlike);
            // jQuery(this).closest('li').addClass('testclass');

            if ( jQuery(this).hasClass("like-h") == '' ) { 
                parntlike -= 1;
            }else{
                parntlike += 1;
                if(parntunlike != 0){
                    parntunlike -=1;
                }
            }
            jQuery(this).next('.wp-ssn-user-like-cunter').text(parntlike);
            jQuery(this).closest('li').next('li').find('.wp-ssn-user-unlike-cunter').text(parntunlike);

            jQuery.ajax({
                type : 'post',
                dataType: 'json',
                data : {
                    'parntid'                 : parntid,
                    'parntreply'              : parntreply,
                    'user_id'                 : user_id,
                    'action'                  : 'likeajax' 
                },
                url : notesAjax.ajax,
                success:function(data){
                    console.log(data);
                    if(data.message == 'success'){
                    }
                }
            });
        });

        jQuery(document.body).on('click', ".wp-ssn-user-unlike", function(){  
            // jQuery('.wp-ssn-user-like').removeClass('like-h');


            jQuery(this).closest('.wp-ssn-user-comment-like').find('.wp-ssn-user-like').removeClass('like-h');
            jQuery(this).toggleClass('unlike-h');
            var parntid = jQuery(this).closest('div.wp-ssn-user-comment-like').data('id');
            var parntreply = jQuery(this).closest('div.wp-ssn-user-comment-like').data('reply');
            var user_id = notesAjax['user_id'];

            var parntunlike = jQuery(this).next('.wp-ssn-user-unlike-cunter').text();
            var parntunlike = Number(parntunlike);

            var parntlike = jQuery(this).closest('li').prev('li').find('.wp-ssn-user-like-cunter').text();
            var parntlike = Number(parntlike);
            if ( jQuery(this).hasClass("unlike-h") == '' ) { 
                parntunlike -= 1;
            }else{
                parntunlike += 1;
                if(parntlike != 0){
                    parntlike -=1;
                }
            }

            jQuery(this).next('.wp-ssn-user-unlike-cunter').text(parntunlike);
            jQuery(this).closest('li').prev('li').find('.wp-ssn-user-like-cunter').text(parntlike);
            
            jQuery.ajax({
                type : 'post',
                dataType: 'json',
                data : {
                    'parntid'                 : parntid,
                    'parntreply'              : parntreply,
                    'user_id'                 : user_id,
                    'action'                  : 'unlikeajax' 
                },
                url : notesAjax.ajax,
                success:function(data){
                    console.log(data);
                    if(data.message == 'success'){
                    }
                }
            });
        });
        //end
        if(notesAjax.rich_text_editor == '1' ){
            jQuery(document.body).on('click', ".sticky-note-text-editor", function(){ 
                // console.log('keyup');  
                tinyMCE.init({
                    mode : "none",
                    statusbar: false,
                    charLimit : notesAjax.wssn_restrict_number,
                    setup: function (editor) {
                        editor.on('change', function () {

                            var words = this.getContent().match(/\S+/g).length;
                            var counter = this.settings.charLimit;
                            var word = counter;

                            if (words > counter) {
                                alert('To bigToo big with your comment. Please try to write within '+ word +' words');
                                jQuery('.note-reply').css('display','none');
                            }
                            else {
                                jQuery('.wpssn_rchars').text(counter-words);
                                jQuery('.note-reply').css('display','block');
                            }
                            editor.save();

                        });

                    },
                });
                tinyMCE.execCommand('mceAddEditor', false, 'sticky_note_text_editor');
            });
        }

        jQuery(document.body).on('keyup', ".sticky-note-text-editor", function(){  
            var words = this.value.match(/\S+/g).length;
            var counter = notesAjax.wssn_restrict_number;
            if (words > counter) {
                var trimmed = jQuery(this).val().split(/\s+/, counter).join(" ");
                jQuery(this).val(trimmed + " ");
            }
            else {
                jQuery('.wpssn_rchars').text(counter-words);
            }
        });





        // jQuery(document.body).on('click', ".note-question-icon-button", function(){ 



        // });

        // $(document).ready(function() {
        //     tinyMCE.init({
        //         mode : "none",
        //         statusbar: false,
        //         setup: function (editor) {
        //             editor.on('change', function () {
        //                 editor.save();
        //             });
        //         },
        //     });
        //     tinyMCE.execCommand('mceAddEditor', false, 'sticky_note_text_editor');
            
        
        // });
        // jQuery(document).ready(function() {
        //     settings = { tinymce: true, quicktags: true }
            
        // wp.editor.initialize('sticky_note_text_editor', settings);
        // });


        // wp.editor.initialize( 'sticky_note_text_editor', {

        //     tinymce: {
        //         wpautop  : true,
        //         theme    : 'modern',
        //         skin     : 'lightgray',
        //         language : 'en',
        //         formats  : {
        //             alignleft  : [
        //                 { selector: 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li', styles: { textAlign: 'left' } },
        //                 { selector: 'img,table,dl.wp-caption', classes: 'alignleft' }
        //             ],
        //             aligncenter: [
        //                 { selector: 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li', styles: { textAlign: 'center' } },
        //                 { selector: 'img,table,dl.wp-caption', classes: 'aligncenter' }
        //             ],
        //             alignright : [
        //                 { selector: 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li', styles: { textAlign: 'right' } },
        //                 { selector: 'img,table,dl.wp-caption', classes: 'alignright' }
        //             ],
        //             strikethrough: { inline: 'del' }
        //         },
        //         relative_urls       : false,
        //         remove_script_host  : false,
        //         convert_urls        : false,
        //         browser_spellcheck  : true,
        //         fix_list_elements   : true,
        //         entities            : '38,amp,60,lt,62,gt',
        //         entity_encoding     : 'raw',
        //         keep_styles         : false,
        //         paste_webkit_styles : 'font-weight font-style color',
        //         preview_styles      : 'font-family font-size font-weight font-style text-decoration text-transform',
        //         tabfocus_elements   : ':prev,:next',
        //         plugins    : 'charmap,hr,media,paste,tabfocus,textcolor,fullscreen,wordpress,wpeditimage,wpgallery,wplink,wpdialogs,wpview',
        //         resize     : 'vertical',
        //         menubar    : false,
        //         indent     : false,
        //         toolbar1   : 'bold,italic,strikethrough,bullist,numlist,blockquote,hr,alignleft,aligncenter,alignright,link,unlink,wp_more,spellchecker,fullscreen,wp_adv',
        //         toolbar2   : 'formatselect,underline,alignjustify,forecolor,pastetext,removeformat,charmap,outdent,indent,undo,redo,wp_help',
        //         toolbar3   : '',
        //         toolbar4   : '',
        //         body_class : 'id post-type-post post-status-publish post-format-standard',
        //         wpeditimage_disable_captions: false,
        //         wpeditimage_html5_captions  : true
        
        //     },
        //     quicktags   : true,
        //     mediaButtons: true
        
        // } )

   }); // End Document ready



function openTab(evt, cityName) {
    var i, tabcontent, tablinks;
    tabcontent = document.getElementsByClassName("tabcontent");
    for (i = 0; i < tabcontent.length; i++) {
        tabcontent[i].style.display = "none";
    }
    tablinks = document.getElementsByClassName("tablinks");
    for (i = 0; i < tablinks.length; i++) {
        tablinks[i].className = tablinks[i].className.replace(" active", "");
    }
    document.getElementById(cityName).style.display = "block";
    evt.currentTarget.className += " active";
}

function setCookie(name,value,days) {
    var expires = "";
    if (days) {
        var date = new Date();
        date.setTime(date.getTime() + (days*24*60*60*1000));
        expires = "; expires=" + date.toUTCString();
    }
    document.cookie = name + "=" + (value || "")  + expires + "; path=/";
}


    // // Returns text statistics for the specified editor by id
    // function getStats(id) {
    //     var body = tinymce.get(id).getBody(), text = tinymce.trim(body.innerText || body.textContent);
    
    //     return {
    //         chars: text.length,
    //         words: text.split(/[\w\u2019\'-]+/).length
    //     };
    // } 
    
    
    
    
    
    // function submitForm() {
    //         // Check if the user has entered less than 10 characters
    //         if (getStats('content').chars < 10) {
    //             alert("You need to enter 1000 characters or more.");
    //             return;
    //         }
    
    //         // Check if the user has entered less than 1 words
    //         if (getStats('content').words < 1) {
    //             alert("You need to enter 1 words or more.");
    //             return;
    //         }
    
    //         // Submit the form
    //         document.forms[0].submit();
    //     }