<?php
get_header();
?> 
<style>
/* archive page */
.cw-grid-box{
    border:1px solid #dcdcdc;
    border-radius:4px;
    /*padding-top:15px;*/
    padding-bottom:15px;
    margin-top:15px;
}
.cw-grid-box .publish-date {
    text-align: center;
}
.cw-grid-box .img-link{
    display:block;
    width: 95%;
    height: auto;
    margin: 0 auto;
}
.cw-grid-box .img-link .img{
    display:block;
    width:100%;
    height:100%;
}
.cw-grid-box .title {
    padding: 10px 0 0 0;
    min-height:62px;
    text-align:center;
}
.cw-grid-box .title a {
    font-size: 14px;
    /*font-weight: bold;*/
    display: inline-block;
    line-height: 1.1;
}
.cw-grid-box .action-btn .count-chapters {
    display: inline-block;
    /*float:left;*/
}
.cw-grid-box .action-btn .play-btn{
    display: inline-block;
    /*float:right;*/
}
.cw-grid-box .action-btn {
    text-align: center;
    padding: 0 20px;
}
.cw-grid-box .action-btn:after{
    display:block;
    clear:both;
    content:'';
}
.pagenation ul {
    padding: 0;
    margin: 0;
}
.pagenation ul li {
    list-style-type: none;
    display: inline-block;
}
.pagenation ul li a {
    padding: 0 10px;
}
.pagenation ul li span {
    background-color: #333;
    color: #fff;
    padding: 0 10px;
}
.text-right {
    text-align: right;
}
.pagenation {
    display: inline-block;
}
	.play-btn button.btn {
    padding: 5px 10px !important;
}
</style>
<?php
/*
if(is_user_logged_in() && !empty(get_query_var('group'))){
	if(!is_user_subscribed()){
		echo '<div style="width:100%;padding:5px;color:#444;" class="alert alert-warning"><i class="fa fa-exclamation-triangle"></i> You are not subscribed for this group.</div>';	
	}
}
*/
function my_paginate_links() {
    global $wp_rewrite, $wp_query;
	
    $wp_query->query_vars['paged'] > 1 ? $current = $wp_query->query_vars['paged'] : $current = 1;
	
	
  
  
    $pagination = array(
        'base' => @add_query_arg('page','%#%'),
        'format' => '',
        'total' => $wp_query->max_num_pages,
        'current' => $current,
        'prev_text' => __('« Previous'),
        'next_text' => __('Next »'),
        'end_size' => 1,
        'mid_size' => 2,
        'show_all' => true,
        'type' => 'list',
    );

    if ( $wp_rewrite->using_permalinks() )
            $pagination['base'] = user_trailingslashit( trailingslashit( remove_query_arg( 's', get_pagenum_link( 1 ) ) ) . 'page/%#%/', 'paged' );

    if ( !empty( $wp_query->query_vars['s'] ) )
            $pagination['add_args'] = array( 's' => get_query_var( 's' ) );

    return paginate_links( $pagination );
} 
?>
    <div class="container">
        <div class="row">
            <div class="col-md-2">
                <?
                    $cats = get_terms(
                        array(
                            'taxonomy'=>'group',
                            'parent'=>'0'
                            )
                        );
                    
                                            
                ?>
				<?php /* ?>
                <select id="cw-cats">
                    <option value="<?php echo site_url('crosswords');?>" <?php if(empty(get_query_var('group'))){echo 'selected';} ?> >All Crosswords</option>
                    <?php
                    
                    
                    foreach($cats as $cat){
                        if($cat->name!='Free Games'){
                        echo '<option value="'.get_term_link($cat->term_id).'"';
                        
                        if(get_query_var('group') == $cat->slug){
                            echo ' selected';
                        }
                        echo '>'.$cat->name.'</option>';
                        }
                    }
                    
                    ?>
                </select>
				<?php  */ ?>
            </div>
            <div class="col-md-10 text-right">
                <!--pagenation-->
                <?php $pagenation=my_paginate_links(); ?>
                <div class="pagenation">                    
                    <?php echo $pagenation;?>
                </div>
            </div>
	    </div>
    </div>
    <div class="container">
        <div class="row ad-container">
            
            <?php		
                while(have_posts()):
                the_post();
                    global $post;
                    $images = wp_get_attachment_url(get_post_thumbnail_id($post->ID),'medium');
                    $img_background = 'background-image: url('.$images.')';
                    if(!$images){
                        $img_background = 'background-color: #000;';
                    }		
                    $chapters = get_posts(array('post_type'=>'cw_chapters','post_parent'=>get_the_ID(),'posts_per_page'=>-1));
                    $chapters = count($chapters);
                    ?>
                    <div class="col-6 col-lg-2 col-md-4 col-sm-6 col-xs-12">
                        <div class="cw-grid-box">
								<?php $cw_crossword_access = get_post_meta( get_the_ID(), 'cw_crossword_access' ,true); ?>
                                <div class="publish-date"><?php echo date('d M Y',strtotime(get_the_date())); ?></div>
								<?php /* ?>
                                <a href="<?php echo esc_url(get_permalink()); ?>" class="img-link">
                                        <div class="img" style="<?php echo $img_background;?>;background-size: cover;"></div>
                                </a>
								<?php */ ?>
								<a href="#" class="img-link">
                                        <img class="img" src="<?php echo $images;?>" />
                                </a>
                                <div class="title">
                                    <a href="<?php echo esc_url(get_permalink()); ?>"><?php echo esc_html__(get_the_title()); ?></a>
                                </div>
                                <div class="action-btn">
                                    <div class="count-chapters"><label><span><?php echo $chapters;?></span> Crosswords</label></div>
                                    
                                    <?php if($cw_crossword_access=='archive-lock'){?>
                                        <div class="play-btn"><button type="button" class="btn btn-default" >ARCHIVE <i class="fa fa-lock"></i></button></div>
                                    <?php }else if($cw_crossword_access=='in-precess-lock'){?>
                                        <div class="play-btn"><button type="button" class="btn btn-default" >IN PROCESS </button></div>
                                    <?php }else{?>
                                        <div class="play-btn"><a href="<?php echo esc_url(get_permalink()); ?>" class="btn btn-default" >Enter</a></div>
                                    <?php }?>
                                    
                                </div>
                        </div>
                    </div>
                <?php		
                endwhile;
                

            //endif;
            ?>
        <!--container end-->
        </div>
    </div>
    <div class="container">
        <div class="row">
            <div class="col-md-12 text-right">
                <!--pagenation-->
                <div class="pagenation">                    
                    <?php echo $pagenation;?>
                </div>
            </div>
        </div>
	</div>

    <?php
get_footer();  