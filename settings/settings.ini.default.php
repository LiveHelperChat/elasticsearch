<?php 

return array (
		'host' 			=> getenv('ES_HOST') ?: 'localhost',//'localhost' or http://login:pass@localhost
		'port' 			=> getenv('ES_PORT') ?: '9200',
		'verify_ssl' 	=> true,
		'index' 		=> 'chat-',
        'index_search' 	=> 'chat-*',
	    'additional_indexes' => array(
	        
	    ),
	    'columns' => [],
	    'use_iam' => false,
	    'iam_region' => 'eu-central-1',
	    'iam_credentials' => array(
	        'access_key' => '',
	        'secret_key' => ''
	    )
);

?>
