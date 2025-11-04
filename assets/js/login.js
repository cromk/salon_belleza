$(document).ready(function () {

	$("#loginForm").on("submit", function(e) {
      e.preventDefault();

      let usuario = $("#usuario").val().trim();
      let password = $("#password").val().trim();

      $.post("controllers/usuarioController.php", { action: "login", usuario, password }, function(res) {
        if (res.success) {
          window.location.href = "views/index.php";
        } else {
          $("#mensaje").removeClass("d-none").text(res.message || "Credenciales incorrectas");
        }
      }, "json")
      .fail(() => {
        $("#mensaje").removeClass("d-none").text("Error de conexión con el servidor");
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