// Variables globales
let productosDisponibles = [];

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
}

// Configurar event listeners
function configurarEventListeners() {
    // Búsqueda de productos
    elementos.busquedaProducto.addEventListener('input', filtrarProductos);
    
    // Event delegation para botones de productos
    elementos.productosBody.addEventListener('click', function(e) {
        if (e.target.classList.contains('btn-agregar-producto')) {
            animarBoton(e.target);
            // La lógica de agregar producto se manejará en PHP
        }
    });
    
    // Animación para botones principales
    const botones = document.querySelectorAll('.button');
    botones.forEach(boton => {
        boton.addEventListener('click', function() {
            animarBoton(this);
        });
    });
    
    // Mostrar/ocultar sección factura con animación
    elementos.btnSiguiente.addEventListener('click', function() {
        if (validarClienteMinimo()) {
            mostrarSeccionFacturaConAnimacion();
            ocultarPanelClienteConAnimacion();
        }
    });
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