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
 * This contains the HTML for the menu bar at the top of the admin center.
 */
function template_generic_menu_dropdown_above()
{
	global $context, $txt;
 	// Which menu are we rendering?
	$context['cur_menu_id'] = isset($context['cur_menu_id']) ? $context['cur_menu_id'] + 1 : 1;
	$menu_context = &$context['menu_data_' . $context['cur_menu_id']];
	$menu_label = isset($context['admin_menu_name']) ? $txt['admin_center'] : (isset($context['moderation_menu_name']) ? $txt['moderation_center'] : '');
 
	echo'
	<a id="menuMounstro" href="#" class="mt-3 sticky-top btn btn-primary mb-1 d-md-none d-block">',$txt['admin_center'] ,'</a>';
	// Load the menu
	// Add mobile menu as well
	echo '
	<div class="row"> 	 
	<div class="offcanvas offcanvas-start p-0" data-bs-scroll="true" tabindex="-1" id="mobileMenu" aria-labelledby="mobileMenuLabel">
		<div class="offcanvas-header">
		<h5 class="offcanvas-title" id="offcanvasScrollingLabel">',$txt['admin_center'] ,'</h5>
		<button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
		</div>
		<div class="offcanvas-body"></div>
	</div>
 	<div id="admin_sidebar" class="col-12 col-md-3 d-none d-md-block"> 
	<div id="genericmenu"  class="sticky-top">
		<div id="menu_', $context['cur_menu_id'], '" > 
				', template_generic_menu($menu_context), ' 
		</div>
	</div> ';

	// This is the main table - we need it so we can keep the content to the right of it.
	echo '</div>
	<div class="col-md-9">
				<div id="admin_content">';

	// It's possible that some pages have their own tabs they wanna force...
	if (!empty($context['tabs']))
	template_generic_menu_tabs($menu_context);
}

/**
 * Part of the admin layer - used with generic_menu_dropdown_above to close the admin content div.
 */
function template_generic_menu_dropdown_below()
{
	echo '</div>
	</div>
</div><!-- #admin_content -->';
}

/**
 * The template for displaying a menu
 *
 * @param array $menu_context An array of menu information
 */
function template_generic_menu(&$menu_context)
{
	global $context,$txt;

	echo '
				<div class="generic_menu">
					<div  id="spirit_nav_', $context['cur_menu_id'], '">';
 
	// Main areas first.
	foreach ($menu_context['sections'] as $key => $section)
	{
	 
	 
		echo '
		<div class="card mb-2"> 
		<div class="card-header accordion-button ', !empty($section['areas']) ? 'subsections' : '', '"  data-bs-toggle="collapse" data-bs-target="#spirate_menu_',$key,'" aria-expanded="true" aria-controls="spirate_menu_',$key,'">
		<a href="', $section['url'], $menu_context['extra_parameters'], ' ">
		', $section['title'], !empty($section['amt']) ? ' <span class="amt">' . $section['amt'] . '</span>' : '', '</a>
		</div>
		<div id="spirate_menu_',$key,'" class="pt-0 card-body accordion-collapse collapse  ', !empty($section['selected']) ? 'show ' : '', '">';

		// For every area of this section show a link to that area (bold if it's currently selected.)
		// @todo Code for additional_items class was deprecated and has been removed. Suggest following up in Sources if required.
		$oneLevel = [];
		
		foreach ($section['areas'] as $i => $area)
		{
			// Not supposed to be printed?
			if (empty($area['label']))
				continue;
			if (empty($area['subsections']))
			$oneLevel[]= '<a class="d-block dropdown-header px-0" href="'. (isset($area['url']) ? $area['url'] : $menu_context['base_url'] . ';area=' . $i). ''. $menu_context['extra_parameters']. '">
			 '.$area['icon'].' '. $area['label'].' '.(!empty($area['amt']) ? ' <span class="amt">' . $area['amt'] . '</span>' : '') .'</a>';

			 // Is this the current area, or just some area?
			if (!empty($area['selected']) && empty($context['tabs']))
			$context['tabs'] = isset($area['subsections']) ? $area['subsections'] : array();

	 
		 
			if (!empty($area['subsections']) && empty($area['hide_subsections']))
			{
				echo'
				<div  class="list-group-item" id="parent_menu_',$i,'">
				<a href="#" class="accordion-button collapsed dropdown-header px-0" data-bs-toggle="collapse" data-bs-target="#spirate_menu__',$i,'" aria-expanded="true" aria-controls="collapse',$i,'">', $area['icon'], $area['label'], !empty($area['amt']) ? ' <span class="amt">' . $area['amt'] . '</span>' : '', '</a>
				<div  id="spirate_menu__',$i,'"  class="accordion-collapse collapse ',!empty($area['selected'])? 'show':'','" data-bs-parent="#parent_menu_',$i,'">';
				foreach ($area['subsections'] as $sa => $sub)
				{
					if (!empty($sub['disabled']))
						continue;

					$url = isset($sub['url']) ? $sub['url'] : (isset($area['url']) ? $area['url'] : $menu_context['base_url'] . ';area=' . $i) . ';sa=' . $sa;

					echo '
										
											<a class="', !empty($sub['selected']) ? 'active ' : '', ' dropdown-item"  href="', $url, $menu_context['extra_parameters'], '">', $sub['label'], !empty($sub['amt']) ? ' <span class="amt">' . $sub['amt'] . '</span>' : '', '</a> 
										';
				} 
				echo'</div>
			</div>';
			}

		}

		if(isset($oneLevel) && !empty($oneLevel))
		echo implode('', $oneLevel);

		echo '		
				</div>
			</div>';
	}

	echo '
					</div><!-- .dropmenu -->
				</div><!-- .generic_menu -->';
}

/**
 * The code for displaying the menu
 *
 * @param array $menu_context An array of menu context data
 */
function template_generic_menu_tabs(&$menu_context)
{
	global $context, $settings, $scripturl, $txt;

	// Handy shortcut.
	$tab_context = &$menu_context['tab_data'];

	if (!empty($tab_context['title']))
	{
		echo '
					<div class="cat_bar">';

		// The function is in Admin.template.php, but since this template is used elsewhere too better check if the function is available
		if (function_exists('template_admin_quick_search'))
			template_admin_quick_search();

		echo '
						<h3 class="catbg">';

		// Exactly how many tabs do we have?
		if (!empty($context['tabs']))
		{
			foreach ($context['tabs'] as $id => $tab)
			{
				// Can this not be accessed?
				if (!empty($tab['disabled']))
				{
					$tab_context['tabs'][$id]['disabled'] = true;
					continue;
				}

				// Did this not even exist - or do we not have a label?
				if (!isset($tab_context['tabs'][$id]))
					$tab_context['tabs'][$id] = array('label' => $tab['label']);
				elseif (!isset($tab_context['tabs'][$id]['label']))
					$tab_context['tabs'][$id]['label'] = $tab['label'];

				// Has a custom URL defined in the main admin structure?
				if (isset($tab['url']) && !isset($tab_context['tabs'][$id]['url']))
					$tab_context['tabs'][$id]['url'] = $tab['url'];

				// Any additional parameters for the url?
				if (isset($tab['add_params']) && !isset($tab_context['tabs'][$id]['add_params']))
					$tab_context['tabs'][$id]['add_params'] = $tab['add_params'];

				// Has it been deemed selected?
				if (!empty($tab['is_selected']))
					$tab_context['tabs'][$id]['is_selected'] = true;

				// Does it have its own help?
				if (!empty($tab['help']))
					$tab_context['tabs'][$id]['help'] = $tab['help'];

				// Is this the last one?
				if (!empty($tab['is_last']) && !isset($tab_context['override_last']))
					$tab_context['tabs'][$id]['is_last'] = true;
			}

			// Find the selected tab
			foreach ($tab_context['tabs'] as $sa => $tab)
			{
				if (!empty($tab['is_selected']) || (isset($menu_context['current_subsection']) && $menu_context['current_subsection'] == $sa))
				{
					$selected_tab = $tab;
					$tab_context['tabs'][$sa]['is_selected'] = true;
				}
			}
		}

		// Show an icon and/or a help item?
		if (!empty($selected_tab['icon_class']) || !empty($tab_context['icon_class']) || !empty($selected_tab['icon']) || !empty($tab_context['icon']) || !empty($selected_tab['help']) || !empty($tab_context['help']))
		{
			if (!empty($selected_tab['icon_class']) || !empty($tab_context['icon_class']))
				echo '
								<span class="', !empty($selected_tab['icon_class']) ? $selected_tab['icon_class'] : $tab_context['icon_class'], ' icon"></span>';
			elseif (!empty($selected_tab['icon']) || !empty($tab_context['icon']))
				echo '
								<img src="', $settings['images_url'], '/icons/', !empty($selected_tab['icon']) ? $selected_tab['icon'] : $tab_context['icon'], '" alt="" class="icon">';

			if (!empty($selected_tab['help']) || !empty($tab_context['help']))
				echo '
								<a href="', $scripturl, '?action=helpadmin;help=', !empty($selected_tab['help']) ? $selected_tab['help'] : $tab_context['help'], '" onclick="return reqOverlayDiv(this.href);" class="help"><span class="main_icons help" title="', $txt['help'], '"></span></a>';

			echo $tab_context['title'];
		}
		else
			echo '
								', $tab_context['title'];

		echo '
						</h3>
					</div><!-- .cat_bar -->';
	}

	// Shall we use the tabs? Yes, it's the only known way!
	if (!empty($selected_tab['description']) || !empty($tab_context['description']))
		echo '
					<p class="information">
						', !empty($selected_tab['description']) ? $selected_tab['description'] : $tab_context['description'], '
					</p>';

	// Print out all the items in this tab (if any).
	if (!empty($context['tabs']))
	{
		// The admin tabs.
		echo '
					 
					<div id="adm_submenus">
						<div id="menu_', $context['cur_menu_id'], '_tabs">  
								 ';

		echo '
								<div class="generic_menu">
									<ul class="nav nav-pills p-2 simple_', $context['cur_menu_id'], '_tabs">';

		foreach ($tab_context['tabs'] as $sa => $tab)
		{
			if (!empty($tab['disabled']))
				continue;

			if (!empty($tab['is_selected']))
				echo '
										<li class="nav-item">
											<a class="nav-link active" href="', isset($tab['url']) ? $tab['url'] : $menu_context['base_url'] . ';area=' . $menu_context['current_area'] . ';sa=' . $sa, $menu_context['extra_parameters'], isset($tab['add_params']) ? $tab['add_params'] : '', '">', $tab['label'], '</a>
										</li>';
			else
				echo '
										<li class="nav-item">
											<a class="nav-link"  href="', isset($tab['url']) ? $tab['url'] : $menu_context['base_url'] . ';area=' . $menu_context['current_area'] . ';sa=' . $sa, $menu_context['extra_parameters'], isset($tab['add_params']) ? $tab['add_params'] : '', '">', $tab['label'], '</a>
										</li>';
		}

		// The end of tabs
		echo '
									</ul> 
							</div>
						</div>
					</div><!-- #adm_submenus -->
					 ';
	}
}

?>