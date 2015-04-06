<?php
remove_shortcode('gallery', 'gallery_shortcode');
add_shortcode('gallery', 'custom_gallery');
function custom_gallery($attr) {
	$post = get_post();
	static $instance = 0;
	$instance++;
	# hard-coding these values so that they can't be broken
	$attr['columns'] = 1;
	$attr['size'] = 'full';
	$attr['link'] = 'none';
	$attr['orderby'] = 'post__in';
	$attr['include'] = $attr['ids'];		

	#Allow plugins/themes to override the default gallery template.

	$output = apply_filters('post_gallery', '', $attr);

	if ( $output != '' )
		return $output;

	if ( isset( $attr['orderby'] ) ) {
		$attr['orderby'] = sanitize_sql_orderby( $attr['orderby'] );
		if ( !$attr['orderby'] )
			unset( $attr['orderby'] );
	}

	extract(shortcode_atts(array(
		'order'      => 'ASC',
		'orderby'    => 'menu_order ID',
		'id'         => $post->ID,
		'itemtag'    => 'div',
		'icontag'    => 'div',
		'captiontag' => 'p',
		'columns'    => 1,
		'size'       => 'thumbnail',
		'include'    => '',
		'exclude'    => ''
	), $attr));

	$id = intval($id);

	if ( 'RAND' == $order )
		$orderby = 'none';

	if ( !empty($include) ) {
		$_attachments = get_posts( array('include' => $include, 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => $order, 'orderby' => $orderby) );
		$attachments = array();
		foreach ( $_attachments as $key => $val ) {
			$attachments[$val->ID] = $_attachments[$key];
		}

	} elseif ( !empty($exclude) ) {
		$attachments = get_children( array('post_parent' => $id, 'exclude' => $exclude, 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => $order, 'orderby' => $orderby) );
	} else {
		$attachments = get_children( array('post_parent' => $id, 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => $order, 'orderby' => $orderby) );
	}

	if ( empty($attachments) )
		return '';

	global $post;
	$output ='<div class="row">';
	$output .='<div class="col-md-12">';
	$output .='<div id="post_gallery" class="carousel slide" data-ride="carousel">
					<div class="carousel-outer">
			 			<div class="carousel-inner">';
							$a=1;
							$cl='';
							foreach ( $attachments as $id => $attachment ){ 
								if($a===1){$cl=' active';}else{ $cl= '';}
								$output .= '<div class="item '.$cl.'">';
								$img =sprintf('<img src="'.$attachment->guid.'" class="img-responsive">');
								$output .= $img ;
								$output .= '</div>';
								$a++;
							}
			$output .='</div>';
			    
			$output .='<a class="left carousel-control" href="#post_gallery" data-slide="prev">
            				<span class="glyphicon glyphicon-chevron-left"></span>
        				</a>
        				<a class="right carousel-control" href="#post_gallery" data-slide="next">
            				<span class="glyphicon glyphicon-chevron-right"></span>
        				</a>
        		</div>';
        		$output .='<ol class="carousel-indicators mCustomScrollbar">';
        					$i=0;
			  				$cls = '';
						  	foreach ( $attachments as $id => $attachment ){ 
						  		if($i===0){$cls=' active';}else{ $cls= '';}
						    	$thb =sprintf('<li data-target="#post_gallery" data-slide-to="'.$i.'" class="'.$cls.'"><img  src="'.$attachment->guid.'"></li>');
						    	$output .= $thb ;
						    	$i++;
							}
 	$output .='</ol></div>';
	$output .='</div></div>';

	return $output;
}

