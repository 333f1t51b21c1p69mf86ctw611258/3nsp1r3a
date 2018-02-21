//the send the request to server so that server can reset session time 
function reset_session() {
    "use strict";
    var xmlhttp;
    xmlhttp = new XMLHttpRequest();
    xmlhttp.open("GET", "#", true);
    xmlhttp.send();
}

