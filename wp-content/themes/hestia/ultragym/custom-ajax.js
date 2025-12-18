jQuery(document).ready(function($) {
    // Initialize validation
    $('#callback').validate({
        rules: {
            username: {
                required: true,
                minlength: 3
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
            username: {
                required: "Please enter your name",
                minlength: "Your name must be at least 3 characters long"
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
        },
        submitHandler: function(form) {
            // Handle form submission via AJAX or normal submission
            $('#submitbtn').prop('disabled', true).text('Submitting...');
            var data = {
                action: 'custom_form_submit',
                security: ajax_object.nonce,
                username: $('#name').val(),
                useremail: $('#email').val(),
                usermobile: $('#mobilenumber').val(),
                usercity: $('#city').val()
            };

            $.ajax({
                url: ajax_object.ajax_url,
                type: 'POST',
                data: data,
                success: function(response) {
                    if (response.success) {
                        $('.request_modal_info--thank').addClass('active');
                    } else {
                        alert("Unable to submit. Try after sometime");
                    }
                },
                error: function() {
                    alert("Something went wrong");
                    $('#submitbtn').prop('disabled', false).text('Submit');
                },
                complete: function() {
                    $('#submitbtn').prop('disabled', false).text('Submit');
                }
            });
        }
    });


    $('#callback2').validate({
        rules: {
            username: {
                required: true,
                minlength: 3
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
            username: {
                required: "Please enter your name",
                minlength: "Your name must be at least 3 characters long"
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
        },
        submitHandler: function(form) {
            // Handle form submission via AJAX or normal submission
            $('#submitbtn').prop('disabled', true).text('Submitting...');
            var data = {
                action: 'custom_form_submit_2',
                security: ajax_object.nonce,
                username: $('#name').val(),
                useremail: $('#email').val(),
                usermobile: $('#mobilenumber').val(),
                usercity: $('#city').val()
            };

            $.ajax({
                url: ajax_object.ajax_url,
                type: 'POST',
                data: data,
                success: function(response) {
                    if (response.success) {
                        $('.request_modal_info--thank').addClass('active');
                    } else {
                        alert("Unable to submit. Try after sometime");
                    }
                },
                error: function() {
                    alert("Something went wrong");
                    $('#submitbtn').prop('disabled', false).text('Submit');
                },
                complete: function() {
                    $('#submitbtn').prop('disabled', false).text('Submit');
                }
            });
        }
    });

    // for studio page form validation and submission
    $('#callback3').validate({
        rules: {
            username: {
                required: true,
                minlength: 3
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
            username: {
                required: "Please enter your name",
                minlength: "Your name must be at least 3 characters long"
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
        },
        submitHandler: function(form) {
            // Handle form submission via AJAX or normal submission
            $('#studiosubmitbtn').prop('disabled', true).text('Submitting...');
            var data = {
                action: 'custom_form_submit_3',
                security: ajax_object.nonce,
                username: $('#name').val(),
                useremail: $('#email').val(),
                usermobile: $('#mobile').val(),
                usercity: $('#city').val()
            };

            $.ajax({
                url: ajax_object.ajax_url,
                type: 'POST',
                data: data,
                success: function(response) {
                    if (response.success) {
                        $('.request_modal_info--thank').addClass('active');
                    } else {
                        alert("Unable to submit. Try after sometime");
                    }
                },
                error: function() {
                    alert("Something went wrong");
                    $('#studiosubmitbtn').prop('disabled', false).text('Submit');
                },
                complete: function() {
                    $('#studiosubmitbtn').prop('disabled', false).text('Submit');
                }
            });
        }
    });
});
