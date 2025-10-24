// Variables globales
let productosDisponibles = [];
let timeoutAutoComplete = null; // Para el debounce del auto-completado

// Elementos del DOM
const elementos = {
    // Cliente
    containerCliente: document.querySelector('.container_cliente'), // Nuevo elemento
    cedulaInput: document.getElementById('cliente-cedula'),
    nombreInput: document.getElementById('cliente-nombre'),
    apellidoInput: document.getElementById('cliente-apellido'),
    telefonoInput: document.getElementById('cliente-telefono'),
    aliasInput: document.getElementById('cliente-alias'),
    btnRegistrar: document.getElementById('btn-registrar'),
    btnSiguiente: document.getElementById('btn-siguiente'),
    
    // Factura
    containerFactura: document.getElementById('container-factura'),
    resumenCliente: document.getElementById('resumen-cliente'),
    tablaFacturaBody: document.getElementById('tabla-factura-body'),
    totalText: document.getElementById('total-text'),
    btnVerCuenta: document.getElementById('btn-ver-cuenta'),
    btnPagar: document.getElementById('btn-pagar'),
    
    // Productos
    busquedaProducto: document.getElementById('busqueda-producto'),
    productosBody: document.getElementById('productos-body')
};

// Inicialización
document.addEventListener('DOMContentLoaded', function() {
    inicializarApp();
});

function inicializarApp() {
    configurarEventListeners();
    inicializarAnimaciones();
    validarElementosDOM();
}

// Validar que todos los elementos DOM existan
function validarElementosDOM() {
    const elementosFaltantes = [];
    
    Object.entries(elementos).forEach(([nombre, elemento]) => {
        if (!elemento) {
            elementosFaltantes.push(nombre);
        }
    });
    
    if (elementosFaltantes.length > 0) {
        console.warn('Elementos DOM faltantes:', elementosFaltantes);
    }
}

// Configurar event listeners
function configurarEventListeners() {
    // Búsqueda de productos con debounce
    if (elementos.busquedaProducto) {
        elementos.busquedaProducto.addEventListener('input', debounce(filtrarProductos, 300));
    }
    
    // Event delegation para botones de productos
    if (elementos.productosBody) {
        elementos.productosBody.addEventListener('click', function(e) {
            if (e.target.classList.contains('btn-agregar-producto')) {
                e.preventDefault();
                animarBoton(e.target);
                agregarProductoAFactura(e.target);
            }
        });
    }
    
    // Botones de cliente
    if (elementos.btnRegistrar) {
        elementos.btnRegistrar.addEventListener('click', registrarCliente);
    }
    
    if (elementos.btnSiguiente) {
        elementos.btnSiguiente.addEventListener('click', continuarConCliente);
    }
    
    // Auto-completado para campos de cliente
    configurarAutoCompletado();
}

// Configurar auto-completado para todos los campos de cliente
function configurarAutoCompletado() {
    const camposCliente = [
        { elemento: elementos.cedulaInput, campo: 'cedula' },
        { elemento: elementos.nombreInput, campo: 'nombre' },
        { elemento: elementos.apellidoInput, campo: 'apellido' },
        { elemento: elementos.telefonoInput, campo: 'telefono' },
        { elemento: elementos.aliasInput, campo: 'alias' }
    ];
    
    camposCliente.forEach(({ elemento, campo }) => {
        if (elemento) {
            elemento.addEventListener('input', function(e) {
                const valor = e.target.value.trim();
                if (valor.length >= 2) {
                    buscarClienteAutoComplete(campo, valor);
                } else {
                    limpiarAutoComplete();
                }
            });
            
            // Limpiar auto-completado cuando el campo pierde el foco
            elemento.addEventListener('blur', function() {
                setTimeout(() => limpiarAutoComplete(), 200);
            });
        }
    });
}

// Buscar cliente para auto-completado
function buscarClienteAutoComplete(campo, valor) {
    // Cancelar búsqueda anterior si existe
    if (timeoutAutoComplete) {
        clearTimeout(timeoutAutoComplete);
    }
    
    timeoutAutoComplete = setTimeout(() => {
        fetch(`../logica/buscar_cliente_autocomplete.php?campo=${encodeURIComponent(campo)}&valor=${encodeURIComponent(valor)}`)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.clientes && data.clientes.length > 0) {
                    // Tomar el primer resultado para auto-rellenar
                    const cliente = data.clientes[0];
                    autoRellenarCamposCliente(cliente);
                    mostrarIndicadorAutoComplete();
                }
            })
            .catch(error => {
                console.error('Error en auto-completado:', error);
            });
    }, 300);
}

// Auto-rellenar campos del cliente
function autoRellenarCamposCliente(cliente) {
    // Solo rellenar campos vacíos para no sobrescribir lo que el usuario está escribiendo
    if (elementos.cedulaInput && !elementos.cedulaInput.value.trim()) {
        elementos.cedulaInput.value = cliente.cedula || '';
        animarCampoAutoComplete(elementos.cedulaInput);
    }
    
    if (elementos.nombreInput && !elementos.nombreInput.value.trim()) {
        elementos.nombreInput.value = cliente.nombre || '';
        animarCampoAutoComplete(elementos.nombreInput);
    }
    
    if (elementos.apellidoInput && !elementos.apellidoInput.value.trim()) {
        elementos.apellidoInput.value = cliente.apellido || '';
        animarCampoAutoComplete(elementos.apellidoInput);
    }
    
    if (elementos.telefonoInput && !elementos.telefonoInput.value.trim()) {
        elementos.telefonoInput.value = cliente.telefono || '';
        animarCampoAutoComplete(elementos.telefonoInput);
    }
    
    if (elementos.aliasInput && !elementos.aliasInput.value.trim()) {
        elementos.aliasInput.value = cliente.alias || '';
        animarCampoAutoComplete(elementos.aliasInput);
    }
}

// Animar campo auto-completado
function animarCampoAutoComplete(campo) {
    campo.style.transition = 'all 0.3s ease';
    campo.style.backgroundColor = '#e8f5e8';
    campo.style.borderColor = '#28a745';
    
    setTimeout(() => {
        campo.style.backgroundColor = '';
        campo.style.borderColor = '';
    }, 2000);
}

// Mostrar indicador de auto-completado
function mostrarIndicadorAutoComplete() {
    // Crear o actualizar indicador visual
    let indicador = document.getElementById('autocomplete-indicator');
    if (!indicador) {
        indicador = document.createElement('div');
        indicador.id = 'autocomplete-indicator';
        indicador.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3);
            z-index: 1000;
            opacity: 0;
            transform: translateY(-10px);
            transition: all 0.3s ease;
        `;
        indicador.textContent = '✓ Datos auto-completados';
        document.body.appendChild(indicador);
    }
    
    // Mostrar indicador
    setTimeout(() => {
        indicador.style.opacity = '1';
        indicador.style.transform = 'translateY(0)';
    }, 100);
    
    // Ocultar después de 3 segundos
    setTimeout(() => {
        indicador.style.opacity = '0';
        indicador.style.transform = 'translateY(-10px)';
    }, 3000);
}

// Limpiar auto-completado
function limpiarAutoComplete() {
    if (timeoutAutoComplete) {
        clearTimeout(timeoutAutoComplete);
        timeoutAutoComplete = null;
    }
}

// Validar que todos los elementos DOM existan
function validarElementosDOM() {
    const elementosFaltantes = [];
    
    Object.entries(elementos).forEach(([nombre, elemento]) => {
        if (!elemento) {
            elementosFaltantes.push(nombre);
        }
    });
    
    if (elementosFaltantes.length > 0) {
        console.warn('Elementos DOM faltantes:', elementosFaltantes);
    }
}

// Configurar event listeners
function configurarEventListeners() {
    // Búsqueda de productos con debounce
    if (elementos.busquedaProducto) {
        elementos.busquedaProducto.addEventListener('input', debounce(filtrarProductos, 300));
    }
    
    // Event delegation para botones de productos
    if (elementos.productosBody) {
        elementos.productosBody.addEventListener('click', function(e) {
            if (e.target.classList.contains('btn-agregar-producto')) {
                e.preventDefault();
                animarBoton(e.target);
                agregarProductoAFactura(e.target);
            }
        });
    }
    
    // Botones de cliente
    if (elementos.btnRegistrar) {
        elementos.btnRegistrar.addEventListener('click', registrarCliente);
    }
    
    if (elementos.btnSiguiente) {
        elementos.btnSiguiente.addEventListener('click', continuarConCliente);
    }
    
    // Animación para botones principales
    const botones = document.querySelectorAll('.button');
    botones.forEach(boton => {
        boton.addEventListener('click', function() {
            animarBoton(this);
        });
    });
}

// Función debounce para optimizar búsquedas
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Agregar producto a la factura
function agregarProductoAFactura(boton) {
    const id = boton.dataset.id;
    const nombre = boton.dataset.nombre;
    const precio = parseFloat(boton.dataset.precio);
    
    if (!id || !nombre || isNaN(precio)) {
        mostrarAlerta('error', 'Error: Datos del producto incompletos');
        return;
    }
    
    // Aquí iría la lógica para agregar el producto a la factura
    mostrarAlerta('success', `Producto "${nombre}" agregado a la factura`);
    efectoAgregarProducto(nombre);
}

// Registrar nuevo cliente
function registrarCliente() {
    const datosCliente = {
        cedula: elementos.cedulaInput?.value?.trim(),
        nombre: elementos.nombreInput?.value?.trim(),
        apellido: elementos.apellidoInput?.value?.trim(),
        telefono: elementos.telefonoInput?.value?.trim(),
        alias: elementos.aliasInput?.value?.trim()
    };
    
    if (!validarDatosCliente(datosCliente)) {
        mostrarAlerta('error', 'Por favor complete todos los campos obligatorios');
        return;
    }
    
    // Aquí iría la lógica para registrar el cliente
    mostrarAlerta('success', 'Cliente registrado exitosamente');
}

// Continuar con cliente existente
function continuarConCliente() {
    if (!validarClienteMinimo()) {
        mostrarAlerta('error', 'Debe ingresar al menos la cédula del cliente');
        return;
    }
    
    mostrarSeccionFacturaConAnimacion();
    ocultarPanelClienteConAnimacion();
}

// Validar datos del cliente
function validarDatosCliente(datos) {
    return datos.cedula && datos.nombre && datos.apellido;
}

// Validación mínima del cliente
function validarClienteMinimo() {
    if (!elementos.cedulaInput.value.trim()) {
        mostrarAlerta('error', 'Ingrese al menos la cédula del cliente');
        elementos.cedulaInput.focus();
        return false;
    }
    return true;
}

// Ocultar panel del cliente con animación
function ocultarPanelClienteConAnimacion() {
    if (elementos.containerCliente) {
        elementos.containerCliente.style.transition = 'all 0.5s ease';
        elementos.containerCliente.style.opacity = '0';
        elementos.containerCliente.style.transform = 'translateX(-20px)';
        elementos.containerCliente.style.maxHeight = '0';
        elementos.containerCliente.style.overflow = 'hidden';
        
        // Ocultar completamente después de la animación
        setTimeout(() => {
            elementos.containerCliente.style.display = 'none';
        }, 500);
    }
}

// Mostrar panel del cliente (por si necesitas restaurarlo)
function mostrarPanelClienteConAnimacion() {
    if (elementos.containerCliente) {
        elementos.containerCliente.style.display = 'grid';
        elementos.containerCliente.style.transition = 'all 0.5s ease';
        elementos.containerCliente.style.opacity = '0';
        elementos.containerCliente.style.transform = 'translateX(-20px)';
        elementos.containerCliente.style.maxHeight = '0';
        
        // Animar después de un pequeño delay
        setTimeout(() => {
            elementos.containerCliente.style.opacity = '1';
            elementos.containerCliente.style.transform = 'translateX(0)';
            elementos.containerCliente.style.maxHeight = '500px';
        }, 50);
    }
}

// Mostrar sección factura con animación
function mostrarSeccionFacturaConAnimacion() {
    elementos.containerFactura.style.opacity = '0';
    elementos.containerFactura.style.transform = 'translateY(20px)';
    elementos.containerFactura.classList.remove('hidden');
    
    setTimeout(() => {
        elementos.containerFactura.style.transition = 'all 0.5s ease';
        elementos.containerFactura.style.opacity = '1';
        elementos.containerFactura.style.transform = 'translateY(0)';
    }, 50);
    
    // Scroll suave a la factura
    elementos.containerFactura.scrollIntoView({ 
        behavior: 'smooth', 
        block: 'start' 
    });
}

// Filtrar productos en tiempo real
function filtrarProductos() {
    const filtro = elementos.busquedaProducto.value.toLowerCase();
    const filas = elementos.productosBody.querySelectorAll('tr');
    
    filas.forEach(fila => {
        const textoFila = fila.textContent.toLowerCase();
        if (textoFila.includes(filtro)) {
            fila.style.display = '';
            // Animación de aparición
            fila.style.animation = 'fadeIn 0.3s ease';
        } else {
            fila.style.display = 'none';
        }
    });
    
    // Mostrar mensaje si no hay resultados
    const filasVisibles = Array.from(filas).some(fila => fila.style.display !== 'none');
    if (!filasVisibles && filtro !== '') {
        mostrarMensajeNoResultados();
    }
}

// Mostrar mensaje cuando no hay resultados
function mostrarMensajeNoResultados() {
    const mensajeExistente = elementos.productosBody.querySelector('.no-results');
    if (!mensajeExistente) {
        const mensaje = document.createElement('tr');
        mensaje.className = 'no-results';
        mensaje.innerHTML = `<td colspan="4" style="text-align: center; color: #666; padding: 20px;">No se encontraron productos</td>`;
        elementos.productosBody.appendChild(mensaje);
    }
}

// Animación para botones
function animarBoton(boton) {
    boton.style.transform = 'scale(0.95)';
    boton.style.transition = 'transform 0.1s ease';
    
    setTimeout(() => {
        boton.style.transform = 'scale(1)';
    }, 100);
}

// Mostrar alertas animadas
function mostrarAlerta(tipo, mensaje) {
    // Eliminar alerta anterior si existe
    const alertaAnterior = document.querySelector('.custom-alert');
    if (alertaAnterior) {
        alertaAnterior.remove();
    }
    
    const alerta = document.createElement('div');
    alerta.className = `custom-alert alert-${tipo}`;
    alerta.textContent = mensaje;
    
    // Estilos para la alerta
    alerta.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 15px 20px;
        border-radius: 8px;
        color: white;
        font-weight: bold;
        z-index: 10000;
        max-width: 300px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.3);
        animation: slideInRight 0.3s ease-out;
    `;
    
    // Colores según el tipo
    const colores = {
        success: '#28a745',
        error: '#dc3545',
        warning: '#ffc107',
        info: '#17a2b8'
    };
    
    alerta.style.backgroundColor = colores[tipo] || colores.info;
    
    document.body.appendChild(alerta);
    
    // Auto-eliminar después de 4 segundos
    setTimeout(() => {
        if (alerta.parentNode) {
            alerta.style.animation = 'slideOutRight 0.3s ease-in';
            setTimeout(() => alerta.remove(), 300);
        }
    }, 4000);
}

// Inicializar animaciones CSS
function inicializarAnimaciones() {
    const estiloAnimaciones = document.createElement('style');
    estiloAnimaciones.textContent = `
        @keyframes slideInRight {
            from { 
                transform: translateX(100%); 
                opacity: 0; 
            }
            to { 
                transform: translateX(0); 
                opacity: 1; 
            }
        }
        
        @keyframes slideOutRight {
            from { 
                transform: translateX(0); 
                opacity: 1; 
            }
            to { 
                transform: translateX(100%); 
                opacity: 0; 
            }
        }
        
        @keyframes fadeIn {
            from { 
                opacity: 0; 
                transform: translateY(5px);
            }
            to { 
                opacity: 1; 
                transform: translateY(0);
            }
        }
        
        @keyframes slideOutLeft {
            from { 
                transform: translateX(0); 
                opacity: 1; 
            }
            to { 
                transform: translateX(-20px); 
                opacity: 0; 
            }
        }
        
        .btn-agregar-producto {
            transition: all 0.2s ease;
        }
        
        .btn-agregar-producto:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }
        
        .button {
            transition: all 0.2s ease;
        }
        
        .button:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }
        
        /* Animación para cuando se muestran elementos */
        .container_factura {
            transition: all 0.5s ease;
        }
        
        /* Animación para panel cliente */
        .container_cliente {
            transition: all 0.5s ease;
            overflow: hidden;
        }
        
        /* Animación para filas de tabla */
        .tabla-productos tr {
            animation: fadeIn 0.3s ease;
        }
        
        .tabla-factura tr {
            transition: all 0.3s ease;
        }
        
        .tabla-factura tr:hover {
            background-color: #f8f9fa;
            transform: scale(1.01);
        }
        
        /* Efecto de carga para botones */
        .button.loading {
            position: relative;
            color: transparent;
        }
        
        .button.loading::after {
            content: '';
            position: absolute;
            width: 16px;
            height: 16px;
            top: 50%;
            left: 50%;
            margin: -8px 0 0 -8px;
            border: 2px solid #ffffff;
            border-radius: 50%;
            border-right-color: transparent;
            animation: spin 0.8s linear infinite;
        }
        
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
    `;
    document.head.appendChild(estiloAnimaciones);
}

// Función para mostrar estado de carga en botones
function mostrarCarga(boton) {
    boton.classList.add('loading');
    boton.disabled = true;
}

function ocultarCarga(boton) {
    boton.classList.remove('loading');
    boton.disabled = false;
}

// Efecto al agregar producto a la factura (puedes llamar esta función desde PHP)
function efectoAgregarProducto(nombreProducto) {
    mostrarAlerta('success', `✓ ${nombreProducto} agregado`);
    
    // Efecto visual en la tabla de factura
    const ultimaFila = elementos.tablaFacturaBody.lastElementChild;
    if (ultimaFila) {
        ultimaFila.style.animation = 'highlightRow 0.6s ease';
        
        // Remover la animación después de que termine
        setTimeout(() => {
            ultimaFila.style.animation = '';
        }, 600);
    }
}

// Agregar esta animación al estilo
const estiloExtra = document.createElement('style');
estiloExtra.textContent = `
    @keyframes highlightRow {
        0% { background-color: #d4edda; }
        100% { background-color: transparent; }
    }
    
    .custom-alert {
        font-family: Arial, sans-serif;
        font-size: 14px;
    }
`;
document.head.appendChild(estiloExtra);

// Función para limpiar filtro
function limpiarFiltro() {
    elementos.busquedaProducto.value = '';
    filtrarProductos();
}

// Efecto al eliminar producto de la factura
function efectoEliminarProducto() {
    mostrarAlerta('info', 'Producto eliminado');
}