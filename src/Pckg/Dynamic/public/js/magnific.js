$(document).ready(function () {

    // Create IE + others compatible event handler
    var eventMethod = window.addEventListener ? "addEventListener" : "attachEvent";
    var eventer = window[eventMethod];
    var messageEvent = eventMethod == "attachEvent" ? "onmessage" : "message";

    // Listen to message from child window
    eventer(messageEvent, function (e) {
        if (e.data == 'popup.close') {
            $.magnificPopup.close();

        } else if (e.data == 'refresh') {
            window.location.href = window.location.href;

        }
    }, false);

    $('.popup-iframe').magnificPopup({
        type: 'iframe'
    });

});