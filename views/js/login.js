$(document).ready(function () {
	$('#login').on('click', '#btnEnviar', btnLogin);
});

/**
 * Funcion que envía el login
 */
function btnLogin(e) {
	e.preventDefault();
	var datos = $('form.login').serialize();
//	var datos = { action:'login', nickname: $('#txtUsuario').val(), password:$('#txtPassword').val() }
	$.ajax({
		type: "POST",
		url: webPath + 'ajax/login/',
		data: datos,
		dataType: "json",
		beforeSend: function () {
			// Antes de mandar
		},
		success: function (json) {
			// Si salió todo bien
			if (json.status) {
				window.location = webPath;
			} else {
				$('#txtPassword').select();
				$('#divError').html(json.mensaje);
			}
		}, error: function () {
			$('#txtPassword').select();
			$('#divError').html('Ocurrió un error al procesar la información.');
		}
	});
}