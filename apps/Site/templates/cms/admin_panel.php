<div id="bz-admin-bar">
    <div class="quicklinks">
    <ul>
    <?php foreach ($adminMenu->getItems() as $menuitem) {
                require 'admin_menuitem.php';
    } ?>
    </ul>
    </div>
    <div class="quicklinks" style="float: right">
    <ul>
    <?php foreach ($quickLinks->getItems() as $menuitem) { 
                require 'admin_menuitem.php';
    } ?>
    </ul>
    </div>
</div>

<script>
$(function() {
    $('body').addClass('cms-show-adminpanel');
    <?php if ($_COOKIE['cms-show-manage-widgets'] == 'true') { ?>
    $('body').addClass('cms-manage-widgets');
    <?php } ?>
    
    var link = $.cookie('X-XHProf-Link');
    if (link) {
        $('.cms-application-mode').after('<li class="menupop"><a href="' + link + '" target="_blank">Profiler</a></li>');
    }
});
</script>