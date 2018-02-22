<h1>Indices</h1>

<table class="table">
    <thead>
        <tr>
            <th>Indice</th>
            <th width="1%"></th>
        </tr>
    </thead>
    <?php foreach ($indices as $indice => $alias) : ?>
    <tr>
        <td><a href="<?php echo erLhcoreClassDesign::baseurl('elasticsearch/getmapping')?>/<?php echo htmlspecialchars($indice)?>"><?php echo htmlspecialchars($indice) ?></a></td>
        <td><a class="btn btn-danger btn-xs csfr-required" onclick="return confirm('<?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('kernel/messages','Are you sure?');?>')" href="<?php echo erLhcoreClassDesign::baseurl('elasticsearch/deleteindice')?>/<?php echo htmlspecialchars($indice)?>">Delete</a></td>
    </tr>
    <?php endforeach; ?>
</table>

<?php include(erLhcoreClassDesign::designtpl('lhkernel/secure_links.tpl.php')); ?>