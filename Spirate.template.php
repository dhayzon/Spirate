<?php
function template_main(){
  
   global $context, $smcFunc; 
   $context['spirate_ajax_content'] = [];
   $checkSession = checkSession($type = 'post', $from_action = '', $is_fatal = FALSE);

   if(!empty($checkSession))
   $checkSession = checkSession($type = 'get', $from_action = '', $is_fatal = FALSE);
   
   if(isset($_GET['sa'])){
      $sa = $smcFunc['htmlspecialchars']($_GET['sa'], ENT_QUOTES);
      $sa = 'template_spirate_data_'.substr($sa,0, 30);
      if (function_exists($sa)  && is_callable($sa) ){
            call_user_func($sa); 
      }  
   } 
   template_spirate_json_result();
 } 
function template_spirate_main(){
   global $context; 
   $context['spirate_ajax_content'] = [];
   $checkSession = checkSession($type = 'post', $from_action = '', $is_fatal = FALSE);

   if(!empty($checkSession))
   $checkSession = checkSession($type = 'get', $from_action = '', $is_fatal = FALSE);
   
   $context['spirate_ajax_content'] = template_spirate_recent();   
   template_spirate_json_result();
    
} 
function template_spirate_json_result(){
   global $context; 
   header('Content-Type: application/json; charset=utf-8');  
   $error = isset($context['spirit_error_post'])?[$context['spirit_error_post']]:['fail'];
   $result['result'] = array(
      'success'=> empty($context['spirate_ajax_content'])? false:true,
      'content'=> empty($context['spirate_ajax_content'])? $error:$context['spirate_ajax_content'],
   );
    
   echo json_encode($result, JSON_PRETTY_PRINT );
}
 
/**
 * @method recent | use native files and functions from smf, get all info from ?action=recent, 
 * @return void  
 */
function template_spirate_recent(){
   global $context,$txt;

   $pagination = preg_replace('/\?action=recent;/','?action=spirate;json;',$context['page_index']);
   $result['posts'] = [];
   $result['pagination'] = '<div class="loadmore pagination">'.$pagination  .'</div>';
 
   foreach ($context['posts'] as $key => $post) {  
      $autor = $post['first_poster']['id'] == $post['poster']['id'] ? true:false; 

      $postBody =  spirate_clear_preview($post['message'],$limit = 250);
      $imagen = spirate_search_image($post['message'],'');
      $result['posts'][] = array(
         'id'=> $post['id'], 
         'html' => '<div class="card mb-2"><div class="card-body pt-2"><a href="'.$post['href'].'" class="btn rounded-pill float-end btn-outline-primary btn-sm px-4">'.$txt['spiratejoin'].'</a><small><a  href="'.$post['board']['href'].'">b/ '.$post['board']['name'].'</a><span class="text-muted"> &#8226; publicado por <a href="#" data-member="'.$post['first_poster']['id'].'"> u/'.$post['first_poster']['name'].'</a> '.$post['time'].'</span></small><h3><a  href="'.$post['href'].'">'.$post['subject'].'</a> </h3>'. $postBody.' '. $imagen.' '.(!empty($post['start']) ? '<span class="main_icons reply"></span> <a href="#" data-member="'.$post['poster']['id'].'"> u/'.$post['poster']['name'].' <span class="float-end"><span class=" main_icons message"></span> '.$post['start'].'</span>':'').'</a></div></div>'
      ); 
   } 
   return $result;
}


/**
 * @method  use native jump_to function from smf Core Nice, This is ugly html but work, smf overlay poup require html 
 * @return void
 */
function template_spirate_jump_to(){ 
	global $context, $txt, $scripturl;
 
	// Since this is a popup of its own we need to start the html, etc.
	echo '<!DOCTYPE html>
<html', $context['right_to_left'] ? ' dir="rtl"' : '', '>
	<head>
		<meta charset="', $context['character_set'], '">
		<meta name="robots" content="noindex">
		<title>', $context['linktree'][0]['name'], '</title>
	</head>
	<body id="help_popup">
		<div class="windowbg description">  
      <div class="d-flex align-items-start">';
      echo'<div class="nav flex-column nav-pills me-3" id="v-pills-tab" role="tablist" aria-orientation="vertical">';
      $conunt = 0;   
         foreach ($context['jump_to'] as $key => $cat) { 
            echo'    
            <button class="nav-link mb-2 ',$conunt==0? 'active':'','" id="cat-',$cat['id'],'-cat-tab" data-bs-toggle="pill" data-bs-target="#cat-',$cat['id'],'-cat" type="button" role="tab" aria-controls="cat-',$cat['id'],'-cat" aria-selected="true">',$cat['name'],'</button>
            ';
            $conunt++;
        }
        $conunt = 0;   
        echo'</div>
        <div class="tab-content bg-secondary w-100 rounded p-3" id="v-pills-tabContent">';
        foreach ($context['jump_to'] as $key => $cat) {
         echo'<div class="tab-pane fade ',$conunt==0? ' show active':'','" id="cat-',$cat['id'],'-cat" role="tabpanel" aria-labelledby="cat-',$cat['id'],'-cat-tab">
            ';
            foreach ($cat['boards'] as $key => $board) {
                echo'<div class="p-2"><a href="',$scripturl,'?action=post;board=', $board['id'],'">', $board['name'],' <span class="main_icons pecil"></span></a><a href="',$scripturl,'?board=', $board['id'],'" class="float-end btn-sm btn-primary">',$txt['viewboard'] ,'  </a></div>';
            } 
            echo'</div>';
            $conunt++;
         }
         echo'</div>
         </div>
         <br>
			<a href="javascript:self.close();" class="main_icons hide_popup"></a>
		</div>
	</body>
</html>';
} 
/**
 * @method  return member info   tooltip user card same reddit and another social network, use SSI native smf functions
 * @return void 
 */
function template_spirate_data_member(){
   global $context,$settings,$txt; 
   $id_member = (int)$_GET['u'];
   $member = $id_member;
   $member = ssi_fetchMember([$member], ''); 
   if(empty($member)){
      $context['spirate_ajax_content'] = ['html'=>'User Not Found'];
      return false;
   } 

   $member = $member[$id_member];
    
   
   if(isset($member['options']['cust_cover'])&& !empty($member['options']['cust_cover']))
   $cover = 'style="background-image:url('.$member['options']['cust_cover'].')"'; 
   else
   $cover = 'style="background-image:url('.$settings['images_url'].'/covers/cover_'.rand(1,6).'.svg)"';


   $html ='<div class="usercard"><span class="cover" '.$cover.'></span><div class="d-flex">'.(template_avatar($member['avatar']['image'],'62','rounded-circle mx-auto',true)).' </div><div class="text-center w-100"> u/'.$member['name_color'].'</div><div>'.$member['online']['link'].'  - '.$member['last_login'].'</div><div class="d-flex text-nowrap mt-2"><div class="pe-3"> '.$member['posts'].'<br> '.$member['post_group'].'</div><div class="vr"></div> '.(!empty($member['group']) ? '<div class="ps-3">'.$member['group'].'<br>'.$txt['spiritgroup'].'</div>':'').'</div><div><a href="'.$member['href'].'" class="mt-3 rounded-pill btn btn-outline-danger w-100">'.sprintf($txt['s_viewprofile'],$member['name']) .'</a></div></div>';
   //ssi_fetchMember([$member], '')
   $context['spirate_ajax_content'] = ['html'=>$html];
   
}

/**
 * return all new topics fron board this method use ssi, in next update will be use native function message index 
 * @method  
 * @return void
 */
function template_spirate_data_news(){
   global $context,$scripturl,$txt,$settings; 
   unset($_GET['length']);

   $board = null;
   $limit = 5; //limit items  
   $start = null;
   $length = 200; 
   $customboardid = !empty($settings['spirate_news_board'])? $settings['spirate_news_board']:'';
   if ($board !== null)
		$board = (int) $board;
	elseif (isset($_GET['board']))
		$board = (int) $_GET['board'];
   
   if(!empty($customboardid) && !isset($_GET['board']))
   $board =  $customboardid;

 
   $posts = ssi_boardNews($board, $limit , $start, $length , '');
 
   $context['spirit_error_post'] = $txt['nomorepost'];

   if(empty($posts))
     return false;

   $topics = []; 
   foreach ($posts as $key => $post) {
       $topics[]['html'] = '<div class="card mb-2 flex-row"><div class="flex-shrink-1 bg-secondary d-none d-md-block text-center p-2"><span class="main_icons trending-up"></span> <br> '. $post['likes']['count'].'</div> <div class="card-body pt-1">'. spirate_youtube($post['body'],'1','float-end').'<small class="text-muted" data-member="'. $post['poster']['id'].'"> u/'. $post['poster']['link'].' '. $post['time'].' </small><h2><a href="'. $post['href'].'">'. $post['subject'].'</a></h2> '. spirate_clear_preview($post['body']).' '. spirate_search_image($post['body'],'20').'<div class="d-flex text-muted mt-2"><span class="d-md-none me-2 d-flex"><span class="main_icons trending-up h2"></span>   '. $post['likes']['count'].'</span>'.str_replace('">','" class="d-flex align-items-center"><span class="h3 main_icons message"></span>  ',$post['link']).' '.str_replace('">','" class="ms-2 d-flex align-items-center"><span class="h3 main_icons message"></span>  ',$post['new_comment']).'</div></div></div>
       ';
   } 
   $result['posts'] = $topics;

   $offset = 5;
	$start = isset($_GET['start']) ? (int) $_GET['start'] : 0;
   


   if($board !== null )
   $result['pagination'] = '<div class="loadmore w-100"><a href="'.$scripturl.'?action=widgets;sa=news;board='.$board.';start='.	($offset+$start) .'" class="d-block btn py-3 btn-primary" data-type="buttom">load more</a></div>';

   $context['spirate_ajax_content'] = $result;
 
 }

 function template_spirate_data_all(){
  global $context;
   $result = []; 
   $result['ssi_topTopicsReplies'] = template_WidgetContent(ssi_topTopicsReplies(6, ''),'topic',true);
   $result['ssi_topTopicsViews'] = template_WidgetContent(ssi_topTopicsViews(6, ''),'topic');
   $result['ssi_topBoards'] = template_WidgetContent(ssi_topBoards(6, ''),'board');
   $result['ssi_topPoster'] = template_WidgetContent(ssi_topPoster(6,''),'user');

   $context['spirate_ajax_content'] =  $result;
    
} 

/**
 * 
 */
function template_WidgetContent($data = [],$type = '',$extra = false){
   $template = '<ul class="list-unstyled">'; 
   foreach ($data as $key => $value) {
      $template .= '<li>'; 
      if($type == 'topic')
      $template .=  '<span class="truncate text-truncate">t/'.$value['link'].'</span><span class="float-end">'.( $extra ?  '<span class="main_icons message"</span>'.spirate_short_number($value['num_replies'] ): '<span class="main_icons eye"</span> '.spirate_short_number($value['num_views'])).' </span>';
      if($type == 'board')
      $template .=  '<span class="truncate text-truncate">b/'.$value['link'].'</span><span class="float-end">'. spirate_short_number(( (int)$value['num_posts']+(int)$value['num_topics'])).'</span>';
      if($type == 'user')
      $template .=  '<span class="truncate text-truncate"><a href="'.$value['href'].'" data-member="'.$value['id'].'">u/'.$value['name'].'</a></span><span class="float-end">'.spirate_short_number( $value['posts']).'</span>';
      $template .= '</li>'; 
   }
   $template .='</ul>';

   $return['html'] = $template;
   return   $return;
}
 