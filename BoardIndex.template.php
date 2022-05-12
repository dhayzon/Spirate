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
 * The top part of the outer layer of the boardindex
 */
function template_boardindex_outer_above()
{
	template_newsfader();
}

/**
 * This shows the newsfader
 */
function template_newsfader()
{
	global $context, $settings;

	// Show the news fader?  (assuming there are things to show...)
	if (!empty($settings['show_newsfader']) && !empty($context['news_lines']))
	{
		echo '
		<ul id="smf_slider" class="roundframe">';

		foreach ($context['news_lines'] as $news)
			echo '
			<li>', $news, '</li>';

		echo '
		</ul>
		<script>
			jQuery("#smf_slider").slippry({
				pause: ', $settings['newsfader_time'], ',
				adaptiveHeight: 0,
				captions: 0,
				controls: 0,
			});
		</script>';
	}
}

/**
 * This actually displays the board index
 */
function template_main()
{
	global $context, $txt, $scripturl;

	echo'
	
	<div class="row">
	<div class="col-12 col-md-8">
		<div class="mb-3 mb-md-0">
		',template_spiate_result(),'
		</div>
	</div>
		<div class="col-12 col-md-4"> 
		<div class="sticky-top">';
    echo'
	<a href="',$scripturl,'?action=spboards" class="d-block text-center mb-3 btn btn-primary w-100 btn-lg" onclick="return reqOverlayDiv(this.href, ' . JavaScriptEscape($txt['stardiscussion']) . ', \'pecil\');" >',$txt['stardiscussion'],'</a>
	';
	
	echo ' 
	<div id="boardindex_table" class="boardindex_table">'; 
	/* Each category in categories is made up of:
	id, href, link, name, is_collapsed (is it collapsed?), can_collapse (is it okay if it is?),
	new (is it new?), collapse_href (href to collapse/expand), collapse_image (up/down image),
	and boards. (see below.) */
	echo'<div id="boards" class="card d-block p-2 mb-2">';
	foreach ($context['categories'] as $category){
		// If theres no parent boards we can see, avoid showing an empty category (unless its collapsed)
		if (empty($category['boards']) && !$category['is_collapsed'])
		continue;
		template_board($category);
	}
	echo'</div>';	
	template_info_center();
	/**
	 * some people need show top user 
	 * template_widget('topusers','Top Users');
	 * $tabs = [['id'=>'reply','name'=>'respuesta'],['id'=>'view','name'=>'nombre']];
	 * template_widget_tabs('toptopics','Top topics',array('id'=>'topTopicViewReply','tabs'=>$tabs));
	 * template_widget('topboards','Top Boards'); 
	 */ 
	 
	echo '</div><!-- sticky-top -->
	</div>';

	// Show the mark all as read button?
	if ($context['user']['is_logged'] && !empty($context['categories']))
		echo '
	<div class="mark_read">
		', template_button_strip($context['mark_read_button'], 'right'), '
	</div>';

	echo'
	</div>
		
	</div>
	';
}
function template_board($category){
	global $txt;
	 $description = !empty($category['description']) ? $category['description']:'' ;
 
	echo ' 
		<div class=" ', $category['is_collapsed'] ? 'collapsed' : '', '" id="category_', $category['id'], '">
			<h3 class="dropdown-header">'; 
	echo ' 
				', str_replace('title="','data-bs-trigger="hover focus"  title="'.$category['name']. '" data-bs-toggle="popover" data-bs-content="'.$description.' ',$category['link']), ' ', !empty($category['new'])? '<span class="badge text-warning">'. $txt['new'].'</span>':'','
			</h3> 
		 ';
		foreach ($category['boards'] as $board)
		{
			echo'
			<div class="board">
				', template_bi_board_info($board) ,'
			</div><!-- .board -->';

		template_bi_board_children($board);
		}

		echo'</div>';
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
 
	echo'
	<a class="dropdown-item d-flex align-items-center rounded" href="', $board['href'], '"  data-board="'. $board['id']. '"  >
	', $board['name'], ' ',$board['new']? '<span class="badge text-warning">'.$txt['new'].'</span>':'','<span></span> <span class="ps-3 ms-auto">';
	template_bi_board_lastpost($board);

	if($board['type'] == 'redirect'){
	     template_bi_board_stats($board); 
	
	}else{
		template_bi_redirect_stats($board);	
	}
	
	echo'</span></a>';
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
		<small class="float-end" title="',$txt['posts'],'">',spirate_short_number($board['posts']+$board['topics']),' </small>';
}

/**
 * Outputs the board stats for a redirect.
 *
 * @param array $board Current board information.
 */
function template_bi_redirect_stats($board)
{
	global $txt;

	echo '<small class="float-end" title="',$txt['redirects'],'">',spirate_short_number($board['posts']),'</small>';
}

/**
 * Outputs the board lastposts for a standard board or a redirect.
 * When on a mobile device, this may be hidden if no last post exists.
 *
 * @param array $board Current board information.
 */
function template_bi_board_lastpost($board)
{
 //', $board['last_post']['last_post_message'], '
	if (!empty($board['last_post']['id']) && isset($board['last_post']['member']['avatar']['image']))
		  template_avatar($board['last_post']['member']['avatar']['image'],'16','rounded-circle ms-2 float-end');
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
			// Has it posts awaiting approval?
			$approve = $child['can_approve_posts'] && ($child['unapproved_posts'] || $child['unapproved_topics']);

			if (!$child['is_redirect'])
				$child['link'] = '<a href="' . $child['href'] . '" class="dropdown-item">' . $child['name'] . ''.($child['new']? ' <span class="badge text-warning">'.$txt['new'].'</span>':'').' <span class="float-end">'.spirate_short_number($child['posts']).'</span></a>';
			else
				$child['link'] = '<a class="dropdown-item" href="' . $child['href'] . '">' . $child['name'] . ' <span class="float-end">'.spirate_short_number($child['posts']).'<span class="main_icons external"></span></span></a>';

			
		 

			$children[] = $child['new'] ? '<span class="strong">' . $child['link'] . '</span>' : '<span>' . $child['link'] . '</span>';
		}

		echo '
			<div id="board_', $board['id'], '_children" class="children">
			 
					<a class="dropdown-header" data-bs-toggle="collapse" href="#child_list_', $board['id'], '" aria-expanded="false" aria-controls="child_list_', $board['id'], '">
					', $txt['sub_boards'], ' <strong class="main_icons plus"></strong></a>
					<div class="collapse bg-secondary rounded mb-2" id="child_list_', $board['id'], '">
					', implode(' ', $children), '
					</div>
				 
			</div><hr>';
	}
}

/**
 * The lower part of the outer layer of the board index
 */
function template_boardindex_outer_below()
{
	//template_info_center();
}

/**
 * Displays the info center
 */
function template_info_center()
{
	global $context, $options, $txt;

	if (empty($context['info_center']))
		return;

	// Here's where the "Info Center" starts...
	echo '
	<div class="card"> 
		<div class="card-body">
		<h4>', sprintf($txt['info_center_title'], $context['forum_name_html_safe']), '</h4>';

	foreach ($context['info_center'] as $block)
	{
		$func = 'template_ic_block_' . $block['tpl'];
		$func();
	}

	echo '
		</div> 
	</div> ';
 
}

/**
 * The recent posts section of the info center
 */
function template_ic_block_recent()
{ 
	 //no need recent
}

/**
 * The calendar section of the info center
 */
function template_ic_block_calendar()
{
	global $context, $scripturl, $txt;

	// Show information about events, birthdays, and holidays on the calendar.
	echo '
			<div class="sub_bar">
				<h4 class="subbg">
					<a href="', $scripturl, '?action=calendar' . '"><span class="main_icons calendar"></span> ', $context['calendar_only_today'] ? $txt['calendar_today'] : $txt['calendar_upcoming'], '</a>
				</h4>
			</div>';

	// Holidays like "Christmas", "Chanukah", and "We Love [Unknown] Day" :P
	if (!empty($context['calendar_holidays']))
		echo '
			<p class="inline holiday">
				<span>', $txt['calendar_prompt'], '</span> ', implode(', ', $context['calendar_holidays']), '
			</p>';

	// People's birthdays. Like mine. And yours, I guess. Kidding.
	if (!empty($context['calendar_birthdays']))
	{
		echo '
			<p class="inline">
				<span class="birthday">', $context['calendar_only_today'] ? $txt['birthdays'] : $txt['birthdays_upcoming'], '</span>';

		// Each member in calendar_birthdays has: id, name (person), age (if they have one set?), is_last. (last in list?), and is_today (birthday is today?)
		foreach ($context['calendar_birthdays'] as $member)
			echo '
				<a href="', $scripturl, '?action=profile;u=', $member['id'], '">', $member['is_today'] ? '<strong class="fix_rtl_names">' : '', $member['name'], $member['is_today'] ? '</strong>' : '', isset($member['age']) ? ' (' . $member['age'] . ')' : '', '</a>', $member['is_last'] ? '' : ', ';

		echo '
			</p>';
	}

	// Events like community get-togethers.
	if (!empty($context['calendar_events']))
	{
		echo '
			<p class="inline">
				<span class="event">', $context['calendar_only_today'] ? $txt['events'] : $txt['events_upcoming'], '</span> ';

		// Each event in calendar_events should have:
		//		title, href, is_last, can_edit (are they allowed?), modify_href, and is_today.
		foreach ($context['calendar_events'] as $event)
			echo '
				', $event['can_edit'] ? '<a href="' . $event['modify_href'] . '" title="' . $txt['calendar_edit'] . '"><span class="main_icons calendar_modify"></span></a> ' : '', $event['href'] == '' ? '' : '<a href="' . $event['href'] . '">', $event['is_today'] ? '<strong>' . $event['title'] . '</strong>' : $event['title'], $event['href'] == '' ? '' : '</a>', $event['is_last'] ? '<br>' : ', ';
		echo '
			</p>';
	}
}

/**
 * The stats section of the info center
 */
function template_ic_block_stats()
{
	global $scripturl, $txt, $context, $settings;
 
	// Show statisticvazal style information...
	echo '
			 
			<div class="pt-3">
				',$txt['posts'],': ', $context['common_stats']['total_posts'], '<br>  
				',$txt['topics'],': ', $context['common_stats']['total_topics'], '<br>
				',$txt['members'],': ', $context['common_stats']['total_members'], '<br>
				', !empty($settings['show_latest_member']) ? ' ' . $txt['latest_member'] . ': <strong> ' . $context['common_stats']['latest_member']['link'] . '</strong>' : '', '<br> 
			</div>';
}

/**
 * The who's online section of the info center
 */
function template_ic_block_online()
{
	global $context, $scripturl, $txt, $modSettings, $settings;
	// "Users online" - in order of activity.
	echo '
			 
				<h4 class="mt-2">
					', $context['show_who'] ? '<a href="' . $scripturl . '?action=who">' : '', ' ', $txt['online_users'], '', $context['show_who'] ? '</a>' : '', '
				</h4>
			 
			<div class="mt-2">
				', $context['show_who'] ? '<a href="' . $scripturl . '?action=who">' : '', '<strong>', $txt['online'], ': </strong>', comma_format($context['num_guests']), ' ', $context['num_guests'] == 1 ? $txt['guest'] : $txt['guests'], ', ', comma_format($context['num_users_online']), ' ', $context['num_users_online'] == 1 ? $txt['user'] : $txt['users'];

	// Handle hidden users and buddies.
	$bracketList = array();

	if ($context['show_buddies'])
		$bracketList[] = comma_format($context['num_buddies']) . ' ' . ($context['num_buddies'] == 1 ? $txt['buddy'] : $txt['buddies']);

	if (!empty($context['num_spiders']))
		$bracketList[] = comma_format($context['num_spiders']) . ' ' . ($context['num_spiders'] == 1 ? $txt['spider'] : $txt['spiders']);

	if (!empty($context['num_users_hidden']))
		$bracketList[] = comma_format($context['num_users_hidden']) . ' ' . ($context['num_spiders'] == 1 ? $txt['hidden'] : $txt['hidden_s']);

	if (!empty($bracketList))
		echo ' (' . implode(', ', $bracketList) . ')';

	echo $context['show_who'] ? '</a>' : '', '

				<br>', $txt['most_online_today'], ': <strong>', comma_format($modSettings['mostOnlineToday']), '</strong><br>
				', $txt['most_online_ever'], ': ', comma_format($modSettings['mostOnline']), ' (', timeformat($modSettings['mostDate']), ')<br>';

	// Assuming there ARE users online... each user in users_online has an id, username, name, group, href, and link.
	if (!empty($context['users_online']))
	{
		echo '
				', sprintf($txt['users_active'], $modSettings['lastActive']), ': ', implode(', ', $context['list_users_online']);

		// Showing membergroups?
		if (!empty($settings['show_group_key']) && !empty($context['membergroups']))
			echo '
				<span class="membergroups">' . implode(', ', $context['membergroups']) . '</span>';
	}

	echo '
			</div>';
}
function template_spiate_result(){
	global $context, $txt,$scripturl;
	echo'
	<div class="card p-3 d-block mb-2">
	 <a href="#" id="loadNews"  class="badge rounded-pill bg-secondary p-3">',$txt['spiratenews'],'</a>
	 <a href="#" id="loadBoardNews"  class="float-end dropdown-toggle badge rounded-pill bg-secondary p-3">
	 ',$txt['boardnews'].'
     </a>
	 <div class="d-inline-block">
	 	<a href="#" class="badge rounded-pill bg-secondary p-3 dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">',$txt['recent_posts'],'</a>
		 <ul class="dropdown-menu" aria-labelledby="recent_posts"> 
		    <li class="px-2"><a class="dropdown-item rounded active" href="#" id="recent_posts" >', $txt['recent_posts'], '</a></li>
			<li class="px-2"><a class="dropdown-item rounded" href="',$scripturl,'?action=unread">', $txt['show_unread_replies'], '</a></li>
			<li class="px-2"><a class="dropdown-item rounded" href="',$scripturl,'?action=unreadreplies">', $txt['unread_since_visit'], '</a></li>
		</ul>
	 </div>
	</div>
';
	template_loading('spirate_result',false);
}

?>