<?php
add_shortcode('cw-group-box',function($attr=[]){
    
    $groupFilter = array(
						'taxonomy'=>'group',
						'parent'=>0
					);
	
	/*
	if(count($attr)>0){
		$attr = array_change_key_case($attr);
	}
	*/
	
	
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
	
	$groupFilter['hide_empty']=FALSE;
	$groups = get_terms($groupFilter);
	
	$html ='<div class="row justify-content-center">';
	foreach($groups as $k=>$group){
	    $subgroups = get_terms(array(
                'taxonomy'=>'group',
            	'hide_empty'=>FALSE,
            	'parent'=>$group->term_id,
            ));
	    $html .='<div class="col-6 col-sm-6 col-md-3 col-lg-2 parent-group-block">';
	        $html .='<div class="group-block">';
	            $html .='<div class="top-block"><a href="'.get_term_link($group->term_id).'">';
    	            $html .='<div class="img-block" style="background-image:url('.get_term_meta($group->term_id,'group_image',TRUE).');"></div>';
    	            $html .='<div class="group-title">'.$group->name.'</div>';
	            $html .='</a></div>';
	            
	            $html .='<div class="bottom-block">';
	            $html .='<div class="group-desc">'.$group->description.'</div>';
	            if(count($subgroups)>0){
	                $html .='<a href="#" class="btn play-list-btn show-sub-group"> See More... </a>';
	            }else{
	                $html .='<a href="'.get_term_link($group->term_id).'" class="btn play-list-btn"> Play List </a>';
	            }
	            
	            $html .='</div>';
	        $html .='</div>';
            
            //sub group
            if(count($subgroups)>0){
                $html .='<div class="sub-group"><div class="row">';
                foreach($subgroups as $subk=>$subgroup){
                    $html .='<div class="col-sm-6 col-md-6 col-lg-6">';
            	        $html .='<div class="group-block">';
            	            $html .='<div class="top-block"><a href="'.get_term_link($subgroup->term_id).'">';
                	            $html .='<div class="img-block" style="background-image:url('.get_term_meta($subgroup->term_id,'group_image',TRUE).');"></div>';
                	            $html .='<div class="group-title">'.$subgroup->name.'</div>';
            	            $html .='</a></div>';
            	            
            	            $html .='<div class="bottom-block">';
            	            $html .='<div class="group-desc">'.$subgroup->description.'</div>';
            	            $html .='<a href="'.get_term_link($subgroup->term_id).'" class="btn play-list-btn"> Play List </a>';
            	            $html .='</div>';
            	        $html .='</div>';
            	    $html .='</div>';
                }
                $html .='</div></div>';
                
            }
            //end
	    $html .='</div>';
	}
	$html .='</div>';
	return $html;
	
});

add_shortcode('cw-promotion-list',function($attr=[]){
    
    $chapters = get_posts(array(
        "post_type"=>'cw_chapters',
        "meta_key" => 'cw_app_home_chapter',
        "meta_value"=>1,
        'orderby'			=>'ID',
		'order'				=>'DESC',
		'posts_per_page'    =>-1,
        ));
        $html ='<div class="row justify-content-center">';
        foreach($chapters as $k=>$chapterpost){
            
            $html .='<div class="col-6 col-sm-6 col-md-3 col-lg-2 cw-promotion-list">';
            
            $html .='<div class="chapter-list-box">
                
				<a class="img-holder" href="'.get_permalink( $chapterpost->post_parent ).'?cpid='.$chapterpost->ID.'">';
				
					$chapter_image = wp_get_attachment_url(get_post_thumbnail_id($chapterpost->ID));
					if(empty($chapter_image)){
						$chapter_image = site_url('/wp-content/uploads/2020/03/newcrosswordlogo-icon-1.png');
					}
					 $html .='<img src="'.$chapter_image.'" /> 
				</a>
				
				<a href="'.get_permalink( $chapterpost->post_parent ).'?cpid='.$chapterpost->ID.'" class="btn btn-small btn-default subscribe-btn">Play</a>		
			</div>';
	        $html .='</div>';
        }
        $html .='</div>';
        return $html;
});

        
?>