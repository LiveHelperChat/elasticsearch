<p>
More information can be found at <a target="_blank" title="https://www.elastic.co/guide/en/elasticsearch/reference/8.10/query-dsl-query-string-query.html" href="https://www.elastic.co/guide/en/elasticsearch/reference/8.10/query-dsl-query-string-query.html">official documentation</a>
</p>

<p>Most frequent fields you can use:<br>

<ul>
    <li><span class="badge bg-secondary">msg_visitor</span> - visitor messages</li>
    <li><span class="badge bg-secondary">msg_operator</span> - operator messages</li>
    <li><span class="badge bg-secondary">msg_system</span> - system messages</li>
    <li><span class="badge bg-secondary">email</span> - visitor e-mail</li>
    <li><span class="badge bg-secondary">city</span> - visitor city. E.g. <i>Frankfurt am Main || Hesse</i></li>
    <li><span class="badge bg-secondary">region</span> - visitor region <i>Hesse</i></li>
    <li><span class="badge bg-secondary">referrer</span> - referrer</li>
    <li><span class="badge bg-secondary">nick</span> - visitor nick</li>
    <li><span class="badge bg-secondary">nick_keyword</span> - visitor nick formated as a keyword. Preferred to use.</li>
    <li><span class="badge bg-secondary">country_code</span> - country code. E.g <i>lt</i></li>
    <li><span class="badge bg-secondary">phone</span> - phone</li>
    <li><span class="badge bg-secondary">uagent</span> - Visitor browser agent headers</li>
    <li><span class="badge bg-secondary">device_type</span> 0 - PC, 1 - mobile, 2 - tablet</li>
    <li><span class="badge bg-secondary">ip</span> - visitor IP</li>
    <?php include(erLhcoreClassDesign::designtpl('lhgenericbot/helpattributes/elasticsearchchat_multiinclude.tpl.php'));?>
</ul>

More fields definition can be found at <a href="https://api.livehelperchat.com/#/">https://api.livehelperchat.com/</a> under models section.

</p>

<p>If you are using expression formatting you can use these samples as a starting point</p>

<ul>
    <li><span class="badge bg-secondary">country_code:de AND msg_visitor:"credit"</span> - search by country and search only in visitor messages</li>
    <li><span class="badge bg-secondary">country_code:de OR ip:192.168.1.254</span> - search by country or IP of the visitor</li>
    <li><span class="badge bg-secondary">msg_visitor:credi*</span> - search in visitor messages by wildcard</li>
    <li><span class="badge bg-secondary">msg_visitor:"credit card"</span> - search in visitor message for exact match</li>
</ul>
