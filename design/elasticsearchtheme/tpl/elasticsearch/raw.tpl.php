<h1><?php echo erTranslationClassLhTranslation::getInstance()->getTranslation('elasticsearch/admin','Raw information')?></h1>

<pre ng-non-bindable>
<?php echo htmlspecialchars(print_r($item,true))?>
</pre>