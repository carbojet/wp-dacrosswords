(function($) {

    var row =  $('input[name=rows]').val();
    var col =  $('input[name=cols]').val();
    var chapterName = $('input[name=chapter_name]').val();
    var desc = 'Description';
    var dir = 'across';
    var lastClick = '';
    var lastActive = '';
    var wordStart  = 0;
    var typing = false;
    var fullWord = Array();
    var globalWords = '';
    var clues = Array();
    var chId = '';
    var editch = false;
    var editClue = false;
    var newChapter = null;
    var currentClue = {};
    var currentCh = {};
    var currentClueIndex = '';
    var frame;
    var elid = new Array();
    if(adminlocaljs.chapters=='' || adminlocaljs.chapters==null){
        var chapters = Array();
    }else{
        var chapters = adminlocaljs.chapters;
        console.log(chapters)
    }
    
    $(document).ready(function(){

        $('body').on('click','#add-intro-file',function(e){
            e.preventDefault();
            if ( frame ) {
                frame.open();
                return;
            }  
            frame = wp.media({
                title: 'Select or Upload the PDF',
                library: {type: 'application/pdf'},
                button: {
                    text: 'Use this media'
                },
                multiple: false,
                // Set to true to allow multiple files to be selected
            });

            frame.on( 'select', function() {
                var attachment = frame.state().get('selection').toJSON();
                var $html = '';
                $.each(attachment,function(index, el) {
                    elid.push(el.id);                    
                });
                $('#intro-file-id').val(elid.toString());
            });
            frame.open();

        })

        $('body').on('click','button[name=italic-mark]',function(e){
            e.preventDefault();
            document.execCommand('italic');
        })
        $('body').on('click','button[name=bold-mark]',function(e){
            e.preventDefault();
            document.execCommand('bold');
        })
        
        $('.text-editor').on('input',function(e){
            content = $(this).html().replace('<i>','&lt;I&gt;').replace('</i>','&lt;/I&gt;').replace('<b>','&lt;B&gt;').replace('</b>','&lt;/B&gt;')
            //console.log(content)
            $(this).next('textarea').val(content)
        })
        
        $('body').on('keypup','.text-editor',function(e){
            var key = e.which || e.keyCode;
            ctrl = e.ctrlKey;
            
            
            console.log(ctrl)
        })
        
        $('form#post').on('submit',function(e){
            //$('input[name=chapter_data]').val(JSON.stringify(chapters));
    
        });
        //convert chapter
        $('body').on('click','button[name=convert_ch]',function(){
            chId = $(this).attr('id');
            currentCh = chapters[chId];
            clues = currentCh.clues
            cw_no_chapters = chapters.length;

            var elem = $(this)

            postData = {
                action:'add_clue_chapter',
                clues:clues,
                post_id:adminlocaljs.current_post_id,
                chapter_name:currentCh.chapterName,
                rows:currentCh.rows,
                cols:currentCh.cols,
            }; 
            $.ajax({
                url: adminlocaljs.ajaxUrl,
                type:'post',
                data:postData,
                async: false,
                beforeSend:function(){
                    $(elem).find('span.compress-status').show();
                },
                success:function(responce){
                    
                    //$(elem).text('Compress');
                    $(elem).find('span.compress-status').hide();
                    console.log(responce)
                },
                error:function(error){
                    console.log(error);
                }
            })
        })
        
        //delete chapter
        $('body').on('click','.operation .delete',function(){
            //chapters.splice($(this).attr('id'),1);
            $(this).closest('.chapter').remove();
            
            postData = {
                action:'delete_chapter',
                post_id:adminlocaljs.current_post_id,
                chapter_index:$(this).attr('id'),
                cw_no_chapters:$('input[name=cw_no_chapters]').val(),
            }; 
            $.ajax({
                url: adminlocaljs.ajaxUrl,
                type:'post',
                data:postData,
                async: false,
                success:function(responce){
                    console.log(responce)
                    $('#detailsTable').hide();
                    $('#cw_table').html('');
                },
                error:function(error){
                    console.log(error);
                }
            })
            
        })
        $('body').on('click','.operation .edit',function(){			
            
            chId = $(this).attr('id');
            $('button[name=convert_ch]').attr('id',chId);
            $('button[name=new_crossword_convert]').attr('id',chId);
            if($(this).attr('data-compress')=='true'){
                $('#detailsTable').find('button[name=convert_ch]').hide();
            }else{
                $('#detailsTable').find('button[name=convert_ch]').show();
            }
            $('#detailsTable').show();
            $('#cw_table').html('');
            if(chId!='-1'){
                editch = true;
                //console.log(editch)
                currentCh = chapters[chId];
                                //console.log(chapters)
                //creating first time clue detail
                if(currentCh.clues!==undefined){
                    postData = {
                        action:'convert_cw_chapter',
                        chapterName:currentCh.chapterName,
                        rows:currentCh.rows,
                        cols:currentCh.cols,
                        post_id:adminlocaljs.current_post_id,
                        chapter_index:chId,
                        no_of_clues:currentCh.clues.length,
                    }; 
                    $.ajax({
                        url: adminlocaljs.ajaxUrl,
                        type:'post',
                        data:postData,
                        success:function(responce){
                            //responce = $.parseJSON(responce)
                            console.log(responce);
                        },
                        error:function(error){
                            console.log(error);
                        }
                    });
                }else{
                    console.log('else')
                    postData = {
                        action:'get_cw_chapter_clues',
                        post_id:adminlocaljs.current_post_id,
                        chapter_index:chId,
                    };
                    $.ajax({
                        url: adminlocaljs.ajaxUrl,
                        type:'post',
                        data:postData,
                        async: false,
                        success:function(responce){
                            console.log(responce);
                            currentCh = responce.data;
                            chapters[chId] = currentCh;
                        },
                        error:function(error){
                            console.log(error);
                        }
                    });
                }
                
                $('input[name=cols]').val(currentCh.cols);
                col = currentCh.cols;
                $('input[name=rows]').val(currentCh.rows);
                row = currentCh.rows;
                console.log(currentCh)
                $('input[name=chapter_name]').val(currentCh.chapterName);
                
                if(currentCh.chapterAuthorName!=null){
                    //$('div[data-name=chapter_author_name]').html(currentCh.chapterAuthorName.replace('&lt;I&gt;','<i>').replace('&lt;/I&gt;','</i>').replace('&lt;B&gt;','<b>').replace('&lt;/B&gt;','</b>'))
                    $('input[name=chapter_author_name]').val(currentCh.chapterAuthorName)
                    
                }
                
                $('input[name=chapter_id]').val(chId);
                
                createTextBoxTable();
                viewAllPredefinedWords(currentCh.clues);
                
            }else{			
                /*
                editch = false;
                console.log(chapters);
                if(chapters==null){
                    chId = 0;
                }else{
                    chId = chapters.length;
                }
                $('input[name=cols]').val(null);col = 0;
                $('input[name=rows]').val(null);row = 0;
                $('input[name=chapter_name]').val(null);
                createTextBoxTable();				
                */
            }		
            
        });
        $.fn.getCurrentCh = function(){
            $('#cw_table').html('');
            createTextBoxTable();
            viewAllPredefinedWords(currentCh.clues);
        }
        $('body').on('click','.chapter.new',function(){
            $('#detailsTable').show();
            $('#cw_table').html('');
            editch = false;
            if(chapters==null){
                chId = 0;
            }else{
                chId = chapters.length;
            }
            
            $('input[name=cols]').val(null);col = 0;
            $('input[name=rows]').val(null);row = 0;
            $('input[name=chapter_name]').val(null);
            createTextBoxTable();
        
        })
        //loading the XML File
        var xmlBookData={};
        $('#cw_custom_attachment').on('change',function(){
            if(this.files && this.files[0]){
                cwFileName =	this.files[0].name; 
                cwFileType =	this.files[0].type;
                
                if(cwFileType!='text/xml'){
                    $('#cw_file_msg').html(cwFileName+' is not a XML file.');
                    $(this).val('');
                    return false;
                }else{
                    $('#cw_file_msg').html('<i class="fa fa-spinner fa-spin fa-3x"></i>');
                    $('#cw_custom_attachment').hide();
                }
                
                var reader = new FileReader();
                reader.readAsText(this.files[0]);
                reader.onload = function(e){
                    var xmlData = e.target.result;
                    var xml = $.parseXML(xmlData);
                    
                    xml = $(xml).children('gametitle');
                    xml = $(xml).children('books');
                    xml = $(xml).children('book');
                    
                    var xmlDesc = $(xml).children('desc').html();
                    var xmlCol = $(xml).children('cellsacross').html();
                    var xmlRow = $(xml).children('cellsdown').html();
                    
                    xml = $(xml).children('crosswords');
                    
                    xml = $(xml).children('crossword');
                    
                    //chapters
                    var xmlBookData = {};
                    $.each(xml,function(chKey,xmlChapter){
                        if(chKey<82){
                        xmlChapter = $(xmlChapter);
                        xmlChapterName = $(xmlChapter).children('title').html();
                        var xmlClues = $(xmlChapter).children('clues').children('clue');
                        
                        //clues/words
                        var xmlClueData = {};
                        $.each(xmlClues,function(clKey,xmlClue){
                            
                            xmlClue = $(xmlClue);
                            
                            xmlWord = {
                                    'number'				:	$(xmlClue).children('number').html(),
                                    'hint'					:	$(xmlClue).children('hint').html(),
                                    'word'					:	$(xmlClue).children('answer').html(),
                                    'X'						:	$(xmlClue).children('y').html(),
                                    'Y'						:	$(xmlClue).children('x').html(),
                                    'dir'					:	$(xmlClue).children('direction').html(),
                                    'clue1'					:	$(xmlClue).children('clue1').html(),
                                    'clue2'					:	$(xmlClue).children('clue2').html()
                                    }							
                            xmlClueData[clKey] = xmlWord;
                        })
                        
                        var xmlTemp = {
                            'clues':xmlClueData,
                            'cols':xmlCol,
                            'rows':xmlRow,
                            'chapterName':xmlChapterName
                        };
                        xmlBookData[chKey] = xmlTemp;
                        }
                        
                    })
                    //console.log(xmlBookData);
                    $('#cw_xml_data').val(JSON.stringify(xmlBookData));
                    $('input[name=post_title]').val(cwFileName.split('.')[0]);
                    $('input[name=post_title]').trigger('focus');
                    $('#cw_desc').html(xmlDesc);
                    $('#cw_file_msg').html(cwFileName);
                    $('#cw_chapters_meta_box').hide();
                    $(this).val('');
                }
            }
        })
        
        
        $('body').on('click','input[name=add_new_word]',function(event){
            lastActive = $(document.activeElement).attr('name');			
            editClue = false;
            if(lastActive=='add_new_word'){
                alert('Please select any one cell where you want to start')
                return false;
            }
            x = lastActive.split('_')[1];
            y = lastActive.split('_')[2];
            currentClue = {
                X:x,
                Y:y,
                clue1:'',
                clue2:'',
                dir:dir,
                hint:'',
                number:'',
                word:''
            }
            openModal(currentClue);
        })
        //delete word
        $('body').on('click','button[name=delete_word]',function(e){
            e.preventDefault();
            elem = $(this)
            var retVal = confirm("Do you want to Delete ?");
               if( retVal == true ) {
                    //clues.splice(currentClueIndex, 1);
                   currentCh.clues = clues;
                   
                   postData = {
                        action:'delete_clue_chapter',
                        post_id:adminlocaljs.current_post_id,
                        chapter_index:chId,
                        clue_index:currentClueIndex,
                    }; 
                    console.log(postData)
                    $.ajax({
                        url: adminlocaljs.ajaxUrl,
                        type:'post',
                        data:postData,
                        async: false,
                        beforeSend:function(){
                            $(elem).attr('disabled','disabled');
                        },
                        success:function(responce){
                            $(elem).removeAttr('disabled');
                            console.log(responce)
                            $('#myModal').modal('toggle');
                            currentCh = responce.data;
                            chapters[chId] = currentCh;
                            
                            $.fn.getCurrentCh();							
                        },
                        error:function(error){
                            console.log(error);
                        }
                    })
               } else {
                  return false;
               }
                        
        })
        $('body').on('click','button[name=update_chapter]',function(){
            var elem = $(this);
            
            if($.isEmptyObject(currentCh)){
                updateChapter = {
                    'cols':Number($('input[name=cols]').val()),
                    'rows':Number($('input[name=rows]').val()),
                    'chapter_index':0,
                    'no_of_clues':0,
                    'chapterName':$('input[name=chapter_name]').val(),
                    'chapterAuthorName':$('input[name=chapter_author_name]').val()
                }
                editch = true;
            }else{
                updateChapter = {
                    'cols':currentCh.cols,
                    'rows':currentCh.rows,
                    'chapter_index':chId,
                    'no_of_clues':currentCh.clues.length,
                    'chapterName':$('input[name=chapter_name]').val(),
                    'chapterAuthorName':$('input[name=chapter_author_name]').val()
                }
            }
            
            
            
            postData = {
                action:'update_chapter',
                chapter:updateChapter,
                post_id:adminlocaljs.current_post_id,
    
            };
            console.log(postData)
            $.ajax({
                url: adminlocaljs.ajaxUrl,
                type:'post',
                data:postData,
                async: false,
                beforeSend: function() {
                    $(elem).attr('disabled','disabled');
                },
                success:function(responce){
                    console.log(responce)
                    currentCh = responce.data;
                    $(elem).removeAttr('disabled');
                },
                error:function(error){
                    console.log(error);
                }
            })
            
        })
        //edit single word
        $('body').on('click','button[name=save]',function(event){
            elem = $(this)
            event.preventDefault();
            error = false;
            
            if($('#cw_x').val()=='' || $('#cw_y').val()==''){
                $('#coordinates-error').html('Provide the Coordinates.').show();
                error = true;
            }else{
                $('#coordinates-error').html('').hide();
            }
            
            if($('#cw_number').val()==''){
                $('#number-error').html('Provide the Number.').show();
                error = true;
            }else{
                $('#number-error').html('').hide();
            }
            
            if($('#cw_word').val()==''){
                $('#word_error').html('Provide a word.').show();
                error = true;
            }else{
                $('#word_error').html('').hide();
            }
            
            if(error){
                return error;
            }
            //a single word data
            var wordData = {
                    'number'		:	$('#cw_number').val(),
                    'clueindex'     :   $('#cw_clue_index').val(),
                    'X'				: 	$('#cw_x').val(),
                    'Y'				: 	$('#cw_y').val(),
                    'word'			:	$('input#cw_word').val().toUpperCase(),
                    'dir'			: 	$('select#cw_dir').val(),
                    'clue1'			:	$('#clue').val(),
                    'clue2'			:	$('#cw_clue2').val(),
                    'hint'			:	$('#hint').val()
            }

            
            if(editch==true && editClue==true){
                console.log('inside 1')
                currentCh.clues[currentClueIndex] = wordData;
                chapters[chId] = currentCh;
                cw_no_chapters = chapters.length;
                postData = {
                    action:'add_clue_chapter',
                    clue:currentCh.clues,
                    post_id:adminlocaljs.current_post_id,
                    chapter_index:chId,
                    //cw_no_chapters:cw_no_chapters,
                    //clue_index:currentClueIndex
                }; 
                console.log(postData);
                $.ajax({
                    url: adminlocaljs.ajaxUrl,
                    type:'post',
                    data:postData,
                    async: false,
                    beforeSend: function() {
                          $(elem).attr('disabled','disabled');
                       },
                    success:function(responce){
                        console.log(responce)
                        $(elem).removeAttr('disabled')
                        $('#status_msg').removeAttr('class').addClass('text-success').html('Saved').fadeIn().delay(2000).fadeOut();
                        $.fn.getCurrentCh()
    
    
                        $('input[name='+lastActive+']').trigger('focusout');
                        typing=true;
                        wordStart=0;
                        lastActive='';
                        lastClick = '';
                        $('#clue').val('');
                        $('#hint').val('');
                        $('input.tdClass').parent().removeClass('selected-indexs');
                        $('#myModal').modal('toggle');
                    },
                    error:function(error){
                        console.log(error);
                    }
                })
                
            }else if(editch==true && editClue==false){
                console.log('inside 2')
                if($.isEmptyObject(currentCh)){
                    
                    console.log('empty current chapter')
                }else{
                    console.log(currentCh)
                    if(currentCh.hasOwnProperty('clues')){
                        console.log('yes clues is there'+ currentCh.clues)
                    }else{
                        console.log('no clues')
                    }    
                }
                
                
                currentCh.clues.push(wordData)
                
                if(currentCh.chapter_index>0){
                    chId = currentCh.chapter_index;
                }
                
                chapters[chId] = currentCh;
                key = currentCh.clues.length;
                cw_no_chapters = chapters.length;
                postData = {
                    action:'add_clue_chapter',
                    //clue:wordData,
                    clue:currentCh.clues,
                    post_id:adminlocaljs.current_post_id,
                    chapter_index:chId,
                    cw_no_chapters:cw_no_chapters,
                    clue_index:key
                }; 
                console.log(postData);
                $.ajax({
                    url: adminlocaljs.ajaxUrl,
                    type:'post',
                    data:postData,
                    async: false,
                    beforeSend: function() {
                          $(elem).attr('disabled','disabled');
                       },
                    success:function(responce){
                        console.log(responce)
                        
                        $(elem).removeAttr('disabled').show();
                        $('#status_msg').removeAttr('class').addClass('text-success').html('Saved').fadeIn().delay(2000).fadeOut();
                        $.fn.getCurrentCh()
    
                        $('input[name='+lastActive+']').trigger('focusout');
                        typing=true;
                        wordStart=0;
                        lastActive='';
                        lastClick = '';
                        $('#clue').val('');
                        $('#hint').val('');
                        $('input.tdClass').parent().removeClass('selected-indexs');
                        $('#myModal').modal('toggle');
                    },
                    error:function(error){
                        console.log(error);
                    }
                })
                
            }else{
                console.log('inside 3')
                if(newChapter==null){
                    if(chapters==null){
                        newchId = chId;
                    }else{
                        newchId=chapters.length;
                    }					
                    clues = Array();
                    clues.push(wordData);
                    newChapter = {
                    'clues':clues,
                    'cols':col,
                    'rows':row,
                    'chapterName':$('input[name=chapter_name]').val(),
                    'chapterAuthorName':$('input[name=chapter_author_name]').val()
                    }
                    
                    postData = {
                        action:'add_chapter',
                        chapter:newChapter,
                        post_id:adminlocaljs.current_post_id,
                        
                    };
                    console.log(postData);
                    $.ajax({
                    url: adminlocaljs.ajaxUrl,
                    type:'post',
                    data:postData,
                    async: false,
                    beforeSend: function() {
                          $(elem).attr('disabled','disabled');
                       },
                    success:function(responce){
                        console.log(responce)
                        
                        $(elem).removeAttr('disabled').show();
                        $('#status_msg').removeAttr('class').addClass('text-success').html('Saved').fadeIn().delay(2000).fadeOut();
    
                        //after adding new chapter changing chapter id 
                        result = responce.data;                    
                        newchId = result.chapter_id
                        currentCh = newChapter;
                        chapters[newchId] = newChapter;
                        chapterDiv = '<div class="chapter" id="'+newchId+'"><h4>'+newChapter.chapterName+'</h4><div class="operation"><span id="'+newchId+'" class="delete"><i class="fa fa-trash"></i></span><span id="'+newchId+'" class="edit"><i class="fa fa-pencil"></i></span></div></div>';				
                        $('.chapter').parent().prepend(chapterDiv)
    
    
                        $.fn.getCurrentCh()
    
                        $('input[name='+lastActive+']').trigger('focusout');
                        typing=true;
                        wordStart=0;
                        lastActive='';
                        lastClick = '';
                        $('#clue').val('');
                        $('#hint').val('');
                        $('input.tdClass').parent().removeClass('selected-indexs');
                        $('#myModal').modal('toggle');
                    },
                    error:function(error){
                        console.log(error);
                    }
                })
                    
                }
                
                
            }
            
            //$('input[name=chapter_data]').val(JSON.stringify(chapters));
            //$('#chapter_data').val(JSON.stringify(chapters));
    
        });
        
        //allow only letters and backspace
        $('body').on('keydown','input.tdClass',function(e){
            keyVal = $(this).val();
            lastActive = $(this).attr('name')
            var key = e.which || e.keyCode;
            if(key<65 || (key>90 && key<97) || key >122){
                event.preventDefault();
                if(key==8){
                    if(editch){
                        return false;
                    }
                    x = $(this).attr('name').split('_') [1];
                    y = $(this).attr('name').split('_') [2];
                    $('input[name=letter_' + x + '_' + y +']').val('');
                    
                    
                    
                    //reset the value if all letters are deleted
                    if($(this).attr('name') == wordStart){
                        //$('body').on('click');
                        typing=false;
                        wordStart = 0;
                        lastClick='';
                        fullWord = Array();
                    }
                    /*
                    if($(this).data('filled')=='1'){
                        $(this).val(keyVal);
                        //return false;
                    }
                    */
    
                    back(x,y);
                    
                }else if(key==13){					
                    clues = currentCh.clues;
                    if(dir=='across'){
                        currentClueIndex = $(this).data('across-index');
                        
                    }else{
                        currentClueIndex = $(this).data('down-index');
                    }
                    if(currentClueIndex === undefined){
                        $('input[name=add_new_word]').trigger('click')
                    }else{
                        editClue = true;
                        currentClue = clues[currentClueIndex];
                        openModal(currentClue);
                    }
                }else if(key==9){
                    if($(this).val() !=''){
                        if(!typing){
                            wordStart =  $(this).attr('name');
                            typing=true;
                        }
                        //push the value at the end of the fullWord
                        x = $(this).attr('name').split('_') [1];
                        y = $(this).attr('name').split('_') [2];
                        x1=x;y1=y;
                        theWord($(this).attr('name'));
                        next(x1,y1);
                        
                    }
                }
                return;
            }
        });
        
        //move next
        $('body').on('input','input.tdClass',function(event){
            if(!typing){
                wordStart =  $(this).attr('name');
                typing=true;
            }
            
            
            var item = $(this).attr('name');
            var x = Number(item.split('_')[1]);
            var y = Number(item.split('_')[2]);
            
            $(this).val($(this).val().toUpperCase());
            
            //push the value at the end of the fullWord
            theWord($(this).attr('name'));
            
            next(x,y);
        });
        
        //highlight
        $('body').on('click','input.tdClass',function(event){
            /*
            if(typing){
                return false;
            }
            */
            $('table').find('.selected-indexs').removeClass('selected-indexs');
            //toggle direction
            if(lastClick!=$(this).attr('name')){
                lastClick = $(this).attr('name');
            }else{
                dir == 'across' ? dir = 'down': dir = 'across';
            }
            var startPos = $(this).attr('name');
            var startX = startPos.split('_')[1];
            var startY = startPos.split('_')[2];
            
            
            if(dir =='across'){
                if($(this).parent().hasClass('across')){
                    startY = $(this).parent().attr('data-across-start');
                    for(i=startY;i<=Number($(this).parent().attr('data-across'));i++){
                        $('input[name=letter_' + startX + '_' + i).parent().addClass('selected-indexs');
                    }
                }else{
                    for(i=startY;i<=Number(col);i++){
                        $('input[name=letter_' + startX + '_' + i).parent().addClass('selected-indexs');
                    }
                }
                    
            }else{
                
                if($(this).parent().hasClass('down')){
                    startX = $(this).parent().attr('data-down-start');
                    for(i=startX;i<=Number($(this).parent().attr('data-down'));i++){
                        $('input[name=letter_' + i + '_' + startY).parent().addClass('selected-indexs');
                    }
                }else{
                    for(i=startX;i<=Number(row);i++){
                        $('input[name=letter_' + i + '_' + startY).parent().addClass('selected-indexs');
                    }
                }
            }
                
            /*
            if(dir =='across'){
                for(i=startY;i<=Number(col);i++){
                    $('input[name=letter_' + startX + '_' + i).parent().addClass('highlight');
                }	
            }else{
                for(i=startX;i<=Number(row);i++){
                    $('input[name=letter_' + i + '_' + startY).parent().addClass('highlight');
                }
            }
            */			
                        
        });
        
        $('body').on('mousedown',function(event){
            if(typing){
                //event.preventDefault();
                //openModal();
            }
        });
        
        $('#myModal').on('hidden.bs.modal',function(){
            closeModal();
        });
    
        //highlight the focused textbox
        $('body').on('focus','input.tdClass',function(){
            $('input.tdClass').removeClass('focus');
            $(this).addClass('focus');
        });
        
        //create the text box table
        createTextBoxTable();
        
        $('input[name=rows],input[name=cols]').change(function(){
            
            row = $('input[name=rows]').val();
            col = $('input[name=cols]').val();
            $('input[name=cw-words]').val('');
            $('table.cw_table').html('');
            createTextBoxTable();
        });
        
        
    });
    
    function viewAllPredefinedWords(editData){
        console.log(editData)
        //view previously entered data
        if(editData.length > 0){
            
            for(row1 in editData){
                var editX = editData[row1]['X'];
                var editY = editData[row1]['Y'];
                var editNumber = editData[row1]['number'];
                var clueIndex = editData[row1]['clueindex'];
                
                i=0;
                
                if(editData[row1]['dir']=='across' || editData[row1]['dir']=='Across'){
                    for(y=Number(editY);y<=Number(editY)+editData[row1]['word'].length -1;y++){
                        
                        tempdata = editData[row1];
                        if(tempdata.word.length==1){
                            $('input[name=letter_'+editX+'_'+y+']').closest('td').append('<span class="letter-count">'+tempdata.clue1+'</span>')
                        }
                        //$('input[name=letter_'+editX+'_'+y+']').val((editData[row1]['word'].split(''))[(i++)]).attr('data-filled','1').parent().css('background-color','#ccc');
                        
                        $('input[name=letter_'+editX+'_'+y+']').val((editData[row1]['word'].split(''))[(i++)]).attr('data-filled','1').attr('data-across-index',row1)
                        
                        $('input[name=letter_'+editX+'_'+y+']').parent().attr('data-across',Number(editY)+editData[row1]['word'].length -1).attr('data-across-start',editY).addClass('across')
                        
                    }
                }else{
                    for(x=Number(editX);x<=Number(editX) + editData[row1]['word'].length -1;x++){
                        //$('input[name=letter_'+x+'_'+editY+']').val((editData[row1]['word'].split(''))[(i++)]).attr('data-filled','1').parent().css('background-color','#ccc');
                        
                        $('input[name=letter_'+x+'_'+editY+']').val((editData[row1]['word'].split(''))[(i++)]).attr('data-filled','1').attr('data-down-index',row1)
                        
                        $('input[name=letter_'+x+'_'+editY+']').parent().attr('data-down',Number(editX) + editData[row1]['word'].length -1).attr('data-down-start',editX).addClass('down')
                    }					
                }
                //$('td[id=cell_'+editX+'_'+editY+'] div.index').append('<span>'+(Number(row1)+1)+'</span>').parent('td').css('background-color','#777').css('color','white');
                if(editNumber!='undefined'){
                    $('td[id=cell_'+editX+'_'+editY+'] div.index').html('<span>'+editNumber+'</span>').parent('td').addClass('start-index').find('input').css('color','white');
                }
            }
        }
    }
    
    //create the text box table
    function createTextBoxTable(){
        //create texbox table
        for(i=1;i<=row;i++){
            $('#cw_table').append('<tr data-row="'+ i +'"></tr>');
            for(j=1;j<=col;j++){
                $('tr[data-row=' + i + ']').append('<td id="cell_'+ i + '_'+ j +'"><div class="index"></div><input readonly="readonly" class="tdClass" type="text" maxlength="1" name="letter_'+ i + '_'+ j +'"/></td>');
            }
        }
    }
        
        
    //move the cursor back
    function back(x,y){
        if(wordStart=='0'){
            return false;
        }
        
        if(dir=='across'){
                $('input[name=letter_' + x + '_' + (Number(y)-1) +']').trigger('focus');
                theWord('letter_' + x + '_' + (Number(y)-1));
            }else{
                
                $('input[name=letter_' + (Number(x)-1) + '_' + y +']').trigger('focus');
                theWord('letter_' + (Number(x)-1) + '_' + y);
            }
    }
    //move curson next
     function next(x,y){
        if(dir=='across'){
                var temp = $('input[name=letter_' + x + '_' + (Number(y)+1) + ']');
                temp.trigger('focus');
            }else{
                var temp = $('input[name=letter_' + (Number(x)+1) + '_' + y + ']');
                temp.trigger('focus');
            }
    }
    //open
    function closeModal(){
        if(!typing){
            $('input[name=' + lastActive+']').trigger('focus');
            typing=true;
        }
        
        
        
    }
    
    
    
    //open the modal
    function openModal(obj={}){		
        if(obj.word===undefined && obj.X!==undefined){			
            /*
            if(fullWord=='' || fullWord==0){
                return;
            }
            
            typing=false;
            $('#chapterName').html(chapterName);
            $('#word').html(fullWord);
            $('#position').html(wordStart.split('_')[1] + '-' + wordStart.split('_')[2]);
            $('#clue').trigger('focus').val('');
            $('#hint').val('');
            */
            $('#cw_x').val(obj.X)
            $('#cw_y').val(obj.Y)
            $('#clue').val('')
            $('div[data-name=clue]').html('')
            $('#cw_clue2').val('')
            $('#cw_clue_index').val(obj.clueindex)
            $('div[data-name=cw_clue2]').html('')
            if(dir=='across' || dir=='Across'){
                $('select#cw_dir').val('Across')
            }else{
                $('select#cw_dir').val('Down')
            }
            
            $('#hint').val('')
            $('div[data-name=hint]').html('')
            $('#cw_word').val('')
            $('#cw_number').val('')
            
        }else{
            $('#cw_x').val(obj.X)
            $('#cw_y').val(obj.Y)
            $('#clue').val(obj.clue1)
            $('div[data-name=clue]').html(obj.clue1.replace('&lt;I&gt;','<i>').replace('&lt;/I&gt;','</i>').replace('&lt;B&gt;','<b>').replace('&lt;/B&gt;','</b>'))
            
            $('#cw_clue2').val(obj.clue2.replace('&lt;I&gt;','<i>').replace('&lt;/I&gt;','</i>').replace('&lt;B&gt;','<b>').replace('&lt;/B&gt;','</b>'))
            $('div[data-name=cw_clue2]').html(obj.clue2)
            if(obj.dir=='across' || obj.dir=='Across'){
                $('select#cw_dir').val('Across')
            }else{
                $('select#cw_dir').val('Down')
            }
            
            $('#hint').val(obj.hint)
            $('div[data-name=hint]').html(obj.hint.replace('&lt;I&gt;','<i>').replace('&lt;/I&gt;','</i>').replace('&lt;B&gt;','<b>').replace('&lt;/B&gt;','</b>'))
            $('#cw_word').val(obj.word)
            $('#cw_number').val(obj.number)
        }
        
        $('#myModal').modal();
        
    }
    //get the full word
    function theWord(now){
        fullWord = '';
        startx=wordStart.split('_')[1];
        starty=wordStart.split('_')[2];
        
        if(dir=='across'){
            x=startx;
            for(y=Number(starty);y<=Number(now.split('_')[2]);y++){
                fullWord += $('input[name=letter_' + x + '_' + y).val();
            }
        }else{
            y=starty;
            for(x=Number(startx);x<=Number(now.split('_')[1]);x++){
                fullWord += $('input[name=letter_' + x + '_' + y).val();
            }
        }
        
    }	
    
    })(jQuery);