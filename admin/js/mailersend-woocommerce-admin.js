/**
 * Admin JavaScript methods
 *
 * @author MailerSend <support@mailersend.com>
 */
(function($) {
    'use strict';

    $(window).load(function() {

        $('.error').hide();

        /**
         * Settings form save button handler
         */
        $('.mailersend_form_save_button').click(function(e) {
            e.preventDefault();

            var btn = this;

            $('.error').hide();

            var from_name = $('#from_name').val();
            var from_address = $('#from_address').val();
            var cc_address = $('#cc_address').val();
            var bcc_address = $('#bcc_address').val();

			var regex = /^([a-zA-Z0-9_\.\-\+])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;


		    if (from_name == '') {

                $("#from_name").closest('.cnt').find('.ms-error').show(); //.delay(5000).hide("slow");
                $("#from_name").focus();
                return false;
            }

            if (from_address == '') {

                $('#from_address').focus();
                $('#from_address').closest('.cnt').find('.ms-error').text('Please enter the "From" email address').show();
                return false;
            }

            if (!regex.test(from_address)) {

                $('#from_address').closest('.cnt').find('.ms-error').text('Please enter a valid "From" email address').show();
                $('#from_address').focus();
                return false;
            }

            if (cc_address !== '') {

                let cc_error = false;

                cc_address.split(',').forEach( function( address) {
                    if (!regex.test(address.trim())) {

                        $('#cc_address').closest('.cnt').find('.ms-error').text('Please enter a valid "CC" email address, ' + address.trim() + ' is not valid.').show();
                        $('#cc_address').focus();
                        cc_error = true;
                    }
                });

                if ( cc_error === true) {
                    return false;
                }
            }

            if (bcc_address !== '') {

                let bcc_error = false;

                bcc_address.split(',').forEach( function( address) {
                    if (!regex.test(address.trim())) {

                        $('#bcc_address').closest('.cnt').find('.ms-error').text('Please enter a valid "BCC" email address, ' + address.trim() + ' is not valid.').show();
                        $('#bcc_address').focus();
                        bcc_error = true;
                    }
                });

                if ( bcc_error === true) {
                    return false;
                }
            }

            $(this).prop('disabled', true);

            var api_key = $("#mailersend_api_key").val();

            $(".ms-submit-spinner").css('visibility', 'visible');

            var data = {
                action: "check_api_key",
                api_key: api_key,
                from_add: from_address,
                cc_add: cc_address,
                bcc_add: bcc_address
            }

            $.post(ajaxurl, data).done(function(data) {

               var response = JSON.parse(data);

               if (response.validated) {

                   // submit the form on valid api key
                   $('#mailersend_data_form').submit();

               } else {

                   $(".ms-submit-spinner").css('visibility', 'hidden');
                   showApiMessage(
                       'error',
                       'Your API token is not valid'
                   );
                   $('#mailersend_api_key').focus();

                   $(btn).prop('disabled', false);
               }
            });

        });

        /**
         * Handler for all test email buttons
         */
        $('.mail_test').click(function(e) {

            e.preventDefault();

            $('.ms-msg').hide();

            var inputEl = $(this).closest('.cnt').find('.template-id');
            var spinner = $(this).closest('.cnt').find('.ms-test-spinner');

            var templateId = $(inputEl).val().trim();

            var messageElement = $(this).closest('.cnt').find('.ms-msg');

            if (templateId == '') {

                showTemplatesMessage('error', 'Please enter a template id', messageElement);
                return;
            }

            var data = {
                action: $(this).data("mail_action"),
                mail_type: $(this).attr("name"),
                template_id: templateId
            };

            var templateName = $(this).attr('name');

            $(spinner).css('visibility', 'visible');
            var buttonElement = this;
            $(this).attr('disabled', true);


            $.post(ajaxurl, data).done(function(resp) {

                $(spinner).css('visibility', 'hidden');
                $(buttonElement).attr('disabled', false);

                var response = {};

                try {

                    response = JSON.parse(resp);
                } catch (err) {

                    console.log(err);
                }

                if (response.success) {

                    showTemplatesMessage('success', 'The test email has been sent to your email address.', messageElement);
                } else {

                    showTemplatesMessage('error', 'There has been an error while trying to send a test email. ' + (response.response ?? ''), messageElement);
                }
            }).fail(function(xhr, status, error) {

                $(spinner).css('visibility', 'hidden');
                $(buttonElement).attr('disabled', false);
                showTemplatesMessage('error', 'There has been an error while trying to send a test email.', messageElement);
            });
        });


        /**
         * Validate key button handler
         */
        $('#api_key_validate').click(function(e) {

            e.preventDefault();

            $('.ms-error, .ms-msg').hide();

            var api_key = $("#mailersend_api_key").val();

            $(".ms-full-spinner").css('visibility', 'visible');

            var data = {
                action: "check_api_key",
                api_key: api_key
            }

            $.post(ajaxurl, data).done(function(data) {

                var response = JSON.parse(data);

                if (response.validated) {

                    var message = "Your API token has been validated";
                    $(".ms-full-spinner").css('visibility', 'hidden');
                    showApiMessage('success', message);
                } else {

                    var message = "Your API token is not valid";
                    $(".ms-full-spinner").css('visibility', 'hidden');
                    showApiMessage(
                        'error',
                        message
                    );
                }
            });
        });


        /**
         * Shows the success/error messages for the page (api validation and templates forms)
         * @param type
         * @param message
         */
        function showApiMessage(type, message) {

            var element = $('#mailersend_api_key').closest('.cnt').find('.ms-msg');

            if (type == 'error') {

                $(element).removeClass('ms-success').addClass('error');
            } else {

                $(element).removeClass('error').addClass('ms-success');
            }

            $(element).text(message).show();
        }


        /**
         * Shows the success/fail messages for each template
         * @param type
         * @param message
         * @param element
         */
        function showTemplatesMessage(type, message, element) {

            if (type == 'error') {

                $(element).removeClass('ms-success').addClass('error');
            } else {

                $(element).removeClass('error').addClass('ms-success');
            }

            $(element).text(message).show();
        }

    });
})(jQuery);