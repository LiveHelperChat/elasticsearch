<?php

$tpl = erLhcoreClassTemplate::getInstance('elasticsearch/reindex.tpl.php');

if ( isset($_POST['reindexAction']) ) {

    if (!isset($_POST['csfr_token']) || !$currentUser->validateCSFRToken($_POST['csfr_token'])) {
        erLhcoreClassModule::redirect('elasticsearch/reindex');
        exit;
    }

    $definition = array(
        'type' => new ezcInputFormDefinitionElement(
            ezcInputFormDefinitionElement::OPTIONAL, 'int', array('min_range' => 0,'max_range' => 1)
        ),
        'hours' => new ezcInputFormDefinitionElement(
            ezcInputFormDefinitionElement::OPTIONAL, 'int', array('min_range' => 1,'max_range' => 10000)
        )
    );

    $form = new ezcInputForm( INPUT_POST, $definition );
    $Errors = array();

    if ( $form->hasValidData( 'type' ) && $form->hasValidData( 'hours' ) ) {
        if ($form->type == 0) {
            $db = ezcDbInstance::get();
            $stmt = $db->prepare('INSERT IGNORE INTO `lhc_lheschat_index` (`chat_id`) SELECT `id` FROM `lh_chat` WHERE `time` >= UNIX_TIMESTAMP() - (:hours * 3600);');
            $stmt->bindValue(':hours',$form->hours,PDO::PARAM_INT);
            $stmt->execute();
            $affected = $stmt->rowCount();
            $tpl->set('affected', $affected);
        } else if ($form->type == 1) {
            $db = ezcDbInstance::get();
            $stmt = $db->prepare('INSERT INTO `lhc_lhesmail_index` (mail_id) SELECT id FROM `lhc_mailconv_msg` WHERE `lhc_mailconv_msg`.`udate` >= UNIX_TIMESTAMP() - (:hours * 3600);');
            $stmt->bindValue(':hours',$form->hours,PDO::PARAM_INT);
            $stmt->execute();
            $affected = $stmt->rowCount();
            $tpl->set('affected', $affected);
        }
    }

    $tpl->set('updated','done');
}

$Result['content'] = $tpl->fetch();

$Result['path'] = array(
    array(
        'url' => erLhcoreClassDesign::baseurl('elasticsearch/index'),
        'title' => erTranslationClassLhTranslation::getInstance()->getTranslation('lhelasticsearch/module', 'Elastic Search')
    ),
    array(
        'title' => erTranslationClassLhTranslation::getInstance()->getTranslation('lhelasticsearch/module', 'Re-index')
    )
);

?>