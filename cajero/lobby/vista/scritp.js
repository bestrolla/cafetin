// ============================================
// 📄 Paginado de tabla - Lobby Cajero
// Autor: Ángel
// ============================================

(function(){
  // Paginador reutilizable para la tabla de factura
  function initPagination() {
    const tableBody = document.querySelector('.tabla-factura tbody');
    if (!tableBody) return;

    // Remover paginador existente
    const existing = document.querySelector('.pagination');
    if (existing) existing.remove();

    const rows = Array.from(tableBody.querySelectorAll('tr'));
    const rowsPerPage = 5; // filas por página
    const totalPages = Math.max(1, Math.ceil(rows.length / rowsPerPage));
    let currentPage = 1;

    const paginationContainer = document.createElement('div');
    paginationContainer.classList.add('pagination');

    function updateTable() {
      rows.forEach((row, index) => {
        row.style.display = index >= (currentPage - 1) * rowsPerPage && index < currentPage * rowsPerPage ? '' : 'none';
      });

      // Actualiza botones
      paginationContainer.querySelectorAll('.page-btn').forEach((btn, idx) => {
        btn.classList.toggle('active', idx + 1 === currentPage);
      });
    }

    for (let i = 1; i <= totalPages; i++) {
      const button = document.createElement('button');
      button.textContent = i;
      button.classList.add('page-btn');
      if (i === currentPage) button.classList.add('active');
      button.addEventListener('click', () => {
        currentPage = i;
        updateTable();
      });
      paginationContainer.appendChild(button);
    }

    const tableContainer = document.querySelector('.tabla');
    if (tableContainer) tableContainer.appendChild(paginationContainer);

    updateTable();
  }

  // Ejecutar al cargar
  document.addEventListener('DOMContentLoaded', initPagination);
  // Re-inicializar cuando la tabla cambie (evento disparado por script.js)
  document.addEventListener('tabla:updated', initPagination);
})();
