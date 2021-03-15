//initilize Jquery
(function($) {
	var sticky = 0;
	var lastClick = '';
	var dir = 'across';
	var currentCh = '';
	var timerStart = false;
	var competitionMode = false;
	var typing = false;
	var savedData = {};
	var currentClues = null;
	var savedClues = Array();
	var currentClue = {};
	var clueList = Array();
	var trialSolution = false;
	var score = 0;
	var clSc = 0;
	var btnUsed = Array();
	//console.log(localjs)
	var group = localjs.currentTermSlug;
	var trial_letter = null;
	var trial_letter_obj = null;
	var bonus = 0;
	var bothDir = false;
	var markRemain  = 50;
	var special_free_game = false;
	var helpMode = true;
	if(localjs.gameModel=='word-game'){
		var seconds = 00;
		var minitues = 05;
	}else{
		var seconds = 00;
		var minitues = 00;
	}
	special_free_game = localjs.special_free_game?true:false;
	
	var crossword_slug = window.location.href;
		crossword_slug = crossword_slug.split('/');
		crossword_slug = crossword_slug[4];
	
	if(localjs.chapters==''){
		var chapters = Array();
	}else{
		var chapters = localjs.chapters;
	}
	
	//console.log(chapters)
	if(localjs.savedGame!=''){
		var savedGame = localjs.savedGame;
	}else{
		var savedGame = '';
	}
	
	if(localjs.currentScore=='' || localjs.currentScore==null){
		score  =   0;
	}else{
		score = localjs.currentScore;
	}

	$(document).ready(function(){	
		setTimeout(function(){
		    $('.play-chapter#'+localjs.cpid).trigger('click')
		},2000)
		
		
		//show the completed game diologue
		if(special_free_game){		
			if(localjs.tornament){
				var savedTitle = $.trim(localjs.tTitle);
				var title = $.trim(localjs.title);
				if(savedTitle==title){
					$('.game_complete_div span.timer_val').text(localjs.tTimer);
					$('.game_complete_div').show();
				}
			}
		}
		

		$("body").on("change","input[name=competition_mode]",function(){
			if($(this).is(":checked")){
				competitionMode = true;
				setTimeout(function(){
					$('#competition').show()
				},1000)
				
			}else{
				competitionMode=false;
				console.log("not checked")
			}
			
		})
		$('body').on('click','label#score .fa-plus-square',function(){		
			$('input[name="change_score"]').val(score).select()
			$('.change_score').show();
		})
		$('body').on('click','input[name="c_score"]',function(){
		
			if($('input[name="change_score"]').val()>0){
				score = $('input[name="change_score"]').val();
				$('label#score span').text(score)			
				$('input[name="currentScore"]').val(score);
			}
			$('.change_score').hide();
		})
	
		$('body').on('click','label#timer .fa-clock-o',function(){		
			$('input[name="change_timer"]').val(minitues+':'+seconds)
			$('.change_timer').addClass('active');
		})
		$('body').on('click','.change_timer input[name="c_timer"]',function(){
			
				timer = $('input[name="change_timer"]').val();
				timer  = timer.split(':');
	
				if(timer.length==2){
					minitues = timer[0];
					seconds = timer[1];
					$('label#timer span').text(minitues+':'+seconds)
				}
			$('.change_timer').removeClass('active');
		})
		//complete game button clicked
		$('body').on('click','#dd-complete-game',function(e){
			e.preventDefault();
			if(localjs.gameModel!='word-game'){
				$.each(currentClues, function(i, tClue) {
	
					var tClueId = i;
					var tDir = tClue.dir.toLowerCase();
					var obj = $('td[data-clue-'+tDir+'='+i+']');
					dir = tDir;
					var tCurrentClue = get_current_user_word(i);
	
					var tRealClue = tClue.word;
			
					compare_word(tCurrentClue,tRealClue,obj);
				})
				
				
				//timerStart=false;		
				var obj = $("input.wrong[data-trial-word=0]");
				if(obj.length<=0){			
					$('div.game_complete_div').find('.timer_val').text($('#timer span').text());
					$('div.game_complete_div').show();
					localjs.pass= true;
				}else{
					localjs.pass = false;
					$('div.game_uncomplete_div').show();
				}
				$.fn.saveGame();
			}else{
				$.each($('td.asn-hold'), function(i, tClue) {
					AttrId = $(this).attr('id').split('_')
					//console.log(AttrId)
				})
			}
		});
		$('body').on('click','#solution-revel',function(e){
			timerStart = false;
			$.each(currentClues, function(i, tClue) {
				var tDir = tClue.dir.toLowerCase();
				var tRealClue = tClue.word;			
				var objs = $('td[data-clue-'+tDir+'='+i+']');
				
				currentClueLetters = tRealClue.split('');
				var returnVal = true;
				for(var i=0;i<objs.length;i++){
					var obj = objs[i];
					$(obj).find('input.tdClass').val(currentClueLetters[i]).addClass('right');
				}
	
				$('#dd-complete-game').hide();
				$('.end_game_div').hide();
			})
		})
	//	console.log(group);
		//save the current data
		
		$('body').on('click','.z-modal-close',function(e){
			$(this).closest('.z-modal').hide()
		})
		$('body').on('click','a[data-modelbox]',function(e){
			e.preventDefault()
			
			postData = {
				action:'get_page_content',
				slog:$(this).attr('data-slog')
			}
			$.ajax({
				url: localjs.ajaxUrl,
				type:'post',
				data:postData,
				success:function(responce){
					$('#instuctionModal').show();
					$('#instuctionModal .z-modal-body').html(responce)
					console.log(responce);
				},
				error:function(error){
					console.log(error);
				}
			})
			
		})

		
		$('body').on('click','.competition-top .close',function(){		
			$('#competition').hide();
		});

		$('body').on('click','.crossword-tip-menu .introduction',function(e){
			e.preventDefault();
			$('#introduction').show();
		});
		$('body').on('click','.instruction-top .close',function(){		
			$('#introduction').hide();
		});
		
		$('body').on('click','.help-to-play',function(e){	
			e.preventDefault();
			$('.elementor-video').trigger('play')
			$('#how-to-play-game-video').show();
		});
		$('body').on('click','.how-to-play-game-video .close',function(){
			$('.elementor-video').trigger('pause')
			$('#how-to-play-game-video').hide();
		});
		
		$('body').on('click','label#save-game',function(){		
			$.fn.saveGame();
		});
		$.fn.saveGame = function(){
			localjs.tTimer = $.trim($('label#timer').find('span').text());
			localjs.tScore = $.trim($('label#score').find('span').text());
			localjs.tTitle = $.trim($('.game-title').text());
			
			$('input#crosswordData').val(JSON.stringify(savedData));
			if(special_free_game){
				//$('input#chId').val('0');
			}else{
				//$('input#chId').val(get_current_level());
			}
			
			$('input#currentScore').val(score);
			postData = {
				action:'save_cw_game',
				crosswordData: savedData,//$('input[name=crosswordData]').val(),
				chId:$('input[name=chId]').val(),
				post_id:$('input[name=post_id]').val(),
				user_id:$('input[name=user_id]').val(),
				tScore:$('input[name=currentScore]').val(),
				tTimer:localjs.tTimer,
			};
			console.log($('input[name=chId]').val(),postData)
			$('#game-play').append('<div class="submit-game"><span>Congratulations! '+localjs.username+' Success! <br>Your time was '+postData.tTimer+'</span></div>')
			/*
			if(postData.pass){
				$('#game-play').append('<div class="submit-game"><span>Congratulations! '+localjs.username+' Success! <br>Your time was '+postData.tTimer+'</span></div>')
			}else{
				$('#game-play').append('<div class="submit-game"><span>Sorry! '+localjs.username+' Incorrect! <br>Try again !</span></div>')
			}
			*/
			//setTimeout(function() { window.location.replace("https://www.crosswordsakenhead.com"); }, 10000);
			$.fn.cwAjax(localjs.ajaxUrl,postData)
			
			//$('form#cwSaveForm').trigger('submit');
		}
		$.fn.cwAjax = function(ajaxUrl,postData){
			console.log('ajaxcalled',postData)
			$.ajax({
				url: ajaxUrl,
				type:'post',
				data:postData,
				success:function(responce){
					//responce = $.parseJSON(responce)
					console.log(responce);
				},
				error:function(error){
					console.log(error);
				}
			})
			
		}
		//setting menu
		$('body').on('click','label#setting span,label#setting .fa',function(){
			$(this).closest('label').find('.menu').addClass('active');
		})
		$('body').on('click','#close-menu .fa',function(){
			$(this).closest('.menu').removeClass('active');
		})
		
		$('body').on('click','label#full-screen',function(){
			$(this).addClass('active').find('span').html('Restore')
			$(this).find('.fa').removeClass('fa-arrows-alt').addClass('fa-window-restore')
			$('.game-container').addClass('full-screen-active')
			//$('.game-container #game-play').css({'width':($(window).height()+140)+'px','height':($(window).height()-60)+'px'})
			$('.game-container #game-play').css({'width':($(window).height()+140)+'px'})
			$('html').addClass('no-scroll');		
			$('label#setting .menu').removeClass('active');
		})
		
		$('body').on('click','label#full-screen.active',function(){
			$(this).removeClass('active').find('span').html('Full Screen')
			$(this).find('.fa').removeClass('fa-window-restore').addClass('fa-arrows-alt')
			$('.game-container').removeClass('full-screen-active')
			$('.game-container #game-play').css({'width':'auto','height':'auto'})
			$('html').removeClass('no-scroll');
		})
		//clue menu
		$('body').on('click','label#clues span,label#clues .fa',function(){
			$(this).closest('label').find('.clue-detail').addClass('active');
		})
		$('body').on('click','#close-clue-detail .fa',function(){
			$(this).closest('.clue-detail').removeClass('active');
		})
		
		
		
		$('body').on('click','label#reset-timer',function(){
			seconds = 00;
			minitues = 00;
			timerStart=false;
		})
		
		//timer active and deactive
		
		$('body').on('click','label#timer .fa-play',function(){
			timerStart=true;
			$(this).addClass('fa-pause').removeClass('fa-play');
			$('.game-pause').removeClass('active')
		})
		
		$('body').on('click','label#timer .fa-pause',function(){
			timerStart=false;
			$(this).removeClass('fa-pause').addClass('fa-play');
			$('.game-pause').addClass('active')
		})
		$('body').on('click','button[name=start-timer]',function(){
		    $('label#timer .fa-play').trigger('click')
		})
		
		/*
		$('body').on('change','.numbers select[name=level]',function(){
			reset();
			timerStart=false;
			typing = false;
			var level = $(this).val()
			currentCh = chapters[level];	
			localjs.currentCh = currentCh;
			currentClues = currentCh.clues;
			localjs.currentClues = currentClues;
			getClueList(currentClues);
			createTextBoxTable(currentCh);
			viewAllPredefinedWords(currentCh.clues);
			$('.clue-detail').removeClass('active');
		})		
		*/

		$('body').on('click','.close-game',function(){
			$('.game-container').hide();
			$('.chapter-list-holder').show();
			$('.game-title').show();
			$('.game-title.chapter-title').hide();
		})
		$('body').on('click','.play-chapter',function(e){
		    e.preventDefault();
			if(competitionMode){
				$("#more_help").hide();
				$("#solution-revel").hide();
			}else{
				$("#more_help").show();
				$("#solution-revel").show();
			}
			$('.game-container').show();
			$('.chapter-list-holder').hide();
			$('.game-title').hide();
			$('.game-title.chapter-title').show();
			//console.log($(this).attr('id'))
			reset();
			timerStart=false;
			typing = false;
			var level = $(this).attr('id')
			$('input[name=chId]').val(level)
			$('input[name=level]').val(level)
			currentCh = chapters[level];
			$('.game-container .chapter-title .cw-chapter-name').text(currentCh.chapterName)
			
			//console.log(chapters)
			localjs.currentCh = currentCh;
			currentClues = currentCh.clues;
			localjs.currentClues = currentClues;
			getClueList(currentClues);
			createTextBoxTable(currentCh);
			viewAllPredefinedWords(currentCh.clues);
			$('.clue-detail').removeClass('active');
			var sticky = $('.tool-bar').offset().top;
		})


		$('body').on('click','li label#reset-game',function(){
			reset();
			timerStart=false;
			typing = false;
			if($('.numbers select[name=level]').length>0){
			var level = $('.numbers select[name=level]').val()
			}else{
				var level = $('input[name=level]').val()
			}
			//console.log(chapters)
			currentCh = chapters[level];
			//console.log(level)
			currentClues = currentCh.clues;
			getClueList(currentClues);
			createTextBoxTable(currentCh);
			viewAllPredefinedWords(currentCh.clues);
			$('.clue-detail').removeClass('active');
			$('#game-play').find('input[data-trial-word="0"]').val('')
		})
		
		//concede btn clicked
		$('body').on('click','input#concede',function(){
			
			// check the points avaliable for the current clue
			if(is_current_clue_saved()){
				if(is_current_clue_saved().concede == 1){
					return false;
				}
			}
			
			typing = true;
			
			var currentClue = currentCh.clues [get_current_clue_id()] .word;
			var currentClueArray = currentClue.split('');
			var obj = $('table').find('.highlight');
			
			for(var i=0;i<obj.length;i++){
				$(obj[i]).find('input.tdClass').removeClass('wrong').addClass('right').val(currentClueArray[i]);
			}
			//save the user clue and the point remain
			if(!is_current_clue_saved()){
				save_current_clue(get_current_clue_id(),currentClue);
			}
			save_current_clue(get_current_clue_id(),currentClue);
			
			savedData.clues [get_current_clue_id()].concede = 1;
			$('input#concede').hide();
			$('input#trial').hide();
			$('input#first_letter').hide();
			$('input#extra_letter').hide();
			$('input#hint').hide();
			
			score = Number(score) - 50;
			if(score <0){
				score = 0;
			}
			set_chapter_score();
			$('#help').hide();
			//$('#sol_reveled').show();
		});
		//trial solution
		$('body').on('click','input#trial',function(){
			
			//trial solution is depriciated
			return false;
			
			//do nothing if the concede btn is used
			if(is_current_clue_saved().concede==1 || is_current_clue_saved().trial == 1){
				return false;
			}
			typing = true;
			var currentClue = currentCh.clues [get_current_clue_id()] .word;
			var currentClueArray = currentClue.split('');
			var currentUserWord = get_current_user_word(get_current_clue_id());
			var currentUserWordArray = currentUserWord.split('');
			
	
			
			var obj = $('td.highlight').find('input.tdClass');
			for(var i=0;i<obj.length;i++){
				if(currentUserWordArray[i]!='-'){
					if(currentUserWordArray[i]!=currentClueArray[i]){
						$(obj[i]).removeClass('right').addClass('wrong').val(currentUserWordArray[i].toLowerCase());
					}else{
						$(obj[i]).removeClass('wrong').addClass('right');
					}
				}
			}
			
			if(!is_current_clue_saved()){
				save_current_clue(get_current_clue_id(),get_current_user_word(get_current_clue_id()).toUpperCase());
			}else{
				savedData.clues[get_current_clue_id()].userAnswer = get_current_user_word(get_current_clue_id()).toUpperCase();
			}
			
			savedData.clues[get_current_clue_id()].trial = 1;
			
			//console.log(savedData);
			$('input#trial').hide();
			
			score = Number(score) - 20;
			if(score <0){
				score = 0;
			}
			set_chapter_score();
			
		})
	
	
		//first letter revel btn clicked
		$('body').on('click','input#first_letter',function(){
			if(is_current_clue_saved().concede==1 || is_current_clue_saved().first==1){
				return false;
			}
			typing = true;
			var currentClue = currentCh.clues [get_current_clue_id()] .word;
			var currentClueArray = currentClue.split('');
			
			
			var obj = $('td.highlight').find('input.tdClass').first();
			$(obj).val(currentClueArray[0]).removeClass('wrong').addClass('right');
			
			var currentUserWord = get_current_user_word(get_current_clue_id());
			var currentUserWordArray = currentUserWord.split('');
			if(!is_current_clue_saved()){
				save_current_clue(get_current_clue_id(),get_current_user_word(get_current_clue_id()).toUpperCase());
			}else{
				savedData.clues[get_current_clue_id()].userAnswer = get_current_user_word(get_current_clue_id()).toUpperCase();
			}
	
			savedData.clues[get_current_clue_id()].first = 1;
			
			//console.log(savedData);
			$('input#first_letter').hide();
			
			score = Number(score) - 20;
			if(score <0){
				score = 0;
			}
			set_chapter_score();
			
		})
		
		
		//click on the hint button
		$('body').on('click','input#hint',function(){
			
			if(is_current_clue_saved().concede==1 || is_current_clue_saved().hint==1){
				return false;
			}
			 var currentHint = currentCh.clues [get_current_clue_id()].hint;
			
			if(!is_current_clue_saved()){
				save_current_clue(get_current_clue_id(),get_current_user_word(get_current_clue_id()));
			}else{
				savedData.clues [get_current_clue_id()].userAnswer = get_current_user_word(get_current_clue_id());
			}
			
			savedData.clues [get_current_clue_id()].hint = 1;
			
			
			$('input#hint').hide();
			$('p#show_hint').show().find('span').text(currentHint);
			
			score = Number(score) - 20;
			if(score <0){
				score = 0;
			}
			set_chapter_score();
			
		})
		
		//extra letters
		$('body').on('click','input#extra_letter',function(){
			if(is_current_clue_saved().concede==1){
				return false;
			}
			var currentClue = currentCh.clues[get_current_clue_id()].word;
			var currentClueArray = currentClue.split('');
			var extra_count = is_current_clue_saved().extra;
			if(extra_count==undefined)extra_count=0;
			
			typing = true;
			if(currentClue.length < 5 || (currentClue.length - (Number(extra_count) * 5)) <5){
				return false;
			}
			
			var obj = $('td.highlight').find('input.tdClass');
			
			var firstDiv = true;
			for(var i=0;i<obj.length;i++){
				if(i==0)continue;//SKIP THE FIRST ONE
				var div;
				if(firstDiv){
					div = 5;
					firstDiv = false;
				}else{
					div  = 4;
				}
				
				if((i) % Number(div) ==0){
					$(obj[i]).val(currentClueArray[i].toUpperCase()).removeClass('wrong').addClass('right');
				
				}
			}
			
			
			
			if(!is_current_clue_saved()){
				save_current_clue(get_current_clue_id(),get_current_user_word(get_current_clue_id()));
			}else{
				savedData.clues [get_current_clue_id()].userAnswer = get_current_user_word(get_current_clue_id());
			}
			
			savedData.clues [get_current_clue_id()].extra = '1';
			
			$('input#extra_letter').hide();
			
			score = Number(score) - 20;
			if(score <0){
				score = 0;
			}
			set_chapter_score();
			
		})
		
	
		//highlight
		$('body').on('click','input.tdClass',function(event){
			//console.log(localjs.gameModel)

				if(localjs.gameModel=='drop-down'){	
					
						if($(this).hasClass('right')){					
							return;
						}
						
						if($(this).closest('td').hasClass('down') && $(this).closest('td').hasClass('across')){
							bothDir = true;
						}else{
							bothDir = false;
						}
				}
	
				var currentClue='';
				// Set the date we're counting down to
				if(!timerStart){
					countDownDate = new Date().getTime();
					//$('label#timer').addClass('active')
					if(!competitionMode){
					    $('label#timer .play-pause').show();
					}
					timerStart=true;
				}
				
				if(localjs.gameModel=='drag-drop' || localjs.gameModel=='word-game'){
	
					
					if(is_end(this)&&is_first(this)){
						dir = changeDirection();
					}
					
					$(this).blur();
					if($.trim( $(this).val() ) !=''){
						if(trial_letter!=null){
							return false;
						}
						if(!$(this).hasClass('fixed')){
							//console.log($(this).val())
							trial_letter = $(this).val();
							trial_letter_obj = $(this);
							$('body').css('cursor','copy');
							$(this).val('')
							$(this).closest('td').removeClass('ans-hold');
						}
					}else{
						if(trial_letter!=null){
							if(!$(this).hasClass('fixed')){					
	
								$(this).val(trial_letter).closest('td').addClass('ans-hold');
								trial_letter = null;
								//trial_letter_obj = null;
								$('body').css('cursor','default');
								//if the field is end as well as start
	
								//show hide the complete game btn for special free game
	
								if(special_free_game){
									if($(this).closest('td.ans-hold').length>0){
										$('#dd-complete-game').show();
										$('.end_game_div').show();
									}else{
										$('#dd-complete-game').hide();
										$('.end_game_div').hide();
									}
								}					
								if(localjs.gameModel=='word-game'){
									typing = false;
								}else{
									typing = true;
								}
							}
						}
					}
					if($.trim( $(this).val() ) =='' && trial_letter==null){					
							dir == 'across' ? dir = 'down': dir = 'across';
					}
					
	
					
					
					
					clueId = $(this).closest('td').attr('data-clue-'+dir);
				}
				
				//start
				if(typing){
					var obj = $('table').find('.highlight');
					if(localjs.gameModel=='books'){
						for(var i=0;i<obj.length;i++){
							if($(obj[i]).find('input.tdClass').val()==''){
								$(obj[i]).find('input.tdClass').val(' ');
							}
						}
					}
	
					if(localjs.gameModel=='books'){
						//clueId = $(obj).attr('data-clue-'+dir);
						clueId = get_current_clue_id();
						
					}
					
					
					//save the clue if not saved
					
					if(!is_current_clue_saved()){
						
						save_current_clue(clueId,get_current_user_word(clueId));
					}else if(localjs.gameModel=='drag-drop'){
							  save_current_clue(clueId,get_current_user_word(clueId));
					}
					
					// console.log(get_current_clue_id());
					if(localjs.gameModel=='drag-drop'){
						//console.log(savedData);return;
						if(!special_free_game){
							savedData.clues[clueId].right = compare_word(get_current_user_word(clueId),currentCh.clues[clueId].word,$('td[data-clue-'+dir+'='+clueId+']'));
						}
					}else{	
					
						savedData.clues[get_current_clue_id()].right = compare_word(get_current_user_word(get_current_clue_id()),currentCh.clues[get_current_clue_id()].word,$('table').find('.highlight'));
					}
					currentClue = is_current_clue_saved();
					
					
					//score area
					if(localjs.gameModel=='books'){	
						set_score(this);
					}
					//end of score area
										
					typing=false;
					set_chapter_score();
					
				}
				else{
					
					//$('#sol_reveled').hide();
				}			
				
				
				////end
				//$('#more_help').hide();
				
				btnUsed = Array();
				
				$('table').find('.focus').removeClass('focus');
				$('table').find('.highlight').removeClass('highlight');
				//toggle direction
				if(lastClick!=$(this).attr('name')){
					lastClick = $(this).attr('name');
				}else{
					if(localjs.gameModel!='drag-drop'){
					dir == 'across' ? dir = 'down': dir = 'across';
					}
				}
				
				//if the field is end as well as start
				if(is_end(this)&&is_first(this)){
					changeDirection();
				}
				
				var startPos = $(this).attr('name');
				var startX = startPos.split('_')[1];
				var startY = startPos.split('_')[2];
				
				$(this).parent().addClass('focus');
				
				
				if(localjs.title.includes('Aken Word'))	{
					if(dir =='across'){
						for(i=startY;i<=Number(currentCh.cols);i++){
							$('input[name=letter_' + startX + '_' + i).parent().addClass('highlight');
						}
					}else{
						
						for(i=startX;i<=Number(currentCh.rows);i++){
							$('input[name=letter_' + i + '_' + startY).parent().addClass('highlight');
						}
					}
				}else{
					if(dir =='across'){
						if($(this).parent().hasClass('across')){
							startY = $(this).parent().attr('data-across-start');
							for(i=startY;i<=Number($(this).parent().attr('data-across'));i++){
								$('input[name=letter_' + startX + '_' + i).parent().addClass('highlight');
							}
						}
						//set clue detail
						current_clue_content($(this).closest('td').attr('data-clue-across'));
							
					}else{
						
						if($(this).parent().hasClass('down')){
							startX = $(this).parent().attr('data-down-start');
							for(i=startX;i<=Number($(this).parent().attr('data-down'));i++){
								$('input[name=letter_' + i + '_' + startY).parent().addClass('highlight');
							}
						}
						//set clue detail
						current_clue_content($(this).closest('td').attr('data-clue-down'));
					}
				}
				if($.isEmptyObject(savedData)){
						$('#help').show();
					}else{
						if(typeof savedData.clues[get_current_clue_id()]==='undefined'){
							$('#help').show();
						}else{
							
							if(savedData.clues[get_current_clue_id()].right==true){
								$('#help').hide();
							}else{
								$('#help').show();
							}
						}					
					}
			
				//carbojet custom code					
				//give an option weather its right or wrong
				if(localjs.helpMode && !$(this).hasClass('fixed')){
					clueId = $(this).closest('td').attr('data-clue-'+dir);
	
					wordStatus = checkDragDropHelp(currentCh.clues[clueId].word,$('td[data-clue-'+dir+'='+clueId+']'));
					//carbojet custom code for drag drop
					if(localjs.activatDragDropHelp){
							objs = $('td[data-clue-'+dir+'='+clueId+']')
							if(wordStatus){									
								for(var i=0;i<objs.length;i++){
									var obj = objs[i];
									var obj2 = $(obj).find('input.tdClass');
									$(obj2).addClass('fixed');				
								}
							}else{									
								for(var i=0;i<objs.length;i++){
									var obj = objs[i];
									var obj2 = $(obj).find('input.tdClass');
									//$(obj2).val(' ');				
								}
							}
						
					}
	
				}
				if(savedData.clues!==undefined){
					const cluecount = savedData.clues.reduce((counter, obj) => obj.userAnswer !=='' ? counter += 1 : counter, 0);
					console.log(cluecount,currentCh.clues.length)
					//autosave on complete all clues
					if(cluecount==currentCh.clues.length){
						$.fn.saveGame();
					}
				}
			});
		
		
		
		//highlight for free trial group game	
		$('body').on('click','td',function(){
			if(localjs.gameModel=='books'){
				return false;
			}	
			
			if($(this).find('input.tdClass').attr('data-trial-word')=='1' && trial_letter==null ){
				if(!$(this).hasClass('fixed')){
					letterLen = $(this).find('input.tdClass').closest('td').find('span.letter-count').text()
					if(letterLen<=0){
						return false;
					}
					$('td.focus').removeClass('focus');
					$(this).addClass('focus');
					trial_letter = $(this).find('input.tdClass').val();
					trial_letter_obj = $(this).find('input.tdClass');
					//$(this).find('input.tdClass').val('')
					$('body').css('cursor','copy');
					//carbojet custom codeing
					letterLen = $(this).find('input.tdClass').closest('td').find('span.letter-count').text()-1
					$(this).find('input.tdClass').closest('td').find('span.letter-count').text(letterLen)
				}
				/*
				if(letterLen<=0){
					$(this).find('input.tdClass').val('')
				}
				*/
			}else if($(this).find('input.tdClass').attr('data-trial-word')=='1' && trial_letter!=null){			
				
				
				if(trial_letter==$(this).find('input.tdClass').val()){
					letterLen = Number($(this).find('input.tdClass').closest('td').find('span.letter-count').text())+1
					$(this).find('input.tdClass').closest('td').find('span.letter-count').text(letterLen)
					if(letterLen>1){
						$(this).find('input.tdClass').closest('td').find('span.letter-count').text(letterLen)
						
					}else if(letterLen==1){
						//
					}
					$(this).find('input.tdClass').val(trial_letter)
					$('body').css('cursor','default');
					trial_letter=null;
					trial_letter_obj=null;
				}
				
				
			}
		});
	
		//typing the letters
		$('body').on('keyup','input.tdClass',function(event){
			if(event.ctrlKey || event.shiftKey || event.altKey || (event.keyCode>111 && event.keyCode<124)){
				event.preventDefault();
				return true;
			}else{
				var key = event.which || event.keyCode;
				if((key>=65 && key<=90) || (key>=97 && key<=122)){
					//no data entering is available is concede is used
					if(is_current_clue_saved().concede==1){
						return false;
					}else if(!$(this).hasClass('right')){
	
						var c = String.fromCharCode(key);
						$(this).val(c.toUpperCase());
					}
					
					jumpNext(this);
					typing = true;
				}else{
					return false;
				}
			}
			
		});
		//only for aken word game
		//do nothing on key down
		$('body').on('keydown','input.tdClass',function(event){
			if(localjs.gameModel=='drop-down'){
				return;
			}
			event.preventDefault();
			var key = event.which || event.keyCode;
			if(key==8){
				if(!$(this).hasClass('right')){
					$(this).val('');
				}
				jumpPrev(this);
			}else if(key==13){
				

				var clueId = $(this).closest('td').data('clue-'+dir);
				
				//go to the next clue
				//clueId = Number(clueId)+1;
				clueId = Number(clueId);
				
				if(currentCh){
					if(currentCh.clues.length < clueId){
						return;
					}
					var nextDir = (currentCh.clues[clueId]).dir.toLowerCase();
					
					if(dir!=nextDir){
						changeDirection();
					}
					
					var tempObj = $('.highlight').find('input.tdClass');
					typing = true;
					$(tempObj[0]).trigger('click').trigger('focus');
					
				}
				if(savedData.clues!==undefined){
					const cluecount = savedData.clues.reduce((counter, obj) => obj.userAnswer !=='' ? counter += 1 : counter, 0);
					console.log(cluecount,currentCh.clues.length)
					//autosave on complete all clues
					if(cluecount==currentCh.clues.length){
						if(competitionMode){
							$.fn.saveGame();
						}
					}
				}
				//console.log(tempObj)
			}else if(key==9){ //tab pressed
				if(is_end(this)){
					$('.highlight').find('input.tdClass').first().trigger('click').trigger('focus');
					
				}else{
					jumpNext(this);
				}
			}else{
				return false
			}
			
		});
		
		$('body').on('click','input[name=start_game]',function(){
			
			//tempcontent = $('span.select2-chosen').html().replace('&lt;I&gt;','<i>').replace('&lt;/I&gt;','</i>');
			//$('span.select2-chosen').html(tempcontent)
			
			if($(this).attr('id')=='no-help-mode'){
				helpMode=false;
				localjs.helpMode = false;
				$('#help').remove()
				$('.help-concede').show();
			}else{
				helpMode=true;
				localjs.helpMode = true;
			}
			$(this).closest('#introduction').fadeOut();
			$('.game').fadeIn();
			sticky = $('#split-bar').offset().top;
			//sticky = $('.tool-bar').offset().top;
			window.onscroll = function() {fixToolBar()};
			
		})
		//$('#introduction').show();
		$('i#help').click(function(){
			if(localjs.gameModel=='drop-down'){
				return;
			}
			//trial solution is not required
			$('input#trial').hide();
			
			
			//concede btn
			if(!is_current_clue_saved()){
				$('input#concede').show();
			}else if(is_current_clue_saved().concede=='1'){
				$('input#concede').hide();
			}else{
				$('input#concede').show();
			}
			
			
			
			
			var extraBtnShow = false;
			//extra letter
			if(!is_current_clue_saved()){
				extraBtnShow = true;
			}else if(is_current_clue_saved().extra=='1' || is_current_clue_saved().concede=='1'){
				extraBtnShow = false;
			}else{
				extraBtnShow = true;
			}
			
			if(extraBtnShow){
				var obj = $('td.highlight').find('input.tdClass');
				var firstDiv = true;
				var extraLetterSkipped = false;
				for(var i=0;i<obj.length;i++){
					if(i==0)continue;//SKIP THE FIRST ONE
					var div;
					if(firstDiv){
						div = 5;
						firstDiv = false;
					}else{
						div  = 4;
					}
					
					if((i) % Number(div) ==0){
						var cLetter = $(obj[i]).val();
						var oWord = currentClue.word;
							oWord = oWord.split('');
							oWord = oWord[i];
						if(oWord!=cLetter){
							extraLetterSkipped = true;
						}
					}	
				}
				if(extraLetterSkipped){
					$('input#extra_letter').show();
				}else{
					$('input#extra_letter').hide();
				}
			}else{
				$('input#extra_letter').hide();
			}
			
			//end of extra letter
			
			//hint btn and letter
			var temp_hint = currentCh.clues [get_current_clue_id()].hint;
			if(temp_hint==''|| temp_hint==undefined){
				$('#show_hint').hide();
				$('input#hint').hide();
			}else if(is_current_clue_saved()!=false){
				if(is_current_clue_saved().hint=='1' || is_current_clue_saved().concede=='1'){
					$('#show_hint').show();
					$('input#hint').hide();
				}else{
					$('#show_hint').hide();
					$('input#hint').show();
				}
			}else{
				$('#show_hint').hide();
				$('input#hint').show();
			}
			
			//end of hint button and lebel
			
			
			
			//first letter
			var obj = $('.highlight').first();
			if($(obj).find('input.tdClass').val()!=' '){
				$('input#first_letter').hide();
			}else if(is_current_clue_saved().first!=undefined && is_current_clue_saved().first=='1'){
				$('input#first_letter').hide();
			}else{
				$('input#first_letter').show();
			}
			
			
			//end of first letter
			if(!competitionMode){
				$('#more_help').show();
			}
			
		});
		
		if(localjs.chapters!==''){
			//score = totalScore + clueScore;
			//click on chapter block
			
			for (var firstKey in chapters) break;
			currentCh = chapters[firstKey];
			localjs.currentCh = currentCh;
			//console.log(currentCh.clues);
			currentClues = currentCh.clues;
			localjs.currentClues = currentClues;
			getClueList(currentClues);
			createTextBoxTable(currentCh);
			viewAllPredefinedWords(currentCh.clues);
			
			if($(window).width() < (currentCh.rows*60) ){
				$('#game-play').css({'width':$(window).width()-50});
			}else{
				$('#game-play').css({'width':currentCh.rows*60});
			}
			
			if($(window).width() < (currentCh.rows*60) ){
				//$('.tool-bar').css({'width':$(window).width()-50,'max-width':$(window).width()-50});
			}else{
				//$('.tool-bar').css({'width':currentCh.rows*60,'max-width':currentCh.rows*60});
			}
			
			
			
		}
		//create the text box table
		if(special_free_game){
			if(localjs.pass=='true'){
				//$('#game-play').append('<div class="submit-game"><span>Congratulations! '+localjs.username+' !<br>Your time was '+localjs.tTimer+'</span></div>')
			}else{
				//$('#game-play').append('<div class="submit-game"><span>Sorry!'+localjs.username+' Incorrect! try again next week</span></div>')
			}
		}
		$('body').on('click','.clue-list-holder .tabs .tab-list ul li',function(){
				$('.clue-list-holder .tabs .tab-list ul li').removeClass('active')
				$(this).addClass('active')
				$('.clue-list-holder .tabs .tab-holder div').removeClass('active')
				$('.clue-list-holder .tabs .tab-holder #'+$(this).attr('id')).addClass('active')
		})
		/*$('body').on('click','#show-clue-list',function(){
			$('.clue-list-holder').addClass('active')
			$('#close-menu').trigger('click')
		})
		$('body').on('click','#close-clue-list',function(){
			$('.clue-list-holder').removeClass('active')
		})*/
		$('body').on('click','#accross-clues ul li',function(){
			$('td[data-clue-across='+$(this).attr('id')+'] .tdClass:eq(0)').trigger('click')
			dir = 'down';
		})
		$('body').on('click','#down-clues ul li',function(){
	
			$('td[data-clue-down='+$(this).attr('id')+'] .tdClass:eq(0)').trigger('click')
			dir = 'across';
			
		})
		$('body').on('click','.dropdown-sticky-menu.off',function(){		
			$('.tool-bar').css({'top':'0'})
			$(this).find('i').removeClass('fa-angle-down').addClass('fa-angle-up')
			$(this).removeClass('off').addClass('on')
		})
		$('body').on('click','.dropdown-sticky-menu.on',function(){
			var height = $('.tool-bar').height()-40;
			$('.tool-bar').css({'top':'-'+height+'px'})
			$(this).find('i').removeClass('fa-angle-up').addClass('fa-angle-down')
			$(this).removeClass('on').addClass('off')
		})
		
		$('body').on('click','label#send-email',function(){
			$('.modal#send-email').show();
		})
		$('body').on('click','label#send-bulk-clue-email',function(){
			$('.modal#send-bulk-clue-email').show();
		})
		$('body').on('click','.close-modal',function(){
			$(this).closest('.modal').hide();
		})
		$('body').on('click','span.add-clue-to-mail',function(){
			clueid = $(this).attr('data-clueid')
			word = $(this).attr('data-solution')
			if($('#bulk-clue-mail .clue-list #clue-row-id-'+clueid).length<=0){
				row ='<div class="row" id="clue-row-id-'+clueid+'"><div class="col-md-2"><div class="form-group"><label class="clue-solution-text">'+word+': Clue:</label><label>'+clueid+'</label></div></div><div class="col-md-9"><div class="row"><div class="col-md-12"><div class="form-group"><input type="hidden" id="clue_no" name="clue_no[]" value="'+clueid+'"><input type="text" class="form-control" id="clue" name="clue_text[]"></div></div></div></div><div class="col-md-1"><div class="remove-clue-row"><i class="fa fa-close"></i></div></div></div>';
				$('#bulk-clue-mail .clue-list').append(row)
			}
			
			$('label#send-bulk-clue-email').trigger('click')
		})
		$('body').on('click','.remove-clue-row',function(){
			$(this).closest('.row').remove();
		})
	
		$('body').on('click','input[name=send_bulk_clue_mail]',function(e){
			e.preventDefault();
			formObj = $(this).closest('form')
			postData = formObj.serialize();
			$.ajax({
				url: localjs.ajaxUrl,
				type:'post',
				data:postData,
				success:function(responce){
					result = responce.data
					$('.message-box').html(result.message)
					//console.log(responce);
				},
				error:function(error){
					console.log(error);
				}
			})
		})
	})
	function getClueList(currentClues){
		var acrossList = '';
		var downList = '';
			$.each(currentClues, function(tClueId, tClue) {
				var tDir = tClue.dir.toLowerCase();
				var clueText= tClue.clue1;
				if(clueText==''){
					clueText= tClue.clue2;
				}
				//carbojet custom code
				if(clueText!='' && tClue.hint!='d'){
					if(tDir=='across'){
						acrossList +='<li id="'+tClueId+'"><span>'+tClue.number+' : </span>'+clueText+'</li>';
					}else{
						downList +='<li id="'+tClueId+'"><span>'+tClue.number+' : </span>'+clueText+'</li>';
					}
				}
			})
				$('.clue-list-holder #accross-clues ul').html(acrossList.replace('&lt;I&gt;','<i>').replace('&lt;/I&gt;','</i>').replace('&lt;B&gt;','<b>').replace('&lt;/B&gt;','</b>'))
				$('.clue-list-holder #down-clues ul').html(downList.replace('&lt;I&gt;','<i>').replace('&lt;/I&gt;','</i>').replace('&lt;B&gt;','<b>').replace('&lt;/B&gt;','</b>'))

			
	}

//set the score
function set_score(obj){
		var clueId = get_current_clue_id();
		var userWord = is_current_clue_saved();
		if(userWord.concede!='1' && userWord.right==true && userWord.scoreRemain >0){
			var scoreRemain = 50;
			if(userWord.extra==1)scoreRemain-=20;
			if(userWord.first==1)scoreRemain-=20;
			if(userWord.hint==1)scoreRemain-=20;
			if(userWord.trial==1)scoreRemain-=20;
			if(scoreRemain<0)scoreRemain=0;
			
			score = Number(score) + scoreRemain;
			set_chapter_score();
			savedData.clues[get_current_clue_id()].scoreRemain = 0;
			savedData.clues[get_current_clue_id()].scoreRemain = 0;
		}
}
//function to reset the preseted values
function reset(){
	lastClick = '';
	dir = 'across';
	currentCh = '';
	timerStart = false;
 
	typing = false;
	savedData = {};
	currentClues = null;
	savedClues = Array();
	currentClue = {};
	trialSolution = false;
	score = 0;
	clSc = 0;
	btnUsed = Array();
	group = localjs.currentTermSlug;
	trial_letter = null;
	trial_letter_obj = null;
	bonus = 0;
	bothDir = false;
		
	if(localjs.gameModel=='word-game'){
		seconds = 00;
		minitues = 50;
	}else{
		seconds = 00;
		minitues = 00;
	}


	if(localjs.chapters==''){
		chapters = Array();
	}else{
		chapters = localjs.chapters;
	}

	if(localjs.savedGame!=''){
		savedGame = localjs.savedGame;
	}else{
		savedGame = '';
	}

	if(localjs.currentScore=='' || localjs.currentScore==null){
		score = 0;
	}else{
		score = localjs.currentScore;
	}

}
//return true is ny btn used other than the concede btn
function is_any_key_used(){
	var is_key_used = is_current_clue_saved();
	if(!is_key_used){
		return false;
	}else if(is_key_used.trial=='1'){
		return true;
	}else if(is_key_used.first=='1'){
		return true;
	}else if(is_key_used.extra=='1'){
		return true;
	}else if(is_key_used.hint=='1'){
		return true;
	}else{
		return false;
	}
}

//add score 
function add(val){
	score = Number(score) + Number(val);
	set_chapter_score();
}
//deduct score 
function deduct(val){
	score = Number(score) - Number(val);
	set_chapter_score();
}
//get the current clue score for user word
function get_current_clue_score(){
	if(is_current_clue_saved().clueScore!=undefined){
		return is_current_clue_saved().clueScore;
	}else{
		return 0;
	}
}
//calculate the total score here
function calculate_total_score(){
	var obj = $('table').find('.highlight');
	var res = compare_word(get_current_user_word(get_current_clue_id()),currentCh.clues [get_current_clue_id()] .word, obj);
	
	if(is_current_clue_saved().clueScore!=undefined){
		var sc = is_current_clue_saved().clueScore;
	}else{
		var sc = 0;
	}
}

//search an element in an Array
function in_array(item,array){
	if(array.indexOf(item) >= 0){
		return true;
	}else{
		return false;
	}
}

//check if the current chapter is played by the user or not
function is_there_any_saved_data(){
	if(savedData.clues!=undefined){
		return true;
	}else{
		return false;
	}
}


//function to check if the current clue is saved in the savedData gloabal variable return Boolean
function is_current_clue_saved(){

	if(savedData.clues!=undefined){
		var currentClues = savedData.clues;
		if(currentClues[get_current_clue_id()]!=undefined){
			if(localjs.gameModel=='drop-down'){
				return currentClues[clueId];
			}else{
				return currentClues[get_current_clue_id()];
			}
			
		}
	}
	return false;
	
}

//function to save user data in the savedData global varialble
function save_current_clue(clueId,word){
	
	//add the new word to the data
	var clue = {
			'userAnswer':word,
			'concede':0,
			'trial':0,
			'extra':0,
			'first':0,
			'hint':0,
			'score':0,
			'right':0,
			'markAdded':0,
			'scoreRemain':50
	};
	/*
	if(savedData.clues!=undefined){
		savedClues = savedData.clues;
	}
	*/
	//console.log(savedData['clues'])
	
	savedClues[clueId.toString()] = clue;
	savedData['clues'] = savedClues;
}


//function to get the current clue id
function get_current_clue_id(){
	return $('input#clueId').val();
}


//get the current chapter id or level
function get_current_level(){
	//return Number($('span#level').attr('data-level'));
	return Number($('select[name=level]').val());
}

	//check drag and drop word is completed and about to fixed
function checkDragDropHelp(realClue, objs){
	
	var currentClue = '';
	for(var i=0;i<objs.length;i++){
		if($(objs[i]).children('input.tdClass').val()!='' && $(objs[i]).children('input.tdClass').val()!=' '){
			currentClue += $(objs[i]).children('input.tdClass').val();	
		}
	}
	if(currentClue.length==realClue.length){
		localjs.activatDragDropHelp = true;
	}else{
		localjs.activatDragDropHelp = false ;
	}
	
	var returnVal = false;
	if(currentClue==realClue){
		returnVal = true;
	}	
	return returnVal;
}

//compare word letter and highlight it
function compare_word(currentClue,realClue, objs){
	if(localjs.gameModel=='drag-drop'){
		
		currentClueLetters = currentClue.split('');
		realClueLetters = realClue.split('');		
		
		var returnVal = true;
		for(var i=0;i<objs.length;i++){
			var obj = objs[i];
			/*if($(obj).find('input.tdClass').hasClass('right')){
				continue;
			}*/
			if(currentClueLetters[i]==realClueLetters[i]){//right answer given
				$(obj).find('input.tdClass').removeClass('wrong').addClass('right');
				
			}else{
				//wrong answer given
				$(obj).find('input.tdClass').removeClass('right').addClass('wrong');
				returnVal = false;
			}
		}
		return returnVal;
	}else{
		currentClueLetters = currentClue.split('');
		right = true;
		realClueLetters = realClue.split('');
		for(var i=0;i<objs.length;i++){
			var obj = objs[i];
			var obj2 = $(obj).find('input.tdClass');
			if(currentClueLetters[i]==realClueLetters[i]){
				$(obj2).removeClass('wrong').addClass('right');
				
			}else{
				right = false;
				if($(obj2).val()==' '){
					continue;
				}
				
				//$(obj2).removeClass('right').addClass('wrong').val(currentClueLetters[i]);
				$(obj2).removeClass('right').val(currentClueLetters[i]);
			}
		}
		
		if(right){
			
			for(var i=0;i<objs.length;i++){
				var obj = objs[i];
				var obj2 = $(obj).find('input.tdClass');
				$(obj2).addClass('fixed');				
			}
			
			$('#help').hide();
			//$('#sol_reveled').show();			
		}else{
			$('#help').show();
			if(localjs.helpMode){
				//if wrong remove wrong letters
				for(var i=0;i<objs.length;i++){
					var obj = objs[i];
					var obj2 = $(obj).find('input.tdClass');
					if(currentClueLetters[i]!=realClueLetters[i]){
						$(obj2).val('');				
					}
				}
				
			}else{			
				
				//if wrong remove all letters
				for(var i=0;i<objs.length;i++){
					var obj = objs[i];
					//console.log(obj);
					var obj2 = $(obj).find('input.tdClass');
					if($(obj2).hasClass('fixed')==false){
						$(obj2).removeClass('right').val('');
					}
				}
				
			}
			
		}
		return right;
	}
}


//get the current word entered by the user
function get_current_user_word(clueId){

	//get the typed word
			var letters = $('td[data-clue-'+dir+'='+clueId+']');
			var word = '';
			for(var i=0;i<letters.length;i++){
				if($(letters[i]).children('input.tdClass').val()==''){
					word += ' ';
					$(letters[i]).children('input.tdClass').val(' ');
				}else{
					word += $(letters[i]).children('input.tdClass').val();	
				}
			}
			return word;
}

//set the current chapter score
function set_chapter_score(){
	var chapterScore = score;
	if(chapterScore<0){
		chapterScore=0;
	}
	
	if(chapterScore<10){
		chapterScore = '0' + chapterScore.toString();
	}else{
		chapterScore = chapterScore.toString();
	}
	
	$('label#score').find('span').text(chapterScore);

}


//manage clue info
function current_clue_content(clueKey){
		if(clueKey!=undefined){
			currentClue = currentCh.clues[clueKey];
			localjs.currentClue = currentClue;
			if(currentClue!='' && localjs.gameModel=='books'){
				$('.clue-detail').addClass('active');
			}else if(localjs.gameModel=='drag-drop' || localjs.gameModel=='word-game'){
				//var time_free_game = crossword_slug.split('-');
				//if(time_free_game[0]=='the' && (time_free_game[1]=='times' || time_free_game[1]=='times2')){
					$('.clue-detail').addClass('active');
					$('#help').remove();
				//}
			}
			$('div#clue1').find('span#clue-text').html(currentClue.clue1.replace('&lt;I&gt;','<i>').replace('&lt;/I&gt;','</i>').replace('&lt;B&gt;','<b>').replace('&lt;/B&gt;','</b>'));
			$('div#clue1').find('span.add-clue-to-mail').attr('data-clueid',currentClue.number+'-'+currentClue.dir)
			$('div#clue1').find('span.add-clue-to-mail').attr('data-solution',currentClue.word)
			var clue2 = currentClue.clue2;
			if(clue2.length>0){
				$('div#clue2').find('span').text(clue2);
				$('div#clue2').show();
			}else{
				$('div#clue2').hide();
			}
			$('#clueId').val(clueKey);
			
			//show or hide the concede button
			if(is_current_clue_saved()){
				
				if(is_current_clue_saved().concede == 1){
					$('#help').hide();
					//$('#sol_reveled').show();
					$('input#concede').hide();
					$('input#trial').hide();
					$('input#first_letter').hide();
					$('input#extra_letter').hide();
					$('input#hint').hide();
				}else{
					//hist is used
					if(is_current_clue_saved().hint==1 ){
						$('input#hint').hide();
						$('#show_hint').show().find('span').text(currentCh.clues [get_current_clue_id()].hint);
					}else{
						$('input#hint').show();
						$('#show_hint').hide().find('span').text('');
					}
					
					

					//first letter is used
					if(is_current_clue_saved().first==1 || currentCh.clues[get_current_clue_id()].hint == '' ){
						$('input#first_letter').hide();
					}else{
						$('input#first_letter').show();
					}
					
					//extra letter
					if(is_current_clue_saved().extra>0 ){	
						var extra_count = is_current_clue_saved().extra;
						if(currentClue.length - (extra_count * 5) < 5){
							$('input#extra_letter').hide();
						}else{
							$('input#extra_letter').show();
						}
					}else{
						$('input#extra_letter').show();
					}
					
					
				}
			}else{
				
				$('input#concede').show();
				$('input#trial').show();
				$('input#first_letter').show();
				$('input#extra_letter').show();
				$('input#hint').show();
				
				
			}
			
		}else{
			$('.clue-detail').removeClass('active');
		}
	}

function is_end(obj){
	var x = Number($(obj).attr('name').split('_')[1]);
	var y = Number($(obj).attr('name').split('_')[2]);
	//check the direction
	if(dir=='across'){
		var next = $('input[name=letter_'+x+'_'+(Number(y)+1)+']');
	}else if(dir=='down'){
		var next = $('input[name=letter_'+(Number(x)+1)+'_'+y+']');
	}
	return next.length ==0;
}
function is_first(obj){
	//get the current x and y position
	var x = Number($(obj).attr('name').split('_')[1]);
	var y = Number($(obj).attr('name').split('_')[2]);
	//check the direction
	if(dir=='across'){
		var prev = $('input[name=letter_'+x+'_'+(Number(y)-1)+']');
	}else if(dir=='down'){
		var prev = $('input[name=letter_'+(Number(x)-1)+'_'+y+']');
	}
	return prev.length == 0;
}
//jump next Position
function jumpNext(o){
	typing = true;
	//get the current x and y position
	var x = Number($(o).attr('name').split('_')[1]);
	var y = Number($(o).attr('name').split('_')[2]);
	//check the direction
	if(dir=='across'){
		var next = $('input[name=letter_'+x+'_'+(Number(y)+1)+']');
		if(next.length){
			$('body').find('.focus').removeClass('focus');
			$(next).trigger('focus').closest('td').addClass('focus');
			lastClick = $(next).attr('name');
		}
		//set clue detail
		current_clue_content($(o).closest('td').attr('data-clue-across'));
	}else if(dir=='down'){
		var next = $('input[name=letter_'+(Number(x)+1)+'_'+y+']');
		if(next.length){
			$('body').find('.focus').removeClass('focus');
			$(next).trigger('focus').closest('td').addClass('focus');
			lastClick = $(next).attr('name');
		}
		//set clue detail
		current_clue_content($(o).closest('td').attr('data-clue-down'));
		
	}
	
}

//change direction
function changeDirection(){
	dir = 	(dir == 'across') ? 'down' : 'across';
	localjs.dir = dir;
	return dir;
}


//Jump previous position
function jumpPrev(o){
	//get the current x and y position
	var x = Number($(o).attr('name').split('_')[1]);
	var y = Number($(o).attr('name').split('_')[2]);
	//check the direction
	if(dir=='across'){
		var prev = $('input[name=letter_'+x+'_'+(Number(y)-1)+']');
		if(prev.length){
			$('body').find('.focus').removeClass('focus');
			$(prev).trigger('focus').closest('td').addClass('focus');
			lastClick = $(prev).attr('name');
		}else{
			typing = false;
		}
	}else if(dir=='down'){
		var prev = $('input[name=letter_'+(Number(x)-1)+'_'+y+']');
		if(prev.length){
			$('body').find('.focus').removeClass('focus');
			$(prev).trigger('focus').closest('td').addClass('focus');
			lastClick = $(prev).attr('name');
		}else{
			typing = false;
		}
	}
	typing=true;
}

function viewAllPredefinedWords(editData){
				var currentSavedCh = undefined;
				if(savedGame!='' && savedGame!=null){
					var currentSavedClues = undefined;
					if(savedGame[get_current_level()]!=undefined){
						currentSavedCh = savedGame[get_current_level()];
						currentSavedClues = currentSavedCh.clues;
						savedData.clues = currentSavedClues;
						//console.log(savedData);
						if(currentSavedCh.scoreValue!=undefined){
							score = currentSavedCh.scoreValue;
							 set_chapter_score();
							 var timerVal = currentSavedCh.timerValue;
							 minitues = Number((timerVal.split(':'))[0]);
							 seconds = Number((timerVal.split(':'))[1]);
						}
					}
				}
		//view previously entered data
		if(editData.length){
			for(row1 in editData){
				var editX = editData[row1]['X'];
				var editY = editData[row1]['Y'];
				var editNumber = editData[row1]['number'];
				i=0;
				var currentSavedClue = undefined;
				var currentSavedClueArray = undefined;
				if(currentSavedClues!=undefined && currentSavedClues!=''){
					if(currentSavedClues[row1]!=undefined && currentSavedClues[row1]!=''){
						var currentSavedClue = currentSavedClues[row1].userAnswer;
						var currentSavedClueArray = currentSavedClue.split('');
					}else{
						var currentSavedClue = undefined;
					}
				}
				
						var trial_word = false;
						var specialWord =false;
						if(localjs.gameModel=='drag-drop' || localjs.gameModel=='word-game'){
							var current_word = editData[row1];
							
							//carbojet custom code
							/*if(current_word.number=='1' || current_word.number=='2' || current_word.number=='3' || current_word.number=='4'){
								trial_word = true;
							}
							*/
							if(current_word.hint=='d'){
								trial_word = true;
							}
							else if(current_word.number=='4'){
								if(localjs.special_free_game){
									
								}else{
									continue;
								}
								//continue;
							}else{
								trial_word = false;
							}
							
							if(localjs.special_free_game){
								//do nothing
							}else{
							    //console.log('inside')
								var temp = current_word.word;
								temp = temp.split(':');
								if(temp.length==2){
									specialWord = true;
								}else{
									specialWord = false;
								}
							}
							
							
						}
						
						i=0;
				
				if(editData[row1]['dir']=='across' || editData[row1]['dir']=='Across'){
						
						
					for(y=Number(editY);y<=Number(editY)+editData[row1]['word'].length -1;y++){				
						$('input[name=letter_'+editX+'_'+y+']').parent().attr('data-across',Number(editY)+editData[row1]['word'].length -1).attr('data-across-start',editY).addClass('across');
						
						if(trial_word){
							tempCurrent = editData[row1];
							$('input[name=letter_'+editX+'_'+y+']').val((editData[row1]['word'].split(''))[(i++)]).attr('data-trial-word','1').attr('disabled','disabled').closest('td').children('.index').remove();
							$('input[name=letter_'+editX+'_'+y+']').closest('td').append('<span class="letter-count">'+tempCurrent.clue1+'</span>')
							$('input[name=letter_'+editX+'_'+y+']').closest('td').addClass('letter-holder')
						}else if(specialWord){
							$('input[name=letter_'+editX+'_'+y+']').val((editData[row1]['word'].split(''))[(i++)]).attr('data-trial-word','0').attr('disabled','disabled').closest('td').children('.index').remove();
						}
						else if(localjs.gameModel=='drag-drop'){
							//carbojet custom code
							$('input[name=letter_'+editX+'_'+y+']').val(' ').attr('data-trial-word','0').attr('readonly','true');
						}else{
							$('input[name=letter_'+editX+'_'+y+']').val(' ').attr('data-trial-word','0');
						}
						
						
						if(currentSavedClue!=undefined && currentSavedClue!=''){
							$('input[name=letter_'+editX+'_'+y+']').val(currentSavedClueArray[i++]);
						}
						
						$('td[id=cell_'+editX+'_'+y+']').attr('data-clue-across',row1);
					}
					
				}else if(editData[row1]['dir']=='down' || editData[row1]['dir']=='Down'){
					
					for(x=Number(editX);x<=Number(editX) + editData[row1]['word'].length -1;x++){
						
						$('input[name=letter_'+x+'_'+editY+']').parent().attr('data-down',Number(editX) + editData[row1]['word'].length -1).attr('data-down-start',editX).addClass('down')
						if(trial_word){
							$('input[name=letter_'+x+'_'+editY+']').val((editData[row1]['word'].split(''))[(i++)]).attr('data-trial-word','1').attr('disabled','disabled').closest('td').children('.index').remove();
						}else if(specialWord){
							$('input[name=letter_'+editX+'_'+y+']').val((editData[row1]['word'].split(''))[(i++)]).attr('data-trial-word','0').attr('disabled','disabled').closest('td').children('.index').remove();
						}else if(localjs.gameModel=='drag-drop'){
							$('input[name=letter_'+x+'_'+editY+']').val(' ').attr('data-trial-word','0').attr('readonly','true');
						}else{
							$('input[name=letter_'+x+'_'+editY+']').val(' ').attr('data-trial-word','0');
						}
						
						if(currentSavedClue!=undefined && currentSavedClue!=''){
							$('input[name=letter_'+x+'_'+editY+']').val(currentSavedClueArray[i++]);
						}
						$('td[id=cell_'+x+'_'+editY+']').attr('data-clue-down',row1);
					}
									
				}
				
				if(editNumber!='undefined'){
					$('td[id=cell_'+editX+'_'+editY+'] div.index').html('<span>'+editNumber+'</span>')
				}
				
				
			}
			
			//disable the chapters

			for (var firstKey in chapters) break;
			disableInputBoxes((chapters[firstKey]).cols , (chapters[firstKey]).rows);
			//set the chapter score
			set_chapter_score();
			
		}		

	}
	
	
function disableInputBoxes(x,y){
	for(var i=1;i<=x;i++){
		for(var j=1;j<=y;j++){
			var obj = $('#cell_'+i+'_'+j);
			if(obj.hasClass('across') || obj.hasClass('down')){continue;}
			if(localjs.gameModel=='word-game'){
				$(obj).find('input').attr('data-trial-word','2').attr('readonly','true');
			}else{
				$(obj).html('<div style="height:40px;"></div>'); 
			}			
		}
	}
}
	
function createTextBoxTable(currentCh){
	  //create texbox table
	  var html = '<table>';
	  for(i=1;i<=currentCh.rows;i++){
		  html +='<tr data-row="'+ i +'">';
		  for(j=1;j<=currentCh.cols;j++){
			html +='<td id="cell_'+ i + '_'+ j +'"><div class="index"></div><input class="tdClass" type="text" maxlength="1" autocomplete="off" name="letter_'+ i + '_'+ j +'"/></td>';
			
			
		  }
		  html +='</tr>'
	  }
	  html +='</table>'; 
	  $('#game-play').html(html);
  }

// Set the date we're counting down to
// Update the count down every 1 second
var x = setInterval(function() {
		minitues = Number(minitues);
		seconds = Number(seconds);
		
		if(timerStart){
			if(localjs.gameModel=='word-game'){
				if(seconds<=0){
					seconds=59;
					if(minitues<=0){
						
						timerStart=false;
						minitues = 00;
						seconds = 00;
					}else{
						minitues--;
					}
				}else{
					seconds--;
				}
			}else{
				
				if(seconds==60){
					seconds=00;
					minitues++;
					if(minitues==60){
						timerStart=false;
					}
				}else{
					seconds++;
				}
			}
		}
		
			
			if(seconds.toString().length<=1){seconds = '0'+seconds;}
			if(minitues.toString().length<=1){minitues = '0'+minitues;}
			
			// Display the result in the element with id="demo"
			$('label#timer span').html(minitues + ':' + seconds );
		
	}, 1000);
//console.log(x);

	/*fixed tool bar*/
	/*
	function fixToolBar(){
		if (window.pageYOffset >= sticky) {
			//$('.tool-bar').addClass("fixed");
			$('#split-bar').addClass("fixed");
			//$('#clue-block').addClass("fixed");
			
		} else {
			//$('.tool-bar').removeClass("fixed");
			$('#split-bar').removeClass("fixed");
			//$('#clue-block').removeClass("fixed");
		}
	}
	window.onscroll = function() {fixToolBar()};
	var sticky = $('.tool-bar').offset().top;
	*/
	/*
	$(window).scroll(function(){
		if (window.pageYOffset >= sticky) {
			$('#split-bar').addClass("fixed");			
		} else {
			$('#split-bar').removeClass("fixed");
		}
	})
	*/
})(jQuery);//end Initilize	
		