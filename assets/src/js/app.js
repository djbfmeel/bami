(function () {
    var method;
    var noop = function noop() {
    };
    var methods = [
        'assert', 'clear', 'count', 'debug', 'dir', 'dirxml', 'error',
        'exception', 'group', 'groupCollapsed', 'groupEnd', 'info', 'log',
        'markTimeline', 'profile', 'profileEnd', 'table', 'time', 'timeEnd',
        'timeStamp', 'trace', 'warn'
    ];
    var length = methods.length;
    var console = (window.console = window.console || {});

    while (length--) {
        method = methods[length];

        // Only stub undefined methods.
        if (!console[method]) {
            console[method] = noop;
        }
    }
}());

$(document).ready(function () {
    client.init();
});

var client = {
    init: function () {

        var rabbitMqHost = '192.168.2.203';
        $client = Stomp.over(new SockJS("http://" + rabbitMqHost + ":15674/stomp"));

        // RabbitMQ SockJS does not support heartbeats so disable them
        $client.heartbeat.incoming = 0;
        $client.heartbeat.outgoing = 0;

        $client.debug = this.onDebug;

        // Make sure the user has limited access rights
        $client.connect("guest", "guest", onConnect, this.onError, "/");

        function onConnect() {
            var $baseUrl = "/topic/";

            //Bami add topic
            $client.subscribe($baseUrl + 'bami.add', function (d) {
                var $value = d.body;
                console.log($value);
                $("#orderList").append('<li>' + $value + '</li>');
            });
        }
    },

    onError: function (e) {
        console.log("STOMP ERROR", e);
    },

    onDebug: function (m) {
        //console.log("STOMP DEBUG", m);
    }
};