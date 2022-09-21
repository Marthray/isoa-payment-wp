var successCallback = function(data) {

	var checkout_form = $( 'form.woocommerce-checkout' );

	// deactivate the tokenRequest function event
	checkout_form.off( 'checkout_place_order', tokenRequest );

	// submit the form now
	checkout_form.submit();

};

var errorCallback = function(data) {
    console.log(data);
};

var tokenRequest = function() {

	// here will be a payment gateway function that process all the card data from your form,
	// maybe it will need your Publishable API key which is misha_params.publishableKey
	// and fires successCallback() on success and errorCallback on failure
	var checkout_form = jQuery( 'form.woocommerce-checkout' );

	if (jQuery('#getTokenStep').length == 0) {
        checkout_form.append('<input type="hidden" id="getTokenStep" name="getTokenStep" value="1">');
    }

	var error_count = jQuery('.woocommerce-error li').length;
	if (error_count >= 1) {
		jQuery('.woocommerce-error').first().empty();
	}
    return true;
		
};

jQuery(function($){
	$(document.body).on('checkout_error', async function () {
		var error_count = $('.woocommerce-error li').length;
		const regex = /(BVC_(.){1,})/

		$('#getTokenStep').val('1');
	
		if (error_count == 1) { // Validation Passed (Just the Fake Error I Created Exists)
			/*let token = null
			//const token = prompt('Por favor, indique el token recibido a su V-Mensaje para autorizar el pago')
			if(token != null && token != '') {
				checkout_form.append('<input type="hidden" id="token" name="token" value='+token+'>');
				$('#getTokenStep').val('');
				$('#place_order').trigger('click');
			}*/
			var error_text = $('.woocommerce-error li').first().text();
			if (regex.test(error_text)){
				$('.woocommerce-error li').first().css('display', 'none');
				const arr = error_text.split('_');
				if(arr.length == 2) {
					idPago = arr[1]; //Agarrar el id del pago

					const { value: token } = await Swal.fire({
						title: 'Token Pago',
						text: 'Por favor, indique el token recibido a su V-Mensaje para autorizar el pago',
						icon: 'info',
						input: 'text',
						inputAttributes: {
						  autocapitalize: 'off'
						},
						showCancelButton: false,
						confirmButtonText: 'Enviar'
					})

					console.log(token)

					if(token != null && token != '') {
						checkout_form.append('<input type="hidden" id="token" name="token" value='+token+'>');
						checkout_form.append('<input type="hidden" id="idPago" name="idPago" value='+idPago+'>');
						$('#getTokenStep').val('');
						$('#place_order').trigger('click');
					}
				}
			}
		}else{ // Validation Failed (Real Errors Exists, Remove the Fake One)
			$('.woocommerce-error li').each(function(){
				var error_text = $(this).text();
				if (regex.test(error_text)){
					$(this).css('display', 'none');
				}
			});
		}
	});

	var checkout_form = $( 'form.woocommerce-checkout' );
	checkout_form.on( 'checkout_place_order', tokenRequest );

});