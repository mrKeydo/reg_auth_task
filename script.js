var data = {};
$(document).ready(function () {
    var tab = $('#tabs .tabs-items > div');
    tab.hide().filter(':first').show();

    // Клики по вкладкам.
    $('#tabs .tabs-nav a').click(function () {
        tab.hide();
        tab.filter(this.hash).show();
        $('#tabs .tabs-nav a').removeClass('active');
        $(this).addClass('active');
        return false;
    }).filter(':first').click();

    // Клики по якорным ссылкам.
    $('.tabs-target').click(function () {
        $('#tabs .tabs-nav a[href=' + $(this).data('id') + ']').click();
    });

    //Для формы авторизации
    $("#login").submit(function (event) {
        resetErrors();
        data = {};
        $("#login input").each(function (i, v) {
            if (v.type !== 'submit') {
                data[v.name] = v.value;
            }
        });

        event.preventDefault();
        var post_url = 'process.php';
        var request_method = $(this).attr("method");

        $.ajax({
            dataType: "json",
            url: post_url,
            type: request_method,
            data: data,
            success: function (resp) {
                if ('not_found' in resp) {
                    $(".not_found").html(resp['not_found']);
                }
                if ('success' in resp) {
                    $(".main").html("<h1>" + resp['success'] + "</h1>");
                } else {
                    $.each(resp, function (i, v) {
                        console.log(i + " => " + v);
                        var msg = '<label class="error" for="' + i + '">' + v + '</label>';
                        $('input[name="' + i + '"], select[name="' + i + '"]').addClass('inputTxtError').after(msg);
                    });
                    var keys = Object.keys(resp);
                    $('input[name="' + keys[0] + '"]').focus();
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                console.log(jqXHR);
            }
        });
    });

    //Для формы регистрации
    $("#registration").submit(function (event) {
        resetErrors();
        data = {};
        $("#registration input").each(function (i, v) {
            if (v.type !== 'submit') {
                data[v.name] = v.value;
            }
        });

        event.preventDefault();
        var post_url = 'process.php';
        var request_method = $(this).attr("method");

        $.ajax({
            dataType: "json",
            url: post_url,
            type: request_method,
            data: data,
            success: function (resp) {
                if ('exist' in resp) {
                    $(".exist").html(resp['exist']);
                }
                if ('created' in resp) {
                    $(".created").html(resp['created']);
                } else {
                    $.each(resp, function (i, v) {
                        var msg = '<label class="error" for="' + i + '">' + v + '</label>';
                        $('input[name="' + i + '"], select[name="' + i + '"]').addClass('inputTxtError').after(msg);
                    });
                    var keys = Object.keys(resp);
                    $('input[name="' + keys[0] + '"]').focus();
                }
            },
            error: function () {
                console.log('there was a problem checking the fields');
            }
        });
    });
});

function resetErrors() {
    $('form input, form select').removeClass('inputTxtError');
    $('label.error').remove();
    $('.exist').html('');
}