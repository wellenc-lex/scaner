function sendnotifications(status, scanid) {

    if (status === 0) {
        action = 0;//off
    }
    else if (status === 1) {
        action = 1;//on
    }

    $.ajax({

        url: '/scan/notifications',
        type: 'POST',
        data: {
            action: action,
            scanid: scanid,
            "_csrf-frontend": yii.getCsrfToken()
        },
        success: function () {
            $("#messagesuccess").hide();
            $("#messagesuccess").slideDown().show();
            setTimeout(function () {
                $("#messagesuccess").slideUp().hide();
            }, 10000);
        },
        error: function () {
            $("#messagefailure").hide();
            $("#messagefailure").slideDown().show();
            setTimeout(function () {
                $("#messagefailure").slideUp().hide();
            }, 10000);
        }
    });

    return false;
}


function sendactive(status, scanid) {

    if (status === 0) {
        action = 0;//off
    }
    else if (status === 1) {
        action = 1;//on
    }

    $.ajax({

        url: '/scan/active',
        type: 'POST',
        data: {
            action: action,
            scanid: scanid,
            "_csrf-frontend": yii.getCsrfToken()
        },
        success: function () {
            $("#messagesuccess").hide();
            $("#messagesuccess").slideDown().show();
            setTimeout(function () {
                $("#messagesuccess").slideUp().hide();
            }, 10000);
        },
        error: function () {
            $("#messagefailure").hide();
            $("#messagefailure").slideDown().show();
            setTimeout(function () {
                $("#messagefailure").slideUp().hide();
            }, 10000);
        }
    });

    return false;
}

function hide(status, scanid) {

    if (status === 0) {
        action = 0;//hide
    } else if (status === 1) {
        action = 1;//unhide
    }

    $.ajax({

        url: '/scan/hide',
        type: 'POST',
        data: {
            action: action,
            scanid: scanid,
            "_csrf-frontend": yii.getCsrfToken()
        },
        success: function () {
            $("#messagesuccess").hide();
            $("#messagesuccess").slideDown().show();
            setTimeout(function () {
                $("#messagesuccess").slideUp().hide();
            }, 10000);
        },
        error: function () {
            $("#messagefailure").hide();
            $("#messagefailure").slideDown().show();
            setTimeout(function () {
                $("#messagefailure").slideUp().hide();
            }, 10000);
        }
    });

    return false;
}

function deletefunc(scanid) {

    $.ajax({

        url: '/scan/delete',
        type: 'POST',
        data: {
            scanid: scanid,
            "_csrf-frontend": yii.getCsrfToken()
        },
        success: function () {
            $("#messagesuccess").hide();
            $("#messagesuccess").slideDown().show();
            setTimeout(function () {
                $("#messagesuccess").slideUp().hide();
            }, 10000);
        },
        error: function () {
            $("#messagefailure").hide();
            $("#messagefailure").slideDown().show();
            setTimeout(function () {
                $("#messagefailure").slideUp().hide();
            }, 10000);
        }
    });

    return false;
}