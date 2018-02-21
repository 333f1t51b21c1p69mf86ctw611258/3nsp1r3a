//trigger the dialog box to let user choose whether extend session or not.

$(document).ready(function () {
    "use strict";
    showdialog();
});

function callback(value) {
    "use strict";
    if (value) {
        $("#resetbutton").click();
        showdialog();
    } else {
        //setTimeout(function () {document.getElementById("button_save").click();}, 10*1000);
        setTimeout(function () {window.location.replace("/Generic/Users/logout"); }, 2 * 1000);
    }
}

function showdialog() {
    "use strict";
    setTimeout(function () {
        var confirm2 = $('#dconfirm');
        confirm2.html("Your session is about to expire soon!<br> Click yes to remain in log on by resetting time.<br>Click no to logout automatically!");
        confirm2.dialog({
            resizable: false,
            modal: true,
            title: "reminder",
            height: 220,
            buttons: {
                "Yes": function () {
                    $(this).dialog('close');
                    callback(true);
                },
                "No": function () {
                    $(this).dialog('close');
                    callback(false);
                }
            }
        });
        setTimeout(function () {
            if (confirm2.dialog("isOpen")) {
                confirm2.dialog('close');
                //setTimeout(function () {document.getElementById("button_save").click();}, 10*1000);
                setTimeout(function () {window.location.replace("/Generic/Users/logout"); }, 2 * 1000);
            }
        }, 50 * 1000);
    }, (timeCount - 1) * 60 * 1000);
}
