$(function () {
    SimpleAuth.General();
    SimpleAuth.AjaxForms();
    SimpleAuth.CopyClipboard();
    SimpleAuth.TableRowRemove();
    SimpleAuth.Profile();
});

var SimpleAuth = {};

SimpleAuth.General = function () {

    if (typeof $.fn.datepicker == "function") {
        $.fn.datepicker.language['id-ID'] = {
            days: ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'],
            daysShort: ['Min', 'Sen', 'Sel', 'Ra', 'Kam', 'Jum', 'Sab'],
            daysMin: ['Min', 'Sen', 'Sel', 'Ra', 'Kam', 'Jum', 'Sab'],
            months: ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'],
            monthsShort: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Aug', 'Sep', 'Okt', 'Nov', 'Des'],
            today: 'Today',
            clear: 'Clear',
            dateFormat: 'mm/dd/yyyy',
            timeFormat: 'hh:ii aa',
            firstDay: 1
        };
    }

    if ($('.select2').length > 0) {
        $('.select2').select2();
    }

    $('input[type="checkbox"].icheck-flat-blue, input[type="radio"].icheck-flat-blue').iCheck({
        checkboxClass: 'icheckbox_square-blue',
        radioClass: 'iradio_square-blue'
    });

    $("body").on("input focus", ":input", function () {
        if ($(this).val()) {
            $(this).removeClass("is-invalid");
        }
    });

    $(document).ajaxSend(function (ev, xhr, settings) {
        var token = $("meta[name='csrftoken']").attr('content');
        xhr.setRequestHeader("X-Csrf-Token", token);
    });

    $(document).ajaxComplete(function (ev, xhr, settings) {
        $("meta[name='csrftoken']").attr('content', xhr.responseJSON.token);
    });

    $(window).on('load', function () {
        setTimeout(function () {
            SimpleAuth.onprogress(0);
        }, 200);
    });

    setTimeout(function () {
        SimpleAuth.onprogress(0);
    }, 200);
}

SimpleAuth.AjaxForms = function () {
    var $form = $("#form-ajax");
    var captcha;

    if ($('#recaptcha').length == 1) {
        onloadCallback = function () {
            captcha = grecaptcha.render(
                document.getElementById("recaptcha"), {
                    sitekey: $('#recaptcha').data("site-key"),
                    theme: $('#recaptcha').data("theme")
                }
            );
        }
    }

    $form.on("submit", function () {
        var submitable = true;

        $form.find(":input.required").not(":disabled").each(function () {
            if (!$(this).val()) {
                $(this).addClass("is-invalid");
                submitable = false;
            }
        });

        if ($form.find(":input[name='email']").length == 1 && !isValidEmail($form.find(":input[name='email']").val())) {
            $form.find(":input[name='email']").addClass("is-invalid");
            submitable = false;
        }

        if (submitable) {

            $.ajax({
                url: $form.attr("action"),
                type: $form.attr("method"),
                dataType: 'jsonp',
                data: $form.serialize(),
                error: function () {
                    SimpleAuth.Alert(lang("Oops! An error occured. Please try again later!"), "error");
                    SimpleAuth.onprogress(0);
                },
                beforeSend: function () {
                    SimpleAuth.onprogress(1);
                },
                complete: function () {
                    SimpleAuth.onprogress(0);
                },
                success: function (resp) {
                    if (typeof resp.redirect === "string") {
                        setTimeout(function () {
                            window.location.href = resp.redirect;
                        }, 500);
                    } else if (typeof resp.message === "string") {
                        var result = resp.result || 0;

                        if (typeof resp.reset_fields === 'object') {
                            $(resp.reset_fields).each(function (k, v) {
                                $form.find(":input[name='" + v + "']").val("");
                            });
                        }

                        if (typeof resp.notice === 'boolean') {
                            switch (result) {
                                case 1:
                                    SimpleAuth.Notice({
                                        title: lang("Success"),
                                        content: resp.message,
                                        type: 'green',
                                        btnCancelClass: "btn-green"
                                    });
                                    break;
                                case 2:
                                    SimpleAuth.Notice({
                                        title: lang("Info"),
                                        content: resp.message,
                                        type: 'blue',
                                        btnCancelClass: "btn-blue"
                                    });
                                    break;
                                default:
                                    SimpleAuth.Notice({
                                        content: resp.message
                                    });
                                    break;
                            }
                        } else {
                            switch (result) {
                                case 1:
                                    SimpleAuth.Alert(resp.message, "success");
                                    break;
                                case 2:
                                    SimpleAuth.Alert(resp.message, "info");
                                    break;
                                default:
                                    SimpleAuth.Alert(resp.message, "error");
                                    break;
                            }
                        }
                    } else {
                        SimpleAuth.Alert(lang("Oops! An error occured. Please try again later!"), "error");
                    }

                    if ($('#recaptcha').length == 1) {
                        grecaptcha.reset(captcha);
                    }
                }
            });
        } else {
            SimpleAuth.Alert(lang("Fill required fields"), "error");
        }

        return false;
    });

    SimpleAuth.DatePicker();
}

SimpleAuth.DatePicker = function () {
    $(".js-datepicker").each(function () {
        $(this).removeClass("js-datepicker");

        if ($(this).data("min-date")) {
            $(this).data("min-date", new Date($(this).data("min-date")))
        }

        if ($(this).data("start-date")) {
            $(this).data("start-date", new Date($(this).data("start-date")))
        }

        $(this).datepicker({
            language: "id-ID",
            dateFormat: "yyyy-mm-dd",
            timeFormat: "hh:ii",
            autoClose: true,
            timepicker: true,
            toggleSelected: false
        });
    });
}
//
SimpleAuth.TableRowRemove = function () {
    $("body").on("click", "button.btn-remove", function () {
        var $this = $(this);

        SimpleAuth.Confirm({
            title: lang("Are you sure to remove data?"),
            confirm: function () {
                $.ajax({
                    url: $this.data("url"),
                    type: 'POST',
                    dataType: 'jsonp',
                    data: {
                        action: "remove",
                        id: $this.data("id")
                    },
                    beforeSend: function() {
                        $this.attr("disabled", true);
                    },
                    complete: function () {
                        $this.attr("disabled", false);
                    },
                    success: function(data)
                    {
                        if (typeof data == "object") {
                            if (data.result == 1) {
                                location.reload();
                            } else {
                                SimpleAuth.Notice({
                                    content: data.message
                                });
                            }
                        }
                    }
                });  
            }
        });
    });

    $('.row-checkall').on('ifChanged', function (event) {
        if (this.checked) {
            $('.row-checkbox').not(":disabled").iCheck('check');
        } else {
            $('.row-checkbox').iCheck('uncheck');
        }
    });

    $('.row-checkall,.row-checkbox').on('ifChanged', function () {
        var dataList = [];
        $('.row-checkbox:checked').each(function () {
            dataList.push($(this).data("id"));
        });

        if (dataList.length > 0) {
            $(".rows-delete").html("Hapus masal (" + dataList.length + ")");
            $(".rows-delete").prop("disabled", false);
        } else {
            $(".rows-delete ").html("Hapus masal");
            $(".rows-delete").prop("disabled", true);
            $('.row-checkall').iCheck('uncheck');
        }
    });

    $("body").on("click", "button.rows-delete", function () {

        var dataList = [];
        $('.row-checkbox:checked').each(function () {
            dataList.push($(this).data("id"));
        });

        SimpleAuth.Confirm({
            title: "Yakin ingin menghapus " + dataList.length + " data?",
            confirm: function () {
                $.ajax({
                    url: $(this).data("url"),
                    type: 'POST',
                    dataType: 'jsonp',
                    data: {
                        action: "remove",
                        id: dataList.join()
                    },
                    complete: function () {
                        location.reload();
                    },
                });
            }
        });
    });
}


$("body").on("click", ".share-file", function () {
    var $this = $(this);
    $.ajax({
        url: $this.data("url"),
        type: 'POST',
        dataType: 'jsonp',
        data: {
            action: "share",
            id: $this.data("id")
        },
        error: function () {
            SimpleAuth.Notice();
        },
        beforeSend: function () {
            SimpleAuth.onprogress(1);
        },
        complete: function () {
            SimpleAuth.onprogress(0);
        },
        success: function (data) {
            if (typeof data == "object") {
                if (data.result == 1) {
                    SimpleAuth.Notice({
                        title: lang("Success"),
                        content: data.message,
                        type: 'green',
                        btnCancelClass: "btn-green",
                        cancel: function () {
                            location.reload();
                        }
                    });
                } else {
                    SimpleAuth.Notice({
                        content: data.message
                    });
                }
            }
        }
    });
});

$("body").on("click", ".remove-file", function () {
    var $this = $(this);
    SimpleAuth.Confirm({
        confirm: function () {
            $.ajax({
                url: $this.data("url"),
                type: 'POST',
                dataType: 'jsonp',
                data: {
                    action: "remove",
                    id: $this.data("id")
                }, 
                error: function () {
                    SimpleAuth.Notice();
                },
                beforeSend: function () {
                    SimpleAuth.onprogress(1);
                },
                complete: function () {
                    SimpleAuth.onprogress(0);
                },
                success: function (data) {
                    if (typeof data == "object") {
                        if (data.result == 1) {
                            location.reload();
                        } else {
                            SimpleAuth.Notice({
                                content: data.message
                            });
                        }
                    }
                }
            });
        }
    });
});

SimpleAuth.FilesShare = function (ids, current) {
    if (current >= ids.length) {
        SimpleAuth.onprogress(0);
        SimpleAuth.Notice({
            title: lang("Success"),
            content: lang("%s files successfully shared").replace("%s", ids.length),
            type: 'green',
            btnCancelClass: "btn-green",
            cancel: function () {
                location.reload();
            }
        });

        return false;
    }

    $.ajax({
        url: window.location,
        type: 'POST',
        dataType: 'jsonp',
        data: {
            action: "share",
            id: ids[current]
        },
        error: function () {
            SimpleAuth.Notice();
        },
        success: function (data) {
            current++;
            $("meta[name='csrftoken']").attr('content', data.token);
            SimpleAuth.FilesShare(ids, current);
        }
    });
}

SimpleAuth.FilesDelete = function (ids, current) {
    if (current >= ids.length) {
        SimpleAuth.onprogress(0);
        SimpleAuth.Notice({
            title: lang("Success"),
            content: lang("%s files successfully deleted").replace("%s", ids.length),
            type: 'green',
            btnCancelClass: "btn-green",
            cancel: function () {
                location.reload();
            }
        });

        return false;
    }

    $.ajax({
        url: window.location,
        type: 'POST',
        dataType: 'jsonp',
        data: {
            action: "remove",
            id: ids[current]
        },
        error: function () {
            SimpleAuth.Notice();
        },
        success: function (data) {
            current++;
            $("meta[name='csrftoken']").attr('content', data.token);
            SimpleAuth.FilesDelete(ids, current);
        }
    });
}

$("body").on("click", ".checked-share", function () {
    var dataList = [];
    $('.row-checkbox:checked').each(function () {
        dataList.push($(this).data("id"));
    });

    if (dataList.length == 0) {
        SimpleAuth.Notice({
            content: lang("Please select a file")
        });
        return false;
    }

    SimpleAuth.onprogress(1);
    SimpleAuth.FilesShare(dataList, 0);
});

$("body").on("click", ".checked-delete", function () {
    var dataList = [];
    $('.row-checkbox:checked').each(function () {
        dataList.push($(this).data("id"));
    });

    if (dataList.length == 0) {
        SimpleAuth.Notice({
            content: lang("Please select a file")
        });
        return false;    
    }

    SimpleAuth.Confirm({
        title: lang("Are you sure to delete %s files?").replace("%s", dataList.length),
        confirm: function () {
            SimpleAuth.onprogress(1);
            SimpleAuth.FilesDelete(dataList, 0);
        }
    });
});

 $(".checkbox-toggle").click(function () {
     var clicks = $(this).data('clicks');
     console.log(clicks);
     if (clicks) {
         $(".row-checkbox").iCheck("uncheck");
         $(".fa", this).removeClass("fa-check-square").addClass('fa-square');
     } else {
         $(".row-checkbox").iCheck("check");
         $(".fa", this).removeClass("fa-square").addClass('fa-check-square');
     }
     $(this).data("clicks", !clicks);
 });

 $('.row-checkall,.row-checkbox').on('ifChanged', function () {
     var dataList = [];
     var anu = [];

     $('.row-checkbox:checked').each(function () {
         dataList.push($(this).data("id"));
     });

     $('.row-checkbox').each(function () {
         anu.push($(this).data("id"));
     });

     if (anu.length == dataList.length) {
         $(".checkbox-toggle").data('clicks', true);
         $(".fa", $(".checkbox-toggle")).removeClass("fa-square").addClass('fa-check-square');
     } else {
         $(".checkbox-toggle").data('clicks', false);
         $(".fa", $(".checkbox-toggle")).removeClass("fa-check-square").addClass('fa-square');
     }

     if (dataList.length > 0) {
         $(".checked-delete").html('<i class="fa fa-trash"></i> ' + lang("Delete") + ' (' + dataList.length + ')');
         $(".checked-delete").prop("disabled", false);
         $(".checked-share").html('<i class="fa fa-share"></i> ' + lang("Share") + ' (' + dataList.length + ')');
         $(".checked-share").prop("disabled", false);
     } else {
         $(".checked-delete").html('<i class="fa fa-trash"></i> ' + lang("Delete"));
         $(".checked-delete").prop("disabled", true);
         $(".checked-share").html('<i class="fa fa-share"></i> ' + lang("Share"));
         $(".checked-share").prop("disabled", true);
         $('.row-checkall').iCheck('uncheck');
     }
 });

$("body").on("click", "button.btn-empty-trash", function () {
    var $this = $(this);

    SimpleAuth.Confirm({
        title: lang("Are you sure to empty trash?"),
        confirm: function () {
             $this.attr("disabled", true);
             $.ajax({
                 url: $this.attr("url"),
                 type: 'POST',
                 dataType: 'jsonp',
                 data: {
                     action: "empty"
                 },
                 success: function (data) {
                     if (typeof data == "object") {
                         if (data.result == 1) {
                             $this.attr("disabled", false);
                             location.reload();
                         } else {
                             alert(data.message);
                         }
                     }
                 }
             });
        }
    });
});

$("body").on("click", ".change-language li a", function () {
    setCookie('lang', $(this).data('lang'), 30)
    location.reload();
});

SimpleAuth.CopyClipboard = function () {
    var clipboard = new ClipboardJS('.btn-copy');
    $('.btn-copy').tooltip({
        selector: true,
        placement: 'bottom'
    });

    clipboard.on('success', function (e) {
        e.clearSelection();
        $(e.trigger).tooltip('hide')
            .attr('data-original-title', lang("Copied!"))
            .tooltip('show');
        setTimeout(function () {
            $(e.trigger).tooltip('destroy');
        }, 500);
    });

    clipboard.on('error', function (e) {
        e.clearSelection();
        $(e.trigger).tooltip('hide')
            .attr('data-original-title', lang("Oops, unable to copy"))
            .tooltip('show');
        setTimeout(function () {
            $(e.trigger).tooltip('destroy');
        }, 500);
    });
}

SimpleAuth.Confirm = function (data = {}) {
    data = $.extend({}, {
        title: lang("Are you sure?"),
        content: lang("It is not possible to get back removed file!"),
        type: 'red',
        confirmText: lang("Yes, Delete"),
        cancelText: lang("Cancel"),
        btnConfirmClass: "btn-red",
        btnCancelClass: "btn-default",
        confirm: function () {},
        cancel: function () {},
    }, data);

    $.confirm({
        title: data.title,
        content: data.content,
        theme: 'material',
        animation: 'scale',
        closeAnimation: 'scale',
        animationSpeed: 400,
        animationBounce: 1,
        type: data.type,
        boxWidth: '30%',
        useBootstrap: false,
        buttons: {
            confirm: {
                text: data.confirmText,
                btnClass: data.btnConfirmClass,
                keys: ['enter'],
                action: typeof data.confirm === 'function' ? data.confirm : function () {}
            },
            cancel: {
                text: data.cancelText,
                btnClass: data.btnCancelClass,
                keys: ['esc'],
                action: typeof data.cancel === 'function' ? data.cancel : function () {}
            },
        }
    });
}

SimpleAuth.Notice = function (data = {}) {

    data = $.extend({}, {
        title: lang("Error"),
        content: lang("Oops! An error occured. Please try again later!"),
        type: 'red',
        cancelText: lang("Close"),
        btnCancelClass: "btn-red",
        cancel: function () {},
    }, data);

    $.confirm({
        title: data.title,
        content: data.content,
        theme: 'material',
        animation: 'scale',
        closeAnimation: 'scale',
        animationSpeed: 400,
        animationBounce: 1,
        type: data.type,
        boxWidth: '30%',
        useBootstrap: false,
        buttons: {
            cancel: {
                text: data.cancelText,
                btnClass: data.btnCancelClass,
                keys: ['esc'],
                action: typeof data.cancel === 'function' ? data.cancel : function () {}
            },
        }
    });
}

SimpleAuth.Alert = function (text, type = 'success') {
    var $message = $("#message");
    var $parent = $("html, body");
    var top = $message.offset().top - 85;

    $parent.animate({
        scrollTop: top + "px"
    });

    $message.html('<div class="alert alert-' + type + '" role="alert">' + text + '</div>');
    $message.fadeTo(2500, 500).slideUp(500, function () {
        $message.alert('close');
    });
}

SimpleAuth.Profile = function () {
    var cropper;
    var image = document.getElementById('img-upload');

    $('#input').on('change', function (e) {
        var files = e.target.files;
        var reader;
        var file;

        var done = function (url) {
            $(this).value = '';
            image.src = url;
            $('#cropModal').modal({
                backdrop: 'static',
                keyboard: false
            });
        };

        if (files && files.length > 0) {
            file = files[0];
            if (URL) {
                done(URL.createObjectURL(file));
            } else if (FileReader) {
                reader = new FileReader();
                reader.onload = function (e) {
                    done(reader.result);
                };
                reader.readAsDataURL(file);
            }
        }
    });

    $('#crop_button').on('click', function () {
        var imgurl = cropper.getCroppedCanvas().toDataURL();
        var img = document.createElement("img");
        img.src = imgurl;
        SimpleAuth.onprogress(1);
        cropper.getCroppedCanvas().toBlob(function (blob) {
            var formData = new FormData();
            formData.append('image', blob, 'avatar.png');
            formData.append('action', 'upload');
            $.ajax({
                url: window.location,
                method: "POST",
                data: formData,
                dataType: 'jsonp',
                processData: false,
                contentType: false,
                complete: function () {
                    $('#cropModal').modal('hide');
                    SimpleAuth.onprogress(0);
                },
                success: function (resp) {
                    if (resp.result == 1) {
                        window.location.href = resp.redirect;
                    } else {
                        SimpleAuth.Alert(resp.message, "error");
                    }
                },
                error: function () {
                    SimpleAuth.Alert("Ups! Terjadi kesalahan. Silahkan ulangi beberapa saat lagi!", "error");
                }
            });
        });
    });

    $('#cropModal').on('shown.bs.modal', function () {
        cropper = new Cropper(image, {
            viewMode: 2,
            aspectRatio: 1 / 1,
            crop: function (e) {
                // console.log(e.detail.x);
                // console.log(e.detail.y);
            }
        });
    }).on('hidden.bs.modal', function () {
        cropper.destroy();
        cropper = null;
    });
}

SimpleAuth.onprogress = function (status = 1) {
    if (status) {
        $("body").addClass("onprogress");
    } else {
        $("body").removeClass("onprogress");
    }
}

function setCookie(cname, cvalue, exdays) {
    var d = new Date();
    d.setTime(d.getTime() + (exdays * 24 * 60 * 60 * 1000));
    var expires = "expires=" + d.toUTCString();
    document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
}

function isValidEmail(email) {
    var re = /^([\w-]+(?:\.[\w-]+)*)@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,}(?:\.[a-z]{2})?)$/i;
    return re.test(email);
}