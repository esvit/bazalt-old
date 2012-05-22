<h1><?php echo $user->getName(); ?></h1>

<p><?php echo $user->login ?></p>

<h3>Roles</h3>

<?php foreach($user->Roles as $role) { ?>

        <?php echo $role->name; ?>

<?php } ?>

<h3>Permitions</h3>

<table class="user-permitions">
<?php foreach($permitions as $component => $permition) { ?>
<tr>

        <?php if (count($permition->roles()) > 0) { ?>
            <tr>
                <th colspan="2">Component "<?php echo $component; ?>"</th>
            </tr>
            <?php foreach($permition->roles() as $val => $role) { ?>
            <tr>
                    <td class="user-right-name"><?php echo $role; ?></td>
                    <?php if ($user->hasRight($component, $val)) { ?>
                        <td class="user-has-right">+</td>
                    <?php } else { ?>
                        <td class="user-no-right">-</td>
                    <?php } ?>
            </tr>
            <?php } ?>

        <?php } ?>

</tr>
<?php } ?>
</table>

<style>
.user-permitions td, .user-permitions th {
    padding: 3px 5px;
}
.user-permitions th {
    font-weight: bold;
    background-color: #CCC;
}
.user-permitions td.user-right-name {
    padding-left: 10px;
}
.user-permitions td.user-has-right, .user-permitions td.user-no-right {
    width: 30px;
    height: 30px;
    text-align: center;
    vertical-align: middle;
}
.user-permitions td.user-has-right {
    background-color: green;
}
.user-permitions td.user-no-right {
    background-color: red;
}
</style>