<li class="menupop <?php echo $menuitem->css(); ?>"<?php if ($menuitem->id()) { echo ' id="' . $menuitem->id() . '-li"'; } ?>>
    <a<?php if ($menuitem->id()) { echo ' id="' . $menuitem->id() . '"'; } ?> href="<?php echo $menuitem->getUrl(); ?>">
    <?php echo $menuitem->title;
        if ($menuitem->hasOption('info')) {
    ?>
        <span class="update-count"><?php echo $menuitem->getOption('info'); ?></span>
        <?php } ?>
    </a>
    
    <?php if($menuitem->hasSubMenu()) { ?>
    <ul>
        <?php foreach ($menuitem->getItems() as $menuitem) {
            require 'admin_menuitem.php';
        } ?>
    </ul>
    <?php } ?>
</li>