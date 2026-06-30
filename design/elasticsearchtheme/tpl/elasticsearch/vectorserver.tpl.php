<h1><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('elasticsearch/admin','Vector Embedding Server')?></h1>

<div class="alert alert-info">
    <strong><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('elasticsearch/admin','Server URL')?>:</strong>
    <?php echo htmlspecialchars($embedServerUrl)?>
</div>

<h3><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('elasticsearch/admin','Search Vector Storage Documents')?></h3>
<form action="" method="post" ng-non-bindable>
    <?php include(erLhcoreClassDesign::designtpl('lhkernel/csfr_token.tpl.php'));?>

    <div class="form-group">
        <label><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('elasticsearch/admin','Search Phrase')?></label>
        <input type="text" class="form-control" name="SearchPhrase" value="<?php echo htmlspecialchars($searchPhrase)?>" placeholder="<?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('elasticsearch/admin','Enter phrase to find similar vector documents...')?>" />
    </div>

    <input type="submit" name="doSearchVector" value="<?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('elasticsearch/admin','Search Stored Vectors')?>" class="btn btn-success" />
</form>

<?php if ($searchResponse !== null) : ?>
<br/>
<?php if (isset($searchResponse['error']) && $searchResponse['error'] === true) : ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($searchResponse['message'])?></div>
<?php else : ?>
    <h4><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('elasticsearch/admin','Search Results')?> (<?php echo htmlspecialchars($searchResponse['total_found'])?> <?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('elasticsearch/admin','found')?>, <?php echo htmlspecialchars($searchResponse['embed_dimensions'])?> dims)</h4>
    <p><em><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('elasticsearch/admin','Query')?>: "<?php echo htmlspecialchars($searchResponse['query'])?>"</em></p>

    <?php if ($searchResponse['total_found'] > 0) : ?>
        <table class="table table-condensed table-striped">
            <thead>
                <tr>
                    <th><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('elasticsearch/admin','Score')?></th>
                    <th><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('elasticsearch/admin','Name')?></th>
                    <th><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('elasticsearch/admin','Content')?></th>
                    <th><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('elasticsearch/admin','Created')?></th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($searchResponse['results'] as $result) : ?>
                <tr>
                    <td><?php echo htmlspecialchars(number_format($result['score'], 4))?></td>
                    <td><?php echo htmlspecialchars($result['name'])?></td>
                    <td style="max-width:400px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?php echo htmlspecialchars($result['content'])?></td>
                    <td><?php echo $result['created_at'] ? htmlspecialchars(date('Y-m-d H:i:s', $result['created_at'] / 1000)) : ''?></td>
                    <td>
                        <?php if (isset($result['index']) && $result['index'] !== null) : ?>
                        <a class="btn btn-secondary btn-xs" href="<?php echo erLhcoreClassDesign::baseurl('elasticsearch/editvector')?>/<?php echo htmlspecialchars($result['index'])?>/<?php echo htmlspecialchars($result['id'])?>"><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('system/buttons','View')?></a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else : ?>
        <div class="alert alert-warning"><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('elasticsearch/admin','No matching documents found.')?></div>
    <?php endif; ?>
<?php endif; ?>
<?php endif; ?>

<hr/>
<h3><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('elasticsearch/admin','Test Embedding Endpoints')?></h3>

<form action="" method="post" ng-non-bindable>
    <?php include(erLhcoreClassDesign::designtpl('lhkernel/csfr_token.tpl.php'));?>

    <div class="form-group">
        <label><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('elasticsearch/admin','Text')?></label>
        <textarea class="form-control" style="font-size:13px" rows="6" name="Text" placeholder="<?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('elasticsearch/admin','Enter text to embed. For multi-document embedding, put each document on a new line.')?>"><?php echo htmlspecialchars($text)?></textarea>
    </div>

    <input type="submit" name="doEmbedQuery" value="<?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('elasticsearch/admin','Embed Query (single)')?>" class="btn btn-secondary" />
    <input type="submit" name="doEmbedDocuments" value="<?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('elasticsearch/admin','Embed Documents (multi-line)')?>" class="btn btn-primary" />
</form>

<?php if ($queryResponse !== null) : ?>
<br/>
<h4><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('elasticsearch/admin','Query Response')?> (HTTP <?php echo htmlspecialchars(isset($queryResponse['_http_code']) ? $queryResponse['_http_code'] : '?')?>)</h4>
<pre style="font-size:11px;max-height:400px;overflow:auto;"><?php
    $display = $queryResponse;
    unset($display['_http_code']);
    /*if (isset($display['embed']) && is_array($display['embed'])) {
        $embedCount = count($display['embed']);
        $display['embed'] = '[' . $embedCount . ' dimensions]';
    }*/
    echo htmlspecialchars(print_r($display, true));
?></pre>
<?php endif; ?>

<?php if ($docsResponse !== null) : ?>
<br/>
<h4><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('elasticsearch/admin','Documents Response')?> (HTTP <?php echo htmlspecialchars(isset($docsResponse['_http_code']) ? $docsResponse['_http_code'] : '?')?>)</h4>
<pre style="font-size:11px;max-height:400px;overflow:auto;"><?php
    $display = $docsResponse;
    /*unset($display['_http_code']);
    if (isset($display['embeddings']) && is_array($display['embeddings'])) {
        $chunksCount = count($display['embeddings']);
        $dims = isset($display['embeddings'][0]) ? count($display['embeddings'][0]) : '?';
        $display['embeddings'] = '[' . $chunksCount . ' chunks, each ' . $dims . ' dims]';
    }*/
    echo htmlspecialchars(print_r($display, true));
?></pre>
<?php endif; ?>
