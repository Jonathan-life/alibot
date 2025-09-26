document.addEventListener("DOMContentLoaded", () => {
  const empresaId = new URLSearchParams(window.location.search).get("id");

  fetch(`/alibot/api/obtener_empresa.php?id=${empresaId}`)
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        const stats = `
          <ul>
            <li><strong>Total de Facturas:</strong> ${data.total_facturas}</li>
            <li><strong>Total Emitido:</strong> S/ ${data.total_importe}</li>
          </ul>
        `;
        document.getElementById("stats").innerHTML = stats;
      } else {
        document.getElementById("stats").innerText = "Error al obtener datos";
      }
    });
});
