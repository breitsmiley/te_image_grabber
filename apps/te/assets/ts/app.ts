const $ = require('jquery');
import 'bootstrap'

$(() => {
    "use strict";
    const $form = $('#appImageControlForm');
    const formDom =  $form[0];
    const $submitButton = $(":submit", $form);
    const $formInputs = $form.find('input');
    const $appImageControlResult = $('#appImageControlResult');
    const $imageGrid = $('#imageGrid');
    const appHelper = {
        init: () => {
            // Forms submit handler
            //----------------------------------
            $form.on("submit", e => {

                e.preventDefault();
                e.stopPropagation();

                formDom.checkValidity();
                formDom.classList.add('was-validated');

                const formData = $form.serialize();
                // --------
                $.ajax({
                    method: 'POST',
                    url: '/ajax/images/grab_form',
                    data: formData,
                    timeout: 120000,
                    beforeSend: () => {
                        $submitButton.prop('disabled', true);
                        $formInputs.prop('readonly', true);
                        $appImageControlResult.hide();
                        $appImageControlResult.html('');
                        $appImageControlResult.removeClass('alert-danger');
                        $appImageControlResult.addClass('alert-success');
                    },
                    complete: () => {
                        $formInputs.prop('readonly', false);
                        $submitButton.prop('disabled', false);
                    }
                }).done(responseData => {
                    if (responseData.status) {

                        $imageGrid.prepend(responseData.data);

                        $appImageControlResult.html('Successfully');
                        $appImageControlResult.removeClass('alert-danger');
                        $appImageControlResult.addClass('alert-success');
                        $appImageControlResult.show().fadeOut(3000);

                    } else {

                        $appImageControlResult.html(responseData.errors);
                        $appImageControlResult.removeClass('alert-success');
                        $appImageControlResult.addClass('alert-danger');
                        $appImageControlResult.show();
                    }
                    formDom.reset();
                    $form.removeClass('was-validated');


                }).fail((jqXHR, textStatus, errorThrown) => {
                    console.log(jqXHR, textStatus, errorThrown);
                });
            });
        }
    };

    appHelper.init();
});
