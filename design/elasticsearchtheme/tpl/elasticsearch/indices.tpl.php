<h1>Indices</h1>

<form action="" method="post">
    <table class="table">
        <thead>
            <tr>
                <th width="1%"></th>
                <th>Indice</th>
                <th>Docs</th>
                <th>Deleted docs</th>
                <th>Megabytes</th>
                <th width="1%"></th>
                <th width="1%"></th>
            </tr>
        </thead>
        <?php $statsTotal = ['docs' => 0, 'deleted' => 0, 'size' => 0];ksort($indices);foreach ($indices as $indice => $alias) : $stats = erLhcoreClassElasticClient::getHandler()->indices()->stats(array('index' => $indice));?>
        <tr>
            <td><input type="checkbox" value="<?php echo htmlspecialchars($indice)?>" name="indices[]" ></td>

            <td><a href="<?php echo erLhcoreClassDesign::baseurl('elasticsearch/getmapping')?>/<?php echo htmlspecialchars($indice)?>"><?php echo htmlspecialchars($indice) ?></a></td>
            <td>
                <?php $statsTotal['docs'] += $stats['_all']['total']['docs']['count']; echo $stats['_all']['total']['docs']['count'];?>
            </td>
            <td>
                <?php $statsTotal['deleted'] += $stats['_all']['total']['docs']['deleted']; echo $stats['_all']['total']['docs']['deleted'];?>
            </td>
            <td>
                <?php $statsTotal['size'] += $stats['_all']['total']['store']['size_in_bytes']; echo round($stats['_all']['total']['store']['size_in_bytes'] / 1048576, 2); ?> MB
            </td>
            <td nowrap=""><a class="btn btn-warning btn-xs csfr-required" onclick="return confirm('<?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('kernel/messages','Are you sure?');?>')" href="<?php echo erLhcoreClassDesign::baseurl('elasticsearch/forcemerge')?>/<?php echo htmlspecialchars($indice)?>">Force merge</a></td>
            <td><a class="btn btn-danger btn-xs csfr-required" onclick="return confirm('<?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('kernel/messages','Are you sure?');?>')" href="<?php echo erLhcoreClassDesign::baseurl('elasticsearch/deleteindice')?>/<?php echo htmlspecialchars($indice)?>">Delete</a></td>
        </tr>
        <?php endforeach; ?>
        <tr>
            <td colspan="2" class="fw-bold">Total</td>
            <td>
                <?php echo $statsTotal['docs'];?>
            </td>
            <td>
                <?php echo $statsTotal['deleted'];?>
            </td>
            <td colspan="3">
                <?php echo round( $statsTotal['size'] / 1048576, 2); ?> MB
            </td>
        </tr>
    </table>

    <?php include(erLhcoreClassDesign::designtpl('lhkernel/csfr_token.tpl.php'));?>

    <div class="form-group">
        <button type="submit" name="DeleteIndices" class="btn btn-danger" value="delete">Delete selected</button>
    </div>
</form>
<?php include(erLhcoreClassDesign::designtpl('lhkernel/secure_links.tpl.php')); ?>