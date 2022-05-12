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
 * This shows the popup that shows who likes a particular post.
 */
function template_popup()
{
	global $context, $settings, $txt, $modSettings;

	// Since this is a popup of its own we need to start the html, etc.
	echo '<!DOCTYPE html>
<html', $context['right_to_left'] ? ' dir="rtl"' : '', '>
	<head>
		<meta charset="', $context['character_set'], '">
		<meta name="robots" content="noindex">
		<title>', $context['page_title'], '</title>
		<link rel="stylesheet" href="', $settings['theme_url'], '/css/index', $context['theme_variant'], '.css', $context['browser_cache'], '">
		<script src="', $settings['default_theme_url'], '/scripts/script.js', $context['browser_cache'], '"></script>
	</head>
	<body id="likes_popup">
		<div class="windowbg">
			<ul id="likes">';

	foreach ($context['likers'] as $liker => $like_details)
		echo '
				<li>
					', $like_details['profile']['avatar']['image'], '
					<span class="like_profile">
						', $like_details['profile']['link_color'], '
						<span class="description">', $like_details['profile']['group'], '</span>
					</span>
					<span class="floatright like_time">', $like_details['time'], '</span>
				</li>';

	echo '
			</ul>
			<br class="clear">
			<a href="javascript:self.close();">', $txt['close_window'], '</a>
		</div><!-- .windowbg -->
	</body>
</html>';
}

/**
 * Display a like button and info about how many people liked something
 */
function template_like()
{
	global $context, $scripturl, $txt;
	header('Content-Type: application/json; charset=utf-8');

	$result['result'] = array(
		'data'=>array(
			'count'=> empty($context['data']['count'])? 0:$context['data']['count'] ,
			'can_like'=> $context['data']['can_like'],
			'already'=> $context['data']['already_liked']? '<span class="main_icons check"><span>':'',
		 
		),
		'success'=>true, 
	); 
	echo json_encode($result);
	 
}

/**
 * A generic template that outputs any data passed to it...
 */
function template_generic()
{
	global $context;
	header('Content-Type: application/json; charset=utf-8');
	
	$result['result'] = [
			'data' => $context['data'],
			'success' => false
	];
	echo json_encode($result);
}

?>