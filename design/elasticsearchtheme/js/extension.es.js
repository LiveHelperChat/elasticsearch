ee.addListener('adminChatTabSubtabClicked', function (chatId, attr) {

    if (attr.attr('aria-controls') == 'online-user-info-eschats-tab-'+chatId && typeof attr.attr('nl') === 'undefined') {
        attr.attr('nl',1);
        $.getJSON(lhinst.wwwDir + "elasticsearch/getpreviouschats/" + chatId, function (data) {
            $('#online-user-info-eschats-tab-'+chatId).append(data.result);
        });
    }

});