<?php
	if(!defined('IN_TEMPLATE'))
    {
      exit('Access denied');
    }
?>
<style type="text/css" media="screen">
    #editor { 
        width : 100%;
        font-size:14px;
    }
</style>
<script>
$(document).ready( function()
{
    var editor = ace.edit("editor");
    editor.setTheme("ace/theme/twilight");
    editor.getSession().setMode("ace/mode/c_cpp");
    editor.setOptions({
        minLines: 20,
        maxLines: Infinity
    });
})
</script>
<div id="editor"><?php if(isset($tmpl['defaultcode'])): ?><?= htmlspecialchars($tmpl['defaultcode']) ?><?php endif;?></div>

