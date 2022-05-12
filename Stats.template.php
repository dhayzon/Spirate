<?php
/**
 * Simple Machines Forum (SMF)
 *
 * @package SMF
 * @author Simple Machines https://www.simplemachines.org
 * @copyright 2022 Simple Machines and individual contributors
 * @license https://www.simplemachines.org/about/smf/license.php BSD
 *
 * @version 2.1.1
 */

/**
 * The stats page.
 */
function template_main()
{
	global $context, $settings, $txt, $scripturl, $modSettings;
    $stats = array(
		'total_members'=> $context['show_member_list'] ? '<a href="' . $scripturl . '?action=mlist">' . $context['num_members'] . '</a>' : $context['num_members'],
		'total_posts'=>$context['num_posts'],
		'total_topics'=>$context['num_topics'],
		'total_cats'=>$context['num_categories'] ,
		'users_online'=>$context['users_online'] ,
		'most_online'=>  $context['most_members_online']['number'].' - '. $context['most_members_online']['date'],
		'users_online_today'=> $context['online_today'],
		'average_members'=> $context['average_members'],
		'average_posts'=> $context['average_posts'],
		'average_topics'=> $context['average_topics'],
		'total_boards'=> $context['num_boards'] ,
		'latest_member'=> $context['common_stats']['latest_member']['link'],
		'average_online'=>  $context['average_online'], 

	);
	if (!empty($modSettings['hitStats']))
			$stats['num_hits'] = $context['num_hits'];

	if (!empty($modSettings['hitStats']))
			$stats['average_hits']  =$context['average_hits'];

	echo '
	<div id="statistics" class="main_section">
		<div class="cat_bar">
			<h3 class="catbg">', $context['page_title'], '</h3>
		</div>
		<div class="roundframe">
			<div class="title_bar mb-3">
				<h2 class="titlebg">
					<span class="main_icons stats"></span> ', $txt['general_stats'], '
				</h2>
			</div>
			<div class="row">'; 

			foreach ($stats as $key => $name) {
				echo'
				<div class="col-6 col-md-3">
					<div class="card p-3 mb-2">
					',$name,' 
					<small>',$txt[$key],'</small>
					</div>
				</div>';
			}
	 
			if (!empty($context['gender']))
			{
				echo '
						', $txt['gender_stats'], ':
						';
		
				foreach ($context['gender'] as $g => $n)
					echo tokenTxtReplace($g), ': ', $n, '<br>';
		
				echo '
						';
			}
		

	echo '
			</div>
	<div class="row">';

	foreach ($context['stats_blocks'] as $name => $block)
	{
		echo '
			<div class="col-12 col-md-6 ">
			<div class="card shadow p-3 mb-2">
				<div class="mb-2">
					<h4 class="titlebg">
						<span class="main_icons ', $name, '"></span> ', $txt['top_' . $name], '
					</h4>
				</div>
				<div class="stats">';

		foreach ($block as $item)
		{
			echo '
			<div class="d-flex">
					<div class="text-truncate" style="width: 250px;">	', $item['link'], '</div>
					
					<div class="ms-auto">'; 
			echo '
					<span>', $item['num'], '</span>
					</div>
			</div>
			';
				if (!empty($item['percent']))
					echo '
							<div class="progress" style="height: 4px;"> <div style="width: ', $item['percent'], '%;" class="progress-bar" role="progressbar" aria-valuenow="', $item['percent'], '" aria-valuemin="0" aria-valuemax="100"></div>
							</div>';
				else
					echo '
							<div class="progress empty"></div>';
		

		}

		echo '</div>
				</div>
			</div><!-- .half_content -->';
	}

	echo '</div>
		</div><!-- .roundframe -->
		<br class="clear">
		<div class="cat_bar">
			<h3 class="catbg">
				<span class="main_icons history"></span>', $txt['forum_history'], '
			</h3>
		</div>';

	if (!empty($context['yearly']))
	{
		echo '
		<table id="stats" class="table_grid">
			<thead>
				<tr class="title_bar">
					<th class="lefttext">', $txt['yearly_summary'], '</th>
					<th>', $txt['stats_new_topics'], '</th>
					<th>', $txt['stats_new_posts'], '</th>
					<th>', $txt['stats_new_members'], '</th>
					<th>', $txt['most_online'], '</th>';

		if (!empty($modSettings['hitStats']))
			echo '
					<th>', $txt['page_views'], '</th>';

		echo '
				</tr>
			</thead>
			<tbody>';

		foreach ($context['yearly'] as $id => $year)
		{
			echo '
				<tr class="windowbg" id="year_', $id, '">
					<th class="lefttext">
						<img id="year_img_', $id, '" src="', $settings['images_url'], '/selected_open.png" alt="*"> <a href="#year_', $id, '" id="year_link_', $id, '">', $year['year'], '</a>
					</th>
					<th>', $year['new_topics'], '</th>
					<th>', $year['new_posts'], '</th>
					<th>', $year['new_members'], '</th>
					<th>', $year['most_members_online'], '</th>';

			if (!empty($modSettings['hitStats']))
				echo '
					<th>', $year['hits'], '</th>';

			echo '
				</tr>';

			foreach ($year['months'] as $month)
			{
				echo '
				<tr class="windowbg" id="tr_month_', $month['id'], '">
					<th class="stats_month">
						<img src="', $settings['images_url'], '/', $month['expanded'] ? 'selected_open.png' : 'selected.png', '" alt="" id="img_', $month['id'], '"> <a id="m', $month['id'], '" href="', $month['href'], '" onclick="return doingExpandCollapse;">', $month['month'], ' ', $month['year'], '</a>
					</th>
					<th>', $month['new_topics'], '</th>
					<th>', $month['new_posts'], '</th>
					<th>', $month['new_members'], '</th>
					<th>', $month['most_members_online'], '</th>';

				if (!empty($modSettings['hitStats']))
					echo '
					<th>', $month['hits'], '</th>';

				echo '
				</tr>';

				if ($month['expanded'])
				{
					foreach ($month['days'] as $day)
					{
						echo '
				<tr class="windowbg" id="tr_day_', $day['year'], '-', $day['month'], '-', $day['day'], '">
					<td class="stats_day">', $day['year'], '-', $day['month'], '-', $day['day'], '</td>
					<td>', $day['new_topics'], '</td>
					<td>', $day['new_posts'], '</td>
					<td>', $day['new_members'], '</td>
					<td>', $day['most_members_online'], '</td>';

						if (!empty($modSettings['hitStats']))
							echo '
					<td>', $day['hits'], '</td>';

						echo '
				</tr>';
					}
				}
			}
		}

		echo '
			</tbody>
		</table>
	</div><!-- #statistics -->
	<script>
		var oStatsCenter = new smf_StatsCenter({
			sTableId: \'stats\',

			reYearPattern: /year_(\d+)/,
			sYearImageCollapsed: \'selected.png\',
			sYearImageExpanded: \'selected_open.png\',
			sYearImageIdPrefix: \'year_img_\',
			sYearLinkIdPrefix: \'year_link_\',

			reMonthPattern: /tr_month_(\d+)/,
			sMonthImageCollapsed: \'selected.png\',
			sMonthImageExpanded: \'selected_open.png\',
			sMonthImageIdPrefix: \'img_\',
			sMonthLinkIdPrefix: \'m\',

			reDayPattern: /tr_day_(\d+-\d+-\d+)/,
			sDayRowClassname: \'windowbg\',
			sDayRowIdPrefix: \'tr_day_\',

			aCollapsedYears: [';

		foreach ($context['collapsed_years'] as $id => $year)
		{
			echo '
				\'', $year, '\'', $id != count($context['collapsed_years']) - 1 ? ',' : '';
		}

		echo '
			],

			aDataCells: [
				\'date\',
				\'new_topics\',
				\'new_posts\',
				\'new_members\',
				\'most_members_online\'', empty($modSettings['hitStats']) ? '' : ',
				\'hits\'', '
			]
		});
	</script>';
	}
}

?>