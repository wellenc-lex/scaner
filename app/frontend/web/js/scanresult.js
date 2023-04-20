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
    Newscan = {nucleiDomain: host, dirscanUrl: host, agreed: '1', 'activescan': '1', 'passivescan': '0', 'notify': '0'};

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

function sendamass(host) {
    amassDomain = host;
    Newscan = {amassDomain: host, agreed: '1', 'activescan': '1', 'passivescan': '0', 'notify': '0'};

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
    var nmaplist = [];
    var dirscanlist = [];
    var UniqueNmaplist = [];
    var UniqueDirscanlist = [];

    $.each($("input[name='nmap']:checked"), function () {
        page = $(this).closest(".page");
        nmaplist.push(page.find("h5.card-title").text());
        nmaplist.push($(this).closest(".card.page-card").find(".card-header.text-truncate").text() );
        $(this).prop("checked", false);
    });
    
    $.each($("input[name='dirscan']:checked"), function () {
        page = $(this).closest(".page");
        dirscanlist.push(page.find("h5.card-title").text());
        dirscanlist.push($(this).closest(".card.page-card").find(".card-header.text-truncate").text() );
        $(this).prop("checked", false);
    });

    var UniqueNmaplist = nmaplist.filter((a) => a);
    var UniqueDirscanlist = dirscanlist.filter((a) => a);

    var nmapDomain = UniqueNmaplist.join(",");

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

    if (UniqueDirscanlist.length != "0") {
        for (n = 0; n < UniqueDirscanlist.length; n++) {
            dirscanUrl = UniqueDirscanlist[n];

            Newscan = {
                "dirscanUrl": dirscanUrl,
                "nucleiDomain": dirscanUrl,
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