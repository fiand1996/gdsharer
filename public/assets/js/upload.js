function initPicker() {
    var picker = new FilePicker({
        apiKey: $("#pick").data("apikey"),
        clientId: $("#pick").data("client"),
        buttonEl: document.getElementById('pick'),
        onSelect: function (files) {
            //console.log(files);
            let data = '';
            files.forEach((file) => {
                data += '<div class="listup" id="' + file.id + '"><img src="' + file
                    .iconUrl + '"> <span class="name">' + file.name +
                    '</span><a href="#" class="cancel pull-right label bg-yellow">'+ lang("Cancel") +'</a><span class="stat"></span></div>';
            })
            $('#uploadList').show();
            $('.fileinfo').append(data);
        }
    });
}

function setStatus(cancel, id, bg, text) {
    if (cancel) {
        $('#' + id).find('.cancel').hide();
    }
    $('#' + id).find('.stat').attr('class', 'stat pull-right label ' + bg);
    $('#' + id).find('.stat').html(text);
}

function startUpload(lst, cur) {
    if (cur >= lst.length) {
        $('#upload').prop('disabled', false);
        $('#pick').prop('disabled', false);
        return false;
    }
    setStatus(true, lst[cur], 'bg-yellow', lang("Loading..."));
    $.post(window.location, {
        id: lst[cur],
        action: 'upload'
    }, function (data) {
        if (data.result == 1) {
            setStatus(false, lst[cur], 'bg-green', lang("Done"));
            $('.results').append('<div>' + data.name +
                '</div><div><button type="button" class="btn btn-xs btn-default js-copy" title="Copy to clipboard" data-selector="true" data-copy="' +
                data.alias + '"><i class="fa fa-clipboard"></i></button> <a href="' + data.alias + '" target="_blank">' + data.alias + '</a></div>');
                $("meta[name='csrftoken']").attr('content', data.token);
        } else {
            setStatus(false, lst[cur], 'bg-red', lang("Failed"));
        }
        cur++;
        startUpload(lst, cur);
    }, 'json').fail(function (xhr, textStatus, errorThrown) {
        //console.log(textStatus);
    });
    return true;
}

$('#pick').on('click', function () {
    $('.fileinfo').html('');
});

$(document).on('click', '.cancel', function () {
    var that = $(this);
    that.parent().remove();
});

$('#upload').on('click', function () {
    var idArray = [];
    $('.listup').each(function () {
        if ($(this).children('.bg-green').length < 1) {
            idArray.push(this.id);
        }
    });

    if (typeof idArray != "undefined" && idArray.length < 1) {
        SimpleAuth.Notice({
            content: lang("Please select a file")
        });
        return;
    }
    
    $('#pick').prop('disabled', true);
    $('#upload').prop('disabled', true);
    $('#uploadResults').show();
    startUpload(idArray, 0);
    return false;
});

$('.js-copy').tooltip({
    selector: true,
    placement: 'bottom'
});

function setTooltip(btn, message) {
    $(btn).tooltip('hide')
        .attr('data-original-title', message)
        .tooltip('show');
}

function hideTooltip(btn) {
    setTimeout(function () {
        $(btn).tooltip('destroy');
    }, 500);
}
var clipboard = new ClipboardJS('.js-copy', {
    text: function (trigger) {
        return $(trigger).attr('data-copy');
    }
});

clipboard.on('success', function (e) {
    e.clearSelection();
    setTooltip(e.trigger, lang("Copied!"));
    hideTooltip(e.trigger);
});

clipboard.on('error', function (e) {
    e.clearSelection();
    setTooltip(e.trigger, lang("Oops, unable to copy"));
    hideTooltip(e.trigger);
});