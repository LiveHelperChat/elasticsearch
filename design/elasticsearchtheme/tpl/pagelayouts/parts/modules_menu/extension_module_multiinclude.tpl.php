<?php if (erLhcoreClassUser::instance()->hasAccessTo('lhelasticsearch','use_admin')) : ?>
<li class="nav-item"><a class="nav-link" href="<?php echo erLhcoreClassDesign::baseurl('elasticsearch/index')?>"><i class="material-icons">&#xE8B6;</i>ElasticSearch</a></li>
<?php endif; ?>