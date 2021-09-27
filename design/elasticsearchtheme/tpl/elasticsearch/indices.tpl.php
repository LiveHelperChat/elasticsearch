<h1>Indices</h1>

<form action="" method="post">
    <table class="table">
        <thead>
            <tr>
                <th width="1%"></th>
                <th>Indice</th>
                <th width="1%"></th>
            </tr>
        </thead>
        <?php foreach ($indices as $indice => $alias) : ?>
        <tr>
            <td><input type="checkbox" value="<?php echo htmlspecialchars($indice)?>" name="indices[]" ></td>
            <td><a href="<?php echo erLhcoreClassDesign::baseurl('elasticsearch/getmapping')?>/<?php echo htmlspecialchars($indice)?>"><?php echo htmlspecialchars($indice) ?></a></td>
            <td><a class="btn btn-danger btn-xs csfr-required" onclick="return confirm('<?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('kernel/messages','Are you sure?');?>')" href="<?php echo erLhcoreClassDesign::baseurl('elasticsearch/deleteindice')?>/<?php echo htmlspecialchars($indice)?>">Delete</a></td>
        </tr>
        <?php endforeach; ?>
    </table>

    <?php include(erLhcoreClassDesign::designtpl('lhkernel/csfr_token.tpl.php'));?>

    <div class="form-group">
        <button type="submit" name="DeleteIndices" class="btn btn-danger" value="delete">Delete selected</button>
    </div>
</form>
<?php include(erLhcoreClassDesign::designtpl('lhkernel/secure_links.tpl.php')); ?>