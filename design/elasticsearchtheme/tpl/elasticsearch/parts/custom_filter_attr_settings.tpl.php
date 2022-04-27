<?php if (isset(erLhcoreClassModule::getExtensionInstance('erLhcoreClassExtensionElasticsearch')->settings['columns'])) : ?>
    <?php foreach (erLhcoreClassModule::getExtensionInstance('erLhcoreClassExtensionElasticsearch')->settings['columns'] as $columnData) : if (isset($columnData['render'])) : ?>
    <div class="<?php (isset($columnData['width'])) ? print $columnData['width'] : print 'col-2';?>">
        <div class="form-group">
            <label><?php echo $columnData['render']['trans'];?></label>
            <?php echo erLhcoreClassAbstract::renderInput($columnData['render']['field'], $columnData['render'], $input) ?>
        </div>
    </div>
    <?php endif; endforeach; ?>
<?php endif; ?>
