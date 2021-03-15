<?php
/*
** Template Name : game play 
**
*/
	get_header();
	global $post;
	$current_user = wp_get_current_user();
	$tornament = false;
	$term = get_the_terms(get_the_ID(),'group');
	$gameModel = get_post_meta( get_the_ID(), 'cw_model_type_box' ,true);
	$cw_clues_visible_box = get_post_meta(get_the_ID(),'cw_clues_visible_box',true);
	$cw_bulk_clue_mail_box = get_post_meta(get_the_ID(),'cw_bulk_clue_mail_box',true);

    if(isset($_GET['cpid'])){
        $cpid = $_GET['cpid'];
    }else{
        $cpid=0;
    }
	if(!$gameModel){
		$gameModel='books';
	}
	$cw_free_box = get_post_meta( get_the_ID(), 'cw_free_box',true );

	if(!$cw_free_box){
		$freeGame=false;
	}
	if($cw_free_box=='yes'){
		$freeGame = true;
	}else{
		$freeGame = false;
	}
	$cw_competition_box = get_post_meta( get_the_ID(), 'cw_compition_box' ,true);
	if(!$cw_competition_box){
		$competition=false;
	}
	if($cw_competition_box=='yes'){
		$competition = true;
	}else{
		$competition = false;
	}
	
	$cw_clue_type_box= get_post_meta( get_the_ID(), 'cw_clue_type_box',true );
	if(!$cw_clue_type_box){
	   $cw_clue_type_box='Cryptic Clue';
	}
        
	if(!empty($term)){
		$term = $term[0];
		$termId = $term->term_id;
	}
	/*
	if(!$freeGame){
		if(!is_user_logged_in()){
		wp_redirect(site_url().'/login?redirect_to='.urlencode(get_permalink()));
		exit;
		}
	}
	*/
	$special_free_game = FALSE;
	if($gameModel=='drag-drop' || $gameModel=='word-game'){
		$special_free_game = TRUE;
	}
	
	//group details
	$currentTerm = get_the_terms($post,'group');
	if($currentTerm){
	if(count($currentTerm)>0){
		$currentTerm = $currentTerm[0];
		$currentTermSlug = $currentTerm->slug;
		
	}else{
		$currentTermSlug = 'none';
	}
	}else{
	    $currentTermSlug = 'none';
	}
	
	if($_POST && $post->post_type=='cw_crosswords' && !empty($_POST['crosswordData'])){
		
		$userCrosswordData = json_decode(stripslashes($_POST['crosswordData']),TRUE);
		//debug_result($userCrosswordData);
		$userCrosswordClues = $userCrosswordData['clues'];
		$userCrosswordClues = array_filter($userCrosswordClues,'filter_null');
		$userCrosswordData['clues'] = $userCrosswordClues;
		
		//echo '<pre>';print_r($userCrosswordData);exit;
		
		$chId = $_POST['chId'];
		$savedCrosswords = unserialize(get_user_meta(get_current_user_id(),'savedCrosswords',TRUE));

		$savedCrosswords[get_the_ID()][$chId] = $userCrosswordData;
		
		
		update_user_meta(get_current_user_id(),'savedCrosswords',serialize($savedCrosswords));
		update_user_meta(get_current_user_id(),'cwScore_'.get_the_ID(),$_POST['currentScore']);
		

	}
	
	$cw_intro_file = get_post_meta( get_the_ID(), 'cw_intro_file' );
	$cw_saved_crosswords = unserialize(get_user_meta(get_current_user_id(),'savedCrosswords',TRUE));
	
	if(!empty($cw_saved_crosswords[get_the_ID()])){
		$cw_current_chapter_saved_data = $cw_saved_crosswords[get_the_ID()];
		
	}else{
		$cw_current_chapter_saved_data = NULL;
	}
	
	$cwScore = get_user_meta(get_current_user_id(),'cwScore'.get_the_ID(),TRUE);
	
	//tornament = get_user_meta(get_current_user_id(),'tornament',TRUE);

	if($tornament){
		$tScore = get_user_meta(get_current_user_id(),'tScore',TRUE);
		$tTimer = get_user_meta(get_current_user_id(),'tTimer',TRUE);
		$tTitle = get_user_meta(get_current_user_id(),'tTitle',TRUE);
		$pass = get_user_meta(get_current_user_id(),'pass',TRUE);
	}else{
		$tScore = null;
		$tTimer = null;
		$tTitle = null;
		$pass = false;
	}
	
	//$xml_chapters = unserialize(get_post_meta(get_the_ID(),'chapters',TRUE));
	
	if(is_array(get_post_meta(get_the_ID(),'chapters',TRUE))){
	   	$cw_chapters = unserialize(get_post_meta(get_the_ID(),'chapters',TRUE));  
	}else{
	    $cw_chapters = array();  
	}
	


	//$cw_no_chapters = get_post_meta(get_the_ID(),'cw_no_chapters',true);
	$compress_chapters = array();

	
	$args = array(
		"post_type"			=> 'cw_chapters',
		"post_parent"		=> get_the_ID(),
		"posts_per_page"		=>-1,
		"order"				=> "ASC",
		"orderby"			=> "ID",
	);
	$get_chapters = get_posts($args);

	if(count($get_chapters)>0){
		foreach($get_chapters as $postchapter){
			$chapter_detail = unserialize( get_post_meta($postchapter->ID,'chapterdetail',true) );
			$chapter_detail['chapterName'] = $postchapter->post_title;			
			
			$chapter_detail['chapter_index'] = $postchapter->ID;
				$clues =  get_post_meta($postchapter->ID,'clues',true);
				$chapter_detail['clues'] = $clues;
				$chapter_detail['no_of_clues'] = count($clues);
			
			$cw_chapters[$postchapter->ID] = (object) $chapter_detail;
		}
	}
	
    /*
	if($xml_chapters==null && $compress_chapters!=null){
		$cw_chapters = $compress_chapters;
	}else{
		$cw_chapters = $xml_chapters;
	}
	*/
	
	wp_enqueue_style('cw-fontawsome','https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css');
	wp_enqueue_style('cw-template-style-css',plugins_url('css/stylesheet.css',__FILE__));
	wp_enqueue_script('cw-template-script-js',plugins_url('js/javascript.js',__FILE__));
	wp_enqueue_style('cw-fontawsome','https://cdnjs.cloudflare.com/ajax/libs/jquery.scrollbar/0.2.11/jquery.scrollbar.min.js');
	
	 //logo image
	
	wp_localize_script('cw-template-script-js','localjs',array(
												'chapters'	=> $cw_chapters,
												'savedGame' => $cw_current_chapter_saved_data,
												'currentScore' => $cwScore,
												'currentTermSlug'=>$currentTermSlug,
												'special_free_game'=>$special_free_game,
												'title'=>$title,
												'ajaxUrl'=>admin_url('admin-ajax.php'),
												'tornament'=>$tornament,
												'tScore' =>$tScore,
												'tTimer' =>$tTimer,
												'tTitle' =>$tTitle,
												'pass'=>$pass,
												'username'=>$current_user->display_name,
												'gameModel'=>$gameModel,
												'freeGame'=>$freeGame,
												'competition'=>$competition,
												'helpMode'=>true,
												'cpid'=>$cpid,
												));
			
		?>
</div><!-- end row-->
</div><!-- end container-->
		<form id="cwSaveForm" name="cwSaveForm" method="post" action="">
			<input type="hidden" name="post_id" value="<?php echo get_the_ID(); ?>"/>
			<input type="hidden" name="user_id" value="<?php echo get_current_user_id();?>"/>
			
			<input type="hidden" name="crosswordData" id="crosswordData" value="0"/>
			<input type="hidden" name="chId" id="chId" value="0"/>
			<input type="hidden" name="currentScore" id="currentScore" value="0"/>
		</form>
	<?php
	$chapterlist = get_posts(
		array(
			'post_type'			=> 'cw_chapters',
			'post_parent'		=> get_the_ID(),
			'posts_per_page'	=> -1,
			'orderby'			=>'ID',
			'order'				=>'ASC',
		)
	);
	//echo count($chapterlist);
	//$chapterlist = array_reverse($chapterlist);
	?>
	<div class="container chapter-list">
		<div class="row">
			<div class="col-11 col-centered">
				<div class="game-title">
					<h1><?php echo $title = get_the_title();?></h1>
				</div>
				<ul class="crossword-tip-menu">
					<li>
						<a href="#" class="competition">
						<i class="fa fa-info"></i>
						<span class="competition-mode">Competition Mode</span>
						<label class="switch">
							<input type="checkbox" name="competition_mode">
							<span class="slider round"></span>
						</label>
						</a>
					</li>
					<li><a href="#" class="introduction" ><i class="fa fa-info"></i>Introduction</a></li><li><a href="#" class="help-to-play"></i>How to Play</a></li></ul>
			</div>
			<div class="col-11 col-centered chapter-list-holder">
				<div class="row">
					<?php foreach($chapterlist as $chapterpost){?>
					<div class="col-3 col-sm-3 col-md-2 col-lg-1 col-xs-1">
						<div class="chapter-list-box">
						    <div class="title"><?php echo $chapterpost->post_title;?></div>
							<div class="img-holder">
							<?php
								$chapter_image = wp_get_attachment_url(get_post_thumbnail_id($chapterpost->ID));
								if(empty($chapter_image)){
									$chapter_image = site_url('/wp-content/uploads/2020/03/newcrosswordlogo-icon-1.png');
								}
								
							?>
								<img src="<?php echo $chapter_image;?>" /> 
							</div>
							
							<?php
							    $hasaccess = pmpro_hasMembershipLevel(2, get_current_user_id()); 
							    $chapterpermission = pmpro_has_membership_access($chapterpost->ID, get_current_user_id(),false);
							    if($chapterpermission){
							        ?>
										<button type="button" class="btn btn-small btn-default play-chapter" data-trigger="ch-<?php echo $chapterpost->ID;?>" id="<?php echo $chapterpost->ID;?>">Play</button>
									<?php
							    }else{
							        if($hasaccess){
    							        ?>
    										<button type="button" class="btn btn-small btn-default play-chapter" data-trigger="ch-<?php echo $chapterpost->ID;?>" id="<?php echo $chapterpost->ID;?>">Play</button>
    									<?php
    									}else{
    										?>
    										<a href="<?php echo site_url();?>/subscribe-now/" class="btn btn-small btn-default subscribe-btn"><i class="fa fa-lock"></i> Play</a>
    										<?php
    								}
							    }
							    
								
							?>							
						</div>
					</div>
					<?php }?>
				</div>
			</div>
		</div>
	</div>
	<div class="container game-container full-screen-active">
        <div class="row game">
            <div class="col-md-12">
                <div class="row">					
					<div class="col-md-12 fixed" id="split-bar">
					    <div class="row">
					        <div class="col-sm-12 col-md-10 col-lg-10 col-xs-10 col-centered">
                				<div class="game-title chapter-title"><?php echo $title = get_the_title();?> - <span class="cw-chapter-name"></span></div>
                			</div>
					    </div>
						<div class="row">
							<div class="tool-bar col-centered">
								<div class="row">									
									<div class="col-6 col-sm-6 col-md-4">
										<div class="close-game">
											<span><i class="fa fa-chevron-left"></i> Back</span>
										</div>
										<?php if(!$special_free_game){?>
										<label id="score"><i class="fa fa-plus-square"></i><span>00</span></label>
										<div class="change_score">
											<input type="text" name="change_score" maxlength="4">
											<input type="button" name="c_score" value="OK">
										</div>	
										<?php } ?>
										<input type="hidden" name="level" value="0">
										<label id="timer">
											<i class="fa fa-clock-o"></i>
											<span>00:00</span>
											<i class="fa fa-pause play-pause"></i>
											<div class="change_timer">
												<input type="text" name="change_timer" maxlength="5">
												<input type="button" name="c_timer" value="[M] OK">
											</div>
										</label>
									</div>
									<div class="col-6 col-sm-6 col-md-8 col-right-align">
										<ul class="game-right-menu">
										    
											<li><label id="reset-game"><i class="fa fa-history"></i><span>Reset Game</span></label></li>
											<?php if(is_user_logged_in()){?>
											<li><label id="save-game"><i class="fa fa-save"></i><span>Save</span></label></li>		
											<?php }else{?>
											<li><label id="reset-timer"><i class="fa fa-clock-o"></i><span>Reset Timer</span></label></li>
											<?php }?>
											
											<?php if($gameModel!='drag-drop'){?>
											<li><label id="solution-revel"><i class="fa fa-eye"></i><span>Solution</span></label></li>
											<?php }else{?>
											    <?php if(current_user_can( 'manage_options' ) ){ ?>
											        <li><label id="solution-revel"><i class="fa fa-eye"></i><span>Solution</span></label></li>
											    <?php } ?>
											<?php }?>
											<?php if($term->name!='Free Trial' && $term->slug!='free-trial'){?>
												<?php if($cw_bulk_clue_mail_box=='yes'){?>
												<li><label id="send-bulk-clue-email"><i class="fa fa-envelope"></i><span>Send Email</span></label></li>
												<?php }else{?>
												<!--<li><label id="send-email"><i class="fa fa-envelope"></i><span>Send Email</span></label></li>-->
												<?php }?>
											<?php }?>
											<li><label id="help-to-play" class="help-to-play"><i class="fa fa-video-camera"></i><span>How to Play</span></label></li>
											<?php if($cw_clues_visible_box!='no'){?>
											<li class="clues-list"><a href="#"><i class="fa fa-list-ul"></i></a>
												<ul id="clue-list">													
													<div class="clue-list-holder">
														<div class="tabs">
															<div class="tab-list">
																<ul>
																	<li class="active" id="accross-clues">Across</li><li id="down-clues">Down</li>
																</ul>
															</div>
															<div class="tab-holder">
																<div id="accross-clues" class="active">
																	<ul></ul>
																</div>
																<div id="down-clues">
																	<ul></ul>
																</div>
															</div>
														</div>
													</div>															
													<!--clue list with tabs carbojet -->																
												
												</ul>
											</li>
											<?php }?>
										</ul>															
									</div>
								</div>
								<div class="row">
									<div id="clues" class="col-md-12">
										<div class="clue-detail">
											<div id="clue-content">
												<div>
													<div id="clue1"><label><?php //echo $cw_clue_type_box;?></label>
													<span id="clue-text">This is the clue one</span> <!--<i id="help" title="Help" class="fa fa-question-circle"></i>-->
													<?php if($cw_bulk_clue_mail_box=='yes'){ ?>
													<span class="add-clue-to-mail" data-clueid="" data-solution="">Add to Email</span>
													<?php }?>
													<div class="help-concede"><input type="button" name="concede" id="concede" value="Concede"></div><!--<label id="sol_reveled" style="padding: 2px 10px;background-color: #b33b33;color: #fff;border-radius: 5px;">Solution Revealed</label>--></div>

													<div id="clue2"><label>Coffee time Clue:</label> <span>if exist then display the clue two</span></div>
													    <?php if( $gameModel!='drag-drop'){?>
														<div id="more_help">
															<p style="display: none;margin-bottom: 0!important;" id="show_hint"><label>Hint: </label> <span>This is the hint.</span></p>
															<input type="button" name="concede" id="concede" value="Concede">
															<input type="button" name="first_letter" id="first_letter" value="1st Letter">
															<input type="button" name="extra_letter" id="extra_letter" value="Extra Letter (s)">
															<?php if($gameModel!='books'){?>
															<input type="button" name="hint" id="hint" value="Hint">
															<?php }?>
															<input type="hidden" name="clueId" id="clueId" value=""/>

														</div>
														<?php }?>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
					<?php
					$theme = get_post_meta( get_the_ID(), 'cw_color_box' ,true);
					if(!$theme){
						$theme='the-times';
					}
					?>
                    <div class="col-md-12 <?php echo $theme; ?>" id="split-stage">
                    	
						<div class="game-pause">
							<!--<input name="start-timer" value="Press Play (Above)To Continue Crossword" type="button">-->
							<button name="start-timer" type="button">PAUSED: Press Play <i class="fa fa-play"></i> To Continue <?php echo get_the_title(); ?></button>
						</div>
						
							<div class="col-md-12">
								<div id="game-play">
								</div>
							</div>
						
                    </div>
                </div>
            </div>			
        </div>
		<div class="row">
			<?php if($term->name!='Free Trial' && $term->slug!='free-trial'){?>
				<div class="col-md-12">
					<?php if($cw_bulk_clue_mail_box=='yes'){?>
					<div class="modal" id="send-bulk-clue-email">
						<div class="modal-body">
							<div class="modal-head">
								<h4>Send to Admin</h4>
							</div>
							<div class="modal-content">
								<?php echo do_shortcode('[bulk-clue-mail]');?>								
							</div>
							<div class="close-modal"><i class="fa fa-close"></i></div>
						</div>
					</div>
					<?php }?>

				</div>
			<?php }?>
            
		</div>
	</div>

	<div class="container">
	    <div class="row">
    	    <div class="col-md-12">   
            	<div class="modal competition" id="competition">
                	<div class="modal-body">
                        <div class="modal-head">
    						<div class="competition-top" style="text-align:right;"><button class="btn btn-default close"><i class="fa fa-close"></i></button></div>
    					</div>
                        <div class="modal-content">
                            <p>
                                In competition mode there is no assistance and no pausing. Begin by selecting your clue on the grid. Play will conclude automatically with a correct completed solution to the crossword and your timed performance posted to the new leader board. This facility is open to subscribers alone. These are identical conditions of play found in crossword championships.
							</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
	</div>

	<div class="container">
	    <div class="row">
    	    <div class="col-md-12">   
            	<div class="modal introduction" id="introduction">
                	<div class="modal-body">
                        <div class="modal-head">
    						<div class="instruction-top" style="text-align:right;"><button class="btn btn-default close"><i class="fa fa-close"></i></button></div>
    					</div>
                        <div class="modal-content">
                            <?php
                                if(!empty($cw_intro_file[0])){
                            ?>
                            <object data="<?php echo wp_get_attachment_url($cw_intro_file[0]);?>#toolbar=0" type="application/pdf" width="100%" height="100%"></object>
                            <?php
                                }else{
                                    $content = apply_filters( 'the_content', get_the_content() );
                                    echo $content; 
                                }
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
	</div>

	<div class="container">
	    <div class="row">
    	    <div class="col-md-12">   
            	<div class="modal how-to-play-game-video" id="how-to-play-game-video">
                	<div class="modal-body">
                        <div class="modal-head">
    						<div class="instruction-top" style="text-align:right;"><button class="btn btn-default close"><i class="fa fa-close"></i></button></div>
    					</div>
                        <div class="modal-content">
							<?php if($gameModel=='drag-drop'){ ?>
                            <video class="elementor-video" src="https://dev.crosswordsakenhead.com/wp-content/uploads/2020/10/20201001_232808.mp4" controls="" controlslist="nodownload" poster="https://dev.crosswordsakenhead.com/wp-content/uploads/2020/10/20201001_232808.mp4" webboost_found_paused="true" webboost_processed="true"></video>
							<?php }else{ ?>
							<video class="elementor-video" src="https://dev.crosswordsakenhead.com/wp-content/uploads/2020/10/20201001_223246.mp4" controls="" controlslist="nodownload" poster="https://dev.crosswordsakenhead.com/wp-content/uploads/2020/10/20201001_223246.mp4" webboost_found_paused="true" webboost_processed="true"></video>
							<?php }?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
	</div>


    <div class="container">
        <div class="row">
            <div class="col-sm-12 col-md-10 col-lg-10 col-xs-10 col-centered">
                <h3>Introduction</h3>
            </div>
			<div class="col-sm-12 col-md-10 col-lg-10 col-xs-10 col-centered">
                <div class="game-intro">
				    <?php
				    $content = apply_filters( 'the_content', get_the_content() );
                    echo $content;
				    ?>
				</div>
	        </div>
        </div>
    </div>

	<!--Instuction Modal -->
<div class="z-modal z-fade" id="instuctionModal" tabindex="-1" role="dialog" aria-labelledby="instuctionModalTitle" aria-hidden="true">
  <div class="z-modal-dialog" role="document">
    <div class="z-modal-content">
      <div class="z-modal-header">
        <h5 class="z-modal-title" id="instuctionModalTitle">Instruction</h5>
      </div>
      <div class="z-modal-body">
        hello
      </div>
      <div class="z-modal-footer">
        <button type="button" class="btn btn-secondary z-modal-close" data-dismiss="z-modal">Close</button>
      </div>
    </div>
  </div>
</div>
<div class="site-main container">
	<div class="row">
    <?php
	get_footer();
?>
