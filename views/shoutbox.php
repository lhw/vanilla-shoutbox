<?php if (!defined('APPLICATION')) exit(); ?>
<div id='shoutbox' class='Box'>
	<?="<h4>".T('Shoutbox')."</h4>\n" ?>

	<div id="shoutscroll">
		<ul id="shoutboxcontent">
		</ul>
		<button id='shoutbox-send'>Send Yo</button>
	</div>

</div>

<script type="text/javascript">
		$('#shoutbox').prependTo('#Content');
		$('#shoutbox').insertAfter('.Info');
		$('#shoutbox-send').click(function() {
			gdn.informMessage(gdn.url('profile/vanilla')); //TODO remember these two
		});
		//gdn.url('');
</script>
