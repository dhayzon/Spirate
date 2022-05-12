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
 * This template handles displaying a topic
 */
function template_main()
{
	global $context, $settings, $options, $txt, $scripturl, $modSettings,$board_info;
	template_display_headers();	

	$is_firstPage = empty($context['start'])? true:false;
 
	// Show the page index... "Pages: [1]".
	echo '
		<div class="pagesection mb-2 d-flex">  
			<div class="pagelinks"> 
				', $context['page_index'], '
			</div>
			', template_button_strip($context['normal_buttons'], ' dropdown-menu-end',['class'=>'ms-auto']), ''; 
			if (!empty($context['mod_buttons']))
		  echo  template_button_strip($context['mod_buttons'], '  dropdown-menu-end', array('class'=>'ms-2','id' => 'moderationbuttons_strip_mobile'));
	echo '
		</div>';


	
	if(empty($context['start']))
	echo'<div class="row"><div class="col-12 col-md-8">';


	// Show the topic information - icon, subject, etc.
	echo '
		<div id="forumposts" ',!$is_firstPage? 'class="card p-3"':'','>
			<form action="', $scripturl, '?action=quickmod2;topic=', $context['current_topic'], '.', $context['start'], '" method="post" accept-charset="', $context['character_set'], '" name="quickModForm" id="quickModForm" onsubmit="return oQuickModify.bInEditMode ? oQuickModify.modifySave(\'' . $context['session_id'] . '\', \'' . $context['session_var'] . '\') : false">';

	$context['ignoredMsgs'] = array();
	$context['removableMessageIDs'] = array();
	$context['spirate_board_info'] = $board_info;
	$couter = 0;
 
	// Get all the messages...
	while ($message = $context['get_message']()){
	 
		if($couter == 1 && $is_firstPage)
		echo'<div class="allComments card p-3">';
		
		template_single_post($message); 
		if(($couter == ( $context['messages_per_page']-1) && $is_firstPage) || ($context['real_num_replies']  ==  $couter && $context['real_num_replies']  != 0))
		echo'</div>';

		$couter++;
	}
 

	echo '
			</form>
		</div><!-- #forumposts -->';
		template_display_footers();
	if(empty($context['start'])){
		echo'</div>
		<div class="col-12 col-md-4">
		<div class="sticky-top">
		', template_display_board(),' 
		 ', template_button_strip($context['normal_buttons'],' show position-relative mb-2  w-100',['hidde'=>true]), '';

		 if (!empty($context['mod_buttons']))
		  echo  template_button_strip($context['mod_buttons'], 'show position-relative  w-100 ', array('id' => 'moderationbuttons_strip','hidde'=>true));
			// Moderation buttons
 
		echo'</div>
		 </div>
		</div>	';
	}



}

/**
 * Template for displaying a single post.
 *
 * @param array $message An array of information about the message to display. Should have 'id' and 'member'. Can also have 'first_new', 'is_ignored' and 'css_class'.
 */
function template_single_post($message)
{
	global $context, $settings, $options, $txt, $scripturl, $modSettings;
	$is_comment = $context['topic_first_message'] !== $message['id'];
	$context['is_comment'] = $is_comment;
	$ignoring = false;

	if ($message['can_remove'])
		$context['removableMessageIDs'][] = $message['id'];

	// Are we ignoring this message?
	if (!empty($message['is_ignored']))
	{
		$ignoring = true;
		$context['ignoredMsgs'][] = $message['id'];
	}
	echo'
	<div  id="msg' . $message['id'] . '"  class="',$is_comment? 'comment':'topic','">
		<div class="position-relative mb-2 ',!$is_comment? ' flex-row card mb-3':'','">';	
	 if(!$is_comment) {
		template_spirate_likes($message);
 
	 }
	 if(!$is_comment) 
		echo'<div class="w-100 p-2">';
	// Show the poster itself, finally!
							echo'
							<div class="post-poster"> 
								<div class="small">';
								if(!$is_comment){
																			// Show the user's avatar.
								if (!empty($modSettings['show_user_images']) && empty($options['show_no_avatars']) && !empty($message['member']['avatar']['image']))
								echo  template_avatar( $message['member']['avatar']['image'],'26','rounded-circle me-2');

									echo'<strong><a href="',$scripturl,'?board=',$context['spirate_board_info']['id'],'">b/',$context['spirate_board_info']['name'],'</a></strong> publicado por <a href="#" data-member="',$message['member']['id'],'">u/',$message['member']['name'],'</a>';
								}else{
										// Show the user's avatar.
								if (!empty($modSettings['show_user_images']) && empty($options['show_no_avatars']) && !empty($message['member']['avatar']['image']))
										echo  template_avatar( $message['member']['avatar']['image'],'26','rounded-circle me-2');

									echo'<a href="#" data-member="',$message['member']['id'],'">u/',$message['member']['name'],'</a> ';
								}			 
								echo'<span class="text-muted"> ', $message['time'], '</span>
								',template_quickbuttons($message['quickbuttons'], 'post'),'
								<div class="d-inline-block"  id="in_topic_mod_check_',$message['id'],'" style="display: none;"></div>
								</div>
							</div>';
	// Some people don't want subject... The div is still required or quick edit breaks.
	
	echo '			',$is_comment?'<div class="vr"></div>':'','
							<div class="post-content ',$is_comment?'ms-4':'','">
								<div id="subject_', $message['id'], '" class="my-2 subject_title', (empty($modSettings['subject_toggle']) ? ' subject_hidden' : ''), '" ',!$is_comment?'':'style="display:none"','>
									<h2>', $message['link'], '</h2>
								</div>';
	// Show the post itself, finally!
	echo ' 					<div class="post"> 
								<div class="inner ',$is_comment?'pt-2':'','" data-msgid="', $message['id'], '" id="msg_', $message['id'], '"', $ignoring ? ' style="display:none;"' : '', '>
									', $message['body'], '
								</div>
							</div>';

	// Show "<< Last Edit: Time by Person >>" if this post was edited. But we need the div even if it wasn't modified!
	// Because we insert into it through AJAX and we don't want to stop themers moving it around if they so wish so they can put it where they want it.
	
	echo ' 
									<span class="smalltext modified', !empty($modSettings['show_modify']) && !empty($message['modified']['name']) ? ' mvisible' : '', '" id="modified_', $message['id'], '">';

	if (!empty($modSettings['show_modify']) && !empty($message['modified']['name']))
		echo
										$message['modified']['last_edit_text'];

	echo '
									</span>';				
	// Show the post itself, finally!
	echo'
							<div class="post-footer">
								<div class="d-flex align-items-center my-2 text-muted">
								';
								if(!$is_comment){
									echo '<span class="main_icons message h2"></span>',$context['num_replies'],' ',$txt['replies'],' <span class="main_icons eye ms-2 h2"></span>',$context['num_replies'],' ',$txt['views'],' '; 
								}else{
									template_spirate_likes($message); 
								}
								
	echo'								
								</div>
							</div><!--end post-footer-->
							</div>
							';		
							if(!$is_comment)
							template_display_login();
	if(!$is_comment) 
		echo'</div>';
	 					
	echo '   
				</div><!-- $message[css_class] -->
	</div>		 ';

	

}

/**
 * The template for displaying the quick reply box.
 */
function template_quickreply()
{
	global $context, $modSettings, $scripturl, $options, $txt;
		

	echo '
		<a id="quickreply_anchor"></a>
		<div class="tborder" id="quickreply"> 
			<div id="quickreply_options"> ';

	// Is the topic locked?
	if ($context['is_locked'])
		echo '
					<p class="alert alert-danger">', $txt['quick_reply_warning'], '</p>';

	// Show a warning if the topic is old
	if (!empty($context['oldTopicError']))
		echo '
					<p class="alert alert-danger">', sprintf($txt['error_old_topic'], $modSettings['oldTopicDays']), '</p>';

	// Does the post need approval?
	if (!$context['can_reply_approved'])
		echo '
					<p><em>', $txt['wait_for_approval'], '</em></p>';

	echo '
					<form action="', $scripturl, '?board=', $context['current_board'], ';action=post2" method="post" accept-charset="', $context['character_set'], '" name="postmodify" id="postmodify" onsubmit="submitonce(this);">
						<input type="hidden" name="topic" value="', $context['current_topic'], '">
						<input type="hidden" name="subject" value="', $context['response_prefix'], $context['subject'], '">
						<input type="hidden" name="icon" value="xx">
						<input type="hidden" name="from_qr" value="1">
						<input type="hidden" name="notify" value="', $context['is_marked_notify'] || !empty($options['auto_notify']) ? '1' : '0', '">
						<input type="hidden" name="not_approved" value="', !$context['can_reply_approved'], '">
						<input type="hidden" name="goback" value="', empty($options['return_to_post']) ? '0' : '1', '">
						<input type="hidden" name="last_msg" value="', $context['topic_last_message'], '">
						<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '">
						<input type="hidden" name="seqnum" value="', $context['form_sequence_number'], '">';

	// Guests just need more.
	if ($context['user']['is_guest'])
	{
		echo '
						<dl id="post_header">
							<dt>
								', $txt['name'], ':
							</dt>
							<dd>
								<input type="text" name="guestname" size="25" value="', $context['name'], '" tabindex="', $context['tabindex']++, '" required>
							</dd>';

		if (empty($modSettings['guest_post_no_email']))
		{
			echo '
							<dt>
								', $txt['email'], ':
							</dt>
							<dd>
								<input type="email" name="email" size="25" value="', $context['email'], '" tabindex="', $context['tabindex']++, '" required>
							</dd>';
		}

		echo '
						</dl>';
	}

	echo '
						', template_control_richedit($context['post_box_name'], 'smileyBox_message', 'bbcBox_message'), '
						<script>
							function insertQuoteFast(messageid)
							{
								var e = document.getElementById("', $context['post_box_name'], '");
								sceditor.instance(e).insertQuoteFast(messageid);

								return false;
							}
						</script>';

	// Is visual verification enabled?
	if ($context['require_verification'])
		echo '
						<div class="post_verification">
							<strong>', $txt['verification'], ':</strong>
							', template_control_verification($context['visual_verification_id'], 'all'), '
						</div>';

	// Finally, the submit buttons.
	echo '
						<span id="post_confirm_buttons">
							', template_control_richedit_buttons($context['post_box_name']), '
						</span>';
	echo '
					</form> 
			</div><!-- #quickreply_options -->
		</div><!-- #quickreply -->
		<br class="clear">';

	// Draft autosave available and the user has it enabled?
	if (!empty($context['drafts_autosave']))
		echo '
		<script>
			var oDraftAutoSave = new smf_DraftAutoSave({
				sSelf: \'oDraftAutoSave\',
				sLastNote: \'draft_lastautosave\',
				sLastID: \'id_draft\',', !empty($context['post_box_name']) ? '
				sSceditorID: \'' . $context['post_box_name'] . '\',' : '', '
				sType: \'', 'quick', '\',
				iBoard: ', (empty($context['current_board']) ? 0 : $context['current_board']), ',
				iFreq: ', (empty($modSettings['masterAutoSaveDraftsDelay']) ? 60000 : $modSettings['masterAutoSaveDraftsDelay'] * 1000), '
			});
		</script>';

	if ($context['show_spellchecking'])
		echo '
		<form action="', $scripturl, '?action=spellcheck" method="post" accept-charset="', $context['character_set'], '" name="spell_form" id="spell_form" target="spellWindow">
			<input type="hidden" name="spellstring" value="">
		</form>';

	echo '
		<script>
			var oQuickReply = new QuickReply({
				bDefaultCollapsed: false,
				iTopicId: ', $context['current_topic'], ',
				iStart: ', $context['start'], ',
				sScriptUrl: smf_scripturl,
				sImagesUrl: smf_images_url,
				sContainerId: "quickreply_options",
				sImageId: "quickReplyExpand",
				sClassCollapsed: "toggle_up",
				sClassExpanded: "toggle_down",
				sJumpAnchor: "quickreply_anchor",
				bIsFull: true
			});
			var oEditorID = "', $context['post_box_name'], '";
			var oEditorObject = oEditorHandle_', $context['post_box_name'], ';
			var oJumpAnchor = "quickreply_anchor";
		</script>';
}
/**
 * @method  
 */
function template_spirate_likes($message){
	global $context,$modSettings, $scripturl,$txt;
	$ignoring = false; 
	$is_comment = $context['is_comment'];
	// Are we ignoring this message?
	if (!empty($message['is_ignored']))
	 $ignoring = true;  
	
	$is_autor = $message['member']['id'] == $context['user']['id'];

	$likeHref = $scripturl.'?action=likes;ltype=msg;sa=like;like='.$message['id'].';'.$context['session_var'].'='.$context['session_id'];
	//	
		// What about likes?
		if (!empty($modSettings['enable_likes']))
		{ 
			//'.$scripturl.'?action=likes;ltype=msg;sa=like;like='.$message['id'].';'.$context['session_var'].'='.$context['session_id'].'
			echo'
			<div class="bg-secondary align-items-center smflikebutton p-2  text-center ',$is_comment?'d-md-flex':'d-md-block','" id="msg_', $message['id'], '_likes">
				<div class="',!$is_comment?'sticky-top postlike':'d-flex align-items-center','">
				<a href="',($is_autor ? '#':$likeHref ),'" class="',$is_autor? '':'msg_like',' d-block   ',!$is_comment?'h2':'h4','">
				<span class="main_icons ',!$is_comment?'trending-up':'like','"></span>
				</a>
				<span class="counter">',$message['likes']['count'],'</span>  
				',$message['likes']['you']? '<span class="main_icons check"></span>  ':'' ,'
				</div>
			</div>
			';
	 
		}
}
function template_display_headers(){
	global $context, $settings, $options, $txt, $scripturl, $modSettings,$board_info;
	echo'<div class="mb-2">';
	// Let them know, if their report was a success!
	if ($context['report_sent'])
		echo '
		<div class="alert alert-danger">
			', $txt['report_sent'], '
		</div>';

		// Let them know why their message became unapproved.
	if ($context['becomesUnapproved'])
		echo '
		<div class="alert alert-warning">
			', $txt['post_becomes_unapproved'], '
		</div>';

	// Show new topic info here?
	echo '
	<div id="display_head" class="card">
		<div class="card-body">
		<h2 class="display_title"> 
		', ($context['is_sticky']) ? ' <span class="main_icons sticky text-warning"></span>' : '', '
		', ($context['is_locked']) ? ' <span class="main_icons lock float-end text-danger"></span>' : '','
			<a id="top_subject" href="',$scripturl,'?topic=',$context['current_topic'],'">', $context['subject'], '</a> 
		</h2>';
	if($context['start'])
		echo'<p><a href="',$scripturl,'?board=',$board_info['id'],'">b/',$board_info['name'],'</a> ', $txt['started_by'], ' ', $context['topic_poster_name'], ', ', $context['topic_started_time'], '</p>';

	// Next - Prev
	echo '', ($context['is_locked']) ? ' <span class="badge bg-danger" style="--smf-bg-opacity:1">'.$txt['locked_topic'].'</span>' : '', '
		<span>', $context['previous_next'], '</span>';

	if (!empty($settings['display_who_viewing']))
	{
	echo '
		<p>';

	// Show just numbers...?
	if ($settings['display_who_viewing'] == 1)
		echo count($context['view_members']), ' ', count($context['view_members']) == 1 ? $txt['who_member'] : $txt['members'];
	// Or show the actual people viewing the topic?
	else
		echo empty($context['view_members_list']) ? '0 ' . $txt['members'] : implode(', ', $context['view_members_list']) . ((empty($context['view_num_hidden']) || $context['can_moderate_forum']) ? '' : ' (+ ' . $context['view_num_hidden'] . ' ' . $txt['hidden'] . ')');

	// Now show how many guests are here too.
	echo $txt['who_and'], $context['view_num_guests'], ' ', $context['view_num_guests'] == 1 ? $txt['guest'] : $txt['guests'], $txt['who_viewing_topic'], '
		</p>';
	}

	// Show the anchor for the top and for the first message. If the first message is new, say so.
	echo '</div>
	</div><!-- #display_head -->
	', $context['first_new_message'] ? '<a id="new"></a>' : '';

	echo'</div>';
}
function template_display_footers(){
	global $context, $settings, $options, $txt, $scripturl, $modSettings;
	

	// Show the page index... "Pages: [1]".
	echo '
		<div class="pagesection d-flex my-2">  
			<div class="pagelinks"> 
				', $context['page_index'], '
			</div> 
		</div>';
 

	// Show quickreply
	if ($context['can_reply'])
		template_quickreply();

	// Show the lower breadcrumbs.
	theme_linktree();
 

	echo '
		<script>';

	if (!empty($options['display_quick_mod']) && $options['display_quick_mod'] == 1 && $context['can_remove_post'])
	{
		echo '
			var oInTopicModeration = new InTopicModeration({
				sSelf: \'oInTopicModeration\',
				sCheckboxContainerMask: \'in_topic_mod_check_\',
				aMessageIds: [\'', implode('\', \'', $context['removableMessageIDs']), '\'],
				sSessionId: smf_session_id,
				sSessionVar: smf_session_var,
				sButtonStrip: \'moderationbuttons\',
				sButtonStripDisplay: \'moderationbuttons_strip\',
				bUseImageButton: false,
				bCanRemove: ', $context['can_remove_post'] ? 'true' : 'false', ',
				sRemoveButtonLabel: \'', $txt['quickmod_delete_selected'], '\',
				sRemoveButtonImage: \'delete_selected.png\',
				sRemoveButtonConfirm: \'', $txt['quickmod_confirm'], '\',
				bCanRestore: ', $context['can_restore_msg'] ? 'true' : 'false', ',
				sRestoreButtonLabel: \'', $txt['quick_mod_restore'], '\',
				sRestoreButtonImage: \'restore_selected.png\',
				sRestoreButtonConfirm: \'', $txt['quickmod_confirm'], '\',
				bCanSplit: ', $context['can_split'] ? 'true' : 'false', ',
				sSplitButtonLabel: \'', $txt['quickmod_split_selected'], '\',
				sSplitButtonImage: \'split_selected.png\',
				sSplitButtonConfirm: \'', $txt['quickmod_confirm'], '\',
				sFormId: \'quickModForm\'
			});';

	 
	}

	echo '
			if (\'XMLHttpRequest\' in window)
			{
				var oQuickModify = new QuickModify({
					sScriptUrl: smf_scripturl,
					sClassName: \'quick_edit\',
					bShowModify: ', $modSettings['show_modify'] ? 'true' : 'false', ',
					iTopicId: ', $context['current_topic'], ',
					sTemplateBodyEdit: ', JavaScriptEscape('
						<div id="quick_edit_body_container">
							<div id="error_box" class="error"></div>
							<textarea class="editor" name="message" rows="12" tabindex="' . $context['tabindex']++ . '">%body%</textarea><br>
							<input type="hidden" name="' . $context['session_var'] . '" value="' . $context['session_id'] . '">
							<input type="hidden" name="topic" value="' . $context['current_topic'] . '">
							<input type="hidden" name="msg" value="%msg_id%">
							<div class="righttext quickModifyMargin">
								<input type="submit" name="post" value="' . $txt['save'] . '" tabindex="' . $context['tabindex']++ . '" onclick="return oQuickModify.modifySave(\'' . $context['session_id'] . '\', \'' . $context['session_var'] . '\');" accesskey="s" class="button">' . ($context['show_spellchecking'] ? ' <input type="button" value="' . $txt['spell_check'] . '" tabindex="' . $context['tabindex']++ . '" onclick="spellCheck(\'quickModForm\', \'message\');" class="button">' : '') . ' <input type="submit" name="cancel" value="' . $txt['modify_cancel'] . '" tabindex="' . $context['tabindex']++ . '" onclick="return oQuickModify.modifyCancel();" class="button">
							</div>
						</div>'), ',
					sTemplateSubjectEdit: ', JavaScriptEscape('<input type="text" name="subject" value="%subject%" size="80" maxlength="80" tabindex="' . $context['tabindex']++ . '">'), ',
					sTemplateBodyNormal: ', JavaScriptEscape('%body%'), ',
					sTemplateSubjectNormal: ', JavaScriptEscape('<a href="' . $scripturl . '?topic=' . $context['current_topic'] . '.msg%msg_id%#msg%msg_id%" rel="nofollow">%subject%</a>'), ',
					sTemplateTopSubject: ', JavaScriptEscape('%subject%'), ',
					sTemplateReasonEdit: ', JavaScriptEscape($txt['reason_for_edit'] . ': <input type="text" name="modify_reason" value="%modify_reason%" size="80" maxlength="80" tabindex="' . $context['tabindex']++ . '" class="quickModifyMargin">'), ',
					sTemplateReasonNormal: ', JavaScriptEscape('%modify_text'), ',
					sErrorBorderStyle: ', JavaScriptEscape('1px solid red'), ($context['can_reply']) ? ',
					sFormRemoveAccessKeys: \'postmodify\'' : '', '
				});

	 
				aIconLists[aIconLists.length] = new IconList({
					sBackReference: "aIconLists[" + aIconLists.length + "]",
					sIconIdPrefix: "msg_icon_",
					sScriptUrl: smf_scripturl,
					bShowModify: ', !empty($modSettings['show_modify']) ? 'true' : 'false', ',
					iBoardId: ', $context['current_board'], ',
					iTopicId: ', $context['current_topic'], ',
					sSessionId: smf_session_id,
					sSessionVar: smf_session_var,
					sLabelIconList: "', $txt['message_icon'], '",
					sBoxBackground: "transparent",
					sBoxBackgroundHover: "#ffffff",
					iBoxBorderWidthHover: 1,
					sBoxBorderColorHover: "#adadad" ,
					sContainerBackground: "#ffffff",
					sContainerBorder: "1px solid #adadad",
					sItemBorder: "1px solid #ffffff",
					sItemBorderHover: "1px dotted gray",
					sItemBackground: "transparent",
					sItemBackgroundHover: "#e0e0f0"
				});
			}';

	if (!empty($context['ignoredMsgs']))
		echo '
			ignore_toggles([', implode(', ', $context['ignoredMsgs']), '], ', JavaScriptEscape($txt['show_ignore_user_post']), ');';

	echo '
		</script>';
}
function template_display_board(){
	global $context,$board_info,$txt,$scripturl;
 
	echo' 
	<a href="',$scripturl,'?action=post;board=',$board_info['id'],'" class="btn d-block text-center mb-3 btn-primary w-100 btn-lg"  >',$txt['stardiscussion'],'</a>
	 
	<div class="card mb-2">
		<div class="card-body">
		<h2><a href="',$scripturl,'?action=board=',$board_info['id'],'">b/',$board_info['name'],'</a></h2>
		<p>',$board_info['description'],'</p> 
		<div class="d-flex">
			<div class="p-3">
			<strong>',$board_info['num_topics'],'</strong><br>',$txt['topics'],'			 
			</div>
			<div class="vr"></div> 
		</div> 
		<a href="',$scripturl,'?board=',$board_info['id'],'" class="mt-2 btn btn-outline-primary d-block rounded-pill">',$txt['viewboard'],'</a>
		</div>
	</div>';
}
function template_display_login(){
	global $txt,$scripturl,$context;

	if($context['user']['is_guest'])
	echo'
	<div class="border p-2 rounded my-3">
	<div class="d-md-flex align-items-center">
		<div class="flex-shrink-1">Inicia sesión o crea una cuenta para dejar un comentario</div>
		<div class="w-75 text-md-end">
		<a href="',$scripturl,'?action=signup" class="rounded-pill btn btn-outline-primary">',$txt['register'],'</a> 
		<a href="',$scripturl,'?action=login" onclick="return reqOverlayDiv(this.href,\'',$txt['login'],'\', \'lll\');" class="rounded-pill btn btn-success open">',$txt['login'],' </a> 
		</div>
	</div></div>';
}
function template_display_poll_calendar(){
	global $context, $settings, $options, $txt, $scripturl, $modSettings;
	// Is this topic also a poll?
	if ($context['is_poll'])
	{
		echo '
		<div id="poll">
			<div class="cat_bar">
				<h3 class="catbg">
					<span class="main_icons poll"></span>', $context['poll']['is_locked'] ? '<span class="main_icons lock"></span>' : '', ' ', $context['poll']['question'], '
				</h3>
			</div>
			<div class="windowbg">
				<div id="poll_options">';

		// Are they not allowed to vote but allowed to view the options?
		if ($context['poll']['show_results'] || !$context['allow_vote'])
		{
			echo '
					<dl class="options">';

			// Show each option with its corresponding percentage bar.
			foreach ($context['poll']['options'] as $option)
			{
				echo '
						<dt class="', $option['voted_this'] ? ' voted' : '', '">', $option['option'], '</dt>
						<dd class="statsbar generic_bar', $option['voted_this'] ? ' voted' : '', '">';

				if ($context['allow_results_view'])
					echo '
							', $option['bar_ndt'], '
							<span class="percentage">', $option['votes'], ' (', $option['percent'], '%)</span>';

				echo '
						</dd>';
			}

			echo '
					</dl>';

			if ($context['allow_results_view'])
				echo '
					<p><strong>', $txt['poll_total_voters'], ':</strong> ', $context['poll']['total_votes'], '</p>';
		}
		// They are allowed to vote! Go to it!
		else
		{
			echo '
					<form action="', $scripturl, '?action=vote;topic=', $context['current_topic'], '.', $context['start'], ';poll=', $context['poll']['id'], '" method="post" accept-charset="', $context['character_set'], '">';

			// Show a warning if they are allowed more than one option.
			if ($context['poll']['allowed_warning'])
				echo '
						<p class="smallpadding">', $context['poll']['allowed_warning'], '</p>';

			echo '
						<ul class="options">';

			// Show each option with its button - a radio likely.
			foreach ($context['poll']['options'] as $option)
				echo '
							<li>', $option['vote_button'], ' <label for="', $option['id'], '">', $option['option'], '</label></li>';

			echo '
						</ul>
						<div class="submitbutton">
							<input type="submit" value="', $txt['poll_vote'], '" class="button">
							<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '">
						</div>
					</form>';
		}

		// Is the clock ticking?
		if (!empty($context['poll']['expire_time']))
			echo '
					<p><strong>', ($context['poll']['is_expired'] ? $txt['poll_expired_on'] : $txt['poll_expires_on']), ':</strong> ', $context['poll']['expire_time'], '</p>';

		echo '
				</div><!-- #poll_options -->
			</div><!-- .windowbg -->
		</div><!-- #poll -->
		<div id="pollmoderation">';

		template_button_strip($context['poll_buttons']);

		echo '
		</div>';
	}

	// Does this topic have some events linked to it?
	if (!empty($context['linked_calendar_events']))
	{
		echo '
		<div class="title_bar">
			<h3 class="titlebg">', $txt['calendar_linked_events'], '</h3>
		</div>
		<div class="information">
			<ul>';

		foreach ($context['linked_calendar_events'] as $event)
		{
			echo '
				<li>
					<strong class="event_title"><a href="', $scripturl, '?action=calendar;event=', $event['id'], '">', $event['title'], '</a></strong>';

			if ($event['can_edit'])
				echo ' <a href="' . $event['modify_href'] . '"><span class="main_icons calendar_modify" title="', $txt['calendar_edit'], '"></span></a>';

			if ($event['can_export'])
				echo ' <a href="' . $event['export_href'] . '"><span class="main_icons calendar_export" title="', $txt['calendar_export'], '"></span></a>';

			echo '
					<br>';

			if (!empty($event['allday']))
			{
				echo '<time datetime="' . $event['start_iso_gmdate'] . '">', trim($event['start_date_local']), '</time>', ($event['start_date'] != $event['end_date']) ? ' &ndash; <time datetime="' . $event['end_iso_gmdate'] . '">' . trim($event['end_date_local']) . '</time>' : '';
			}
			else
			{
				// Display event info relative to user's local timezone
				echo '<time datetime="' . $event['start_iso_gmdate'] . '">', trim($event['start_date_local']), ', ', trim($event['start_time_local']), '</time> &ndash; <time datetime="' . $event['end_iso_gmdate'] . '">';

				if ($event['start_date_local'] != $event['end_date_local'])
					echo trim($event['end_date_local']) . ', ';

				echo trim($event['end_time_local']);

				// Display event info relative to original timezone
				if ($event['start_date_local'] . $event['start_time_local'] != $event['start_date_orig'] . $event['start_time_orig'])
				{
					echo '</time> (<time datetime="' . $event['start_iso_gmdate'] . '">';

					if ($event['start_date_orig'] != $event['start_date_local'] || $event['end_date_orig'] != $event['end_date_local'] || $event['start_date_orig'] != $event['end_date_orig'])
						echo trim($event['start_date_orig']), ', ';

					echo trim($event['start_time_orig']), '</time> &ndash; <time datetime="' . $event['end_iso_gmdate'] . '">';

					if ($event['start_date_orig'] != $event['end_date_orig'])
						echo trim($event['end_date_orig']) . ', ';

					echo trim($event['end_time_orig']), ' ', $event['tz_abbrev'], '</time>)';
				}
				// Event is scheduled in the user's own timezone? Let 'em know, just to avoid confusion
				else
					echo ' ', $event['tz_abbrev'], '</time>';
			}

			if (!empty($event['location']))
				echo '
					<br>', $event['location'];

			echo '
				</li>';
		}
		echo '
			</ul>
		</div><!-- .information -->';
	}
	}
?>