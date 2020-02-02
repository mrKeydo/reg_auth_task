//Проверяем, залогинен ли пользователь
var post_url = 'process.php';
var request_method = 'post';

$.ajax({
    dataType: "json",
    url: post_url,
    type: request_method,
    success: function (resp) {
        if ('success' in resp) {
            success_output(resp);
        }
    }
});


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
                    success_output(resp);
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
            dataType: 'json',
            url: post_url,
            type: request_method,
            data: data,
            success: function (resp) {
                if ('exist' in resp) {
                    $('.output').html(resp['exist']);
                }
                if ('created' in resp) {
                    $('.output').html(resp['created']);
                } else {
                    $.each(resp, function (i, v) {
                        var msg = '<label class="error" for="' + i + '">' + v + '</label>';
                        $('input[name="' + i + '"], select[name="' + i + '"]').addClass('inputTxtError').after(msg);
                    });
                    var keys = Object.keys(resp);
                    $('input[name="' + keys[0] + '"]').focus();
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                console.log('there was a problem checking the fields');
                console.log(jqXHR);
            }
        });
    });

    $('.quit').click(function (event) {
        eraseCookie('session');
        eraseCookie('name');
        document.location.reload(true);
    });
});

function resetErrors() {
    $('form input, form select').removeClass('inputTxtError');
    $('label.error').remove();
    $('.exist').html('');
    $('.not_found').html('');
    $('.created').html('');
}

function success_output(resp) {
    $(".main").html(resp['success']);
}

function eraseCookie(name) {   
    document.cookie = name+'=; Max-Age=-99999999;';  
}