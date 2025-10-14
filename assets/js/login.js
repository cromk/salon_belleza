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
    
});