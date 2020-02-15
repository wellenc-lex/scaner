function sendnmap(host) {

    nmapDomain = host;
    Newscan = {nmapDomain: host, 'agreed': '1', 'activescan': '1', 'passivescan': '0', 'notify': '0'};

    $.ajax({

        url: '/site/newscan',
        type: 'POST',
        data: {
            Newscan: Newscan,
            "_csrf-frontend": yii.getCsrfToken(),
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


function senddirscan(host) {
    dirscanDomain = host;
    Newscan = {dirscanUrl: host, agreed: '1', 'activescan': '1', 'passivescan': '0', 'notify': '0'};

    $.ajax({

        url: '/site/newscan',
        type: 'POST',
        data: {
            Newscan: Newscan,
            "_csrf-frontend": yii.getCsrfToken(),

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

function sendvhost(host) {
    vhostDomain = host;
    Newscan = {vhostDomain: host, "agreed": '1', 'activescan': '1', 'passivescan': '0', 'notify': '0'};

    $.ajax({

        url: '/site/newscan',
        type: 'POST',
        data: {
            Newscan: Newscan,
            "_csrf-frontend": yii.getCsrfToken(),
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


function sendtoscan() {
    //цикл, pop on send for dirscan
    //onclick change class = checkbox-active

    //or get each .page and check its checboxes

    //var page    = $(this).closest(".page"); // нужно опросить все в кластере, а не только 1
    //var nmap     = page.find("h5.card-title").text();
    //var dirscan     = page.find("h5.card-title").text();

    var nmaplist = [];
    var dirscanlist = [];

    $.each($("input[name='nmap']:checked"), function () {
        page = $(this).closest(".page");
        nmaplist.push(page.find("h5.card-title").text());
        $(this).prop("checked", false);
    });

    $.each($("input[name='dirscan']:checked"), function () {
        page = $(this).closest(".page");
        dirscanlist.push(page.find("h5.card-title").text());
        $(this).prop("checked", false);
    });

    var nmapDomain = nmaplist.join(",");

    if (nmapDomain !== "") {
        Newscan = {
            "nmapDomain": nmapDomain,
            "agreed": '1',
            'activescan': '1',
            'passivescan': '0',
            'notify': '0'
        };

        $.ajax({

            url: '/site/newscan',
            type: 'POST',
            data: {
                Newscan: Newscan,
                "_csrf-frontend": yii.getCsrfToken(),
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

    }

    if (dirscanlist.length != "0") {
        for (n = 0; n < dirscanlist.length; n++) {
            dirscanUrl = dirscanlist[n];

            Newscan = {
                "dirscanUrl": dirscanUrl,
                "agreed": '1',
                'activescan': '1',
                'passivescan': '0',
                'notify': '0'
            };

            $.ajax({

                url: '/site/newscan',
                type: 'POST',
                data: {
                    Newscan: Newscan,
                    "_csrf-frontend": yii.getCsrfToken(),
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
        }


    }

    return false;

}