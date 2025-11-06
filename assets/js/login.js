$(document).ready(function () {

    $("#loginForm").on("submit", function(e) {
      e.preventDefault();

      const btn = $('#btnLogin');
      btn.prop('disabled', true).attr('aria-busy', 'true');

      let usuario = $("#usuario").val().trim();
      let password = $("#password").val().trim();

      // Petición AJAX más robusta
      $.ajax({
        url: "controllers/usuarioController.php",
        method: 'POST',
        dataType: 'json',
        data: { action: "login", usuario, password },
        timeout: 10000
      }).done(function(res) {
        if (res && res.success) {
          // Redirigir al panel
          window.location.href = "views/index.php";
        } else {
          const msg = (res && res.message) ? res.message : "Usuario o contraseña incorrectos";
          $("#mensaje").removeClass("d-none alert-success").addClass("alert-danger").text(msg);
        }
      }).fail(function(jqXHR, textStatus, errorThrown) {
        // Manejo de errores: mostrar mensaje útil
        let msg = "Error de conexión con el servidor";
        if (textStatus === 'timeout') msg = 'La petición tardó demasiado. Intenta de nuevo.';
        $("#mensaje").removeClass("d-none alert-success").addClass("alert-danger").text(msg);
      }).always(function() {
        // Siempre reactivar el botón para permitir reintentos
        btn.prop('disabled', false).removeAttr('aria-busy');
      });
    });
    
    // Toggle mostrar/ocultar contraseña
    $(document).on('click', '#togglePassword', function () {
      const input = $('#password');
      const icon = $(this).find('i');
      const type = input.attr('type') === 'password' ? 'text' : 'password';
      input.attr('type', type);
      icon.toggleClass('bi-eye bi-eye-slash');
    });

});