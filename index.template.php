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

/*	This template is, perhaps, the most important template in the theme. It
	contains the main template layer that displays the header and footer of
	the forum, namely with main_above and main_below. It also contains the
	menu sub template, which appropriately displays the menu; the init sub
	template, which is there to set the theme up; (init can be missing.) and
	the linktree sub template, which sorts out the link tree.

	The init sub template should load any data and set any hardcoded options.

	The main_above sub template is what is shown above the main content, and
	should contain anything that should be shown up there.

	The main_below sub template, conversely, is shown after the main content.
	It should probably contain the copyright statement and some other things.

	The linktree sub template should display the link tree, using the data
	in the $context['linktree'] variable.

	The menu sub template should display all the relevant buttons the user
	wants and or needs.

	For more information on the templating system, please see the site at:
	https://www.simplemachines.org/
*/

/**
 * Initialize the template... mainly little settings.
 */
function template_init()
{
	global $settings, $txt,$modSettings, $context,$topic,$board;

	$clearBody = array(
		'widgets',
		'spirate'
	);
 
	if((empty($topic) && !empty($board) && !isset($context['current_action'])) || in_array($context['current_action'] ,$clearBody))
	 $modSettings['enableBBC'] = 0;
 
	/** custom settings for spirate theme */
	$settings['number_recent_posts'] = '0';
	/* $context, $options and $txt may be available for use, but may not be fully populated yet. */

	// The version this template/theme is for. This should probably be the version of SMF it was created for.
	$settings['theme_version'] = '2.1';

	// Set the following variable to true if this theme requires the optional theme strings file to be loaded.
	$settings['require_theme_strings'] = true;

	// Set the following variable to true if this theme wants to display the avatar of the user that posted the last and the first post on the message index and recent pages.
	$settings['avatars_on_indexes'] = true;

	// Set the following variable to true if this theme wants to display the avatar of the user that posted the last post on the board index.
	$settings['avatars_on_boardIndex'] = true;

	// Set the following variable to true if this theme wants to display the login and register buttons in the main forum menu.
	$settings['login_main_menu'] = false;

	// This defines the formatting for the page indexes used throughout the forum.
	$settings['page_index'] = array(
		'extra_before' => ' ',
		'previous_page' => '&laquo;',
		'current_page' => ' <span class="page-item active"><span class="page-link">%1$d</span></span> ',
		'page' => ' <span class="page-item"> <a class="page-link" href="{URL}">%2$s</a></span> ',
		'expand_pages' => '<span class="page-item"><span class="page-link" onclick="expandPages(this, {LINK}, {FIRST_PAGE}, {LAST_PAGE}, {PER_PAGE});"> ... </span></span>',
		'next_page' => '&raquo;',
		'extra_after' => '',
	);
	// Allow css/js files to be disabled for this specific theme.
	// Add the identifier as an array key. IE array('smf_script'); Some external files might not add identifiers, on those cases SMF uses its filename as reference.
	if (!isset($settings['disable_files']))
		$settings['disable_files'] = array();

	 
	if (@$_REQUEST['action'] == 'spirate')
		$settings['catch_action'] = array(
			'template' => 'Spirate',
			'function'=> 'RecentPosts',
			'filename'=> 'Recent.php', 
			'layers'=>[],
			'sub_template' => 'spirate_main',
		);
	if (@$_REQUEST['action'] == 'widgets')
		$settings['catch_action'] = array(
			'template' => 'Spirate',
			'function'=> 'ssi_checkPassword',// requiere but is not used,  please not change this function name
			'filename'=> '$boarddir/SSI.php',// posible conflict some servers please change "/" to "\"
			'layers'=>[], 
		);
	if (@$_REQUEST['action'] == 'spboards')
		$settings['catch_action'] = array(
			'template' => 'Spirate',
			'function'=> 'GetJumpTo', 
			'filename'=> 'Xml.php', 
			'layers'=>[],
			'sub_template' => 'spirate_jump_to',
		);		
 
		
}

/**
 * The main sub template above the content.
 */
function template_html_above()
{
	global $context, $scripturl, $txt, $modSettings,$settings; 
 
	// Show right to left, the language code, and the character set for ease of translating.
	echo '<!DOCTYPE html>
<html', $context['right_to_left'] ? ' dir="rtl"' : '', !empty($txt['lang_locale']) ? ' lang="' . str_replace("_", "-", substr($txt['lang_locale'], 0, strcspn($txt['lang_locale'], "."))) . '"' : '', ' >
<head>
	<meta charset="', $context['character_set'], '">';

	/*
		You don't need to manually load index.css, this will be set up for you.
		Note that RTL will also be loaded for you.
		To load other CSS and JS files you should use the functions
		loadCSSFile() and loadJavaScriptFile() respectively.
		This approach will let you take advantage of SMF's automatic CSS
		minimization and other benefits. You can, of course, manually add any
		other files you want after template_css() has been run.

	*	Short example:
			- CSS: loadCSSFile('filename.css', array('minimize' => true));
			- JS:  loadJavaScriptFile('filename.js', array('minimize' => true));
			You can also read more detailed usages of the parameters for these
			functions on the SMF wiki.

	*	Themes:
			The most efficient way of writing multi themes is to use a master
			index.css plus variant.css files. If you've set them up properly
			(through $settings['theme_variants']), the variant files will be loaded
			for you automatically.
			Additionally, tweaking the CSS for the editor requires you to include
			a custom 'jquery.sceditor.theme.css' file in the css folder if you need it.

	*	MODs:
			If you want to load CSS or JS files in here, the best way is to use the
			'integrate_load_theme' hook for adding multiple files, or using
			'integrate_pre_css_output', 'integrate_pre_javascript_output' for a single file.
	*/ 
	// load in any css from mods or themes so they can overwrite if wanted
	if(!empty($settings['spirate_brand_color']))
	$context['css_header'][] = ':root{--smf-primary: '.$settings['spirate_brand_color'].';}';


	template_css();
	
	
	$spirateSettngs = array(
		'dark'=> !empty($settings['spirate_color']) ? true:false,
		'home'=> empty($context['current_action']) && empty($context['current_topic']) && empty($context['current_board'])? true:false,
		'feed' => array(
			'default'=> !empty($settings['spirate_index']) ?  'news':'recent',
			'board'=> !empty($settings['spirate_news_board']) ?  true:false, 
		)
	);

	$context['javascript_vars']['spirate'] = json_encode($spirateSettngs);
	// load in any javascript files from mods and themes
	template_javascript();
 
	echo '
	<title>', $context['page_title_html_safe'], '</title>
	<meta name="viewport" content="width=device-width, initial-scale=1">';

	// Content related meta tags, like description, keywords, Open Graph stuff, etc...
	foreach ($context['meta_tags'] as $meta_tag)
	{
		echo '
	<meta';

		foreach ($meta_tag as $meta_key => $meta_value)
			echo ' ', $meta_key, '="', $meta_value, '"';

		echo '>';
	}

	/*	What is your Lollipop's color?
		Theme Authors, you can change the color here to make sure your theme's main color gets visible on tab */
	echo '
	<meta name="theme-color" content="#557EA0">';

	// Please don't index these Mr Robot.
	if (!empty($context['robot_no_index']))
		echo '
	<meta name="robots" content="noindex">';

	// Present a canonical url for search engines to prevent duplicate content in their indices.
	if (!empty($context['canonical_url']))
		echo '
	<link rel="canonical" href="', $context['canonical_url'], '">';

	// Show all the relative links, such as help, search, contents, and the like.
	echo '
	<link rel="help" href="', $scripturl, '?action=help">
	<link rel="contents" href="', $scripturl, '">', ($context['allow_search'] ? '
	<link rel="search" href="' . $scripturl . '?action=search">' : '');

	// If RSS feeds are enabled, advertise the presence of one.
	if (!empty($modSettings['xmlnews_enable']) && (!empty($modSettings['allow_guestAccess']) || $context['user']['is_logged']))
		echo '
	<link rel="alternate" type="application/rss+xml" title="', $context['forum_name_html_safe'], ' - ', $txt['rss'], '" href="', $scripturl, '?action=.xml;type=rss2', !empty($context['current_board']) ? ';board=' . $context['current_board'] : '', '">
	<link rel="alternate" type="application/atom+xml" title="', $context['forum_name_html_safe'], ' - ', $txt['atom'], '" href="', $scripturl, '?action=.xml;type=atom', !empty($context['current_board']) ? ';board=' . $context['current_board'] : '', '">';

	// If we're viewing a topic, these should be the previous and next topics, respectively.
	if (!empty($context['links']['next']))
		echo '
	<link rel="next" href="', $context['links']['next'], '">';

	if (!empty($context['links']['prev']))
		echo '
	<link rel="prev" href="', $context['links']['prev'], '">';

	// If we're in a board, or a topic for that matter, the index will be the board's index.
	if (!empty($context['current_board']))
		echo '
	<link rel="index" href="', $scripturl, '?board=', $context['current_board'], '.0">';
 
	echo' 

	';
	// Output any remaining HTML headers. (from mods, maybe?)
	echo $context['html_headers'];

	echo'
	<script>
	(()=>{var e=localStorage.getItem("spirate-theme-appearance");(spirate.dark&&!e||(e&&"light"!==e?"dark"===e:window.matchMedia("(prefers-color-scheme: dark)").matches))&&document.documentElement.classList.add("dark")})();
	</script>';
	 
	if(!empty($settings['spirtate_analytics']))
	echo'
	<!-- Global site tag (gtag.js) - Google Analytics -->
	<script async src="https://www.googletagmanager.com/gtag/js?id=',$settings['spirtate_analytics'],'"></script>
	<script>
	window.dataLayer = window.dataLayer || [];
	function gtag(){dataLayer.push(arguments);}
	gtag(\'js\', new Date());

	gtag(\'config\', \'',$settings['spirtate_analytics'],'\');
	</script>
	';
	echo '
</head>
<body id="', $context['browser_body_id'], '" class="action_', !empty($context['current_action']) ? $context['current_action'] : (!empty($context['current_board']) ?
		'messageindex' : (!empty($context['current_topic']) ? 'display' : 'home')), !empty($context['current_board']) ? ' board_' . $context['current_board'] : '', '">
';

}

/**
 * The upper part of the main template layer. This is the stuff that shows above the main forum content.
 */
function template_body_above()
{
	global $context, $settings, $scripturl, $txt, $modSettings, $maintenance,$adminSection,$fluidcontainer;

	$fluidcontainer = !empty($settings['spirtate_fluid_container'])? true:false;
	echo'
	<header id="header">',	template_menu(),'</header>';

	$adminSection = function_exists('template_generic_menu_dropdown_above'); 
 

	echo'
	
	',template_user(),'
	<section>';
     if(function_exists('template_spirate_board_info') && isset($context['current_board']) && empty($context['current_action']))
	 			template_spirate_board_info();
	echo '
	<div class="',$adminSection || $fluidcontainer? 'container-fluid':'container','">
		<div id="upper_section" class="my-2">  ';
	// Show a random news item? (or you could pick one from news_lines...)
	if (!empty($settings['enable_news']) && !empty($context['random_news_line']))
		echo '
					<div class="alert alert-warning">
						<h4 class="d-block d-md-inline-block">', $txt['news'], ': </h4>
						 ', $context['random_news_line'], '  
					</div>';

	echo '
	</div>
	</div><!-- #upper_section --> 
	</section>
	<section> '; 
	// The main content should go here.
	echo '
		<div id="content_section" class="',$adminSection || $fluidcontainer? 'container-fluid':'container','">
			<div id="main_content_section">';
}
function template_theme_search(){
	global $context,$scripturl,$modSettings,$txt;
	if (!empty($modSettings['userLanguage']) && !empty($context['languages']) && count($context['languages']) > 1)
	{
		echo '
			<form id="languages_form" method="get" class="floatright">
				<select id="language_select" name="language" onchange="this.form.submit()">';

		foreach ($context['languages'] as $language)
			echo '
					<option value="', $language['filename'], '"', isset($context['user']['language']) && $context['user']['language'] == $language['filename'] ? ' selected="selected"' : '', '>', str_replace('-utf8', '', $language['name']), '</option>';

		echo '
				</select>
				<noscript>
					<input type="submit" value="', $txt['quick_mod_go'], '">
				</noscript>
			</form>';
	}

	if ($context['allow_search'])
	{
		echo '
		<div class="w-md-75 d-flex">
			<form id="search_form" class="m-0 d-flex w-100" action="', $scripturl, '?action=search2" method="post" accept-charset="', $context['character_set'], '">
			<div class="search-box ms-auto ">
				<input id="search_form_inp" type="search" name="search" value="" placeholder="',sprintf($txt['search_in'],$context['forum_name_html_safe']),'" class="ps-5 w-100">
				<label for="search_form_inp"><span class="main_icons search"></span></label>
			</div>';
		echo' <div class="d-flex justify-content-end ms-2 ">';
		// Using the quick search dropdown?
		$selected = !empty($context['current_topic']) ? 'current_topic' : (!empty($context['current_board']) ? 'current_board' : 'all');

		echo '
				<select name="search_selection" >
					<option value="all"', ($selected == 'all' ? ' selected' : ''), '>', $txt['search_entireforum'], ' </option>';

		// Can't limit it to a specific topic if we are not in one
		if (!empty($context['current_topic']))
			echo '
					<option value="topic"', ($selected == 'current_topic' ? ' selected' : ''), '>', $txt['search_thistopic'], '</option>';

		// Can't limit it to a specific board if we are not in one
		if (!empty($context['current_board']))
			echo '
					<option value="board"', ($selected == 'current_board' ? ' selected' : ''), '>', $txt['search_thisboard'], '</option>';

		// Can't search for members if we can't see the memberlist
		if (!empty($context['allow_memberlist']))
			echo '
					<option value="members"', ($selected == 'members' ? ' selected' : ''), '>', $txt['search_members'], ' </option>';

		echo '
				</select>';

		// Search within current topic?
		if (!empty($context['current_topic']))
			echo '
				<input type="hidden" name="sd_topic" value="', $context['current_topic'], '">';

		// If we're on a certain board, limit it to this board ;).
		elseif (!empty($context['current_board']))
			echo '
				<input type="hidden" name="sd_brd" value="', $context['current_board'], '">';

		echo '
				<input type="submit" name="search2" value="', $txt['search'], '" class="ms-2 btn btn-primary">
				<input type="hidden" name="advanced" value="0">
				</div>
			</form>
			</div>';
	}
}
function template_user(){
	global $context, $settings, $scripturl, $txt, $modSettings, $maintenance,$adminSection, $fluidcontainer;
		// If the user is logged in, display some things that might be useful.
		echo'
		<nav class="user-nav navbar">
  			<div class="',$adminSection || $fluidcontainer ? 'container-fluid':'container','">';	
			  theme_linktree(); 
	   echo'<ul class="user_menu py-3 nav justify-content-end ms-auto  position-relative">';
	   echo'
	   <li class="nav-item">
		   <a class="nav-link bg-secondary btn_more" href="',$scripturl,'?action=spboards"  onclick="return reqOverlayDiv(this.href, ' . JavaScriptEscape($txt['stardiscussion']) . ', \'pecil\');" >  <span class="main_icons boards"></span>  </a>
	   </li>
	   ';		  
		if ($context['user']['is_logged'])
		{
 
		    echo'
			<li class="nav-item">
				<a class="nav-link" href="',$scripturl,'?action=profile;u=',$context['user']['id'],'">
				<span class="d-flex align-items-center badge badge-pill bg-secondary rounded-pill py-0 pe-0 ps-3">', $context['user']['name'], '';
				if (!empty($context['user']['avatar']))
				echo template_avatar($context['user']['avatar']['image'],'35','ms-1 rounded-circle');
				echo'</span></a>
			</li>
			';
			if ($context['allow_pm'])
			echo'
			<li class="nav-item">
				<a class="nav-link bg-secondary btn_more ', !empty($context['self_pm']) ? 'active' : '', '" data-bs-toggle="dropdown" href="#" role="button" aria-expanded="false" id="pm_menu_top" data-bs-auto-close="outside" data-bs-display="static" >
				<span class="main_icons inbox"></span>
				', !empty($context['user']['unread_messages']) ? '
				<span class=" text-white position-absolute top-0 start-75 translate-middle badge rounded-pill bg-danger">' . $context['user']['unread_messages'] . '</span>' : '', ' 
				</a>
				<div class="dropdown-menu dropdown-menu-end"> 
						',template_loading('pm_menu'),' 
					<a class="w-100 text-center d-block py-2"  href="', $scripturl, '?action=pm">
					', $txt['pm_short'], '
					</a>
				</div>
			</li>';
			// Thirdly, alerts
			echo '
					<li class="nav-item">
						<a class="nav-link bg-secondary btn_more mx-2 ', !empty($context['self_alerts']) ? 'active' : '', '" data-bs-toggle="dropdown" href="#" data-bs-display="static"  aria-expanded="false" id="alerts_menu_top" data-bs-auto-close="outside">
							<span class="main_icons alerts"></span> 
							', !empty($context['user']['alerts']) ? '<span class=" text-white position-absolute top-0 start-75 translate-middle badge rounded-pill bg-danger">' . $context['user']['alerts'] . '</span>' : '', '
						</a>
						<div class="dropdown-menu dropdown-menu-end"> 
							',template_loading('alerts_menu'),' 
						 <a class="w-100 text-center d-block py-2" href="', $scripturl, '?action=profile;area=showalerts;u=', $context['user']['id'], '">', $txt['alerts'], '</a>
						</div>
					</li>';
				echo' 
				<li class="nav-item dropdown">
						<a  class="nav-link bg-secondary btn_more ', !empty($context['self_profile']) ? ' active' : '', '" href="', $scripturl, '?action=profile" id="profile_menu_top" onclick="return false;" data-bs-toggle="dropdown"   role="button" aria-expanded="false" data-bs-auto-close="outside">
						<span class="main_icons plus"></span>';  
			echo '</a>
						<div class="dropdown-menu  dropdown-menu-end">  
						  ',template_loading('profile_menu',false),' 
						'; 
				// A logout button for people without JavaScript.
				if (empty($settings['login_main_menu']))
					echo '
						<span id="nojs_logout"  class="nav-item">
							<a href="', $scripturl, '?action=logout;', $context['session_var'], '=', $context['session_id'], '">', $txt['logout'], '</a>
							<script>document.getElementById("nojs_logout").style.display = "none";</script>
						</span>';
						echo'
						
						</div>
					</li>';
		
		 
		}
		echo' 
		</ul>';
		/*
		// Otherwise they're a guest. Ask them to either register or login.
		elseif (empty($maintenance))
		{
			// Some people like to do things the old-fashioned way.
			if (!empty($settings['login_main_menu']))
			{
				echo '
			 
					<div class="welcome">', sprintf($txt[$context['can_register'] ? 'welcome_guest_register' : 'welcome_guest'], $context['forum_name_html_safe'], $scripturl . '?action=login', 'return reqOverlayDiv(this.href, ' . JavaScriptEscape($txt['login']) . ', \'login\');', $scripturl . '?action=signup'), '</div>
				 ';
			}
			else
			{
				echo '
				 
					<div class="welcome">
						', sprintf($txt['welcome_to_forum'], $context['forum_name_html_safe']), '
				
					 
						<a href="', $scripturl, '?action=login" class="', $context['current_action'] == 'login' ? 'active' : 'open','" onclick="return reqOverlayDiv(this.href, ' . JavaScriptEscape($txt['login']) . ', \'login\');">
							<span class="main_icons login"></span>
							<span class="textmenu">', $txt['login'], '</span>
						</a>
					 
						<a href="', $scripturl, '?action=signup" class="', $context['current_action'] == 'signup' ? 'active' : 'open','">
							<span class="main_icons regcenter"></span>
							<span class="textmenu">', $txt['register'], '</span>
						</a>
					 	</div>';
			}
		}
		else
			// In maintenance mode, only login is allowed and don't show OverlayDiv
			echo '
				<div class="welcome">
					<span>', sprintf($txt['welcome_guest'], $context['forum_name_html_safe'], $scripturl . '?action=login', 'return true;'), '</span>
				</div>';
		*/
	echo'
	</div>
	</nav>';
}
/**
 * The stuff shown immediately below the main content, including the footer
 */
function template_body_below()
{
	global $context, $txt, $scripturl, $modSettings,$settings,$fluidcontainer;
 
	echo '
			</div><!-- #main_content_section -->
		</div><!-- #content_section --> 
</section><!-- #section -->';
 
 
	// Show the footer with copyright, terms and help links.
	echo '
	<footer id="footer">
		<div class="container',$fluidcontainer? '-fluid':'',' pt-5 ">';

	// There is now a global "Go to top" link at the right.
	echo '
		<div class="d-md-none clearfix mb-3">',template_daynight(),' </div>
		<div class="w-100 text-center mb-3">';

    if(!empty($settings['site_slogan']))
		echo'
		<figure>
			<blockquote class="blockquote">
				<p>' . $settings['site_slogan'] . '</p>
			</blockquote>
			<figcaption class="blockquote-footer">
			',$context['forum_name'],' 
			</figcaption>
		</figure>
		
		'; 
		echo'
			<time datetime="', smf_gmstrftime('%FT%TZ'), '" class="d-block">', $context['current_time'], '</time>
			<small><a href="', $scripturl, '?action=help">', $txt['help'], '</a> ', (!empty($modSettings['requireAgreement'])) ? '| <a href="' . $scripturl . '?action=agreement">' . $txt['terms_and_rules'] . '</a>' : '', ' | <a href="#header">', $txt['go_up'], ' &#9650;</a></small>
			 ';
				// Show the load time?
			if ($context['show_load_time'])
			echo '<br>
			<small>', sprintf($txt['page_created_full'], $context['load_time'], $context['load_queries']), '</small>';
		echo'
		<div class="d-flex small py-3">
		 <div class="me-auto">&copy; ', date("Y"), ' ',$context['forum_name'],' <a class="text-muted" href="',$txt['theme_copyright_url'],'"  target="_blank">',$txt['theme_copyright'],'</a></div>
		  <div >', theme_copyright(), '</div>
		</div>
		';	
	echo'	
		</div>'; 

	echo '
		</div>
	</footer><!-- #footer -->';

}
function template_daynight($hidde = false){
	global $settings;
	$darkmode = !empty($settings['spirate_color']) ? true:false;
	if($hidde)
	echo'<span  class="daynight ms-2 d-none d-md-inline-block">',$darkmode? '🌚':'🌞','</span>';
	else
	echo'<span  class="daynight float-end d-md-none">',$darkmode? '🌚':'🌞','</span>';
}
/**
 * This shows any deferred JavaScript and closes out the HTML
 */
function template_html_below()
{
	// Load in any javascipt that could be deferred to the end of the page
	template_javascript(true); 
	echo '
</body>
</html>';
}

/**
 * Show a linktree. This is that thing that shows "My Community | General Category | General Discussion"..
 *
 * @param bool $force_show Whether to force showing it even if settings say otherwise
 */
function theme_linktree($force_show = false)
{
	global $context, $shown_linktree, $scripturl, $txt;

	// If linktree is empty, just return - also allow an override.
	if (empty($context['linktree']) || (!empty($context['dont_default_linktree']) && !$force_show))
		return;

	echo '
	<nav aria-label="breadcrumb">
					<ul class="breadcrumb my-2">';

	// Each tree item has a URL and name. Some may have extra_before and extra_after.
	foreach ($context['linktree'] as $link_num => $tree)
	{
		 
		echo '
						<li class="small  breadcrumb-item ',$link_num >=2 ? '':'d-none d-md-block','  ', ($link_num == count($context['linktree']) - 1) ? ' active ' : '', '" >';

		// Don't show a separator for the first one.
		// Better here. Always points to the next level when the linktree breaks to a second line.
		// Picked a better looking HTML entity, and added support for RTL plus a span for styling.
		//if ($link_num != 0)
		//	echo '
		//					<span class="dot bg-primary"></span>';

		// Show something before the link?
		if (isset($tree['extra_before']))
			echo $tree['extra_before'], ' ';

 		// Show the link, including a URL if it should have one.
		if (isset($tree['url']))
			echo '
							<a href="' . $tree['url'] . '"><span>' . $tree['name'] . '</span></a>';
		else
			echo '
							<span>' . $tree['name'] . '</span>';

 

		echo '
						</li>';
	}

	echo '
					</ul>
				</nav><!-- .navigate_section -->';

	$shown_linktree = true;
}

/**
 * Show the menu up top. Something like [home] [help] [profile] [logout]...
 */
function template_menu()
{
	global $context,$scripturl,$txt;
 //<span  class="daynight ms-auto">🌞</span>
	echo '
	<nav id="index-main-menu" class="navbar navbar-expand-md">
		<div class="container-fluid">  
		<a id="navbar-brand" class="mx-2" href="', $scripturl, '"><h1 class="forumtitle d-table" >', empty($context['header_logo_url_html_safe']) ? $context['forum_name_html_safe'] : '<img src="' . $context['header_logo_url_html_safe'] . '" alt="' . $context['forum_name_html_safe'] . '" style="width: 153px;">', '</h1> </a> 
		
		
		<a class=" py-2 d-md-none" href="#" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
		  <span class="ps-3"><span class="h1 main_icons hmenu"></span></span>
		</a>

     	 <div class="collapse navbar-collapse" id="navbarSupportedContent">
			',$context['user']['is_logged']? template_menu_spirate():'','
			 ', template_theme_search(),'  ';
			if($context['user']['is_guest']){
			echo'<ul class="navbar-nav w-md-25 d-flex mt-2 mt-md-0"> 
				<li class="ms-md-auto">
				<a href="',$scripturl,'?action=signup" class="d-md-inline-block d-block btn btn-outline-primary rounded-pill">',$txt['register'],' </a>
				<a href="', $scripturl ,'?action=login" onclick="return reqOverlayDiv(this.href, ' . JavaScriptEscape($txt['login']) . ', \'login\');" class="d-md-inline-block mt-md-0 d-block mt-2  btn btn-primary rounded-pill">',$txt['login'],' </a> 
				</li> 
			</ul>';
			}
			echo'
			',template_daynight(true),'
	 	</div>	
	 	</div>	
	</nav>'; 
}
function template_menu_spirate(){
	global $context,$scripturl;
 
	echo'<ul class="navbar-nav me-auto mb-2 mb-md-0">';

	// Note: Menu markup has been cleaned up to remove unnecessary spans and classes.
	foreach ($context['menu_buttons'] as $act => $button)
	{
		echo '
						<li class="nav-item button_', $act, '', !empty($button['sub_buttons']) ? ' dropdown subsections"' : '"', '>
							<a  ', !empty($button['sub_buttons']) ? 'data-bs-toggle="dropdown" aria-expanded="false"' : '', '  class="nav-link  ', !empty($button['sub_buttons']) ? 'dropdown-toggle' : '', ' ', $button['active_button'] ? 'active' : '', '" href="', $button['href'], '"', isset($button['target']) ? ' target="' . $button['target'] . '"' : '', isset($button['onclick']) ? ' onclick="' . $button['onclick'] . '"' : '', '>
								', $button['icon'], '<span class="textmenu">', $button['title'], !empty($button['amt']) ? ' <span class="amt">' . $button['amt'] . '</span>' : '', '</span>
							</a>';

		// 2nd level menus
		if (!empty($button['sub_buttons']))
		{
			echo '
							<ul class="dropdown-menu">';

			foreach ($button['sub_buttons'] as $childbutton)
			{
				echo '
								<li', !empty($childbutton['sub_buttons']) ? ' class="subsections"' : '', '>
									<a class="dropdown-item"  href="', $childbutton['href'], '"', isset($childbutton['target']) ? ' target="' . $childbutton['target'] . '"' : '', isset($childbutton['onclick']) ? ' onclick="' . $childbutton['onclick'] . '"' : '', '>
										', $childbutton['title'], !empty($childbutton['amt']) ? ' <span class="amt">' . $childbutton['amt'] . '</span>' : '', '
									</a>';
				// 3rd level menus :)
				if (!empty($childbutton['sub_buttons']))
				{
					echo '
									<ul>';

					foreach ($childbutton['sub_buttons'] as $grandchildbutton)
						echo '
										<li>
											<a  class="dropdown-item" href="', $grandchildbutton['href'], '"', isset($grandchildbutton['target']) ? ' target="' . $grandchildbutton['target'] . '"' : '', isset($grandchildbutton['onclick']) ? ' onclick="' . $grandchildbutton['onclick'] . '"' : '', '>
												', $grandchildbutton['title'], !empty($grandchildbutton['amt']) ? ' <span class="amt">' . $grandchildbutton['amt'] . '</span>' : '', '
											</a>
										</li>';

					echo '
									</ul>';
				}

				echo '
								</li>';
			}
			echo '
							</ul>';
		}
		echo '
						</li>';
	}

	echo '
					</ul><!-- .menu_nav -->';
}
/**
 * Generate a strip of buttons.
 *
 * @param array $button_strip An array with info for displaying the strip
 * @param string $direction The direction
 * @param array $strip_options Options for the button strip
 */
/**
 * Generate a strip of buttons.
 *
 * @param array $button_strip An array with info for displaying the strip
 * @param string $direction The direction
 * @param array $strip_options Options for the button strip
 */
function template_button_strip($button_strip, $direction = '', $strip_options = array())
{
	global $context, $txt;

	if (!is_array($strip_options))
		$strip_options = array();

	// Create the buttons...
	$buttons = array();
	foreach ($button_strip as $key => $value)
	{
		// As of 2.1, the 'test' for each button happens while the array is being generated. The extra 'test' check here is deprecated but kept for backward compatibility (update your mods, folks!)
		if (!isset($value['test']) || !empty($context[$value['test']]))
		{
			if (!isset($value['id']))
				$value['id'] = $key;

			$button = '';

			if(!empty( $value['url']))
			$button .= '<li>
			<a class="dropdown-item button_strip_' . $key . (!empty($value['active']) ? ' active' : ''). '" ' . (!empty($value['url']) ? 'href="' . $value['url'] . '"' : '') . ' ' . (isset($value['custom']) ? ' ' . $value['custom'] : '') . '>'.(!empty($value['icon']) ? '<span class="main_icons '.$value['icon'].'"></span>' : '').'' . $txt[$value['text']] . '</a></li>';

			if (!empty($value['sub_buttons']))
			{
				$button .= ' <li><hr class="dropdown-divider"></li> ';
				foreach ($value['sub_buttons'] as $element)
				{
					if (isset($element['test']) && empty($context[$element['test']]))
						continue; 

					$button .= '
					<li><a class="dropdown-item" href="' . $element['url'] . '"><strong>' . $txt[$element['text']] . '</strong></a></li>';
					 
					
				} 
			}

			$buttons[] = $button;
		}
	}

	// No buttons? No button strip either.
	if (empty($buttons))
		return;

	echo '
	<div class="dropdown ', (!empty($strip_options['class']) ?  $strip_options['class'] : ''),'" ', (empty($buttons) ? ' style="display: none;"' : ''), (!empty($strip_options['id']) ? ' id="' . $strip_options['id'] . '"' : ''), '>
	', (empty($strip_options['hidde']) ?' <a class="btn btn_more bg-secondary" href="#"  data-bs-toggle="dropdown" aria-expanded="false"> 
			<span class="main_icons more-vertical"></span>
	    </a>':''),'
	   <ul class="dropdown-menu ', !empty($direction) ? $direction : '', '">
			', implode('', $buttons), '
	   </ul>
	  </div>';
}
/**
 * Generate a list of quickbuttons.
 *
 * @param array $list_items An array with info for displaying the strip
 * @param string $list_class Used for integration hooks and as a class name
 * @param string $output_method The output method. If 'echo', simply displays the buttons, otherwise returns the HTML for them
 * @return void|string Returns nothing unless output_method is something other than 'echo'
 */
function template_quickbuttons($list_items, $list_class = null, $output_method = 'echo')
{
	global $txt;

	// Enable manipulation with hooks
	if (!empty($list_class))
		call_integration_hook('integrate_' . $list_class . '_quickbuttons', array(&$list_items));

	// Make sure the list has at least one shown item
	foreach ($list_items as $key => $li)
	{
		// Is there a sublist, and does it have any shown items
		if ($key == 'more')
		{
			foreach ($li as $subkey => $subli)
				if (isset($subli['show']) && !$subli['show'])
					unset($list_items[$key][$subkey]);

			if (empty($list_items[$key]))
				unset($list_items[$key]);
		}
		// A normal list item
		elseif (isset($li['show']) && !$li['show'])
			unset($list_items[$key]);
	}
    unset($list_items['quickmod']);
	// Now check if there are any items left
	if (empty($list_items))
		return;

	// Print the quickbuttons
	$output = '<div class="dropdown d-inline-block">
	<a href="#" role="button" data-bs-toggle="dropdown" aria-expanded="true">
	<span class="main_icons horizontal"></span></a>
		<ul class="dropdown-menu quickbuttons' . (!empty($list_class) ? ' quickbuttons_' . $list_class : '') . '">';

	// This is used for a list item or a sublist item
	$list_item_format = function($li)
	{
		$html = '
			<li class="dropdown-item"' .(!empty($li['id']) ? ' id="' . $li['id'] . '"' : '') . (!empty($li['custom']) ? ' ' . $li['custom'] : '') . '>';

		if (isset($li['content']))
			$html .= $li['content'];
		else
			$html .= '
				<a href="' . (!empty($li['href']) ? $li['href'] : 'javascript:void(0);') . '"' . (!empty($li['javascript']) ? ' ' . $li['javascript'] : '') . '>
					' . (!empty($li['icon']) ? '<span class="main_icons ' . $li['icon'] . '"></span>' : '') . (!empty($li['label']) ? $li['label'] : '') . '
				</a>';

		$html .= '
			</li>';

		return $html;
	};
 
	foreach ($list_items as $key => $li)
	{
		// Handle the sublist
		if ($key == 'more')
		{
			$output .= '  ';

			foreach ($li as $subli)
				$output .= $list_item_format($subli);

			$output .= '
				 
			 ';
		}
		// Ordinary list item
		else
			$output .= $list_item_format($li);
	}

	$output .= '
		</ul></div><!-- .quickbuttons -->';

	// There are a few spots where the result needs to be returned
	if ($output_method == 'echo')
		echo $output;
	else
		return $output;
}

/**
 * The upper part of the maintenance warning box
 */
function template_maint_warning_above()
{
	global $txt, $context, $scripturl;

	echo '
	<div class="errorbox" id="errors">
		<dl>
			<dt>
				<strong id="error_serious">', $txt['forum_in_maintenance'], '</strong>
			</dt>
			<dd class="error" id="error_list">
				', sprintf($txt['maintenance_page'], $scripturl . '?action=admin;area=serversettings;' . $context['session_var'] . '=' . $context['session_id']), '
			</dd>
		</dl>
	</div>';
}

/**
 * The lower part of the maintenance warning box.
 */
function template_maint_warning_below()
{

}
function template_avatar($avatar ='', $size ='',$class= '',$return = false){

	if(empty($avatar))
	return false;
	
	$avatar = str_replace('>',' width="'.$size.'" height="'.$size.'">',$avatar); 
	$avatar = str_replace('avatar"',$class.' avatar"',$avatar); 

	if($return)
	return $avatar;

	 echo $avatar;
 
}
function template_widget($id,$title=''){
	echo'
	<div class="card mb-2 widget">
	 <div class="card-body">
	  <h3>',$title,'</h3>
	 ',template_loading($id,'',$class='mt-2'),'
	 </div>
	</div>';
}
function template_widget_tabs($id,$title='',$extra= array()){
	echo'
	<div class="card mb-2 widget">
	 <div class="card-body">
	  <h3 class="mb-2">',$title,'</h3> 
	 	<ul class="nav nav-tabs" id="',$extra['id'],'">'; 
			foreach ($extra['tabs'] as $tab) {
				echo'<li class="nav-item">
						<a class="tabNav nav-link active py-2 " aria-current="page" href="#" data-id="',$tab['id'],'">',$tab['name'],'</a>
					</li> 
				';
			}
		 echo' 
    	</ul>

	 ',template_loading($id,'',$class='mt-2'),'
	 </div>
	</div>';
}
function template_loading($id ='', $style= true,$class=''){
	echo'
	<div id="',$id,'" class="'.$class.' ',($style? 'scrollable':''),'" ',$style ? 'style="min-height:260px;min-width: 256px;"':'','>
		<div class="spinner-border" role="status"> 
		</div>
	</div> ';	
}
function spirate_youtube($string ='',$size='1', $class=''){

	if(empty($string))
	return false; 
  
	$str = preg_match_all(
		'/(\[youtube\]?)(.*?)(\[\/youtube\])/',
		$string,
		$matches 
	); 
	$match = $matches[2];
    
    if(!isset($match) || empty($match))
	  return false;
 
	$id_video = '';
	$fix = array_unique($match);
	$one = count($fix) == 1 ? $fix: count($fix);
 
	if(count($fix) == 1) 
		$id_video = implode('',$one); 
	else 
		$id_video = $fix[array_rand($fix)];
	 
    $thum = 'https://img.youtube.com/vi/'.$id_video.'/'.$size.'.jpg';
	 
	if(!preg_match('/^[a-zA-Z0-9-]+$/', $id_video))
	 return false;
	 
	return  '
	<a href="https://www.youtube.com/watch?v='.$id_video.'" class="yt-thumbnail '.$class.'">
	 <img src="'.$thum.'" alt=""/>  
	</a>'; 
}
function spirate_search_image($string ='',$size='6', $class=''){
 
	if(empty($string))
	return false; 
  
	$str = preg_match_all(
		'/(\[img\])(.+?)(\[\/img])/',
		$string,
		$matches 
	); 
 
	$match = $matches[2];
    
    if(!isset($match) || empty($match))
	  return false;
 
	$image = '';
	$fix = array_unique($match);
	$one = count($fix) == 1 ? $fix: count($fix);
 
	if(count($fix) == 1) 
		$image = implode('',$one); 
	else 
		$image = $fix[array_rand($fix)];
	 
    $thum = $image;
 
	 if(!preg_match('/\.(jpg|jpeg|gif|png)(\?.*)?$/', $thum))
	  return false;
	//ms-auto thumbnail d-block bg-cover

	if(empty($size))

	return '<img src="'.$thum.'"  class="img-fluid" >';

	return  '
	<a href="'.$thum.'" data-fancybox="images" class="bg-cover d-block '.$class.'" style="background-image:url('.$thum.');width:'.$size.'rem;height:'.$size.'rem"> 
	</a>'; 
}
function spirate_clear_preview($string, $limit = 0){
  $regex = '/(http|ftp|https):\/\/([\w_-]+(?:(?:\.[\w_-]+)+))([\w.,@?^=%&:\/~+#-]*[\w@?^=%&\/~+#-])|\[\/?.+?\]/';
  /**
   * find and remove any url links
   */
  $string  = strip_tags($string);
  $string = preg_replace($regex,' ',$string);
  $string = preg_replace('/<br>|&nbsp;/', ' ',trim($string));
   
  if(empty($string))
   return '';

   $moretext  = false;
  if($limit && strlen($string) > $limit){
    $string = substr($string, 0,  $limit).'...';
	$moretext = true;
  }


	return '<div class="text-wrap text-break '.($moretext ? 'moretext':'').'">'.$string.'</div>';
}
// Converts a number into a short version, eg: 1000 -> 1k
// Based on: http://stackoverflow.com/a/4371114
function spirate_short_number( $n, $precision = 1 ) {
	if ($n < 900) {
		// 0 - 900
		$n_format = number_format($n, $precision);
		$suffix = '';
	} else if ($n < 900000) {
		// 0.9k-850k
		$n_format = number_format($n / 1000, $precision);
		$suffix = 'K';
	} else if ($n < 900000000) {
		// 0.9m-850m
		$n_format = number_format($n / 1000000, $precision);
		$suffix = 'M';
	} else if ($n < 900000000000) {
		// 0.9b-850b
		$n_format = number_format($n / 1000000000, $precision);
		$suffix = 'B';
	} else {
		// 0.9t+
		$n_format = number_format($n / 1000000000000, $precision);
		$suffix = 'T';
	}

  // Remove unecessary zeroes after decimal. "1.0" -> "1"; "1.00" -> "1"
  // Intentionally does not affect partials, eg "1.50" -> "1.50"
	if ( $precision > 0 ) {
		$dotzero = '.' . str_repeat( '0', $precision );
		$n_format = str_replace( $dotzero, '', $n_format );
	}

	return $n_format . $suffix;
}
?>