<p>
    More information can be found at <a target="_blank" title="https://www.elastic.co/guide/en/elasticsearch/reference/8.10/query-dsl-query-string-query.html" href="https://www.elastic.co/guide/en/elasticsearch/reference/8.10/query-dsl-query-string-query.html">official documentation</a>
</p>

<p>Most frequent fields you can use:<br>

<ul>
    <li><span class="badge bg-secondary">subject</span> - Subject</li>
    <li><span class="badge bg-secondary">alt_body</span> - message Body</li>
    <li><span class="badge bg-secondary">from_name</span> - From name</li>
    <li><span class="badge bg-secondary">sender_name</span> - Sender name</li>
    <li><span class="badge bg-secondary">delivery_status</span> - Delivery status</li>
    <li><span class="badge bg-secondary">rfc822_body</span> - Undelivered mail body</li>
    <li><span class="badge bg-secondary">reply_to_data</span> - In Reply to data</li>
    <li><span class="badge bg-secondary">to_data</span> - In To data</li>
    <li><span class="badge bg-secondary">cc_data</span> - In CC data</li>
    <li><span class="badge bg-secondary">bcc_data</span> - In BCC data</li>
    <li><span class="badge bg-secondary">mb_folder</span> - In mailbox path</li>
    <li><span class="badge bg-secondary">customer_name</span> - Customer name</li>
    <li><span class="badge bg-secondary">customer_address</span> - Customer email</li>
    <li><span class="badge bg-secondary">customer_address_clean</span> - Customer email without dots in the name</li>
    <li><span class="badge bg-secondary">from_address</span> - From address of the mail message</li>
    <li><span class="badge bg-secondary">from_address_clean</span> - From address of the mail message without donts in the name</li>
    <?php include(erLhcoreClassDesign::designtpl('lhgenericbot/helpattributes/elasticsearchmail_multiinclude.tpl.php'));?>
</ul>

More fields definition can be found at <a href="https://api.livehelperchat.com/#/">https://api.livehelperchat.com/</a> under models section.

</p>

<p>If you are using expression formatting you can use these samples as a starting point</p>

<ul>
    <li><span class="badge bg-secondary">alt_body:credit AND subject:"credit"</span> - search in mail body and subject</li>
    <li><span class="badge bg-secondary">alt_body:credi*</span> - search in mail body by wildcard</li>
    <li><span class="badge bg-secondary">alt_body:"credit card"</span> - search in mail body for exact match</li>
</ul>
