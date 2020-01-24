/**
 * PrestaShop module for Printful
 *
 * @author    Printful
 * @copyright 2019 Printful
 * @license   GPL 3 license see LICENSE.txt
 */

var PrintfulConnect = {
    formId: null,
    statusCheckController: null,
    statusCheckAction: null,
    token: null,
    oldPSVersion: false,
    init: function (params) {
        // setting up defaults
        this.formId = params.formId || null;
        this.token = params.token || null;
        this.statusCheckUrl = params.statusCheckUrl || null;
        this.statusCheckController = params.statusCheckController || null;
        this.statusCheckAction = params.statusCheckAction || null;
        this.oldPSVersion = params.oldPSVersion || false;

        // wiring events
        this.wireEvents();
    },
    wireEvents: function () {
        // on form submit, open new tab
        $('#' + this.formId).submit(function (e) {
            var button = $('button[type="submit"]', $(this));
            var connectingText = button.data('connecting-text');

            button
                .html(connectingText)
                .prop('disabled', true);

            var url = $(this).attr('action');
            var data = $(this).serialize();

            if (!PrintfulConnect.oldPSVersion) {
                url += '&' + data;
                window.open(url);

                // starting listening for connection status
                PrintfulConnect.listenStatus();
            } else {
                document.location = url + '&oldPsVersion=1';
            }

            e.preventDefault();
        });
    },
    listenStatus: function () {
        if (this.interval) {
            clearInterval(this.interval);
        }
        this.interval = setInterval(this.checkStatus.bind(this), 3000);
    },
    checkStatus: function () {
        $.ajax({
            type: 'POST',
            cache: false,
            dataType: 'json',
            url: this.statusCheckUrl,
            data: {
                ajax: true,
                controller: this.statusCheckController,
                action: this.statusCheckAction,
            },
            success: function (response) {
                if (response.status) { // if we are connected, reload
                    document.location.reload();
                }
            }
        });
    }
};