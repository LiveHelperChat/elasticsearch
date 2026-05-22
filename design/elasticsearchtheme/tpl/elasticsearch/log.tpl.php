<?php
$modalHeaderTitle = erTranslationClassLhTranslation::getInstance()->getTranslation('system/buttons','Debug data');
$modalHeaderClass = 'pt-1 pb-1 ps-2 pe-2';
$modalSize = 'xl';
$modalBodyClass = 'p-1';
$appendPrintExportURL = '';
?>
<?php include(erLhcoreClassDesign::designtpl('lhkernel/modal_header.tpl.php'));?>

<div class="modal-body" style="max-height: 500px; overflow: auto; font-family: sans-serif">
    <?php $hideHeader = true;?>
    <?php include(erLhcoreClassDesign::designtpl('elasticsearch/raw.tpl.php'));?>
</div>

<div class="modal-footer ps-0 pe-0 ms-0 me-0">
    <div class="row w-100 ps-0 pe-0 ms-0 me-0">
    <div class="col"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('system/buttons','Close')?></button></div>
    </div>
</div>

<?php include(erLhcoreClassDesign::designtpl('lhkernel/modal_footer.tpl.php'));?>
