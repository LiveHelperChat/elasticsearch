ee.addListener('adminChatTabSubtabClicked', function (chatId, attr) {
    if (attr.attr('aria-controls') == 'online-user-info-eschats-tab-'+chatId && typeof attr.attr('nl') === 'undefined') {
        attr.attr('nl',1);
        $.getJSON(lhinst.wwwDir + "elasticsearch/getpreviouschats/" + chatId, function (data) {
            $('#online-user-info-eschats-tab-'+chatId).append(data.result);
        });
    } else if (attr.attr('aria-controls') == 'online-user-info-chats-tab-'+chatId && $('#use-elastic-prev-chatid-'+chatId).length > 0 && $('#use-elastic-prev-chatid-'+chatId).val() == '0') {
        $.getJSON(lhinst.wwwDir + "elasticsearch/getpreviouschatsbyid/" + chatId, function (data) {
            $('#use-elastic-prev-chatid-content-'+chatId).html(data.result);
        });
    }
});