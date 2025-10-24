document.addEventListener('DOMContentLoaded', function () {
  const searchInput = document.getElementById('search-input');
  const statusFilter = document.getElementById('status-filter');
  const table = document.getElementById('tabla-inventario');
  const tbody = table ? table.querySelector('tbody') : null;
  const pagination = document.getElementById('pagination');

  // 🔹 ELEMENTOS PARA CÁLCULOS AUTOMÁTICOS
  const cajasInput = document.getElementById('caja_produc');
  const unidadesPorCajaInput = document.getElementById('cantidad_caja');
  const precioCajaInput = document.getElementById('precio_caja');
  const precioUnidadInput = document.getElementById('precio_produc');
  
  // 🔹 FUNCIONES DE CÁLCULO AUTOMÁTICO
  function calcularPrecioUnidad() {
    const cajas = parseFloat(cajasInput?.value) || 0;
    const unidadesPorCaja = parseFloat(unidadesPorCajaInput?.value) || 0;
    const precioCaja = parseFloat(precioCajaInput?.value) || 0;
    
    if (unidadesPorCaja > 0 && precioCaja > 0) {
      const precioUnidad = precioCaja / unidadesPorCaja;
      if (precioUnidadInput) {
        precioUnidadInput.value = precioUnidad.toFixed(2);
        
        // Agregar animación visual para indicar que se calculó
        precioUnidadInput.style.backgroundColor = '#e8f5e8';
        setTimeout(() => {
          precioUnidadInput.style.backgroundColor = '';
        }, 1000);
      }
    } else if (precioUnidadInput) {
      precioUnidadInput.value = '';
    }
  }
  
  function mostrarUnidadesTotales() {
    const cajas = parseFloat(cajasInput?.value) || 0;
    const unidadesPorCaja = parseFloat(unidadesPorCajaInput?.value) || 0;
    const totalUnidades = cajas * unidadesPorCaja;
    
    // Mostrar el total en algún lugar visible (puedes agregar un elemento para esto)
    console.log(`Total de unidades: ${totalUnidades}`);
  }
  
  // 🔹 EVENT LISTENERS PARA CÁLCULOS AUTOMÁTICOS
  if (precioCajaInput && unidadesPorCajaInput) {
    precioCajaInput.addEventListener('input', calcularPrecioUnidad);
    unidadesPorCajaInput.addEventListener('input', calcularPrecioUnidad);
  }
  
  if (cajasInput && unidadesPorCajaInput) {
    cajasInput.addEventListener('input', mostrarUnidadesTotales);
    unidadesPorCajaInput.addEventListener('input', mostrarUnidadesTotales);
  }

  // Toggle switch functionality
  const toggleSwitch = document.querySelector('.toggle-switch');
  const toggleInput = document.getElementById('edit-activo');
  
  if (toggleSwitch && toggleInput) {
    toggleSwitch.addEventListener('click', function(e) {
      e.preventDefault();
      toggleInput.checked = !toggleInput.checked;
      
      // Trigger change event for any form validation or other listeners
      const changeEvent = new Event('change', { bubbles: true });
      toggleInput.dispatchEvent(changeEvent);
    });
    
    // Also handle direct clicks on the input (for accessibility)
    toggleInput.addEventListener('click', function(e) {
      e.stopPropagation();
    });
  }

  const API = '../logica/obtener_inventario.php';
  let state = {
    q: '',
    status: 'todo',
    page: 1,
    per_page: 10
  };

  const debounce = (fn, delay = 200) => {
    let t;
    return (...args) => {
      clearTimeout(t);
      t = setTimeout(() => fn.apply(this, args), delay);
    };
  };

  async function loadData() {
    if (!tbody) return;
    const params = new URLSearchParams({
      q: state.q,
      status: state.status,
      page: state.page,
      per_page: state.per_page
    });
    try {
      const res = await fetch(API + '?' + params.toString());
      const contentType = res.headers.get('content-type') || '';

      if (!res.ok) {
        // intenta leer texto para ver el error del servidor
        const text = await res.text();
        console.error('HTTP error:', res.status, text);
        tbody.innerHTML = `<tr><td colspan="7">Error en la petición al servidor (ver consola)</td></tr>`;
        return;
      }

      if (!contentType.includes('application/json')) {
        // respuesta no-JSON (HTML de debug/error). lo mostramos en consola y en la tabla.
        const text = await res.text();
        console.error('Respuesta no JSON del servidor:', text);
        tbody.innerHTML = `<tr><td colspan="7">Respuesta inválida del servidor. Revisa la consola / Network tab.</td></tr>`;
        return;
      }

      const data = await res.json();
      if (data.success) {
        // Guardar los productos en sessionStorage para el modal
        sessionStorage.setItem('productos_actuales', JSON.stringify(data.inventario));
        renderTable(data.inventario);
        // Como obtener_inventario.php no tiene paginación, no renderizamos paginación
        // renderPagination(data.page, data.total_pages);
      } else {
        tbody.innerHTML = `<tr><td colspan="7">Error: ${data.message}</td></tr>`;
      }
    } catch (error) {
      console.error('Error al cargar datos:', error);
      tbody.innerHTML = `<tr><td colspan="7">Error de conexión</td></tr>`;
    }
  }

  function renderTable(products) {
    if (!tbody) return;
    if (!products || products.length === 0) {
      tbody.innerHTML = '<tr><td colspan="7">No hay productos disponibles</td></tr>';
      return;
    }

    tbody.innerHTML = products.map(p => {
      const cantidad_total = p.caja_produc * p.cantidad_caja;
      const estado = p.activo ? 'Activo' : 'Inactivo';
      return `
        <tr>
          <td>${p.nombre_produc}</td>
          <td>${cantidad_total}</td>
          <td>${p.caja_produc}</td>
          <td>$${parseFloat(p.precio_produc).toFixed(2)}</td>
          <td>$${parseFloat(p.precio_venta).toFixed(2)}</td>
          <td><span class="status ${p.activo ? 'active' : 'inactive'}">${estado}</span></td>
          <td>
            <div class="actions">
              <button class="btn btn-edit" data-id="${p.id_producto}">
                <span class="btn-icon">✏️</span>
                <span class="btn-text">Editar</span>
              </button>
              <button class="btn btn-delete" data-id="${p.id_producto}">
                <span class="btn-icon">🗑️</span>
                <span class="btn-text">Eliminar</span>
              </button>
            </div>
          </td>
        </tr>
      `;
    }).join('');
  }

  function renderPagination(currentPage, totalPages) {
    if (!pagination) return;
    if (totalPages <= 1) {
      pagination.innerHTML = '';
      return;
    }

    let html = '';
    
    // Botón anterior
    if (currentPage > 1) {
      html += `<button class="page-btn" data-page="${currentPage - 1}">Anterior</button>`;
    }

    // Números de página
    for (let i = 1; i <= totalPages; i++) {
      if (i === currentPage) {
        html += `<button class="page-btn active" data-page="${i}">${i}</button>`;
      } else {
        html += `<button class="page-btn" data-page="${i}">${i}</button>`;
      }
    }

    // Botón siguiente
    if (currentPage < totalPages) {
      html += `<button class="page-btn" data-page="${currentPage + 1}">Siguiente</button>`;
    }

    pagination.innerHTML = html;
  }

  // Event listeners
  if (searchInput) {
    searchInput.addEventListener('input', debounce((e) => {
      state.q = e.target.value;
      state.page = 1;
      loadData();
    }));
  }

  if (statusFilter) {
    statusFilter.addEventListener('change', (e) => {
      state.status = e.target.value;
      state.page = 1;
      loadData();
    });
  }

  // Event delegation para botones de la tabla
  if (table) {
    table.addEventListener('click', function(e) {
      if (e.target.classList.contains('btn-edit')) {
        const id = e.target.getAttribute('data-id');
        abrirModalEditar(id);
      } else if (e.target.classList.contains('btn-delete')) {
        const id = e.target.getAttribute('data-id');
        if (confirm('¿Estás seguro de que quieres eliminar este producto?')) {
          fetch('../logica/eliminar_producto.php', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'id_producto=' + encodeURIComponent(id)
          })
          .then(response => response.json())
          .then(data => {
            if (data.success) {
              alert('Producto eliminado correctamente');
              loadData(); // Recargar la tabla
            } else {
              alert('Error: ' + data.message);
            }
          })
          .catch(error => {
            console.error('Error:', error);
            alert('Error al eliminar el producto');
          });
        }
      }
    });
  }

  // Event delegation para paginación
  if (pagination) {
    pagination.addEventListener('click', function(e) {
      if (e.target.classList.contains('page-btn') && !e.target.classList.contains('active')) {
        const page = parseInt(e.target.getAttribute('data-page'));
        if (page && page !== state.page) {
          state.page = page;
          loadData();
        }
      }
    });
  }

  // Funciones para el modal de edición (ahora dentro del ámbito correcto)
  window.abrirModalEditar = function(id) {
    // Buscar el producto en los datos actuales
    const productos = JSON.parse(sessionStorage.getItem('productos_actuales') || '[]');
    const producto = productos.find(p => p.id_producto == id);
    
    if (!producto) {
      alert('Producto no encontrado');
      return;
    }
    
    // Llenar el formulario con los datos del producto
    document.getElementById('edit-id').value = producto.id_producto;
    document.getElementById('edit-nombre').value = producto.nombre_produc;
    document.getElementById('edit-cajas').value = producto.caja_produc;
    document.getElementById('edit-unidades').value = producto.cantidad_caja;
    document.getElementById('edit-precio-caja').value = producto.precio_caja || '';
    document.getElementById('edit-precio-unidad').value = producto.precio_produc;
    document.getElementById('edit-precio-venta').value = producto.precio_venta;
    document.getElementById('edit-activo').checked = producto.activo;
    
    // 🔹 AGREGAR EVENT LISTENERS PARA CÁLCULOS EN EL MODAL
    const editPrecioCaja = document.getElementById('edit-precio-caja');
    const editUnidades = document.getElementById('edit-unidades');
    const editPrecioUnidad = document.getElementById('edit-precio-unidad');
    const editCajas = document.getElementById('edit-cajas');
    
    // Función para calcular precio por unidad en el modal
    function calcularPrecioUnidadModal() {
      const precioCaja = parseFloat(editPrecioCaja?.value) || 0;
      const unidadesPorCaja = parseFloat(editUnidades?.value) || 0;
      
      if (unidadesPorCaja > 0 && precioCaja > 0) {
        const precioUnidad = precioCaja / unidadesPorCaja;
        if (editPrecioUnidad) {
          editPrecioUnidad.value = precioUnidad.toFixed(2);
          
          // Animación visual
          editPrecioUnidad.style.backgroundColor = '#e8f5e8';
          setTimeout(() => {
            editPrecioUnidad.style.backgroundColor = '';
          }, 1000);
        }
      } else if (editPrecioUnidad) {
        editPrecioUnidad.value = '';
      }
    }
    
    // Remover event listeners anteriores si existen
    if (editPrecioCaja) {
      editPrecioCaja.removeEventListener('input', calcularPrecioUnidadModal);
      editPrecioCaja.addEventListener('input', calcularPrecioUnidadModal);
    }
    
    if (editUnidades) {
      editUnidades.removeEventListener('input', calcularPrecioUnidadModal);
      editUnidades.addEventListener('input', calcularPrecioUnidadModal);
    }
    
    // Mostrar el modal
    document.getElementById('modal-editar').style.display = 'block';
  };

  window.cerrarModal = function() {
    document.getElementById('modal-editar').style.display = 'none';
  };

  window.guardarProducto = function(event) {
    event.preventDefault();
    
    const formData = new FormData(document.getElementById('form-editar'));
    
    fetch('../logica/actualizar_producto.php', {
      method: 'POST',
      body: formData
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        alert('Producto actualizado correctamente');
        cerrarModal();
        loadData(); // Ahora loadData está disponible en este ámbito
      } else {
        alert('Error: ' + data.message);
      }
    })
    .catch(error => {
      console.error('Error:', error);
      alert('Error al actualizar el producto');
    });
  };

  // Cargar datos iniciales
  loadData()

});

// Cerrar modal al hacer clic fuera de él
window.onclick = function(event) {
  const modal = document.getElementById('modal-editar');
  if (event.target === modal) {
    cerrarModal();
  }
}