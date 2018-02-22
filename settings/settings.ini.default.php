<?php 

return array (
		'host' 			=> 'localhost',//'localhost',		
		'port' 			=> '9200',
		'index' 		=> 'chat-',
        'index_search' 	=> 'chat-*',
	    'additional_indexes' => array(
	        
	    ),
	    'use_iam' => false,
	    'iam_region' => 'eu-central-1',
	    'iam_credentials' => array(
	        'access_key' => '',
	        'secret_key' => ''
	    )
);

?>