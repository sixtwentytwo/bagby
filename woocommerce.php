<?php get_header(); ?>

<div id="content">

<div id="inner-content" class="wrap clearfix">
<?php if (is_active_sidebar('sidebar2')) : ?>
<div id="main" class="m-all t-2of3 d-2of3 wrap cf" role="main">
<?php else: ?>
<div id="main" class="m-all t-all d-all wrap cf" role="main">
<?php endif;?>
<?php woocommerce_content(); ?>

</div>

<?php get_sidebar(); ?>

</div>

</div>
</div>

<?php get_footer(); ?>
