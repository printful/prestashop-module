{*
 * PrestaShop module for Printful
 *
 * @author    Printful
 * @copyright 2019 Printful
 * @license   GPL 3 license see LICENSE.txt
 *}

<form class="form form-horizontal" method="post" action="{$action|escape:'html':'UTF-8'}" id="PrintfulConnectForm">
    <div class="row justify-content-center">
        <div class="col-xl-12">
            <div class="card">
                <div class="card-block row">
                    <div class="card-text">
                        <div class="container">
                            <div class="row justify-content-center text-center mb-5">
                                <div class="col-8">
                                    <img class="connect-logo" src="{$logoPath|escape:'html':'UTF-8'}"/><br/>
                                    {l s='You\'re nearly done! Create a new API key or select an existing one you have made for your Printful integration and press \'Connect\'. Your PrestaShop store will be connected to Printful for automatic production and order fulfillment!'  mod='printful'}
                                </div>
                            </div>
                            <div class="row mb-2 text-center justify-content-center">
                                <div class="col-4">
                                    <label class="form-control-label">{l s='Api key'  mod='printful'}</label>
                                    {html_options name=webservice_id options=$webserviceOptions}
                                </div>
                            </div>
                            <div class="row text-center justify-content-center">
                                <div class="col-4">
                                    <button type="submit" class="btn btn-info btn-lg" name="printful_connect" data-connecting-text="{l s='Connecting...' d='Admin.Actions' mod='printful'}">
                                        {l s='Connect' d='Admin.Actions' mod='printful'}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
<script>
    $(document).ready(function () {
        PrintfulConnect.init({
            'formId': 'PrintfulConnectForm',
            'statusCheckUrl': {$statusCheckUrl|@json_encode},
            'statusCheckController': {$statusCheckController|@json_encode},
            'statusCheckAction': {$statusCheckAction|@json_encode},
            'token': {$smarty.get.token|@json_encode},
            'oldPSVersion': {$oldPSVersion|@json_encode}
        });
    });
</script>