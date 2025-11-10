document.addEventListener('DOMContentLoaded', function () {
  const searchInput = document.getElementById('search-input');
  const statusFilter = document.getElementById('status-filter');
  const table = document.getElementById('tabla-inventario');
  const tbody = table ? table.querySelector('tbody') : null;
  const pagination = document.getElementById('pagination');
  const formProducto = document.getElementById('form-producto');
  const submitBtn = formProducto ? formProducto.querySelector('.submit-btn') : null;
  const nombreProductoInput = document.getElementById('nombre_produc');
  const editNombreInput = document.getElementById('edit-nombre');

  // 🔹 ELEMENTOS PARA CÁLCULOS AUTOMÁTICOS
  const cajasInput = document.getElementById('caja_produc');
  const unidadesPorCajaInput = document.getElementById('cantidad_caja');
  const precioCajaInput = document.getElementById('precio_caja');
  const precioUnidadInput = document.getElementById('precio_produc');
  const cantidadTotalInput = document.getElementById('cantidad_total');

  // Validaciones y sanitización
  const toLettersOnly = (str) => (str || '').replace(/[^a-zA-ZÁÉÍÓÚÜÑáéíóúüñ\s]/g, '').replace(/\s{2,}/g, ' ');
  const capitalizeFirst = (str) => {
    const s = (str || '').trim();
    if (!s) return '';
    return s.charAt(0).toUpperCase() + s.slice(1).toLowerCase();
  };
  const toIntOnly = (str) => (str || '').replace(/[^0-9]/g, '');
  const toDecimal = (str) => {
    const s = (str || '').replace(/[^0-9.,]/g, '').replace(/,/g, '.');
    const parts = s.split('.');
    if (parts.length > 2) {
      return parts[0] + '.' + parts.slice(1).join('');
    }
    return s;
  };

  // Aplicar reglas a campos de texto: solo letras y primera mayúscula
  if (nombreProductoInput) {
    nombreProductoInput.addEventListener('input', (e) => {
      const v = toLettersOnly(e.target.value);
      if (v !== e.target.value) e.target.value = v;
    });
    nombreProductoInput.addEventListener('blur', (e) => {
      e.target.value = capitalizeFirst(e.target.value);
    });
  }

  if (editNombreInput) {
    editNombreInput.addEventListener('input', (e) => {
      const v = toLettersOnly(e.target.value);
      if (v !== e.target.value) e.target.value = v;
    });
    editNombreInput.addEventListener('blur', (e) => {
      e.target.value = capitalizeFirst(e.target.value);
    });
  }

  // Números enteros no negativos
  const intFields = ['caja_produc', 'cantidad_caja', 'edit-cajas', 'edit-unidades', 'edit-total'];
  intFields.forEach(id => {
    const el = document.getElementById(id);
    if (!el) return;
    el.addEventListener('input', (e) => {
      const v = toIntOnly(e.target.value);
      if (v !== e.target.value) e.target.value = v;
    });
    el.addEventListener('blur', (e) => {
      const min = parseInt(e.target.getAttribute('min') || '0', 10);
      const n = parseInt(e.target.value || '0', 10);
      e.target.value = isNaN(n) ? '' : Math.max(min, n);
    });
  });

  // Números decimales no negativos
  const decimalFields = ['precio_caja', 'precio_venta', 'edit-precio-caja', 'edit-precio-venta'];
  decimalFields.forEach(id => {
    const el = document.getElementById(id);
    if (!el) return;
    el.addEventListener('input', (e) => {
      const v = toDecimal(e.target.value);
      if (v !== e.target.value) e.target.value = v;
    });
    el.addEventListener('blur', (e) => {
      let n = parseFloat(e.target.value);
      if (isNaN(n) || n < 0) n = 0;
      const step = parseFloat(e.target.getAttribute('step') || '0.01');
      e.target.value = n.toFixed(step >= 1 ? 0 : 2);
    });
  });
  
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
    if (cantidadTotalInput) {
      cantidadTotalInput.value = Number.isFinite(totalUnidades) ? totalUnidades : 0;
    }
  }
  
  // 🔹 EVENT LISTENERS PARA CÁLCULOS AUTOMÁTICOS
  if (precioCajaInput && unidadesPorCajaInput) {
    precioCajaInput.addEventListener('input', calcularPrecioUnidad);
    unidadesPorCajaInput.addEventListener('input', calcularPrecioUnidad);
  }
  
  if (cajasInput && unidadesPorCajaInput) {
    cajasInput.addEventListener('input', mostrarUnidadesTotales);
    unidadesPorCajaInput.addEventListener('input', mostrarUnidadesTotales);
    // Calcular valor inicial si ya hay datos
    mostrarUnidadesTotales();
  }

  if (formProducto) {
    formProducto.addEventListener('submit', function (event) {
      event.preventDefault();
      // Normalizar nombre antes de enviar
      if (nombreProductoInput) {
        nombreProductoInput.value = capitalizeFirst(toLettersOnly(nombreProductoInput.value));
      }
      if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.textContent = 'Guardando...';
      }

      const formData = new FormData(formProducto);
      fetch('../logica/agregar_producto.php', {
        method: 'POST',
        body: formData
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          mostrarToast('success', 'Producto agregado con éxito');
          formProducto.reset();
          mostrarUnidadesTotales();
          if (precioUnidadInput) {
            precioUnidadInput.value = '';
          }
          loadData();
        } else {
          mostrarToast('error', 'Error: ' + (data.message || 'No se pudo agregar'));
        }
      })
      .catch(error => {
        console.error('Error al agregar producto:', error);
        mostrarToast('error', 'Error al agregar el producto');
      })
      .finally(() => {
        if (submitBtn) {
          submitBtn.disabled = false;
          submitBtn.textContent = 'Guardar Producto';
        }
      });
    });
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
      const cantidad_total = p.cantidad_total !== undefined && p.cantidad_total !== null
        ? parseFloat(p.cantidad_total)
        : p.caja_produc * p.cantidad_caja;
      const totalDisplay = Number.isFinite(cantidad_total) ? cantidad_total : 0;
      const estado = p.activo ? 'Activo' : 'Inactivo';
      return `
        <tr>
          <td>${p.nombre_produc}</td>
          <td>${totalDisplay}</td>
          <td>${p.caja_produc}</td>
          <td>$${parseFloat(p.precio_produc).toFixed(2)}</td>
          <td>$${parseFloat(p.precio_venta).toFixed(2)}</td>
          <td><span class="status ${p.activo ? 'active' : 'inactive'}">${estado}</span></td>
          <td>
            <div class="actions">
              <button class="btn btn-add-stock" data-id="${p.id_producto}" title="Agregar llegada de producto">
                <span class="btn-icon">➕</span>
                <span class="btn-text">Agregar</span>
              </button>
              <button class="btn btn-history" data-id="${p.id_producto}" title="Ver historial de llegadas">
                <span class="btn-icon">📜</span>
                <span class="btn-text">Historial</span>
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
  // Panel de confirmación (HTML) reutilizable
  function confirmarAccion(mensaje, opciones = {}) {
    return new Promise(resolve => {
      const {
        titulo = 'Confirmación',
        textoConfirmar = 'Confirmar',
        textoCancelar = 'Cancelar'
      } = opciones;
      const overlay = document.createElement('div');
      overlay.style.cssText = 'position:fixed;inset:0;background:rgba(0,0,0,.5);display:flex;align-items:center;justify-content:center;z-index:10000;';
      const panel = document.createElement('div');
      panel.style.cssText = 'background:#fff;border-radius:10px;width:90%;max-width:420px;box-shadow:0 10px 25px rgba(0,0,0,.2);overflow:hidden;transform:translateY(-10px);opacity:0;transition:all .2s ease;';
      const header = document.createElement('div');
      header.textContent = titulo;
      header.style.cssText = 'background:#222;color:#fff;padding:12px 16px;font-weight:600;';
      const body = document.createElement('div');
      body.textContent = mensaje;
      body.style.cssText = 'padding:16px;color:#333;line-height:1.5;';
      const footer = document.createElement('div');
      footer.style.cssText = 'display:flex;gap:10px;padding:12px 16px;justify-content:flex-end;background:#f7f7f7;';
      const btnCancelar = document.createElement('button');
      btnCancelar.textContent = textoCancelar;
      btnCancelar.style.cssText = 'padding:8px 14px;border-radius:6px;border:1px solid #ccc;background:#fff;cursor:pointer;';
      const btnConfirmar = document.createElement('button');
      btnConfirmar.textContent = textoConfirmar;
      btnConfirmar.style.cssText = 'padding:8px 14px;border-radius:6px;border:1px solid #dc3545;background:#dc3545;color:#fff;cursor:pointer;';
      btnCancelar.addEventListener('click', () => { document.body.removeChild(overlay); resolve(false); });
      btnConfirmar.addEventListener('click', () => { document.body.removeChild(overlay); resolve(true); });
      footer.appendChild(btnCancelar);
      footer.appendChild(btnConfirmar);
      panel.appendChild(header); panel.appendChild(body); panel.appendChild(footer);
      overlay.appendChild(panel);
      document.body.appendChild(overlay);
      requestAnimationFrame(() => { panel.style.transform='translateY(0)'; panel.style.opacity='1'; });
    });
  }

  // Toast reutilizable para mensajes informativos
  function mostrarToast(tipo = 'info', mensaje = '') {
    const colores = {
      success: { bg: '#28a745', border: '#1f8a37' },
      error: { bg: '#dc3545', border: '#b02a37' },
      info: { bg: '#17a2b8', border: '#117a8b' },
      warning: { bg: '#ffc107', border: '#e0a800' }
    };
    const { bg, border } = colores[tipo] || colores.info;

    let contenedor = document.getElementById('toast-contenedor-global');
    if (!contenedor) {
      contenedor = document.createElement('div');
      contenedor.id = 'toast-contenedor-global';
      contenedor.style.cssText = 'position:fixed;top:16px;right:16px;z-index:10001;display:flex;flex-direction:column;gap:10px;align-items:flex-end;';
      document.body.appendChild(contenedor);
    }

    const toast = document.createElement('div');
    toast.style.cssText = `color:#fff;background:${bg};border-left:5px solid ${border};padding:10px 12px;border-radius:8px;box-shadow:0 6px 14px rgba(0,0,0,.15);min-width:240px;max-width:360px;opacity:0;transform:translateY(-6px);transition:all .2s ease;font-size:14px;`;
    toast.textContent = mensaje;
    contenedor.appendChild(toast);

    requestAnimationFrame(() => { toast.style.opacity = '1'; toast.style.transform = 'translateY(0)'; });
    setTimeout(() => { toast.style.opacity = '0'; toast.style.transform = 'translateY(-6px)'; setTimeout(() => { if (toast.parentNode === contenedor) contenedor.removeChild(toast); }, 200); }, 3500);
  }

  if (table) {
    table.addEventListener('click', async function(e) {
      const btn = e.target.closest('button');
      if (!btn) return;
      if (btn.classList.contains('btn-add-stock')) {
        const id = btn.getAttribute('data-id');
        abrirModalAgregarStock(id);
      } else if (btn.classList.contains('btn-history')) {
        const id = btn.getAttribute('data-id');
        abrirModalHistorial(id);
      } else if (btn.classList.contains('btn-delete')) {
        const id = btn.getAttribute('data-id');
        const confirmado = await confirmarAccion('¿Estás seguro de que quieres eliminar este producto?', {
          titulo: 'Eliminar producto',
          textoConfirmar: 'Eliminar',
          textoCancelar: 'Cancelar'
        });
        if (!confirmado) return;
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
              mostrarToast('success', 'Producto eliminado correctamente');
              loadData(); // Recargar la tabla
            } else {
              mostrarToast('error', 'Error: ' + (data.message || 'No se pudo eliminar'));
            }
          })
          .catch(error => {
            console.error('Error:', error);
            mostrarToast('error', 'Error al eliminar el producto');
          });
      }
    });
  }

  // Modal dinámico para agregar stock
  window.abrirModalAgregarStock = function(id) {
    const productos = JSON.parse(sessionStorage.getItem('productos_actuales') || '[]');
    const producto = productos.find(p => p.id_producto == id);
    if (!producto) return;
    const overlay = document.createElement('div');
    overlay.style.cssText = 'position:fixed;inset:0;background:rgba(0,0,0,.5);display:flex;align-items:center;justify-content:center;z-index:10000;';
    const panel = document.createElement('div');
    panel.style.cssText = 'background:#fff;border-radius:10px;width:92%;max-width:520px;box-shadow:0 10px 25px rgba(0,0,0,.2);overflow:hidden;';
    panel.innerHTML = `
      <div style="background:#222;color:#fff;padding:12px 16px;font-weight:600;display:flex;justify-content:space-between;align-items:center;">
        <span>Agregar llegada de producto</span>
        <button id="cerrar-agregar" style="background:transparent;border:none;color:#fff;font-size:18px;cursor:pointer;">✖</button>
      </div>
      <div style="padding:16px;color:#333;line-height:1.5;">
        <div style="margin-bottom:10px;">
          <strong>Producto:</strong> ${producto.nombre_produc}
        </div>
        <form id="form-agregar-stock">
          <div class="form-group">
            <label>Cajas a agregar</label>
            <input type="number" name="cajas_agregar" min="0" step="1" value="0" required />
          </div>
          <div class="form-group">
            <label>Unidades sueltas a agregar</label>
            <input type="number" name="unidades_sueltas_agregar" min="0" step="1" value="0" required />
          </div>
          <div class="form-group">
            <label>Observación (opcional)</label>
            <textarea name="observacion" rows="3" maxlength="255" placeholder="Ej: Llegada semanal proveedor X" style="resize:none; height:80px; min-height:80px; max-height:80px; width:100%; overflow:auto;"></textarea>
          </div>
          <div style="display:flex;gap:10px;align-items:center;margin-top:10px;">
            <button type="submit" class="btn btn-primary">Agregar al inventario</button>
            <button type="button" id="cancelar-agregar" class="btn">Cancelar</button>
          </div>
        </form>
      </div>
    `;
    overlay.appendChild(panel);
    document.body.appendChild(overlay);
    const cerrar = panel.querySelector('#cerrar-agregar');
    const cancelar = panel.querySelector('#cancelar-agregar');
    cerrar.addEventListener('click', () => { document.body.removeChild(overlay); });
    cancelar.addEventListener('click', () => { document.body.removeChild(overlay); });
    const form = panel.querySelector('#form-agregar-stock');
    const cajasAg = form.querySelector('input[name="cajas_agregar"]');
    const sueltasAg = form.querySelector('input[name="unidades_sueltas_agregar"]');
    const obs = form.querySelector('textarea[name="observacion"]');
    if (cajasAg) {
      cajasAg.addEventListener('input', (e) => {
        const v = toIntOnly(e.target.value);
        if (v !== e.target.value) e.target.value = v;
      });
      cajasAg.addEventListener('blur', (e) => {
        const n = parseInt(e.target.value || '0', 10);
        e.target.value = isNaN(n) ? '0' : Math.max(0, n);
      });
    }
    if (sueltasAg) {
      sueltasAg.addEventListener('input', (e) => {
        const v = toIntOnly(e.target.value);
        if (v !== e.target.value) e.target.value = v;
      });
      sueltasAg.addEventListener('blur', (e) => {
        const n = parseInt(e.target.value || '0', 10);
        e.target.value = isNaN(n) ? '0' : Math.max(0, n);
      });
    }
    if (obs) {
      obs.addEventListener('blur', (e) => {
        e.target.value = capitalizeFirst(e.target.value);
      });
    }
    form.addEventListener('submit', async (ev) => {
      ev.preventDefault();
      const fd = new FormData(form);
      fd.append('id_producto', producto.id_producto);
      try {
        const res = await fetch('../logica/agregar_stock.php', { method: 'POST', body: fd });
        const data = await res.json();
        if (data.success) {
          mostrarToast('success', 'Stock agregado y historial registrado');
          document.body.removeChild(overlay);
          loadData();
        } else {
          mostrarToast('error', 'Error: ' + (data.message || 'No se pudo agregar stock'));
        }
      } catch (err) {
        console.error(err);
        mostrarToast('error', 'Error de conexión al agregar stock');
      }
    });
  };

  // Modal dinámico para ver historial
  window.abrirModalHistorial = async function(id) {
    try {
      const res = await fetch('../logica/obtener_historial.php?id_producto=' + encodeURIComponent(id));
      const data = await res.json();
      const overlay = document.createElement('div');
      overlay.style.cssText = 'position:fixed;inset:0;background:rgba(0,0,0,.5);display:flex;align-items:center;justify-content:center;z-index:10000;';
      const panel = document.createElement('div');
      panel.style.cssText = 'background:#fff;border-radius:10px;width:96%;max-width:1000px;box-shadow:0 10px 25px rgba(0,0,0,.2);overflow:hidden;';
      const header = document.createElement('div');
      header.style.cssText = 'background:#222;color:#fff;padding:12px 16px;font-weight:600;display:flex;justify-content:space-between;align-items:center;';
      header.innerHTML = '<span>Historial del producto</span><button id="cerrar-historial" style="background:transparent;border:none;color:#fff;font-size:18px;cursor:pointer;">✖</button>';
      const body = document.createElement('div');
      body.style.cssText = 'padding:16px;color:#333;line-height:1.5;max-height:70vh;overflow:auto;';
      if (data.success && Array.isArray(data.historial) && data.historial.length) {
        const rows = data.historial.map(h => `
          <tr>
            <td style="white-space:nowrap">${h.fecha_registro}</td>
            <td>${h.cajas_agregar}</td>
            <td>${h.unidades_por_caja}</td>
            <td>${h.unidades_sueltas_agregar}</td>
            <td>${h.unidades_agregadas_total}</td>
            <td>$${parseFloat(h.precio_venta_usd).toFixed(2)}</td>
            <td>Bs ${(parseFloat(h.precio_venta_bs)).toFixed(2)}</td>
          </tr>
        `).join('');
        body.innerHTML = `
          <table class="table" style="width:100%;border-collapse:collapse;table-layout:auto;">
            <thead>
              <tr>
                <th>Fecha</th>
                <th>Cajas</th>
                <th>Unid/Caja</th>
                <th>Sueltas</th>
                <th>Total Unid Agregadas</th>
                <th>Precio USD</th>
                <th>Precio Bs</th>
              </tr>
            </thead>
            <tbody>${rows}</tbody>
          </table>
        `;
      } else {
        body.textContent = 'No hay historial disponible para este producto.';
      }
      panel.appendChild(header);
      panel.appendChild(body);
      overlay.appendChild(panel);
      document.body.appendChild(overlay);
      overlay.querySelector('#cerrar-historial').addEventListener('click', () => { document.body.removeChild(overlay); });
    } catch (err) {
      console.error(err);
      mostrarToast('error', 'Error al cargar historial');
    }
  };

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
      // alert('Producto no encontrado');
      return;
    }
    
    // Llenar el formulario con los datos del producto
    document.getElementById('edit-id').value = producto.id_producto;
    document.getElementById('edit-nombre').value = producto.nombre_produc;
    document.getElementById('edit-cajas').value = producto.caja_produc;
    document.getElementById('edit-unidades').value = producto.cantidad_caja;
    const totalProducto = producto.cantidad_total !== undefined && producto.cantidad_total !== null
      ? parseFloat(producto.cantidad_total)
      : producto.caja_produc * producto.cantidad_caja;
    document.getElementById('edit-total').value = Number.isFinite(totalProducto) ? totalProducto : 0;
    document.getElementById('edit-precio-caja').value = producto.precio_caja || '';
    document.getElementById('edit-precio-unidad').value = producto.precio_produc;
    document.getElementById('edit-precio-venta').value = producto.precio_venta;
    document.getElementById('edit-activo').checked = producto.activo;
    
    // 🔹 AGREGAR EVENT LISTENERS PARA CÁLCULOS EN EL MODAL
    const editPrecioCaja = document.getElementById('edit-precio-caja');
    const editUnidades = document.getElementById('edit-unidades');
    const editPrecioUnidad = document.getElementById('edit-precio-unidad');
    const editCajas = document.getElementById('edit-cajas');
    const editTotal = document.getElementById('edit-total');
    
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
    
    function calcularTotalModal() {
      const cajasModal = parseFloat(editCajas?.value) || 0;
      const unidadesModal = parseFloat(editUnidades?.value) || 0;
      const totalModal = cajasModal * unidadesModal;
      if (editTotal) {
        editTotal.value = Number.isFinite(totalModal) ? totalModal : 0;
      }
    }

    // Remover y agregar event listeners
    if (editPrecioCaja) {
      editPrecioCaja.removeEventListener('input', calcularPrecioUnidadModal);
      editPrecioCaja.addEventListener('input', calcularPrecioUnidadModal);
    }
    
    if (editUnidades) {
      editUnidades.removeEventListener('input', calcularPrecioUnidadModal);
      editUnidades.addEventListener('input', calcularPrecioUnidadModal);
      editUnidades.removeEventListener('input', calcularTotalModal);
      editUnidades.addEventListener('input', calcularTotalModal);
    }

    if (editCajas) {
      editCajas.removeEventListener('input', calcularTotalModal);
      editCajas.addEventListener('input', calcularTotalModal);
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
        mostrarToast('success', 'Producto actualizado correctamente');
        cerrarModal();
        loadData(); // Ahora loadData está disponible en este ámbito
      } else {
        mostrarToast('error', 'Error: ' + (data.message || 'No se pudo actualizar'));
      }
    })
    .catch(error => {
      console.error('Error:', error);
      mostrarToast('error', 'Error al actualizar el producto');
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