jQuery(document).ready(function ($) {

    const $formContainer    = $('#frm_form_3_container');
    const $thankSlide       = $('.request_modal_info--thank');
    const $errorSlide       = $('.request_modal_info--error');
    const $error_text       = $errorSlide.find('.error--text');
    const $formSlide        = $('.request_modal_info--formbox');

    // Reset all states first
    $thankSlide.removeClass('active');
    $errorSlide.removeClass('active');
    $formSlide.removeClass('active');

    // Only if Formidable form exists
    if ($formContainer.length>0) {

        let get_form        = $formContainer.find('form');
        let msgText         = '';

        // If form is not there go for Error / Duplicate / Blocked submission
        if( get_form.length === 0 ) {
            const $status   = $formContainer.find('[role="status"] p');

            if ($status.length) {

                msgText = $status.text().trim();

                if (msgText !== "") {

                    // SUCCESS
                    if ($formContainer.find('.frm_message').length) {
                        $thankSlide.addClass('active');
                        return;
                    }

                    // ERROR / DUPLICATE
                    if ($formContainer.find('.frm_error_style').length) {
                        $errorSlide.find('.error--text').text(msgText).end().addClass('active');
                        return;
                    }

                }else{
                    msgText = 'Something went wrong. Please try again.';
                    $errorSlide.find('.error--text').text(msgText).end().addClass('active');
                    return;
                }
            }
        }
    }

    // Form Validation using jQuery Validate plugin
    var validator = $('#callback3').validate({
        rules: {
            firstname: {
                required: true,
                minlength: 3
            },
            lastname: {
                required: true,
                minlength: 2
            },
            useremail: {
                required: true,
                email: true
            },
            usermobile: {
                required: true,
                digits: true,
                minlength: 10,
                maxlength: 10
            },
            usercity: {
                required: true,
                minlength: 2
            }
        },
        messages: {
            firstname: {
                required: "Please enter your first name",
                minlength: "Your first name must be at least 3 characters long"
            },
            lastname: {
                required: "Please enter your last name",
                minlength: "Your last name must be at least 3 characters long"
            },
            useremail: {
                required: "Please enter your email address",
                email: "Please enter a valid email address"
            },
            usermobile: {
                required: "Please enter your mobile number",
                digits: "Please enter only digits",
                minlength: "Mobile number must be 10 digits",
                maxlength: "Mobile number must be 10 digits"
            },
            usercity: {
                required: "Please enter your city",
                minlength: "City name must be at least 2 characters long"
            }
        }
    });

    $('#studiosubmitbtn').on('click', function () {

        if (!validator.form()) return;  // stop if invalid

        $(this).prop('disabled', true).text('Submitting...');

        // Map values
        $('input[name="item_meta[31][first]"]').val($('#firstname').val());
        $('input[name="item_meta[31][last]"]').val($('#lastname').val());
        $('input[name="item_meta[32]"]').val($('#email').val());
        $('input[name="item_meta[33]"]').val($('#mobile').val());
        $('input[name="item_meta[22]"]').val($('#city').val());
        $('textarea[name="item_meta[14]"]').val('Studio Enquiry');

        // Submit ONLY the hidden Formidable form
        $('.portl_form_modal #frm_form_3_container .frm_button_submit').trigger('click');
    });


    // Formidable AJAX/form submission complete
    $(document).on('frmFormComplete', function (event, form, response) {

        // console.log('Formidable form submission complete');
        // console.log(form.id);
        // console.log(response);

        // Only for Form ID = form_contactuspage
        if (!form || form.id !== 'form_contactuspage') return;

        // Reset all states first
        $formSlide.removeClass('active');
        $thankSlide.removeClass('active');
        $errorSlide.removeClass('active');
        $error_text.text('');

        if (response.errors.length === 0) {
            $thankSlide.addClass('active');
        } else {
            let errorMsg = 'Something went wrong. Please try again.';

            const $submit_status   = $formContainer.find('[role="status"] p');

            if ($submit_status.length) {
                errorMsg = $submit_status.text().trim();
                if (errorMsg !== '') {
                    // SUCCESS / DUPLICATE / ERROR
                    if ($formContainer.find('.frm_message').length) {
                        $thankSlide.addClass('active');
                    }else if ($formContainer.find('.frm_error_style').length) {
                        $errorSlide.find('.error--text').text(errorMsg).end().addClass('active');
                    }
                }else{
                    $errorSlide.find('.error--text').text(errorMsg).end().addClass('active');
                }
            }
        }

        $('#studiosubmitbtn').prop('disabled', false).text('Submit');
    });

});