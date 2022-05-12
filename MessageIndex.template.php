<?php
/**
 * Simple Machines Forum (SMF)
 *
 * @package SMF
 * @author Simple Machines https://www.simplemachines.org
 * @copyright 2022 Simple Machines and individual contributors
 * @license https://www.simplemachines.org/about/smf/license.php BSD
 *
 * @version 2.1.0
 */

/**
 * The main messageindex.
 */
function template_main()
{
	global $context, $settings, $options, $scripturl, $modSettings, $txt,$board;
	//fast aprove on messageindex  
	$approveLink = $scripturl.'?action=moderate;area=postmod;sa=approve;topic=%1$s;msg=%2$s;'.$context['session_var'].'='.$context['session_id'].'';

	
	$existBoard = !empty($context['boards'])? true:false; 
 
	echo'
	<div class="row"> 
	<div class="col-12 col-md-4  order-1">
	
	<div>
	<a href="',$scripturl,'?action=post;board=',$board,'" class="btn d-block text-center mb-3 btn-primary w-100 btn-lg">Start a Discussion</a>
	</div>
	'; 
	if ($existBoard)
	{
			echo '
		<div id="board_', $context['current_board'], '_childboards" class="boardindex_table main_container">
			 
			<h3 class="m-3 mt-0">', $txt['sub_boards'], '</h3>
			 ';

			foreach ($context['boards'] as $board)
			{
				echo '
			<div id="board_', $board['id'], '" class="card mb-2 ', (!empty($board['css_class']) ? $board['css_class'] : ''), '">
				
				<div class="card-body">
				<div class="board_icon">
					', function_exists('template_bi_' . $board['type'] . '_icon') ? call_user_func('template_bi_' . $board['type'] . '_icon', $board) : template_bi_board_icon($board), '
				</div>
				<div class="info">
					', function_exists('template_bi_' . $board['type'] . '_info') ? call_user_func('template_bi_' . $board['type'] . '_info', $board) : template_bi_board_info($board), '
				</div><!-- .info -->';

				// Show some basic information about the number of posts, etc.
				echo '
				<div class="board_stats">
					', function_exists('template_bi_' . $board['type'] . '_stats') ? call_user_func('template_bi_' . $board['type'] . '_stats', $board) : template_bi_board_stats($board), '
				</div>';

				// Show the last post if there is one.
				echo '
				<div class="lastpost">
					', function_exists('template_bi_' . $board['type'] . '_lastpost') ? call_user_func('template_bi_' . $board['type'] . '_lastpost', $board) : template_bi_board_lastpost($board), '
				</div>';

				// Won't somebody think of the children!
				if (function_exists('template_bi_' . $board['type'] . '_children'))
					call_user_func('template_bi_' . $board['type'] . '_children', $board);
				else
					template_bi_board_children($board);

					echo '
					</div>
			</div><!-- #board_[id] -->';
			}

			echo '
		</div><!-- #board_[current_board]_childboards -->';
	}
	echo'<div class="sticky-top"> ';
	//usage template_widget('spirit_board','boards');
	template_button_strip($context['normal_buttons'],'show w-100',['hidde'=>true]);
	echo'
	</div>
	</div>
	<div class="col-12 col-md-8"> ';
	
	if (!empty($context['topics'])){
	echo'
		<div class="mb-2">
		';
		foreach ($context['topics'] as $key =>  $topic) {
			if ($topic['is_sticky']){
				 
				echo'
				<a href="', $topic['first_post']['href'],'" class="card mb-2 text-warning d-block p-3 shadow">
				<span class="float-end small">', $txt['views'], ' ', $topic['views'], '</span> 
				<strong><span class="main_icons sticky"></span> ', $topic['first_post']['member']['name'],': </strong>', $topic['first_post']['subject'],'
				',!empty($topic['first_post']['id']) ? '<div class="text-muted small">'.$topic['first_post']['member']['name'] .'</div>':'','
				
				</a>'; 
				unset($context['topics'][$key]);
			}
		}

		echo'
		</div>';
	}


	if (!$context['no_topic_listing'])
	{
		echo '
	<div class="pagesection mb-2"> 
		<div class="pagelinks"> 
			', $context['page_index'], '
		</div>
		'; 

		echo '
	</div>';

		// If Quick Moderation is enabled start the form.
		if (!empty($context['can_quick_mod']) && $options['display_quick_mod'] > 0 && !empty($context['topics']))
			echo '
	<form action="', $scripturl, '?action=quickmod;board=', $context['current_board'], '.', $context['start'], '" method="post" accept-charset="', $context['character_set'], '" class="clear" name="quickModForm" id="quickModForm">';

		echo '
		<div id="messageindex">';

		echo '
			<div id="topic_header" class="d-flex">';
			
		// Are there actually any topics to show?
		if (!empty($context['topics']))
		{
			

			// Show a "select all" box for quick moderation?
			if (!empty($context['can_quick_mod']) && $options['display_quick_mod'] == 1)
				echo '
				<div class="moderation p-2 mr-1">
					<input type="checkbox" onclick="invertAll(this, this.form, \'topics[]\');">
				</div>';

			// If it's on in "image" mode, don't show anything but the column.
			elseif (!empty($context['can_quick_mod']))
				echo '
				<div class="moderation p-2 mr-1"></div>';

				template_order_topics();
		}

		// No topics... just say, "sorry bub".
		else
			echo '
				<h3 class="titlebg">', $txt['topic_alert_none'], '</h3>';

		echo '', template_button_strip($context['normal_buttons'], 'dropdown-menu-end' ,['class'=>'ms-auto']),'
			</div><!-- #topic_header -->';
		
		

		// Contain the topic list
		echo '
			<div id="topic_container">';

		foreach ($context['topics'] as $topic)
		{
			$lastaAvatar = isset($topic['last_post']['member']['avatar']['image'])? $topic['last_post']['member']['avatar']['image']:false;
			$firstAvatar = isset($topic['first_post']['member']['avatar']['image'])? $topic['first_post']['member']['avatar']['image']:false;

			$preview = $topic['first_post']['preview'];
			echo '
				<div class="card mb-2"> 
				<div class="card-body">  
				<div class="ms-2"> 
					<div class="info', !empty($context['can_quick_mod']) ? '' : ' info_block', '">
						<div ', (!empty($topic['quick_mod']['modify']) ? 'id="topic_' . $topic['first_post']['id'] . '"  ondblclick="oQuickModifyTopic.modify_topic(\'' . $topic['id'] . '\', \'' . $topic['first_post']['id'] . '\');"' : ''), '>';  

			echo '
							<div class="message_index_title">';
							if($firstAvatar) 
					template_avatar($firstAvatar,'22','rounded-circle me-2'); 
					echo'	<a href=" ', $topic['first_post']['href'],'" class="rounded-pill btn btn-outline-primary float-end py-1 mb-2">Unirse</a>
							<small>', $txt['started_by'], ' <a href="#" data-member="', $topic['first_post']['member']['id'], '">u/', $topic['first_post']['member']['name'], '</a>  &#8226; ' . $topic['first_post']['time'] . '</small>
							<span class="board_icon">
							<img src="', $topic['first_post']['icon_url'], '" alt="">
							', $topic['is_posted_in'] ? '<span class="main_icons profile_sm"></span>' : '', '
							</span>
						
								', $topic['new'] && $context['user']['is_logged'] ? '<a href="' . $topic['new_href'] . '" id="newicon' . $topic['first_post']['id'] . '" class="badge bg-success">' . $txt['new'] . '</a>' : '', '

								<div class="preview mt-2 d-flex w-100">
									<div id="msg_', $topic['first_post']['id'], '">
									<h2 class="d-inline-block"> ', $topic['first_post']['link'],'</h2>
									 ',!empty($preview) ? spirate_clear_preview($preview):'','
									</div>';

									if(!empty($preview)){
										 echo'
										   <div class="ms-auto">';
										   if(spirate_youtube($preview) !== false)
										   		echo spirate_youtube($preview);
										    
											echo spirate_search_image($preview);
										echo'   
										   </div>';
									}
									 echo'
								</div>
							</div>				 
						</div><!-- #topic_[first_post][id] -->
					</div><!-- .info --> 
					'; 

			// Show the quick moderation options?
			if (!empty($context['can_quick_mod']))
			{
				 
				echo '
					<div class="moderation">';

				if ($options['display_quick_mod'] == 1)
					echo '
						<input type="checkbox" name="topics[]" value="', $topic['id'], '">';
				else
				{
					// Check permissions on each and show only the ones they are allowed to use.
					if ($topic['quick_mod']['remove'])
						echo '<a class="btn_more d-inline-block" href="', $scripturl, '?action=quickmod;board=', $context['current_board'], '.', $context['start'], ';actions%5B', $topic['id'], '%5D=remove;', $context['session_var'], '=', $context['session_id'], '" class="you_sure"><span class="main_icons delete" title="', $txt['remove_topic'], '"></span></a>';

					if ($topic['quick_mod']['lock'])
						echo '<a class="btn_more d-inline-block" href="', $scripturl, '?action=quickmod;board=', $context['current_board'], '.', $context['start'], ';actions%5B', $topic['id'], '%5D=lock;', $context['session_var'], '=', $context['session_id'], '" class="you_sure"><span class="main_icons lock" title="', $topic['is_locked'] ? $txt['set_unlock'] : $txt['set_lock'], '"></span></a>';

				 

					if ($topic['quick_mod']['sticky'])
						echo '<a class="btn_more d-inline-block" href="', $scripturl, '?action=quickmod;board=', $context['current_board'], '.', $context['start'], ';actions%5B', $topic['id'], '%5D=sticky;', $context['session_var'], '=', $context['session_id'], '" class="you_sure"><span class="main_icons sticky" title="', $topic['is_sticky'] ? $txt['set_nonsticky'] : $txt['set_sticky'], '"></span></a>';

					if ($topic['quick_mod']['move'])
						echo '<a class="btn_more d-inline-block" href="', $scripturl, '?action=movetopic;current_board=', $context['current_board'], ';board=', $context['current_board'], '.', $context['start'], ';topic=', $topic['id'], '.0"><span class="main_icons move" title="', $txt['move_topic'], '"></span></a>';
				}
				echo '
					</div><!-- .moderation -->';
			}
			echo '</div>
				<div class="ms-auto d-flex text-muted">
					<div class="text-center bg-light p-3 rounded ">
						 ', $topic['replies'], ' 
						<span>', $txt['replies'], ' <span class="main_icons message"></span></span>
					</div>
					<div class="text-center bg-light p-3 rounded  ms-2">
						 ', $topic['views'], ' 
						<span>', $txt['views'], '  <span class="main_icons eye"></span></span>
					</div> 
				';
				
				echo'
				 </div>
				</div>'; 
	//approved button
	if(!$topic['approved'] && $context['can_approve'])
	echo'<a  href="',sprintf($approveLink,  $topic['id_topic'] ,  $topic['id_first_msg'] ) ,'" class="card-footer p-3 text-center bg-warning" title="' , $txt['awaiting_approval'] , ' " tabindex="0" data-bs-toggle="popover" data-bs-trigger="hover focus" data-bs-content="Disabled popover">'. $txt['approve'].'</a>';

				echo'
			</div><!--card -->
				', !empty($topic['pages']) ? '<div id="pages' . $topic['first_post']['id'] . '" class="pagination small mb-2">' . $topic['pages'] . '</div>' : '', '
				';
				
		}
		echo '
			</div><!-- #topic_container -->';

		if (!empty($context['can_quick_mod']) && $options['display_quick_mod'] == 1 && !empty($context['topics']))
		{
			echo '
			<div class="d-flex align-items-center mb-2" id="quick_actions">
				<select class="qaction" name="qaction"', $context['can_move'] ? ' onchange="this.form.move_to.disabled = (this.options[this.selectedIndex].value != \'move\');"' : '', '>
					<option value="">--------</option>';

			foreach ($context['qmod_actions'] as $qmod_action)
				if ($context['can_' . $qmod_action])
					echo '
					<option value="' . $qmod_action . '">' . $txt['quick_mod_' . $qmod_action] . '</option>';

			echo '
				</select>';

			// Show a list of boards they can move the topic to.
			if ($context['can_move'])
				echo '
				<span id="quick_mod_jump_to" class="mx-2"></span>';

			echo '
				<input type="submit" value="', $txt['quick_mod_go'], '" onclick="return document.forms.quickModForm.qaction.value != \'\' &amp;&amp; confirm(\'', $txt['quickmod_confirm'], '\');" class="btn btn-primary qaction">
			</div><!-- #quick_actions -->';
		}

		echo '
		</div><!-- #messageindex -->';

		// Finish off the form - again.
		if (!empty($context['can_quick_mod']) && $options['display_quick_mod'] > 0 && !empty($context['topics']))
			echo '
		<input type="hidden" name="' . $context['session_var'] . '" value="' . $context['session_id'] . '">
	</form>';

		echo '
	<div class="pagesection mb-2">  
		<div class="pagelinks"> 
			', $context['page_index'], '
		</div>'; 
		echo '
	</div>';
	}

	// Show breadcrumbs at the bottom too.
	theme_linktree();

	if (!empty($context['can_quick_mod']) && $options['display_quick_mod'] == 1 && !empty($context['topics']) && $context['can_move'])
		echo '
	<script>
		if (typeof(window.XMLHttpRequest) != "undefined")
			aJumpTo[aJumpTo.length] = new JumpTo({
				sContainerId: "quick_mod_jump_to",
				sClassName: "qaction",
				sJumpToTemplate: "%dropdown_list%",
				iCurBoardId: ', $context['current_board'], ',
				iCurBoardChildLevel: ', $context['jump_to']['child_level'], ',
				sCurBoardName: "', $context['jump_to']['board_name'], '",
				sBoardChildLevelIndicator: "==",
				sBoardPrefix: "=> ",
				sCatSeparator: "-----------------------------",
				sCatPrefix: "",
				bNoRedirect: true,
				bDisabled: true,
				sCustomName: "move_to"
			});
	</script>';

	// Javascript for inline editing.
	echo '
	<script>
		var oQuickModifyTopic = new QuickModifyTopic({
			aHidePrefixes: Array("lockicon", "stickyicon", "pages", "newicon"),
			bMouseOnDiv: false,
		});
	</script>';

	template_topic_legend();
 
 
	echo'</div></div><!--end row-->';
}

/**
 * Outputs the board icon for a standard board.
 *
 * @param array $board Current board information.
 */
function template_bi_board_icon($board)
{
	global $context, $scripturl;

	echo '
		<a href="', ($context['user']['is_guest'] ? $board['href'] : $scripturl . '?action=unread;board=' . $board['id'] . '.0;children'), '" class="board_', $board['board_class'], '"', !empty($board['board_tooltip']) ? ' title="' . $board['board_tooltip'] . '"' : '', '></a>';
}

/**
 * Outputs the board icon for a redirect.
 *
 * @param array $board Current board information.
 */
function template_bi_redirect_icon($board)
{
	global $context, $scripturl;

	echo '
		<a href="', $board['href'], '" class="board_', $board['board_class'], '"', !empty($board['board_tooltip']) ? ' title="' . $board['board_tooltip'] . '"' : '', '></a>';
}

/**
 * Outputs the board info for a standard board or redirect.
 *
 * @param array $board Current board information.
 */
function template_bi_board_info($board)
{
	global $context, $scripturl, $txt;

	echo '
		<a class="subject mobile_subject" href="', $board['href'], '" id="b', $board['id'], '"> 
		<h3 class="card-title">', $board['name'], '</h3>
		</a>';

	// Has it outstanding posts for approval?
	if ($board['can_approve_posts'] && ($board['unapproved_posts'] || $board['unapproved_topics']))
		echo '
		<a href="', $scripturl, '?action=moderate;area=postmod;sa=', ($board['unapproved_topics'] > 0 ? 'topics' : 'posts'), ';brd=', $board['id'], ';', $context['session_var'], '=', $context['session_id'], '" title="', sprintf($txt['unapproved_posts'], $board['unapproved_topics'], $board['unapproved_posts']), '" class="moderation_link amt">!</a>';

	echo '
		<div class="board_description">', $board['description'], '</div>';

	// Show the "Moderators: ". Each has name, href, link, and id. (but we're gonna use link_moderators.)
	if (!empty($board['moderators']) || !empty($board['moderator_groups']))
		echo '
		<p class="moderators">', count($board['link_moderators']) === 1 ? $txt['moderator'] : $txt['moderators'], ': ', implode(', ', $board['link_moderators']), '</p>';
}

/**
 * Outputs the board stats for a standard board.
 *
 * @param array $board Current board information.
 */
function template_bi_board_stats($board)
{
	global $txt;

	echo '
		<p>
			<strong>', $txt['posts'], ':</strong> ', comma_format($board['posts']), '<strong> ', $txt['board_topics'], ': </strong>', comma_format($board['topics']), '
		</p>';
}

/**
 * Outputs the board stats for a redirect.
 *
 * @param array $board Current board information.
 */
function template_bi_redirect_stats($board)
{
	global $txt;

	echo '
		<p>
			', $txt['redirects'], ': ', comma_format($board['posts']), '
		</p>';
}

/**
 * Outputs the board lastposts for a standard board or a redirect.
 * When on a mobile device, this may be hidden if no last post exists.
 *
 * @param array $board Current board information.
 */
function template_bi_board_lastpost($board)
{
	if (!empty($board['last_post']['id']))
		echo '
			<p>', $board['last_post']['last_post_message'], '</p>';
}

/**
 * Outputs the board children for a standard board.
 *
 * @param array $board Current board information.
 */
function template_bi_board_children($board)
{
	global $txt, $scripturl, $context;

	// Show the "Child Boards: ". (there's a link_children but we're going to bold the new ones...)
	if (!empty($board['children']))
	{
		// Sort the links into an array with new boards bold so it can be imploded.
		$children = array();
		/* Each child in each board's children has:
			id, name, description, new (is it new?), topics (#), posts (#), href, link, and last_post. */
		foreach ($board['children'] as $child)
		{
			if (!$child['is_redirect'])
				$child['link'] = '' . ($child['new'] ? '<a href="' . $scripturl . '?action=unread;board=' . $child['id'] . '" title="' . $txt['new_posts'] . ' (' . $txt['board_topics'] . ': ' . comma_format($child['topics']) . ', ' . $txt['posts'] . ': ' . comma_format($child['posts']) . ')" class="new_posts">' . $txt['new'] . '</a> ' : '') . '<a href="' . $child['href'] . '" ' . ($child['new'] ? 'class="board_new_posts" ' : '') . 'title="' . ($child['new'] ? $txt['new_posts'] : $txt['old_posts']) . ' (' . $txt['board_topics'] . ': ' . comma_format($child['topics']) . ', ' . $txt['posts'] . ': ' . comma_format($child['posts']) . ')">' . $child['name'] . '</a>';
			else
				$child['link'] = '<a href="' . $child['href'] . '" title="' . comma_format($child['posts']) . ' ' . $txt['redirects'] . ' - ' . $child['short_description'] . '">' . $child['name'] . '</a>';

			// Has it posts awaiting approval?
			if ($child['can_approve_posts'] && ($child['unapproved_posts'] || $child['unapproved_topics']))
				$child['link'] .= 'ssssssssssss';

			$children[] = $child['new'] ? '<span class="strong">' . $child['link'] . '</span>' : '<span>' . $child['link'] . '</span>';
		}

		echo '
			<div id="board_', $board['id'], '_children" class="children">
				<p><strong id="child_list_', $board['id'], '">', $txt['sub_boards'], '</strong>', implode(' ', $children), '</p>
			</div>';
	}
}

/**
 * Shows a legend for topic icons.
 */
function template_spirate_board_info()
{
	global $context, $settings, $txt, $modSettings;
 

echo'<div class="board-detail position-relative" > 
		<div class="container">
			<div class="py-3 py-md-5">';
		echo '
		<div class="text-center py-5 d-block">
		<h2 class="mb-2">
		b/', $context['name'], '</h2>
		
		';

	if (isset($context['description']) && $context['description'] != '')
	echo '
	', $context['description'], '';

	echo'</div><div>';
	if (!empty($context['moderators']))
	echo '
	<div class="alert alert-warning small"> <span class="main_icons moderate align-baseline"></span>', count($context['moderators']) === 1 ? $txt['moderator'] : $txt['moderators'], ': ', implode(', ', $context['link_moderators']), '.</div>';

	if (!empty($settings['display_who_viewing']))
	{
	echo '
		<div class="alert alert-warning small"> <span class="main_icons eye me-2"></span>';

	// Show just numbers...?
	if ($settings['display_who_viewing'] == 1)
		echo count($context['view_members']), ' ', count($context['view_members']) == 1 ? $txt['who_member'] : $txt['members'];
	// Or show the actual people viewing the topic?
	else
		echo empty($context['view_members_list']) ? '0 ' . $txt['members'] : implode(', ', $context['view_members_list']) . ((empty($context['view_num_hidden']) || $context['can_moderate_forum']) ? '' : ' (+ ' . $context['view_num_hidden'] . ' ' . $txt['hidden'] . ')');

	// Now show how many guests are here too.
	echo $txt['who_and'], $context['view_num_guests'], ' ', $context['view_num_guests'] == 1 ? $txt['guest'] : $txt['guests'], $txt['who_viewing_board'], '
		 
		</div>';
	}

	echo '
	</div>';

	// Let them know why their message became unapproved.
	if ($context['becomesUnapproved'])
	echo '
	<div class="alert alert-warning small">
	<span class="main_icons alerts"></span> ', $txt['post_becomes_unapproved'], '
	</div>';

	// If this person can approve items and we have some awaiting approval tell them.
	if (!empty($context['unapproved_posts_message']))
	echo '
	<div class="alert alert-warning small">
	<span class="main_icons alerts"></span> ', $context['unapproved_posts_message'], '
	</div>';

echo'	</div>
	</div> 
</div>';
}
/**
 * Shows a legend for topic icons.
 */
function template_topic_legend()
{
	global $context, $settings, $txt, $modSettings;

 
}
function template_order_topics(){
	global $context,$txt;
	if(!isset($context['topics_headers']))
		 return false; 
	echo'
	<nav class="mb-2">
	<div class="dropdown ">
	<a href="#" class="btn btn-secondary dropdown-toggle"  id="short_topics" data-bs-toggle="dropdown" aria-expanded="false">
	 ',$txt['short_by'],'
	</a>
	<ul class="dropdown-menu" aria-labelledby="short_topics">  
  	';
    foreach ($context['topics_headers'] as $key => $order) {
	 echo'<li>',str_replace('<a','<a class="dropdown-item" ',$order),'</li>';
	} 
	echo'</ul>
	</div>
	</nav>';
}
?>