{#

Copyright (C) 2015 Manuel Faux <mfaux@conf.at>                            

All rights reserved.

Redistribution and use in source and binary forms, with or without modification,
are permitted provided that the following conditions are met:

1.  Redistributions of source code must retain the above copyright notice,
    this list of conditions and the following disclaimer.

2.  Redistributions in binary form must reproduce the above copyright notice,
    this list of conditions and the following disclaimer in the documentation
    and/or other materials provided with the distribution.

THIS SOFTWARE IS PROVIDED “AS IS” AND ANY EXPRESS OR IMPLIED WARRANTIES,
INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY
AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
AUTHOR BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY,
OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
POSSIBILITY OF SUCH DAMAGE.

#}

<script type="text/javascript">
    $(document).ready(function() {
        var data_get_map = {'frm_GeneralSettings':"/api/aiccu/settings/get"};

        // load initial data
        mapDataToFormUI(data_get_map).done(function() {
            // Load success
            ajaxCall(url="/api/proxy/service/status", sendData={}, callback=function(data,status) {
                updateServiceStatusUI(data['status']);
            });
        });

        $("#save_GeneralSettings").click(function() {
            saveFormToEndpoint(url="/api/aiccu/settings/set",formid='frm_GeneralSettings',callback_ok=function() {
                // Save success
                $("#frm_GeneralSettings_progress").addClass("fa fa-spinner fa-pulse");

                ajaxCall(url="/api/aiccu/service/reconfigure", sendData={}, callback=function(data,status){
                    // when done, disable progress animation.
                    $("#frm_GeneralSettings_progress").removeClass("fa fa-spinner fa-pulse");

                    if (status != "success" || data['status'] != 'ok' ) {
                        // fix error handling
                        BootstrapDialog.show({
                            type:BootstrapDialog.TYPE_WARNING,
                            title: frm_title,
                            message: JSON.stringify(data),
                            draggable: true
                        });
                    } else {
                        // request service status after successful save and update status box (wait a few seconds before update)
                        setTimeout(function(){
                            ajaxCall(url="/api/proxy/service/status", sendData={}, callback=function(data,status) {
                                updateServiceStatusUI(data['status']);
                            });
                        },3000);
                    }
                });
            });
        });
    });
</script>

{{ partial("layout_partials/base_form",['fields':generalForm,'id':'frm_GeneralSettings','apply_btn_id':'save_GeneralSettings'])}}

{#
vim: filetype=html
#}
