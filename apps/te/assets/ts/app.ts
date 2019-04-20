/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you require will output into a single css file (app.css in this case)
// require('../css/app.css');


// Need jQuery? Install it with "yarn add jquery", then uncomment to require it.
const $ = require('jquery');

import 'bootstrap'
// import $ from "jquery";
// console.log('111Hello Webpack Encore! Edit me in assets/js/app.js');
//
// (function() {
//     'use strict';
//     window.addEventListener('load', function() {
//         // Fetch all the forms we want to apply custom Bootstrap validation styles to
//         var forms = document.getElementsByClassName('needs-validation');
//         // Loop over them and prevent submission
//         var validation = Array.prototype.filter.call(forms, function(form) {
//             form.addEventListener('submit', function(event) {
//                 if (form.checkValidity() === false) {
//                     event.preventDefault();
//                     event.stopPropagation();
//                 }
//                 form.classList.add('was-validated');
//             }, false);
//         });
//     }, false);
// })();

$(() => {
    "use strict";

    // console.log('111Hello Webpack Encore! Edit me in assets/js/app.js');

    const appHelper = {

        $form: $('#appImageControlForm'),
        $appImageControlResult: $('#appImageControlResult'),
        init: () => {
            console.log('111Hello Webpack Encore! Edit me in assets/js/app.js');

            // const $form = appHelper.$form;
            // const domForm = $form[0];
            // domForm.addEventListener('submit', function(event) {
            //     if (domForm.checkValidity() === false) {
            //         event.preventDefault();
            //         event.stopPropagation();
            //     }
            //     domForm.classList.add('was-validated');
            // }, false);

            // Forms submit handler
            //----------------------------------
            appHelper.$form.on("submit", e => {
            //     //
            //     // $('#appImageControlForm').checkValidity();
            //
                e.preventDefault();
                e.stopPropagation();

                const $form =  appHelper.$form;
                const formDom =  appHelper.$form[0];

                const isValid = formDom.checkValidity();
                formDom.classList.add('was-validated');

                // if(!isValid) {
                    // e.preventDefault();
                    // e.stopPropagation();
                    // return;
                    // console.log(111);
                    // return;
                // }

                // const form = e.currentTarget;
                // const $form = $(form);
                const $submitButton = $(":submit", $form);
                // const $spinner = $submitButton.find("span");
                const formData = $form.serialize();

                // --------
                $.ajax({
                    method: 'POST',
                    url: '/ajax/images/grab_form',
                    data: formData,

                    beforeSend: () => {
                        // $spinner.addClass('ajaxSpinner');
                        appHelper.$appImageControlResult.html('');
                        appHelper.$appImageControlResult.removeClass('alert-danger');
                        appHelper.$appImageControlResult.addClass('alert-success');

                        appHelper.$appImageControlResult.hide();
                        $submitButton.prop('disabled', true);


                    },
                    complete: () => {
                        // $spinner.removeClass('ajaxSpinner');
                        // $spinner.html( $spinner.data('txt'));
                        $submitButton.prop('disabled', false);
                    }
                }).done(responseData => {
                    // window.location.href = "/step1";
                    console.log(responseData);



                    if (responseData.status) {

                        // window.location.href = "/step1";
                        // $.redirect("/step1", {type: responseData.data.type}, "POST");

                        // $.post("/step1", {data1: 'data2'});

                    } else {
                        // let htmlList = '<ul>';
                        // for (const fieldName in responseData.errors) {
                        //     // console.log(fieldName, data.errors[fieldName]);
                        //
                        //     // console.log(`input[value='${fieldName}']`);
                        //     // console.log($form.find(`input[value='${fieldName}']`));
                        //
                        //     const errorMsg = isValid ? '' : 'Введите ваш телефон';
                        //     // const $input = $form.find(`input[name='${fieldName}']`);
                        //     // formsHelper.processInputError($input, false, responseData.errors[fieldName]);
                        //
                        //     // console.log($input);
                        //     htmlList += `${errorMsg}`;
                        // }
                        // htmlList += '</ul>';
                        //
                        // appHelper.$appImageControlResult.html();
                        // appHelper.$appImageControlResult.show();

                        appHelper.$appImageControlResult.html(responseData.errors);
                        appHelper.$appImageControlResult.removeClass('alert-success');
                        appHelper.$appImageControlResult.addClass('alert-danger');
                    }

                    appHelper.$appImageControlResult.show();

                }).fail((jqXHR, textStatus, errorThrown) => {
                    console.log(jqXHR, textStatus, errorThrown);
                });



                // formDom.classList.add('was-validated');
                // console.log(222);
            //
            //     // const form = e.currentTarget;
            //     // const $form = $(form);
            //     // const $submitButton = $(":submit", $form);
            //     // const $spinner = $submitButton.find("span");
            //     // const formData = $form.serialize();
            //     // $('#appImageControlForm').get(0).checkValidity();
            //     // if (!formsHelper.isValid(form)) {
            //     //     return;
            //     // }
            //
            //
            //
            //     // if ($form[0].checkValidity() === false) {
            //     //     event.preventDefault();
            //     //     event.stopPropagation();
            //     // }
            //     // form.classList.add('was-validated');
            //
            //     // console.log(formData);
            //     // console.log(appHelper.$form[0].check);
            //
            //     // // --------
            //     // $.ajax({
            //     //     method: 'POST',
            //     //     url: '/ajax/order/form_submit',
            //     //     data: formData,
            //     //
            //     //     beforeSend: () => {
            //     //         $spinner.addClass('ajaxSpinner');
            //     //         $spinner.data('txt', $spinner.html());
            //     //         $spinner.html('');
            //     //         // $submitButton.prop('disabled', true);
            //     //     },
            //     //     complete: () => {
            //     //         $spinner.removeClass('ajaxSpinner');
            //     //         $spinner.html( $spinner.data('txt'));
            //     //         // $submitButton.prop('disabled', false);
            //     //     }
            //     // }).done(responseData => {
            //     //     // window.location.href = "/step1";
            //     //     // console.log(data);
            //     //
            //     //     if (responseData.status) {
            //     //
            //     //         // window.location.href = "/step1";
            //     //         $.redirect("/step1", {type: responseData.data.type}, "POST");
            //     //
            //     //         // $.post("/step1", {data1: 'data2'});
            //     //
            //     //     } else {
            //     //         for (const fieldName in responseData.errors) {
            //     //             // console.log(fieldName, data.errors[fieldName]);
            //     //
            //     //             // console.log(`input[value='${fieldName}']`);
            //     //             // console.log($form.find(`input[value='${fieldName}']`));
            //     //
            //     //             // const errorMsg = isValid ? '' : 'Введите ваш телефон';
            //     //             const $input = $form.find(`input[name='${fieldName}']`);
            //     //             formsHelper.processInputError($input, false, responseData.errors[fieldName]);
            //     //
            //     //             // console.log($input);
            //     //         }
            //     //     }
            //     //
            //     // }).fail((jqXHR, textStatus, errorThrown) => {
            //     //     console.log(jqXHR, textStatus, errorThrown);
            //     // });
            //
            });
        }
    };

    appHelper.init();
});
