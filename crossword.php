<?php

/**

* plugin name: DA Crosswords

* Author: Santosh Satapathy

* Description: This plugin is very usefull for creating and publishing crossword game.

*/

require_once plugin_dir_path(__FILE__).'cw-functions.php';

//check if the user is subscribed for the current group

function is_user_subscribed(){

		global $post,$wp_query;

		$mylevel = pmpro_getMembershipLevelsForUser();

		$mylevel = $mylevel[0];

		$pmpro_levels = pmpro_getAllLevels(false, true);

		$pmpro_levels = $pmpro_levels[$mylevel->id];

		$allowedCats = array();

		if(isset($pmpro_levels->allowed_cats)){

			$allowedCats = $pmpro_levels->allowed_cats;

			$allowedCats = unserialize($allowedCats);

		}

		

		//

		if(is_single()){

			$currentTerm = get_the_terms($post,'group');

			$currentTerm = $currentTerm[0];

			$currentTerm = $currentTerm->term_id;

		}else{

			$currentSlug =  get_query_var('group');

			$currentTerm = get_term_by('slug',$currentSlug,'group');

			$currentTerm = $currentTerm->term_id;

		}

		if(!in_array($currentTerm,$allowedCats)){

			return FALSE;

		}else{

			return TRUE;

		}



}



//display the trial crosswords in front page

add_shortcode('cw-trials',function($attr=[]){

	

	if(!empty($attr['label-text'])){

		$lebel_text = $attr['label-text'];

	}else{

		$lebel_text = '';

	}

	

	if(!empty($attr['number'])){

		$filter['number']=$attr['number'];

	}

	$filter = array(

					'taxonomy'=>'group',

					'hide_empty'=>TRUE,

					'parent'=>0,

					'slug'=>'free-trial'

				);

	

	$cats = get_terms($filter);

	$cat = $cats[0];

	$cat_image = get_term_meta($cat->term_id,'group_image',TRUE);

	

	$data = '<div class="col-md-3 col-sm-6 col-lg-3 col-xs-12"><div class="image-grid"><span class="trial-label-index">'.$lebel_text.'</span><a href="#" ><img  src="'.$cat_image.'" /></a></div></div>';

	echo $data;

});

//display all crosswords in index page



function cw_crosswordlist($args=[]){

	$args = array(

	'fields'=>'ids',

	'post_status' => 'publish',

    'post_type'=> 'cw_crosswords',

	'posts_per_page'=>-1,

	'meta_key' => 'cw_current_pos',

    'orderby' => 'meta_value_num',

    'order' => 'ASC',

    );

	

	

	$allposts = get_posts($args);

	

	/*

	echo "<pre>";

	var_dump($allposts);

	echo "</pre>";

	*/

	//<th>Puzzles</th>

	$record=1;

	$page=1;

	$row=1;

	$table = '<table>

		<tr><th>Title</th>

		<th>Page</th>

		

		<th>Count</th>

		</tr>';

	foreach($allposts as $id){

		set_time_limit(0);

		if($record>=11){$page++;$record=1;}

		//$chapters = count(unserialize(get_post_meta($id,'chapters',TRUE)));

		

		//'.get_post_meta($id,'cw_current_pos',true).'

		//<td>'.$chapters.'</td>

			$table .= '<tr>

			<td><a href="'.esc_url(get_permalink($id)).'">'.esc_html__(get_the_title($id)).'</a></td><td><a href="https://www.crosswordsakenhead.com/crosswords/page/'.$page.'">'.$page.'</a></td>

			<td>'.$row.'</td>';

			$table .= '</tr>';			

			$row++;

			$record++;

			//usleep(500000);

	}

	

	$table .= '</table>';

	echo $table;

}

add_shortcode('cw-crosswordlist','cw_crosswordlist');

//display the categories in front page

add_shortcode('cw-categories',function($attr=[]){

	

	$groupFilter = array(

						'taxonomy'=>'group',

						'hide_empty'=>TRUE,

						'parent'=>0

					);

	if(count($attr)>0){

		$attr = array_change_key_case($attr);

	}

	

	//get category bby slug name

	if(!empty($attr['slug'])){

		$groupFilter['slug'] = $attr['slug'];

	}

	

	//limit the number of category

	if(!empty($attr['limit'])){

		$groupFilter['number'] = $attr['limit'];

	}

	//skip some records

	if(!empty($attr['offset'])){

		$groupFilter['offset'] = $attr['offset'];

	}

	//hide/display the categories that have no posts

	if(!empty($attr['hide-empty'])){

		$groupFilter['hide_empty'] = $attr['hide-empty'] ? TRUE : FALSE;

	}

	

	

	

	

	

	$groups = get_terms($groupFilter);

	

	//debug_result($groups);

	

	echo '<div>';

	foreach($groups as $group){

			

		

		echo '<div class="col-md-3 col-lg-3 col-sm-6 col-xs-6">'.

			'<a href="';

			if(!empty($attr['cat-view'])){

				echo esc_url(get_term_link($group->term_id));

			}else{

				echo esc_url(get_permalink());

			}

		echo '"><img src="'.get_term_meta($group->term_id,'group_image',TRUE).'">';

				//get_the_post_thumbnail($post->ID,'medium');

				

		//hide/display the hide the footer and the footer title div

		if(!isset($attr['hide-footer'])){

			echo '<div class="cw-cat-title">'.

						$group->name.

					'</div>';

		}elseif(!empty($attr['show-title'])){

			if(!empty($attr['title'])){

				$title = $attr['title'];

			}else{

				$title = $term->name;

			}

			echo '<div class="title"><h3><a style="color:#007bff;" href="';

			if(!empty($attr['cat-view'])){

				echo esc_url(get_term_link($group->term_id));

			}else{

				echo esc_url(get_permalink());

			}

			

			echo '">'.$title.'</a></h3></div>';

		}

		

		echo '</a>'.

			'</div>';

		wp_reset_postdata();

	}

	echo '</div>';

});



//filter and remove the null value from an array

function filter_null($data){

	if($data==''){

		return FALSE;

	}else{

		return TRUE;

	}

}





function cw_templates($template){

    

	global $post;

	

	

	 if(get_query_var('post_type')!=='cw_crosswords' && empty(get_query_var('group'))){

		 return get_page_template();

	 }

	

	 //listing template included if user custom template exist

	if(is_archive() || is_search()){

		

			if(file_exists(get_stylesheet_directory().'/archive-ad.php')){

					return get_stylesheet_directory().'/archive-ad.php';

			}else{

				return plugin_dir_path(__FILE__).'templates/archive-cw.php';

			}

	}

   

	if(is_single()){

		if(file_exists(get_stylesheet_directory().'/single-ad.php')){

				return get_stylesheet_directory().'/single-ad.php';

		}else{

			return plugin_dir_path(__FILE__).'templates/single-cw.php';

		}

	}

	return $template;

}

add_filter("template_include","cw_templates",1,1);



//save the crosswords

add_action('save_post','cw_save_crosswords');

function cw_save_crosswords($data){

	global $post;

	

	if($_POST && is_object($post)){	

		if($post->post_type=='cw_crosswords'){

		

			if(!empty($_POST['cw_xml_data'])) {

					// Check if the type is supported. If not, throw an error.

						$chapters = json_decode(stripslashes($_POST['cw_xml_data']),TRUE);

						unset($_POST['cw_xml_data']);

						foreach($chapters as $key=>$val){

							$chapters[$key] = (object)$chapters[$key];

							

						}

						unset($key);

						unset($val);



						update_post_meta($post->ID,'chapters',serialize($chapters));

						unset($chapters);

						update_post_meta($post->ID,'cw_desc',$_POST['cw_desc']);

				   

				}else{

			

					$chapters = json_decode(stripslashes($_POST['chapter_data']));

					update_post_meta($post->ID,'chapters',serialize($chapters));					

					//update_post_meta($post->ID,'cw_desc',$_POST['cw_desc']);

				}

				

			/*

			if(!empty($_FILES['cw_intro_doc']['name'])){

					$file = wp_upload_bits( $_FILES['cw_intro_doc']['name'], NULL, file_get_contents($_FILES['cw_intro_doc']['tmp_name'])) ;

					update_post_meta($post->ID,'cw_intro_file',$file);

			}

			*/

			if(isset( $_REQUEST['intro_file_id'] )){

				update_post_meta($post->ID,'cw_intro_file',sanitize_text_field( $_POST['intro_file_id'] ));

			}

			if ( isset( $_REQUEST['cw_current_pos'] ) ) {

				update_post_meta( $post->ID, 'cw_current_pos', sanitize_text_field( $_POST['cw_current_pos'] ) );

			}

			

			if ( isset( $_REQUEST['cw_model_type_box'] ) ) {

				update_post_meta( $post->ID, 'cw_model_type_box', sanitize_text_field( $_POST['cw_model_type_box'] ) );

			}

			/*

			if ( isset( $_REQUEST['cw_free_box'] ) ) {

				update_post_meta( $post->ID, 'cw_free_box', sanitize_text_field( $_POST['cw_free_box'] ) );

			}

			*/

			/*

			if ( isset( $_REQUEST['cw_compition_box'] ) ) {

				update_post_meta( $post->ID, 'cw_compition_box', sanitize_text_field( $_POST['cw_compition_box'] ) );

			}

			*/

			if ( isset( $_REQUEST['cw_color_box'] ) ) {

				update_post_meta( $post->ID, 'cw_color_box', sanitize_text_field( $_POST['cw_color_box'] ) );

			}

			/*

			if ( isset( $_REQUEST['cw_logo_type_box'] ) ) {

				update_post_meta( $post->ID, 'cw_logo_type_box', sanitize_text_field( $_POST['cw_logo_type_box'] ) );

			}

			*/

			/*

            if ( isset( $_REQUEST['cw_clue_type_box'] ) ) {

				update_post_meta( $post->ID, 'cw_clue_type_box', sanitize_text_field( $_POST['cw_clue_type_box'] ) );

			}

			*/

			if ( isset( $_REQUEST['cw_clues_visible_box'] ) ) {

				update_post_meta( $post->ID, 'cw_clues_visible_box', sanitize_text_field( $_POST['cw_clues_visible_box'] ) );

			}

			/*

			if ( isset( $_REQUEST['cw_bulk_clue_mail_box'] ) ) {

				update_post_meta( $post->ID, 'cw_bulk_clue_mail_box', sanitize_text_field( $_POST['cw_bulk_clue_mail_box'] ) );

			}

			*/

			if ( isset( $_REQUEST['cw_crossword_access'] ) ) {

				update_post_meta( $post->ID, 'cw_crossword_access', sanitize_text_field( $_POST['cw_crossword_access'] ) );

			}

			

			if(isset($_REQUEST['cw_puzzle_lock']) && $_REQUEST['cw_puzzle_lock']==1){

			    cw_extended_pmpro_save($post->ID,$_REQUEST['cw_extended_pmpro_level_id']);

			    

			}

		}

		

		if($post->post_type=='cw_chapters'){

		    if ( isset( $_REQUEST['cw_app_home_chapter'] ) ) {

				update_post_meta( $post->ID, 'cw_app_home_chapter', sanitize_text_field( $_POST['cw_app_home_chapter'] ) );

			}

		}

	}

}





//function to display the chapters metabox

function cw_display_chapters_meta_box(){

	//echo '<pre>';

	

	//echo '<pre>';print_r($chapters);exit;

	echo '<div class="col-md-12">';

	?>

<p>Compressed VERSION</p>

<?php

	$post_id  =get_the_ID();



	$args = array(

		"post_type"			=> "cw_chapters",

		"post_parent"		=> $post_id,

		"posts_per_page"	=> -1,

		'orderby'			=>'ID',

		'order'				=>'ASC',

		

	);

	$get_chapters = get_posts($args);

	/*

	//$cw_no_chapters = get_post_meta(get_the_ID(),'cw_no_chapters',true);

	//var_dump(get_post_meta(get_the_ID(),'chapters',TRUE));

	if(is_array(get_post_meta(get_the_ID(),'chapters',TRUE))){

	    $xml_chapters = unserialize(get_post_meta(get_the_ID(),'chapters',TRUE));   

	}else{

	    $xml_chapters = array();

	}

	*/

	$xml_chapters = array();

    $compress_chapters = array();

    

	//new code for fetch chapters

	if(count($get_chapters)>0){

	    $compress_chapters = array();

		foreach($get_chapters as $k=>$postchapter){

			$chapter_detail = unserialize( get_post_meta($postchapter->ID,'chapterdetail',true) );

			$compress_chapters[$postchapter->ID] = $chapter_detail;

			

			?>

			<div class="chapter" id="<?php echo $postchapter->ID;?>">

				<h4><?php echo $postchapter->post_title;?></h4>				

				<div class="operation">

				    <a href="<?php echo get_site_url().'/wp-admin/post.php?post='.$postchapter->ID.'&action=edit';?>"><i class="fa fa-link"></i></a>

				    <span id="<?php echo $postchapter->ID;?>" class="delete"><i class="fa fa-trash"></i></span>

				    <span id="<?php echo $postchapter->ID;?>" class="edit" data-compress="true"><i class="fa fa-pencil"></i></span>

			    </div>

			</div>

			<?php

		}

	}else{

	    /*

		if($cw_no_chapters==null){$k=-1;}	

		for($i=0; $i<$cw_no_chapters;$i++){

			

			$chapter_detail = unserialize( get_post_meta($post_id,'chapterdetail'.$i,true) );



			if($chapter_detail==false){

				$k=-1;

				break;

			}

			$k = $chapter_detail['chapter_index'];

			$compress_chapters[$k] = $chapter_detail;

			

			?>

			<div class="chapter" id="<?php echo $k;?>">

				<h4><?php echo $chapter_detail['chapterName'];?></h4>				

				<div class="operation"><span id="<?php echo $k;?>" class="delete"><i class="fa fa-trash"></i></span>

				<span id="<?php echo $k;?>" class="edit" data-compress="false"><i class="fa fa-pencil"></i></span></div>

			</div>

			<?php		

		}

		*/

	}

	$xml_chapters = unserialize(get_post_meta(get_the_ID(),'chapters',TRUE)); 

	

	if($xml_chapters!=null){

		?>

	<p>XML VERSION</p>

    <?php

        $k=-1;

		foreach($xml_chapters as $key=>$chapter){			

			$k++;

			$compress_chapters[$key] = $chapter;

			?>

			<div class="chapter" id="<?php echo $k;?>"><h4><?php echo $chapter->chapterName;?></h4>

				

            <div class="operation"><span id="<?php echo $k;?>" class="delete"><i class="fa fa-trash"></i></span>

            <span id="<?php echo $k;?>" class="edit" data-compress="false"><i class="fa fa-pencil"></i></span></div>

			

</div>

		<?php 

		

		}

	}

	$chapters = $compress_chapters;

	/*

	if($xml_chapters!=null){

	    $chapters = array_merge($compress_chapters,$xml_chapters);

	}else{

	    $chapters = $compress_chapters;

	}

	*/



	echo '<div class="chapter new" id="-1"><h4>+ Add New Crossword</h4></div>

		</div>';

	if(is_array($chapters)){

		?>

		<input type="hidden" name="cw_no_chapters" value="<?php echo count($chapters);?>">

	<?php

	}

	?>

	<input type="hidden" name="chapter_data" id="chapter_data" value=''/>

	

	<table style="width: 100%;display: none;" id="detailsTable">

	<hr>

		<tr>

			<th>Dimension</th>

			<td>

				<input type="text" size="5" placeholder="Rows" name="rows" value=""/>

				<input type="text" size="5" placeholder="Cols" name="cols" value=""/>

			</td>

			<th>Crossword</th>

			<td>

				<input type="text" placeholder="Chapter Name" name="chapter_name" value=""/>

				<input type="hidden" name="cw-words" value=''/>

				<input type="hidden" name="chapter_id" value=''/>

			</td>

			<th>Editorial</th>

			<td>

				<input name="chapter_author_name" type="text">				

			</td>

			<td>

				<input type="button" name="add_new_word" value="Add New Word" class="btn btn-primary" style="display:none;" />

				<button type="button" name="update_chapter" class="button button-primary button-small">update</button>

				<button type="button" name="convert_ch" id="" class="button button-primary button-small">Compress <span class="compress-status" style="display:none;"><i class="fa fa-spinner fa-spin"></i></span></span></button>

				<button type="button" name="chapter_reset" id="" class="button button-primary button-small">Reset</button>

			</td>

		</tr>

		

	</table>

	

	<table id="cw_table" class="cw_table" border="1" style="border-collapse: collapse;"></table>

	

	<!-- Modal -->

	  <div class="modal fade" id="myModal" role="dialog">

	    <div class="modal-dialog modal-md">

	      <div class="modal-content">

	        <div class="modal-header">

	          <h3 class="modal-title">Word Details</h3>

	          <button type="button" class="close" data-dismiss="modal">&times;</button>

	        </div>

	        <div class="modal-body">

				<div class="col-md-12">

					<form class="form-horizontal" id="modalForm" onsubmit="return false;">

					    <input type="hidden" name="clue_index">

						<div class="form-group">

							<label for="clue">Coordinates</label>

							<table>

								<tr>

									<td><input type="text" name="cw_y" id="cw_y" value="" class="form-control" placeholder="X value"/></td>

									<td><input type="text" name="cw_x" id="cw_x" value="" class="form-control" placeholder="Y value"/></td>

								</tr>

							</table>

							<p class="text-danger" id="coordinates-error"></p>

							

						</div>

						

						<div class="form-group">

							<label for="clue">Clue 1</label>

							<!--<input type="text"name="clue" id="clue" value="" class="form-control"/>-->

							<p>

								<button type="button" name="italic-mark"><i>I</i></button>

								<button type="button" name="bold-mark"><b>B</b></button>

							</p>

							<div data-id="clue" data-name="clue" class="text-editor" contenteditable="true"></div>

							<textarea name="clue" id="clue" style="display: none;"></textarea>

							<p class="text-danger" id="clue_error"></p>

							

						</div>

						<div class="form-group">

							<label for="clue">Clue 2</label>

							<!--<input type="text"name="cw_clue2" id="cw_clue2" value=""class="form-control"/>-->

							<div data-id="cw_clue2" data-name="cw_clue2" class="text-editor" contenteditable="true"></div>

							<textarea name="cw_clue2" id="cw_clue2" style="display: none;"></textarea>

							<p class="text-danger" id=""></p>

						</div>

							<div class="form-group">

							<label for="cw_dir">Direction</label>

							<select name="cw_dir" id="cw_dir" class="form-control">

							    <option value="Across">Across</option>

							    <option value="Down">Down</option>

							</select>

							<p class="text-danger" id=""></p>

						</div>

						<div class="form-group">

							<label for="hint">Hint</label>

							<!--<input type="text" name="hint" placeholder="Hint" id="hint" value="" class="form-control"/>-->

							<div data-id="hint" data-name="hint" class="text-editor" contenteditable="true"></div>

							<textarea name="hint" id="hint" style="display: none;"></textarea>

							<p class="text-danger" id="hint_error"></p>

						</div>

						<div class="form-group">

							<label for="cw_number">Number</label>

							<input type="text"name="cw_number" id="cw_number" value=""class="form-control"/>

							<p class="text-danger" id="number-error"></p>

						</div>

						<div class="form-group">

							<label for="cw_randomLetterNoSet">Random Letter NoSet</label>

							<input type="checkbox"name="cw_randomLetterNoSet" id="cw_randomLetterNoSet" checked class="form-control"/>

							<p class="text-danger" id=""></p>

						</div>

						<div class="form-group">

							<label for="cw_word">Word</label>

							<input type="text"name="cw_word" id="cw_word" value="" class="form-control"/>

							<p class="text-danger" id=""></p>

						</div>						

						

						<div class="form-group pull-right">

							<button type="button" name="save" class="btn btn-success" id="save">Save</button>

							<button type="button" name="delete_word" class="btn btn-info" id="delete-word">Delete</button>

	          				<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>

						</div>

						

						<div class="form-group pull-left">

							<h4 id="status_msg" style="font-style: 16px;display: none;"></h4>

						</div>

						<input type="hidden" name="cw_clue_index" id="cw_clue_index">

						

					</form>

				</div>

	        </div>

	        <div class="modal-footer"></div>

	      </div>

	    </div>

	</div>

		

<?php 





    $compress_chapters = array();

	$get_chapters = array();

	$chapters = array();

    $args = array(

		"post_type"			=> "cw_chapters",

		"posts_per_page"		=>-1,

		"post_status"		=> "publish",

		"ordre"				=> "ID",

		"orderby"			=> "ASC",

		"post_parent"		=>get_the_ID()

	);

	$get_chapters = get_posts($args);

	if(count($get_chapters)>0){

		foreach($get_chapters as $k=>$chapter){

			$chapter_detail = unserialize( get_post_meta($chapter->ID,'chapterdetail',true) );		

			//$compress_chapters[$chapter->ID] = $chapter_detail;

			$chapters[$chapter->ID] = $chapter_detail;

		}

	}

	

	    $xml_chapters = unserialize(get_post_meta(get_the_ID(),'chapters',TRUE));

	    if($xml_chapters!=null){

	        foreach($xml_chapters as $k=>$data){

	            $chapters[$k] = $data;

	        }

	    }

	wp_enqueue_style('cw-style-css',plugins_url('assets/css/style.css',__FILE__));

	wp_enqueue_script('cw-script-js',plugins_url('assets/js/script.js',__FILE__));

	wp_localize_script('cw-script-js','adminlocaljs',array('chapters'	=> $chapters,							

												'current_post_id'=>get_the_id(),

												'ajaxUrl'=>admin_url('admin-ajax.php'),

												));



}



//initialize the admin meu icon an d create the cuetom post types

add_action('init','cw_create_custom_menu');

function cw_create_custom_menu(){	

	register_post_type('cw_crosswords',array(

		'labels'		=> array(

								'name'				=>	'Crosswords',

								'singular_name'		=>	'Crossword',

								'add_new'			=>	'Add New',

								'add_new_item'		=>	'Add New Crossword',

								'edit'				=>	'Edit',

								'edit_item'			=>	'Edit Crossword',

								'view'				=>	'View',

								'view_items'		=>	'View Crossword',

								'search_items'		=>	'Search Crosswords',

								'not_found'			=>	'No Crosswords Found',

								'not_found_in_trash'=>	'No Crosswords Found in Trash',

								'parent'			=>	'Parent Crossword'

								),

		'public'		=> TRUE,

		'menu_position'	=> 3,

		'menu_icon'		=> 'dashicons-editor-kitchensink',

		'has_archive'	=> TRUE,

		'rewrite' 		=> array('slug' => 'crosswords'),

		'supports'		=> array('thumbnail','title','editor')

		));

	//remove_post_type_support('cw_crosswords','editor');	

}





add_action('init','cw_create_chapters_post_type');

function cw_create_chapters_post_type(){

	register_post_type('cw_chapters',array(

		'labels'		=> array(

								'name'				=>	'Chapters',

								'singular_name'		=>	'Chapter',

								'add_new'			=>	'Add New',

								'add_new_item'		=>	'Add New Chapter',

								'edit'				=>	'Edit',

								'edit_item'			=>	'Edit Chapter',

								'view'				=>	'View',

								'view_items'		=>	'View Chapter',

								'search_items'		=>	'Search Chapter',

								'not_found'			=>	'No Chapters Found',

								'not_found_in_trash'=>	'No Chapters Found in Trash',

								'parent'			=>	'Parent Chapter'

								),

		'public'		=> TRUE,

		"show_in_menu"  => FALSE,

		'menu_icon'		=> 'dashicons-editor-kitchensink',

		'has_archive'	=> TRUE,

		'rewrite' 		=> array('slug' => 'cwchapters'),

		'supports'		=> array('thumbnail','title')

		));

	remove_post_type_support('cw_chapters','editor');

}





add_action('init','cw_create_device_post_type');

function cw_create_device_post_type(){

	register_post_type('cw_device_install',array(

		'labels'		=> array(

								'name'				=>	'Devices',

								'singular_name'		=>	'Device',

								'add_new'			=>	'Add New',

								'add_new_item'		=>	'Add New Device',

								'edit'				=>	'Edit',

								'edit_item'			=>	'Edit Device',

								'view'				=>	'View',

								'view_items'		=>	'View Device',

								'search_items'		=>	'Search Device',

								'not_found'			=>	'No Devices Found',

								'not_found_in_trash'=>	'No Devices Found in Trash',

								'parent'			=>	'Parent Device'

								),

		'public'		=> TRUE,

		"show_in_menu"  => FALSE,

		'menu_icon'		=> 'dashicons-editor-kitchensink',

		'has_archive'	=> TRUE,

		'rewrite' 		=> array('slug' => 'devices'),

		'supports'		=> array('thumbnail','title')

		));

	remove_post_type_support('cw_device_install','editor');

}





add_action('admin_menu', 'cw_add_submenu');

function cw_add_submenu(){



	add_submenu_page(

        'edit.php?post_type=cw_crosswords',

        'Chapters',

        'Chapters',

        'manage_options',

		'edit.php?post_type=cw_chapters' );



	add_submenu_page(

		'edit.php?post_type=cw_crosswords',

		'Devices',

		'Devices',

		'manage_options',

		'edit.php?post_type=cw_device_install' );

	add_submenu_page(

        'edit.php?post_type=cw_crosswords',

        'Options',

        'Options',

        'manage_options',

        'cw-options',

        'cw_options_callback' );

}



//upload intro metabox

function cw_upload_doc_meta_box(){

	$cw_intro_file = get_post_meta(get_the_ID(),'cw_intro_file',true);

	$img = wp_get_attachment_image_src($cw_intro_file,'thumbnail',true);

	$html = '<p class="description">';

    $html .= 'Upload the .doc file.';

    $html .= '</p>';

	$html .='<p>';

    $html .= '<img src="'.$img[0].'" width="'.$img[1].'" height="'.$img[2].'">';

    $html .='</p>';

	$html .= '<input type="hidden" name="intro_file_id" id="intro-file-id">';

	$html .='<button type="button" class="btn btn-default" id="add-intro-file">Upload PDF File</button>';

    $html .= '<span class="text-danger"></span>';

     

    echo $html;

}



//show app home 

function cw_app_home_chapter_box(){

	$cw_app_home_chapter = get_post_meta(get_the_ID(),'cw_app_home_chapter',TRUE);

	

	$html ='<p> On / Off show at home page of App</p>';

	if($cw_app_home_chapter==1){

		$html .='<p>

  	<label>Yes <input name="cw_app_home_chapter" type="radio" value="1" checked ></label>

	<label>No <input name="cw_app_home_chapter" type="radio" value="0" ></label>

  </p>';

	}else{

		$html .='<p>

  	<label>Yes <input name="cw_app_home_chapter" type="radio" value="1"  ></label>

	<label>No <input name="cw_app_home_chapter" type="radio" value="0" checked></label>

  </p>';

	}

  echo $html;

}



//sort metabox

function cw_sort_box(){

	$cw_current_pos = get_post_meta(get_the_ID(),'cw_current_pos',TRUE);

	if($cw_current_pos==null){

		$cw_current_pos = 1;

	}

	$html ='<p>Enter the position at which you would like the Crossword to appear. For exampe, Crossword "1" will appear first, Crossword "2" second, and so forth.</p>

  <p><input type="number" name="cw_current_pos" value="'.$cw_current_pos.'" /></p>';

  echo $html;

}



function cw_model_type_box(){

	$cw_model_type_box = get_post_meta(get_the_ID(),'cw_model_type_box',TRUE);

	$list = array('books'=>'Books','drag-drop'=>'Drag Drop','word-game'=>'Word Game');

	$html ='<p>Game type</p>

  <p>

  <select name="cw_model_type_box">';

  foreach($list as $k=>$v){

	if($k==$cw_model_type_box){

		$html .='<option value="'.$k.'" selected>'.$v.'</option>';

	}else{

		$html .='<option value="'.$k.'">'.$v.'</option>';

	}

  }

  $html .='</select></p>';

  echo $html;

}

/*

function cw_free_box(){

	$cw_free_box = get_post_meta(get_the_ID(),'cw_free_box',TRUE);	

	$list = array('no'=>'No','yes'=>'Yes');

	$html ='<p>Is It Free (if yes user can play without subscription)</p>

  <p>

  <select name="cw_free_box">';

  foreach($list as $k=>$v){

	if($k==$cw_free_box){

		$html .='<option value="'.$k.'" selected>'.$v.'</option>';

	}else{

		$html .='<option value="'.$k.'">'.$v.'</option>';

	}

  }

  $html .='</select></p>';

  echo $html;

}

*/



/*

function cw_compition_box(){

	$cw_competition_box = get_post_meta(get_the_ID(),'cw_competition_box',TRUE);	

	$list = array('no'=>'No','yes'=>'Yes');

	$html ='<p>Competition (if yes user needs to subscribe to play game)</p>

  <p>

  <select name="cw_competition_box">';

  foreach($list as $k=>$v){

    if(isset($cw_compition_box)){

    	if($k==$cw_compition_box){

    		$html .='<option value="'.$k.'" selected>'.$v.'</option>';

    	}else{

    		$html .='<option value="'.$k.'">'.$v.'</option>';

    	}

    }else{

		$html .='<option value="'.$k.'">'.$v.'</option>';

	}

  }

  $html .='</select></p>';

  echo $html;

}

*/

function cw_color_box(){

	$cw_color_box = get_post_meta(get_the_ID(),'cw_color_box',TRUE);	

	$list = array('the-times'=>'Blue','the-sun'=>'Orange','the-sunday-times'=>'Purple');

	$html ='<p>Theme (Select Theme For Grid)</p>

  <p>

  <select name="cw_color_box">';

  foreach($list as $k=>$v){

	if($k==$cw_color_box){

		$html .='<option value="'.$k.'" selected>'.$v.'</option>';

	}else{

		$html .='<option value="'.$k.'">'.$v.'</option>';

	}

  }

  $html .='</select></p>';

  echo $html;

}

/*

function cw_logo_type_box(){

	$cw_logo_type_box = get_post_meta(get_the_ID(),'cw_logo_type_box',TRUE);	

	$list = array('the-times'=>'The Times Logo','the-sun'=>'The Sun Logo','the-sunday-times'=>'The Sunday Times Logo');

	$html ='<p>Logos (Select Logo For Game)</p>

  <p>

  <select name="cw_logo_type_box"><option value="">No Logo</option>';

  foreach($list as $k=>$v){

	if($k==$cw_logo_type_box){

		$html .='<option value="'.$k.'" selected>'.$v.'</option>';

	}else{

		$html .='<option value="'.$k.'">'.$v.'</option>';

	}

  }

  $html .='</select></p>';

  echo $html;

}

*/

/*

function cw_clue_type_box(){

	$cw_clue_type_box = get_post_meta(get_the_ID(),'cw_clue_type_box',TRUE);	

	$list = array('Cryptic Clue'=>'Cryptic Clue','Concise Clue'=>'Concise Clue');

	$html ='<p>Clues(Select Clue Type For Game)</p>

  <p>

  <select name="cw_clue_type_box">';

  foreach($list as $k=>$v){

	if($k==$cw_clue_type_box){

		$html .='<option value="'.$k.'" selected>'.$v.'</option>';

	}else{

		$html .='<option value="'.$k.'">'.$v.'</option>';

	}

  }

  $html .='</select></p>';

  echo $html;

}

*/

function cw_clues_visible_box(){

	$cw_clues_visible_box = get_post_meta(get_the_ID(),'cw_clues_visible_box',TRUE);

	$html ='<p> Show Clues list On / Off </p>';

	if($cw_clues_visible_box=='yes'){

		$html .='<p>

  	<label>Yes <input name="cw_clues_visible_box" type="radio" value="yes" checked ></label>

	<label>No <input name="cw_clues_visible_box" type="radio" value="no" ></label>

  </p>';

	}else{

		$html .='<p>

  	<label>Yes <input name="cw_clues_visible_box" type="radio" value="yes"  ></label>

	<label>No <input name="cw_clues_visible_box" type="radio" value="no" checked></label>

  </p>';

	}

  

  echo $html;

}



/*

function cw_bulk_clue_mail_box(){

	$cw_bulk_clue_mail_box = get_post_meta(get_the_ID(),'cw_bulk_clue_mail_box',TRUE);

	$html ='<p> Mail Bulk Clues On / Off </p>';

	if($cw_bulk_clue_mail_box=='yes'){

		$html .='<p>

  	<label>Yes <input name="cw_bulk_clue_mail_box" type="radio" value="yes" checked ></label>

	<label>No <input name="cw_bulk_clue_mail_box" type="radio" value="no" ></label>

  </p>';

	}else{

		$html .='<p>

  	<label>Yes <input name="cw_bulk_clue_mail_box" type="radio" value="yes"  ></label>

	<label>No <input name="cw_bulk_clue_mail_box" type="radio" value="no" checked></label>

  </p>';

	}

  

  echo $html;

}

*/

/*access to crossword*/

function cw_crossword_access_box(){

	$cw_crossword_access = get_post_meta(get_the_ID(),'cw_crossword_access',TRUE);

	?>

	<p> Select Any One Option below </p>

	<?php

	if($cw_crossword_access=='archive-lock'){

	    ?>

        <p>

      	    <label>ENTER <input name="cw_crossword_access" type="radio" value="enter" ></label>

    	    <label>ARCHIVE LOCK <input name="cw_crossword_access" type="radio" value="archive-lock" checked></label>

    	    <label>IN PROCESS LOCK <input name="cw_crossword_access" type="radio" value="in-precess-lock" ></label>

        </p>

    <?php

		

	}else if($cw_crossword_access=='in-precess-lock'){

	    ?>

        <p>

          	<label>ENTER <input name="cw_crossword_access" type="radio" value="enter" ></label>

        	<label>ARCHIVE LOCK<input name="cw_crossword_access" type="radio" value="archive-lock"></label>

        	<label>IN PROCESS LOCK <input name="cw_crossword_access" type="radio" value="in-precess-lock" checked></label>

        </p>

    <?php

		

	}else{

	    ?>

    	<p>

          	<label>ENTER <input name="cw_crossword_access" type="radio" value="enter" checked ></label>

        	<label>ARCHIVE LOCK <input name="cw_crossword_access" type="radio" value="archive-lock" ></label>

        	<label>IN PROCESS LOCK <input name="cw_crossword_access" type="radio" value="in-precess-lock" ></label>

        </p>

    <?php

	}

	?>

	    <p> Lock All Puzzles </p>

    	<?php

    	

    	global $post, $wpdb;

    	$membership_levels = pmpro_getAllLevels(true, true);

    	



    	$level = $membership_levels[2];

    	?>

	    <p>

	        <input name="cw_extended_pmpro_level_id" type="hidden" value="<?php echo $level->id;?>">

	        <label>Yes <input name="cw_puzzle_lock" type="radio" value="1" checked ></label>

        	<label>No <input name="cw_puzzle_lock" type="radio" value="0" ></label>

	    </p>

	<?php

}



function cw_add_order_column( $defaults ){

	global $post;

	if($post->post_type=='cw_crosswords'){

		$defaults['cw_current_pos'] = 'Position';

	}



    return $defaults;

}

add_filter('manage_posts_columns' , 'cw_add_order_column');



/* Display custom post order in the post list */



function cw_order_value( $column, $post_id ){

  $postObj = get_post($post_id);

  if($postObj->post_type=='cw_crosswords'){

	if ($column == 'cw_current_pos' ){

		$cw_current_pos = get_post_meta( $post_id, 'cw_current_pos', true);

		if($cw_current_pos==null){

			$cw_current_pos=1;

		}

		echo '<p>' . $cw_current_pos . '</p>';

	}

  }

}

add_action( 'manage_posts_custom_column' , 'cw_order_value' , 10 , 2 );







function cw_sort_by( $query ) {



if( $query->is_main_query() && (is_post_type_archive( 'cw_crosswords' ) || is_tax( 'group' ))) {

		$query->set( 'orderby', 'meta_value_num' );

		$query->set( 'meta_key', 'cw_current_pos' );

		$query->set( 'order' , 'ASC' );

	}

}

add_action( 'pre_get_posts', 'cw_sort_by' );







//import books

function cw_import_book_meta_box(){



	 wp_nonce_field(plugin_basename(__FILE__), 'wp_custom_attachment_nonce');

     

    $html = '<p class="description">';

    $html .= 'Upload your XML here.';

    $html .= '</p>';

    $html .= '<input type="file" id="cw_custom_attachment" name="cw_custom_attachment"/>';

    $html .= '<input type="hidden" id="cw_xml_data" name="cw_xml_data" />';

    $html .= '<span class="text-danger" id="cw_file_msg"></span>';

     

    echo $html;

}

//display the ui of the user defined metabox

function display_crossword_meta_boxes($data){ ?>



<textarea name="cw_desc" id="cw_desc" style="width:100%;" rows="8"><?php echo get_post_meta(get_the_ID(),'cw_desc',TRUE);?></textarea>



<?php

	$compress_chapters = array();

	$get_chapters = array();

	$chapters = array();

    $args = array(

		"post_type"			=> "cw_chapters",

		"posts_per_page"		=>-1,

		"post_status"		=> "publish",

		"ordre"				=> "ID",

		"orderby"			=> "ASC",

		"post_parent"		=>get_the_ID()

	);

	$get_chapters = get_posts($args);

	if(count($get_chapters)>0){

		foreach($get_chapters as $k=>$chapter){

			$chapter_detail = unserialize( get_post_meta($chapter->ID,'chapterdetail',true) );		

			//$compress_chapters[$chapter->ID] = $chapter_detail;

			$chapters[$chapter->ID] = $chapter_detail;

		}

	}

	

	    $xml_chapters = unserialize(get_post_meta(get_the_ID(),'chapters',TRUE));

	    if($xml_chapters!=null){

	        foreach($xml_chapters as $k=>$data){

	            $chapters[$k] = $data;

	        }

	    }

	

	wp_enqueue_style('cw-style-css',plugins_url('assets/css/style.css',__FILE__));

	wp_enqueue_script('cw-script-js',plugins_url('assets/js/script.js',__FILE__));

	wp_localize_script('cw-script-js','adminlocaljs',array('chapters'	=> $chapters,							

												'current_post_id'=>get_the_id(),

												'ajaxUrl'=>admin_url('admin-ajax.php'),

												));

}



function get_load_chapter_assets(){

    

    $compress_chapters = array();

	$get_chapters = array();

	$chapters = array();

    $args = array(

		"post_type"			=> "cw_chapters",

		"posts_per_page"		=>-1,

		"post_status"		=> "publish",

		"ordre"				=> "ID",

		"orderby"			=> "ASC",

		"post_parent"		=>get_the_ID()

	);

	$get_chapters = get_posts($args);

	if(count($get_chapters)>0){

		foreach($get_chapters as $k=>$chapter){

			$chapter_detail = unserialize( get_post_meta($chapter->ID,'chapterdetail',true) );		

			//$compress_chapters[$chapter->ID] = $chapter_detail;

			$chapters[$chapter->ID] = $chapter_detail;

		}

	}

	

	    $xml_chapters = unserialize(get_post_meta(get_the_ID(),'chapters',TRUE));

	    if($xml_chapters!=null){

	        foreach($xml_chapters as $k=>$data){

	            $chapters[$k] = $data;

	        }

	    }

	

	wp_enqueue_style('cw-style-css',plugins_url('assets/css/style.css',__FILE__));

	wp_enqueue_script('cw-script-js',plugins_url('assets/js/script.js',__FILE__));

	wp_localize_script('cw-script-js','adminlocaljs',array('chapters'	=> $chapters,							

												'current_post_id'=>get_the_id(),

												'ajaxUrl'=>admin_url('admin-ajax.php'),

												));

    

}

//create or hide the metaboxes

add_action('admin_init','cw_add_meta_boxes');

function cw_add_meta_boxes(){

    global $post;

    //get_load_chapter_assets();

    $chapters = array();

	wp_enqueue_style('cw-boostrap','https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css');

	wp_enqueue_style('cw-fontawsome','https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css');

	//wp_enqueue_script('cw-jQuery','https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js');

	wp_enqueue_script('cw-boostrap','https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js');

	

	

	

	//add_meta_box('cw_crossword_meta_box','Description','display_crossword_meta_boxes','cw_crosswords','normal','high');

	add_meta_box('cw_chapters_meta_box','Chapters','cw_display_chapters_meta_box','cw_crosswords','normal','high');

	

	add_meta_box('cw_import_meta_box','Import Book','cw_import_book_meta_box','cw_crosswords','side','low');

	add_meta_box('cw_doc_upload_meta_box','Introduction','cw_upload_doc_meta_box','cw_crosswords','side','low');

	

	add_meta_box('cw_sort_meta_box','Crossword Position','cw_sort_box','cw_crosswords','side','high');

	

	add_meta_box('cw_model_type_meta_box','Game Model','cw_model_type_box','cw_crosswords','side','low');

	/*

	add_meta_box('cw_free_meta_box','Free Game','cw_free_box','cw_crosswords','side','low');

	*/

	/*

	add_meta_box('cw_compition_meta_box','Competition','cw_compition_box','cw_crosswords','side','low');

	*/

	add_meta_box('cw_color_meta_box','Theme','cw_color_box','cw_crosswords','side','low');

	/*

	add_meta_box('cw_logo_type_meta_box','LOGOS','cw_logo_type_box','cw_crosswords','side','low');

	*/

	/*

    add_meta_box('cw_clue_type_meta_box','Clues','cw_clue_type_box','cw_crosswords','side','low');

	*/

	add_meta_box('cw_clues_visible_meta_box','Clues','cw_clues_visible_box','cw_crosswords','side','low');

	/*

	add_meta_box('cw_bulk_clue_mail_meta_box','Bulk Clue Mail','cw_bulk_clue_mail_box','cw_crosswords','side','low');

	*/

	add_meta_box('cw_crossword_access_meta_box','Access','cw_crossword_access_box','cw_crosswords','side','low');

	

	add_meta_box('cw_app_home_chapter_meta_box','Show At App','cw_app_home_chapter_box','cw_chapters','side','low');



}



function chapter_grid_column($defaults) {

	global $post;

	if($post->post_type=='cw_chapters'){

		$defaults['crossword_chapter_parent_column'] = 'Crossword Book';

	}

    return $defaults;

}

add_filter('manage_posts_columns', 'chapter_grid_column');



function chapter_grid_column_content($column_name, $post_ID){

	$post = get_post($post_ID);

	if($post->post_type=='cw_chapters'){

		if($column_name=='crossword_chapter_parent_column'){			

			echo $title = get_the_title($post->post_parent);

		}

	}

}

add_action('manage_posts_custom_column', 'chapter_grid_column_content', 10, 2);



//texonomy start

// hook into the init action and call create_book_taxonomies when it fires

add_action( 'init', 'create_crossword_taxonomies', 0 );



//add enctype="multipart/form-data" to upload a file

add_action('post_edit_form_tag',function(){

	echo ' enctype="multipart/form-data"';

});



// create taxonomiy group and writers for the post type "cw_crosswords"

function create_crossword_taxonomies() {

	// Add new taxonomy, make it hierarchical (like categories)

	$labels = array(

		'name'              => 'Groups',

		'singular_name'     => 'Group' ,

		'search_items'      => __( 'Search Genres', 'textdomain' ),

		'all_items'         => __( 'All Group', 'textdomain' ),

		'parent_item'       => __( 'Parent Group', 'textdomain' ),

		'parent_item_colon' => __( 'Parent Group:', 'textdomain' ),

		'edit_item'         => __( 'Edit Group', 'textdomain' ),

		'update_item'       => __( 'Update Group', 'textdomain' ),

		'add_new_item'      => __( 'Add New Group', 'textdomain' ),

		'new_item_name'     => __( 'New Group Name', 'textdomain' ),

		'menu_name'         => __( 'Group', 'textdomain' ),

	);



	$args = array(

		'hierarchical'      => true,

		'labels'            => $labels,

		'show_ui'           => true,

		'show_admin_column' => true,

		'query_var'         => true,

		'rewrite'           => array( 'slug' => 'group' ),

	);

	register_taxonomy( 'group', array( 'cw_crosswords' ), $args );

}





// Add the fields to the "presenters" taxonomy, using our callback function  

add_action( 'group_edit_form_fields', 'group_taxonomy_image_upload', 10, 2 ); 



// A callback function to add a custom field to our "presenters" taxonomy  

function group_taxonomy_image_upload($tag) {  



   // Check for existing taxonomy meta for the term you're editing  

    $t_id = $tag->term_id; // Get the ID of the term you're editing  

    $term_meta = get_option( "taxonomy_term_$t_id" ); // Do the check  

?>  



<script>

(function($) {

	$(document).ready(function(){

		$('#edittag').attr('enctype','multipart/form-data');

		$('input[name=group_image]').change(function(){

			if(this.files && this.files[0]){

				var reader = new FileReader();

				reader.onload = function(e){

					$('#cw_group_image').children('img').attr('src',e.target.result);

				}

				reader.readAsDataURL(this.files[0]);

			}else{

				$('#cw_group_image').children('img').attr('src','<?php echo get_term_meta($t_id,"group_image",true);?>');

			}

		});

	});

})(jQuery);

</script>

  

<tr class="form-field">  

    <th scope="row" valign="top">  

        <label for="group-image">Group Image</label>  

    </th>  

    <td id="cw_group_image">  

    	<img src="<?if(!empty(get_term_meta($t_id,'group_image',TRUE))){echo get_term_meta($t_id,'group_image',TRUE);}?>" height="100"/><br />

        <input type="file" name="group_image"/>

    </td>  

</tr>  

  

<?php  

} 







 

// Save the changes made on the "group" taxonomy, using our callback function  

add_action( 'edited_group', 'save_taxonomy_image_upload', 10, 2 ); 



// A callback function to save our extra taxonomy field(s)  

function save_taxonomy_image_upload( $term_id ) {	

	if(!empty($_FILES['group_image']['name'])){		

		//inlcude the file to handle the upload

		$allowed_types = array(

								'image/jpeg',

								'image/png',

								'image/gif',

								'image/bmp'

								);

		if(!in_array($_FILES['group_image']['type'] , $allowed_types)){

			echo 'File not supproted. Allowed types are jpg/png/gif/bmp';exit;

		}

		

		

		if ( ! function_exists( 'wp_handle_upload' ) ) {

		    require_once( ABSPATH . 'wp-admin/isncludes/file.php' );

		}

	

		$uploadedfile = $_FILES['group_image'];



		$movefile = wp_handle_upload($uploadedfile,array('test_form' => FALSE));



		if ( $movefile && ! isset( $movefile['error'] ) ) {

			update_term_meta($term_id,'group_image',$movefile['url']);

		} else {

		    

		    echo $movefile['error'];exit;

		}

	}

}

add_action( 'wp_ajax_get_page_content', 'get_page_content' );

function get_page_content(){

	$page_slog = $_POST['slog'];

    $page_id =	url_to_postid( site_url($page_slog) );

    $page_data = get_page( $page_id ); 

    echo  apply_filters('the_content', $page_data->post_content);

   // echo get_post_field('post_content', $page_id);

	wp_die();	

}



add_action( 'wp_ajax_save_cw_game', 'save_cw_game' );

function save_cw_game(){
	global $wpdb;
	if(isset($_POST['crosswordData'])){
		$post_id = $_POST['post_id'];
		$user_id = $_POST['user_id'];
		$tScore = $_POST['tScore'];
		$tTimer = $_POST['tTimer'];

		$userCrosswordData = json_decode(stripslashes($_POST['crosswordData']),TRUE);
		$userCrosswordData = $_POST['crosswordData'];
		$userCrosswordClues = $userCrosswordData['clues'];

		$userCrosswordClues = array_filter($userCrosswordClues,'filter_null');
		$userCrosswordData['clues'] = $userCrosswordClues;

		$chId = $_POST['chId'];
		$savedCrosswords = unserialize(get_user_meta($user_id,'savedCrosswords',TRUE));
		$savedCrosswords[$post_id][$chId] = $userCrosswordData;

		/*
		$timerandscore = unserialize(get_user_meta($user_id,'timerandscore',TRUE));
		if(!is_array($timerandscore)){
			$timerandscore = array();
		}
		$timerandscore[$post_id][$chId] = array('score'=>$tScore,'timer'=>$tTimer);
		update_user_meta($user_id,'timerandscore',serialize($timerandscore));
		$cwScore = $timerandscore[$post_id][$chId]['score'] ;
		*/
		$leaderboardid = $user_id.'-'.$post_id.'-'.$chId;
		$sql = "SELECT * FROM wp_leader_board WHERE `id` = '".$leaderboardid."'";
		$savecrossword = serialize($savedCrosswords);
		$results = $wpdb->query($sql);
		if(!$results){
			$sql = "INSERT INTO `wp_leader_board` (`id`, `score`, `timer`,`crossword`) VALUES ('".$leaderboardid."', '".$tScore."', '".$tTimer."','".$savecrossword."')";
			$result = $wpdb->query($sql);
		}else{
			$sql = "UPDATE wp_leader_board SET `score` = '".$tScore."', `timer` = '".$tTimer."', `crossword` = '".$savecrossword."' WHERE `wp_leader_board`.`id` = '".$leaderboardid."'";
			$result = $wpdb->query($sql);
		}
		//delete_user_meta($user_id,'savedCrosswords');
		//update_user_meta($user_id,'savedCrosswords',serialize($savedCrosswords));
		
		//update_user_meta($user_id,'cwScore_'.$post_id,$_POST['currentScore']);
		/*
		if(isset($_POST['tornament']) && $_POST['tornament']==true){
			$tornament = true;
			$tScore = $_POST['tScode'];
			$tTitle = $_POST['tTitle'];
			$tTimer = $_POST['tTimer'];		
			$pass = $_POST['pass'];
		}else{
			$tornament = false;
			$tScore = null;
			$tTimer = null;
			$tTitle = null;
			$pass = false;
		}
		*/
		/*
		update_user_meta($user_id,'tornament',$tornament);

		update_user_meta($user_id,'tScore',$tScore);

		update_user_meta($user_id,'tTitle',$tTitle);

		update_user_meta($user_id,'tTimer',$tTimer);

		update_user_meta($user_id,'pass',$pass);

		*/		
		//$cw_intro_file = get_post_meta( $post_id, 'cw_intro_file' );
		/*
		$cw_saved_crosswords = $savedCrosswords;
		if(!empty($cw_saved_crosswords[$post_id])){
			$cw_current_chapter_saved_data = $cw_saved_crosswords[$post_id];
		}else{
			$cw_current_chapter_saved_data = NULL;
		}
		//$cwScore = get_user_meta($user_id,'cwScore'.$post_id,TRUE);
		$cw_chapters = unserialize(get_post_meta($post_id,'chapters',TRUE));
		*/
		/*
		if($pass){
			//email
			$mail_status = 'false';
			$userDetail = get_user_by('id', $user_id);
			$to = $userDetail->user_email;
			$subject = 'Congratulations !'.$userDetail->display_name;
			$body = '
				<h2>Hi '.$userDetail->display_name.',</h2>
				<div style="text-align:center;">
				<img src="https://www.crosswordsakenhead.com/wp-content/plugins/dacrosswords/templates/images/The-Times-Logo.png"  style="width:350px;margin:0 auto;">
				</div>
				<h2>'.$tTitle.'</h2>
				<p style="font-size:24px;">Your time was '.$tTimer.'<br>We will notify you if this was the winning time.<br/>Thank you for playing!</p>
			';
			$headers = array('Content-Type: text/html; charset=UTF-8','From: CROSSWORD AKENHEAD &lt;<david@crosswordsakenhead.com>');
			$mail_status = wp_mail( $to, $subject, $body, $headers );
		}else{
			$mail_status='null';
		}
		*/

		echo json_encode(array(
			'savedGame' => $cw_current_chapter_saved_data,
			'tScore'=>$tScore,
			'tTimer'=>$tTimer,
			'result'=>$result
		));
		exit();
	}

}

add_action( 'wp_ajax_update_chapter', 'update_chapter' );

function update_chapter(){

	$chapter = $_POST['chapter'];

	$post_id = $_POST['post_id'];

	

	if($chapter['chapter_index']==0){

		$args = array(

			'post_type' 		=> 'cw_chapters',

			'post_title'		=> $chapter['chapterName'],

			'post_status'		=> 'publish',

			'post_parent'		=> $post_id,

		);

		

		$chapter_id = wp_insert_post($args);

		if($chapter_id>0){

		    $chapter_detail = array(

    			'chapterName'=>$chapter['chapterName'],

    			'rows'=>$chapter['rows'],

    			'cols'=>$chapter['cols'],

    			'no_of_clues'=>$chapter['no_of_clues'],

    			'chapter_index'=>$chapter_id,

    			'chapterAuthorName'=>$chapter['chapterAuthorName']

    		);

		    

			$status = add_post_meta ($chapter_id,'chapterdetail',serialize( $chapter_detail));

		}

		

	}else{

	    $chapter_detail = array(

							'chapterName'=>$chapter['chapterName'],

							'rows'=>$chapter['rows'],

							'cols'=>$chapter['cols'],

							'no_of_clues'=>$chapter['no_of_clues'],

							'chapter_index'=>$chapter['chapter_index'],

							'chapterAuthorName'=>$chapter['chapterAuthorName']

						);

	    $status = update_post_meta( $chapter['chapter_index'],'chapterdetail',serialize( $chapter_detail) );

	}

	$clues =  get_post_meta($chapter_id,'clues',true);

	if(!$clues){

	    $clues = array();    

	}

	$chapter_detail['clues'] =  $clues;

	

	/*

	update_post_meta($post_id,'chapterdetail'.$chapter['chapter_index'],serialize( $chapter_detail));

	$chapter_detail = unserialize( get_post_meta($post_id,'chapterdetail'.$chapter['chapter_index'],true));

	*/
	header( "Content-Type: application/json" );
	echo json_encode(array(

		'data'	=> $chapter_detail,

		'status' =>$status,

		));

	

	exit();

	

}

add_action( 'wp_ajax_add_chapter', 'add_chapter' );

function add_chapter(){

	$chapter = $_POST['chapter'];

	$post_id = $_POST['post_id'];

	$chapter_detail = array(

							'chapterName'=>$chapter['chapterName'],

							'rows'=>$chapter['rows'],

							'cols'=>$chapter['cols'],

							'no_of_clues'=>$chapter['no_of_clues'],

							'chapter_index'=>$chapter['chapter_index'],

							'chapterAuthorName'=>$chapter['chapterAuthorName']

						);

	

	

	$clue = $chapter['clues'][0];

	$clues = $chapter['clues'];

	$cw_no_chapters = $chapter['chapter_index']+1;

	

	

	update_post_meta($post_id,'chapterdetail'.$chapter['chapter_index'],serialize( $chapter_detail));





	//create new chapter post type

	$args = array(

		'post_type' 		=> 'cw_chapters',

		'post_title'		=> $chapter_detail['chapterName'],

		'post_status'		=> 'publish',

		'post_parent'		=> $post_id,

	);

	$chapter_id = wp_insert_post($args);

	unset($chapter_detail['chapterName']);	

	unset($chapter['chapter_index']);

	update_post_meta($chapter_id,'chapterdetail',serialize( $chapter_detail));

	$chapter['chapter_id'] = $chapter_id;



	

	update_post_meta($post_id,'cw_no_chapters',$cw_no_chapters);

	if(count($clues)>0){

		//update_post_meta($post_id,'chapter'.$chapter['chapter_index'].'clue0',$clue);

		update_post_meta($chapter['chapter_id'],'clues',serialize($clues));

		$status=true;

	}else{

		$status=false;

	}

	

	header( "Content-Type: application/json" );

	

	echo json_encode(array(

		'data'	=> $chapter,

		));

	

	exit();

}

add_action( 'wp_ajax_convert_cw_chapter', 'convert_cw_chapter' );

function convert_cw_chapter(){

	$chapterName = $_POST['chapterName'];

	$post_id = $_POST['post_id'];

	$chapter_index = $_POST['chapter_index'];

	$rows = $_POST['rows'];

	$cols = $_POST['cols'];

	$no_of_clues = $_POST['no_of_clues'];

	

	

	$chapter_detail = array(

							'chapterName'=>$chapterName,

							'rows'=>$rows,

							'cols'=>$cols,

							'no_of_clues'=>$no_of_clues,

							'chapter_index'=>$chapter_index

						);

	

	update_post_meta($post_id,'chapterdetail'.$chapter_index,serialize( $chapter_detail));

	header( "Content-Type: application/json" );

	

	echo json_encode(array(

			'data'	=> $chapter_detail

		));

	

	exit();

}

add_action( 'wp_ajax_add_clue_chapter', 'add_clue_chapter' );

function add_clue_chapter(){

	

	if(!isset($_POST['clues'])){

		

		$clue = $_POST['clue'];

		$post_id = $_POST['post_id'];

		$chapter_index = $_POST['chapter_index'];

		//$cw_no_chapters = $_POST['cw_no_chapters'];

		//$clue_index = $_POST['clue_index'];



        /*

		$clues = get_post_meta($chapter_index,'clues',true);

		if(count($clues)>0){

			if(isset($clues[$clue_index])){

				$clues[$clue_index] = $clue;

			}else{

				$clues[] = $clue;

			}

		}else{

			$clues[] = $clue;

		}

		*/

		

		

		//$status = update_post_meta($chapter_id,'clues', $clue );

		

		$status = update_post_meta( $chapter_index, 'clues', $clue );

		

		$clues = $clue;

	}else{

	    

		$clues = $_POST['clues'];

		$post_id = $_POST['post_id'];

		$chapter_detail['rows'] = $_POST['rows'];

		$chapter_detail['cols'] = $_POST['cols'];

		$status = true;

		

		$args = array(

			'post_type' 		=> 'cw_chapters',

			'post_title'		=> $_POST['chapter_name'],

			'post_status'		=> 'publish',

			'post_parent'		=> $post_id,

		);

		$chapter_id = wp_insert_post($args);

		if($chapter_id>0){

			add_post_meta ($chapter_id,'clues',$clues);

			add_post_meta ($chapter_id,'chapterdetail',serialize( $chapter_detail));

			$clues =  get_post_meta($chapter_id,'clues',true);

			//$clues = unserialize($clues);



			$status = true;

		}else{

			$status = false;

		}

	}

	wp_send_json_success(array(

		'data'=> $clues,

		'status'=>$status,

	));

}

add_action( 'wp_ajax_get_cw_chapter_clues', 'get_cw_chapter_clues' );

function get_cw_chapter_clues(){

	

	$post_id = $_POST['post_id'];

	$chapter_index = $_POST['chapter_index'];

    $args = array(

        "ID"                =>$chapter_index,

		"post_type"			=> "cw_chapters",

		"post_parent"		=> $post_id,

	);

	$get_chapters = get_posts($args);

    

    	

	if(count($get_chapters)<0){

	    $chapter_detail = unserialize(get_post_meta($post_id,'chapterdetail'.$chapter_index,true));

		$no_of_clues = $chapter_detail['no_of_clues'];

		$clues =array();

		for($i=0;$i<$no_of_clues;$i++){

			$clue = get_post_meta($post_id,'chapter'.$chapter_index.'clue'.$i);

			$temp_clue = (array) $clue[0];

			$temp_clue['clueindex'] = $i;

			$temp_clue = (object) $temp_clue;

			

			

			if($clue!=null){

				$clues[] = $temp_clue;

			}

		}

		$chapter['clues'] = $clues;

		$chapter = array_merge($chapter,$chapter_detail);		

	}else{

		

		$chapter_detail = unserialize( get_post_meta($chapter_index,'chapterdetail',true));

		$chapter_detail['chapterName'] = $get_chapters[0]->post_title;

		$no_of_clues = $chapter_detail['no_of_clues'];

		$clues =array();

		$clues =  get_post_meta($chapter_index,'clues',true);

		if(!$clues){

		    $clues =array();

		}

		$chapter['clues'] = $clues;

		$chapter = array_merge($chapter,$chapter_detail);

	}

		

	header( "Content-Type: application/json" );

	

	echo json_encode(array(

			'data'=> $chapter,

		));

	exit();

}

add_action( 'wp_ajax_delete_clue_chapter', 'delete_clue_chapter' );



function delete_clue_chapter(){

    

	$chapter_index = $_POST['chapter_index'];

	$clue_index = $_POST['clue_index'];

	

	$clues =  get_post_meta($chapter_index,'clues',true);

	

	unset($clues[$clue_index]);

	$clues = array_values($clues);

	update_post_meta($chapter_index,'clues', $clues );



	$chapter_detail = unserialize( get_post_meta($chapter_index,'chapterdetail',true));

	$chapter_detail['no_of_clues'] = $chapter_detail['no_of_clues']-1;

	update_post_meta($chapter_index,'chapterdetail'.$chapter_index,serialize( $chapter_detail));

	

	$chapter['clues'] = get_post_meta($chapter_index,'clues',true);;

	$chapter = array_merge($chapter,$chapter_detail);

	

	header( "Content-Type: application/json" );

	echo json_encode(array(

			'data'=> $chapter,

		));

	exit();

}

add_action( 'wp_ajax_delete_chapter', 'delete_chapter' );

function delete_chapter(){

	$post_id = $_POST['post_id'];

	$chapter_index = $_POST['chapter_index'];

	$cw_no_chapters = $_POST['cw_no_chapters'];



	wp_delete_post($chapter_index,true);



	/*

	$chapter_detail = unserialize( get_post_meta($post_id,'chapterdetail'.$chapter_index,true));

	$no_of_clues = $chapter_detail['no_of_clues'];

	$clues =array();

	for($i=0;$i<$no_of_clues;$i++){

		delete_post_meta($post_id,'chapter'.$chapter_index.'clue'.$i);

	}

	

	delete_post_meta($post_id,'chapterdetail'.$chapter_index);

	

	update_post_meta($post_id,'cw_no_chapters',$cw_no_chapters);

	*/

	

	header( "Content-Type: application/json" );

	echo json_encode(array(

			'data'=> $_POST,

		));

	exit();

}

/*

add_action('admin_menu', 'cw_add_submenu');

function cw_add_submenu(){

	add_submenu_page(

        'edit.php?post_type=cw_crosswords',

        'Options',

        'Options',

        'manage_options',

        'cw-options',

        'cw_options_callback' );

}



function cw_switch_free_game(){

	$cat = $_POST['catlist'];

	$cwfreebox = $_POST['cwfreebox'];

	if($cwfreebox=='yes'){$metaval='no';}else{$metaval='yes';}

	$postids = array();

	$args = array(

		'post_type'			=>'cw_crosswords',			

		'posts_per_page'	=>30,

		'pages'				=>1,

		"post_status"		=>"publish",

		'tax_query'			=>array(

			array(

			'taxonomy'	=>'group',

			'field'		=>'term_id',

			'terms'		=>$cat,

			),

		),

		'meta_query'	=>array(

			array(

				'key'	=>'cw_free_box',

				'value'	=>$metaval,

			),

		),

		

	);

		$postlist = get_posts($args);

		$postids = array();

		foreach($postlist as $post){

			update_post_meta($post->ID,'cw_free_box',sanitize_text_field( $cwfreebox ));

			$postids[]=$post->ID;

		}

	wp_send_json_success(array($postids,$args));

}

add_action( 'wp_ajax_cw_switch_free_game', 'cw_switch_free_game' );

*/

function cw_options_callback(){

	?>

	<style>

	.lds-ellipsis {

    display: none;

    position: relative;

    width: 50px;

    top: -9px;

}

.lds-ellipsis div {

    position: absolute;

    width: 11px;

    height: 11px;

    border-radius: 50%;

    background: #ffffff;

    animation-timing-function: cubic-bezier(0, 1, 1, 0);

}

.lds-ellipsis div:nth-child(1) {

    left: 6px;

    animation: lds-ellipsis1 0.6s infinite;

}

.lds-ellipsis div:nth-child(2) {

    left: 6px;

    animation: lds-ellipsis2 0.6s infinite;

}

.lds-ellipsis div:nth-child(3) {

    left: 26px;

    animation: lds-ellipsis2 0.6s infinite;

}

.lds-ellipsis div:nth-child(4) {

    left: 45px;

    animation: lds-ellipsis3 0.6s infinite;

}

.new-collection .lds-ellipsis div {

    background: #70AFAD !important;

}

</style>

		<table class="form-table">



			<tbody>

			<tr>

				<th scope="row"><label for="category-name">Categories</label></th>

				<td>

					<?php

						$cats = get_categories(array(

							'taxonomy' => 'group',

							'orderby' => 'name',

							'order'   => 'ASC',

							'hide_empty' => 0,

						));

						foreach($cats as $cat){

							?>

								<label><input type="checkbox" value="<?php echo $cat->term_id;?>" name="cat_check" class="cat-list">

								<?php echo $cat->name;?>

								</label>

							<?php

						}

					?>

				</td>

			</tr>

			<!--

			<tr>

				<th scope="row"><label for="make-subscribe">Free Game</label></th>

				<td>

				<p>Select optio to make game free</p>

				<p>

					<select name="cw_free_box">

					<option value="no">No</option>

					<option value="yes">Yes</option>

					</select>

				</p>

				<p><button type="button" name="switch_free_game">Switch <div class="lds-ellipsis"><div></div><div></div><div></div><div></div></div></button></p>

				</td>

			</tr>

			-->

			</tbody>

		</table>

		<script>

			$('document').ready(function(){

				$('body').on('click','button[name=switch_free_game]',function(){

					Elem = $(this)

					var catlist = '';

				    $('input.cat-list').each(function(){

				        if($(this).prop('checked')){

				            catlist = $(this).attr('value')

				        }

					})

					cwfreebox = $('select[name=cw_free_box]').val()

					postData = {action:'cw_switch_free_game','catlist':catlist,'cwfreebox':cwfreebox};

					console.log(postData)

					$.ajax({

						url: '<?php echo admin_url('admin-ajax.php');?>',

						type:'post',

						data:postData,

						beforeSend:function(){

							Elem.find('.lds-ellipsis').show();

						},

						success:function(responce){

							Elem.find('.lds-ellipsis').hide();

							console.log(responce)

						},

						error:function(error){

							console.log(error);

						}

					})

				})

			})

		</script>

	<?php

}



function bulk_clue_mail(){

	if(isset($_POST['sender_email'])){

		//extract($_POST);

		//$admin_email = get_option( 'admin_email' );

		$sender_name = $_POST['sender_name'];

		$sender_country = $_POST['sender_country'];

		$sender_email = $_POST['sender_email'];

		$clue_no = $_POST['clue_no'];

		$clue_text = $_POST['clue_text'];

		$to = 'davidakenhead8@gmail.com';

		$subject = 'bulk clue list from '.$sender_name;

		$body = '

		<table>

			<tbody>

				<tr>

					<td><label>Name</label></td>

					<td><label>'.$sender_name.'</label></td>

					<td><label>Country</label></td>

					<td><label>'.$sender_country.'</label></td>

					<td><label>Email</label></td>

					<td><label>'.$sender_email.'</label></td>

				</tr>';

		$body .='

			<tr>

				<td colspan="2"><label>Clue No</label></td>

				<td colspan="4"><label>Entered Clue</label></td>

			</tr>

			';

			foreach($clue_no as $k=>$val){

				$body .='

				<tr>

					<td colspan="2"><label>'.$val.'</label></td>

					<td colspan="4"><label>'.$clue_text[$k].'</label></td>

				</tr>

				';

			}

		

		$body .='

		</tbody>

		</table>';

		$headers = array('Content-Type: text/html; charset=UTF-8','Reply-To: '.$sender_name.' <'.$sender_email.'>');

		

		$responce = wp_mail( $to, $subject, $body, $headers );

		wp_send_json_success( array( 'success' =>true ,'message'=>'Email Sent','mail'=>$responce,'email'=>$admin_email,'demo'=>$_POST) );

		die();

	}else{

	?>

	<form method="post" id="bulk-clue-mail">

		<div class="row">

			<div class="col-md-4">

				<div class="form-group">

					<label>Name:</label>

					<input type="text" class="form-control" id="sender_name" name="sender_name" />

				</div>

			</div>

			<div class="col-md-4">

				<div class="form-group">

					<label>Country:</label>

					<input type="text" class="form-control" id="sender_country" name="sender_country" />

				</div>

			</div>

			<div class="col-md-4">

				<div class="form-group">

					<label>Your Email:</label>

					<input type="email" class="form-control" id="sender_email" name="sender_email" />

				</div>

			</div>

		</div>

		<div class="clue-list">

					

		</div>

		<div class="row">

			<div class="col-md-4">

				<input type="hidden" name="action" value="bulk_clue_mail" />

				<input type="submit" name="send_bulk_clue_mail" value="Send Mail" />
			</div>
			<div class="col-md-12"><div class="message-box"></div></div>		
		</div>
	</form>
	<?php
	}
}

add_action( 'wp_ajax_bulk_clue_mail', 'bulk_clue_mail' );
add_shortcode('bulk-clue-mail','bulk_clue_mail');
add_shortcode('leader-board','get_top_scored');

function get_top_scored(){
	global $wpdb;
	//$sql = "SELECT id,timer,MAX(score) AS top_score FROM wp_leader_board" ;
	$sql = "SELECT * FROM wp_leader_board ORDER BY `score` DESC LIMIT 10";
	//$sql = "INSERT INTO `wp_leader_board` (`id`, `score`, `timer`) VALUES ('1545-5214-65123', '123', '1234')";
	$results = $wpdb->get_results($sql);
	
	//var_dump($results);
	?>
		<table width="100%">
			<tr><th>Player</th><th>Score</th><th>Timer</th></tr>
			<?php
				foreach($results as $leader){
					?>
						<tr>
							<?php
							$leaderid = explode("-",$leader->id);
							$user = get_user_by('id',$leaderid[0]);
							if($user){
								?>
									<td><?php echo $user->user_login;?></td>
									<td><?php echo $leader->score;?> Points</td>
									<td><?php echo $leader->timer;?> Min</td>	
								<?php
							}
							?>
						</tr>
					<?php
				}
			?>
		</table>
	<?php
}


function wpcw_post_type_archive($query) {

    if (!is_admin() && $query->is_archive('cw_crosswords') && $query->is_main_query()) {

            $query->set('post_type', 'cw_crosswords');

            $query->set('posts_per_page', 12);

   }

    return $query;

}

add_action( 'pre_get_posts', 'wpcw_post_type_archive' ); 





/*paid membership pro extended functional*/

function cw_extended_pmpro_save($post_id,$level){

    global $wpdb;

    

    $getchapterids = get_posts(

        array (

            'fields' => 'ids',

            "post_type"			=> "cw_chapters",

    		"post_parent"		=> $post_id,

    		"posts_per_page"	=> -1,

    		'orderby'			=>'ID',

    		'order'				=>'ASC',

        )

    );

	

	if(is_array($getchapterids))

	{

		foreach($getchapterids as $chapter_id){

		    //remove all memberships for this page

	        $wpdb->query("DELETE FROM {$wpdb->pmpro_memberships_pages} WHERE page_id = '$chapter_id'");

	        //add new memberships for this page

	        $wpdb->query("INSERT INTO {$wpdb->pmpro_memberships_pages} (membership_id, page_id) VALUES('" . intval($level) . "', '" . intval($chapter_id) . "')");

		}

			

	}

		

	

}

?>