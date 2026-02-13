<h1><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('lhelasticsearch/indices', 'Indices');?></h1>

<form action="<?php echo erLhcoreClassDesign::baseurl('elasticsearch/indices')?>" method="get" class="mb-3">
    <div class="row">
        <div class="col-md-4">
            <div class="input-group">
                <input type="text" name="search" id="searchIndices" class="form-control" placeholder="<?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('lhelasticsearch/indices', 'Search indices pattern... E.g chat* OR *');?><?php echo date('Y');?>*" value="<?php echo htmlspecialchars(isset($_GET['search']) ? $_GET['search'] : '')?>">
                <button type="submit" class="btn btn-primary"><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('lhelasticsearch/indices', 'Search');?></button>
                <?php if (isset($_GET['search']) && $_GET['search'] != '') : ?>
                <a href="<?php echo erLhcoreClassDesign::baseurl('elasticsearch/indices')?>" class="btn btn-secondary"><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('lhelasticsearch/indices', 'Clear');?></a>
                <?php endif; ?>
            </div>
        </div>
        <div class="col-md-4">
            <button type="button" id="loadAllStatsBtn" class="btn btn-success">
                <?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('lhelasticsearch/indices', 'Load All Stats');?>
            </button>
            <span id="loadAllProgress" style="display:none; margin-left: 10px;">
                <i class="fa fa-spinner fa-spin"></i> <span id="progressText"></span>
            </span>
        </div>
    </div>
</form>

<form action="" method="post">
    <table class="table" id="indicesTable">
        <thead>
            <tr>
                <th width="1%"></th>
                <th><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('lhelasticsearch/indices', 'Indice');?></th>
                <th><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('lhelasticsearch/indices', 'Docs');?></th>
                <th><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('lhelasticsearch/indices', 'Deleted docs');?></th>
                <th><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('lhelasticsearch/indices', 'Megabytes');?></th>
                <th width="1%"><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('lhelasticsearch/indices', 'Stats');?></th>
                <th width="1%"></th>
                <th width="1%"></th>
            </tr>
        </thead>
        <tbody>
        <?php ksort($indices);foreach ($indices as $indice => $alias) : ?>
        <tr data-indice="<?php echo htmlspecialchars($indice)?>">
            <td><input type="checkbox" value="<?php echo htmlspecialchars($indice)?>" name="indices[]" ></td>

            <td><a href="<?php echo erLhcoreClassDesign::baseurl('elasticsearch/getmapping')?>/<?php echo htmlspecialchars($indice)?>"><?php echo htmlspecialchars($indice) ?></a></td>
            <td class="stats-docs">-</td>
            <td class="stats-deleted">-</td>
            <td class="stats-size">-</td>
            <td nowrap="">
                <button type="button" class="btn btn-info btn-xs load-stats-btn" data-indice="<?php echo htmlspecialchars($indice)?>"><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('lhelasticsearch/indices', 'Load');?></button>
                <span class="loading-indicator" style="display:none;"><i class="fa fa-spinner fa-spin"></i></span>
            </td>
            <td nowrap=""><a class="btn btn-warning btn-xs csfr-required" onclick="return confirm('<?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('kernel/messages','Are you sure?');?>')" href="<?php echo erLhcoreClassDesign::baseurl('elasticsearch/forcemerge')?>/<?php echo htmlspecialchars($indice)?>"><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('lhelasticsearch/indices', 'Force merge');?></a></td>
            <td><a class="btn btn-danger btn-xs csfr-required" onclick="return confirm('<?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('kernel/messages','Are you sure?');?>')" href="<?php echo erLhcoreClassDesign::baseurl('elasticsearch/deleteindice')?>/<?php echo htmlspecialchars($indice)?>"><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('lhelasticsearch/indices', 'Delete');?></a></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <?php if (empty($indices)) : ?>
    <div class="alert alert-info">
        <?php if (!isset($_GET['search']) || $_GET['search'] == '') : ?>
            <?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('lhelasticsearch/indices', 'Enter a search pattern to find indices. Examples:');?> <code>chat*</code>, <code>*<?php echo date('Y');?>*</code>
        <?php else : ?>
            <?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('lhelasticsearch/indices', 'No indices found matching the search pattern.');?>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <?php include(erLhcoreClassDesign::designtpl('lhkernel/csfr_token.tpl.php'));?>

    <div class="form-group">
        <button type="submit" name="DeleteIndices" class="btn btn-danger" value="delete"><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('lhelasticsearch/indices', 'Delete selected');?></button>
    </div>
</form>
<?php include(erLhcoreClassDesign::designtpl('lhkernel/secure_links.tpl.php')); ?>

<script>
(function() {
    // Load stats functionality
    var loadBtns = document.querySelectorAll('.load-stats-btn');
    
    loadBtns.forEach(function(btn) {
        btn.addEventListener('click', function() {
            var indice = this.getAttribute('data-indice');
            var row = this.closest('tr');
            var loadingIndicator = row.querySelector('.loading-indicator');
            
            btn.disabled = true;
            loadingIndicator.style.display = 'inline';
            
            var xhr = new XMLHttpRequest();
            xhr.open('GET', '<?php echo erLhcoreClassDesign::baseurl('elasticsearch/indices')?>?load_stats=1&indice=' + encodeURIComponent(indice), true);
            
            xhr.onload = function() {
                if (xhr.status === 200) {
                    var data = JSON.parse(xhr.responseText);
                    
                    if (data.success) {
                        row.querySelector('.stats-docs').textContent = data.docs;
                        row.querySelector('.stats-deleted').textContent = data.deleted;
                        row.querySelector('.stats-size').textContent = data.size + ' MB';
                        btn.textContent = '<?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('lhelasticsearch/indices', 'Reload');?>';
                    } else {
                        alert('<?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('lhelasticsearch/indices', 'Error loading statistics');?>');
                    }
                } else {
                    alert('<?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('lhelasticsearch/indices', 'Error loading statistics');?>');
                }
                
                btn.disabled = false;
                loadingIndicator.style.display = 'none';
            };
            
            xhr.onerror = function() {
                alert('<?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('lhelasticsearch/indices', 'Network error occurred');?>');
                btn.disabled = false;
                loadingIndicator.style.display = 'none';
            };
            
            xhr.send();
        });
    });
    
    // Load all stats functionality
    var loadAllBtn = document.getElementById('loadAllStatsBtn');
    if (loadAllBtn) {
        loadAllBtn.addEventListener('click', function() {
            var allLoadBtns = document.querySelectorAll('.load-stats-btn');
            if (allLoadBtns.length === 0) {
                alert('<?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('lhelasticsearch/indices', 'No indices to load');?>');
                return;
            }
            
            var currentIndex = 0;
            var totalCount = allLoadBtns.length;
            var progressIndicator = document.getElementById('loadAllProgress');
            var progressText = document.getElementById('progressText');
            
            loadAllBtn.disabled = true;
            progressIndicator.style.display = 'inline';
            
            function loadNext() {
                if (currentIndex >= allLoadBtns.length) {
                    // All done
                    loadAllBtn.disabled = false;
                    progressIndicator.style.display = 'none';
                    return;
                }
                
                var btn = allLoadBtns[currentIndex];
                var indice = btn.getAttribute('data-indice');
                var row = btn.closest('tr');
                var loadingIndicator = row.querySelector('.loading-indicator');
                
                progressText.textContent = '(' + (currentIndex + 1) + '/' + totalCount + ')';
                
                btn.disabled = true;
                loadingIndicator.style.display = 'inline';
                
                var xhr = new XMLHttpRequest();
                xhr.open('GET', '<?php echo erLhcoreClassDesign::baseurl('elasticsearch/indices')?>?load_stats=1&indice=' + encodeURIComponent(indice), true);
                
                xhr.onload = function() {
                    if (xhr.status === 200) {
                        var data = JSON.parse(xhr.responseText);
                        
                        if (data.success) {
                            row.querySelector('.stats-docs').textContent = data.docs;
                            row.querySelector('.stats-deleted').textContent = data.deleted;
                            row.querySelector('.stats-size').textContent = data.size + ' MB';
                            btn.textContent = '<?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('lhelasticsearch/indices', 'Reload');?>';
                        }
                    }
                    
                    btn.disabled = false;
                    loadingIndicator.style.display = 'none';
                    
                    // Move to next item
                    currentIndex++;
                    loadNext();
                };
                
                xhr.onerror = function() {
                    btn.disabled = false;
                    loadingIndicator.style.display = 'none';
                    
                    var shouldContinue = confirm('<?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('lhelasticsearch/indices', 'Error loading stats for');?> ' + indice + '. <?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('lhelasticsearch/indices', 'Continue with remaining indices?');?>');
                    
                    if (shouldContinue) {
                        currentIndex++;
                        loadNext();
                    } else {
                        loadAllBtn.disabled = false;
                        progressIndicator.style.display = 'none';
                    }
                };
                
                xhr.send();
            }
            
            // Start loading
            loadNext();
        });
    }
})();
</script>