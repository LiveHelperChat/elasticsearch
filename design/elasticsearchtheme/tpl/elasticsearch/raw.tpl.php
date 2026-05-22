<?php if (!isset($hideHeader)) : ?>
<h1><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('elasticsearch/admin','Raw information')?></h1>
<?php endif; ?>

<?php if ($item instanceof erLhcoreClassModelESMail) : ?>
    <a class="btn btn-danger btn-xs csfr-required" onclick="return confirm('<?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('kernel/messages','Are you sure?');?>')" href="<?php echo erLhcoreClassDesign::baseurl('elasticsearch/deletemail')?>/<?php echo $item->meta_data['index']?>/<?php echo $item->id?>"><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('user/userlist','Delete');?></a>
    <?php include(erLhcoreClassDesign::designtpl('lhkernel/secure_links.tpl.php')); ?>
<?php endif; ?>

<?php if ($item instanceof LiveHelperChatExtension\elasticsearch\providers\Index\RestLog) : ?>
<?php 
$item->meta_msg = json_decode($item->meta_msg, true);
if (isset($item->meta_msg['content']['html']['content']) && isset($item->meta_msg['content']['html']['debug']) && $item->meta_msg['content']['html']['debug'] === true) {
    $item->meta_msg['content']['html']['content'] = json_decode($item->meta_msg['content']['html']['content'],true);
}
?>
<?php endif; ?>

<div id="json-renderer" style="overflow: auto; font-family: sans-serif"></div>

<script src="<?php echo erLhcoreClassDesign::designJS('js/jsonview.js');?>"></script>
<link rel="stylesheet" href="<?php echo erLhcoreClassDesign::designCSS('css/jsonview.css');?>">

<script>
    $(function() {
        var data = <?php echo json_encode($item, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);?>;
        const tree = jsonview.create(data);
        jsonview.render(tree, document.getElementById("json-renderer"));
        jsonview.expand(tree);
    });
</script>

