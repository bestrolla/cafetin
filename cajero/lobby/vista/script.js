// Variables globales
let productosDisponibles = [];
// ID del cliente seleccionado/registrado para usar en ventas y créditos
let clienteSeleccionadoId = null;
let timeoutAutoComplete = null; // Para el debounce del auto-completado

// Elementos del DOM
const elementos = {
    // Cliente
    containerCliente: document.getElementById('container-cliente'),
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

// Inicializar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    // Precargar tasa oficial desde Configuración y bloquear si falla
    cargarTasaDesdeConfiguracionCajeroLobby()
        .then(() => {
            inicializarApp();
        })
        .catch((e) => {
            try { mostrarAlerta('error', 'No se pudo cargar la tasa oficial. Verifique la Configuración en Admin.'); } catch(_) {}
        });
});

function inicializarApp() {
    validarElementosDOM();
    configurarEventListeners();
    inicializarAnimaciones();
    agregarEstilosSugerencias();
    cargarTodosLosProductos();
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
    // Búsqueda de productos con autocompletado mejorado
    if (elementos.busquedaProducto) {
        elementos.busquedaProducto.addEventListener('input', debounce(filtrarProductos, 300));
        
        // Limpiar búsqueda al hacer blur si está vacío
        elementos.busquedaProducto.addEventListener('blur', function() {
            if (!this.value.trim()) {
                // Recargar todos los productos
                setTimeout(() => {
                    cargarTodosLosProductos();
                }, 100);
            }
        });
        
        // Limpiar búsqueda con Escape
        elementos.busquedaProducto.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                this.value = '';
                cargarTodosLosProductos();
                this.blur();
            }
        });
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
    
    // Event listeners para los botones de la factura
    if (elementos.btnVerCuenta) {
        elementos.btnVerCuenta.addEventListener('click', agregarACuenta);
    }
    
    if (elementos.btnPagar) {
        elementos.btnPagar.addEventListener('click', procesarPago);
    }
    
    // Event listener para búsqueda de productos
    if (elementos.busquedaProducto) {
        elementos.busquedaProducto.addEventListener('input', filtrarProductos);
    }
    
    // Event listener para agregar productos desde la tabla
    if (elementos.productosBody) {
        elementos.productosBody.addEventListener('click', function(e) {
            if (e.target.classList.contains('btn-agregar-producto')) {
                agregarProductoAFactura(e.target);
            }
        });
    }
    
    // Auto-completado para campos de cliente
    configurarAutoCompletado();

    // Validaciones y sanitización de campos de cliente
    const toLettersOnly = (str) => (str || '').replace(/[^a-zA-ZÁÉÍÓÚÜÑáéíóúüñ\s]/g, '').replace(/\s{2,}/g, ' ');
    const capitalizeFirst = (str) => {
        const s = (str || '').trim();
        if (!s) return '';
        return s.charAt(0).toUpperCase() + s.slice(1).toLowerCase();
    };
    const toIntOnly = (str) => (str || '').replace(/[^0-9]/g, '');

    // Letras: nombre, apellido, alias
    [elementos.nombreInput, elementos.apellidoInput, elementos.aliasInput].forEach(el => {
        if (!el) return;
        el.addEventListener('input', (e) => {
            const v = toLettersOnly(e.target.value);
            if (v !== e.target.value) e.target.value = v;
        });
        el.addEventListener('blur', (e) => {
            e.target.value = capitalizeFirst(e.target.value);
        });
    });

    // Números: cédula y teléfono
    [elementos.cedulaInput, elementos.telefonoInput].forEach(el => {
        if (!el) return;
        el.addEventListener('input', (e) => {
            const v = toIntOnly(e.target.value);
            if (v !== e.target.value) e.target.value = v;
        });
        el.addEventListener('blur', (e) => {
            e.target.value = toIntOnly(e.target.value);
        });
    });

    // Validación en tiempo real para habilitar/deshabilitar botones
    configurarValidacionTiempoReal();
    
    // Animación para botones principales
    const botones = document.querySelectorAll('.button');
    botones.forEach(boton => {
        boton.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-2px)';
        });
        
        boton.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
    });

    // Botón toggle de moneda
    const btnToggleMoneda = document.getElementById('btn-toggle-moneda');
    if (btnToggleMoneda) {
        btnToggleMoneda.addEventListener('click', toggleMoneda);
    }

    // Inicializar estado visual de moneda en tablas/encabezados al cargar
    aplicarMonedaEnUI();
}

// Configurar validación en tiempo real
function configurarValidacionTiempoReal() {
    const camposCliente = [
        elementos.cedulaInput,
        elementos.nombreInput,
        elementos.apellidoInput,
        elementos.telefonoInput,
        elementos.aliasInput
    ];
    
    camposCliente.forEach(campo => {
        if (campo) {
            campo.addEventListener('input', validarFormularioCompleto);
            campo.addEventListener('blur', validarFormularioCompleto);
        }
    });
    
    // Validación inicial
    validarFormularioCompleto();
}

// Validar formulario completo y actualizar estado de botones
function validarFormularioCompleto() {
    const todosLosCamposCompletos = validarTodosLosCampos();
    const listoParaRegistrar = !!(elementos.nombreInput?.value?.trim() && elementos.apellidoInput?.value?.trim());
    
    // Actualizar estado del botón Siguiente
    if (elementos.btnSiguiente) {
        if (todosLosCamposCompletos) {
            habilitarBoton(elementos.btnSiguiente);
        } else {
            deshabilitarBoton(elementos.btnSiguiente);
        }
    }
    
    // Actualizar estado del botón Registrar (mínimo nombre y apellido)
    if (elementos.btnRegistrar) {
        if (listoParaRegistrar) {
            habilitarBoton(elementos.btnRegistrar);
        } else {
            deshabilitarBoton(elementos.btnRegistrar);
        }
    }
    
    // Actualizar indicadores visuales
    actualizarIndicadoresVisuales();
}

// Validar que todos los campos estén completos
function validarTodosLosCampos() {
    const campos = [
        elementos.cedulaInput?.value?.trim(),
        elementos.nombreInput?.value?.trim(),
        elementos.apellidoInput?.value?.trim(),
        elementos.telefonoInput?.value?.trim(),
        elementos.aliasInput?.value?.trim()
    ];
    
    return campos.every(campo => campo && campo.length > 0);
}

// Habilitar botón
function habilitarBoton(boton) {
    if (boton) {
        boton.disabled = false;
        boton.style.opacity = '1';
        boton.style.cursor = 'pointer';
        boton.style.filter = 'none';
        boton.classList.remove('disabled');
    }
}

// Deshabilitar botón
function deshabilitarBoton(boton) {
    if (boton) {
        boton.disabled = true;
        boton.style.opacity = '0.5';
        boton.style.cursor = 'not-allowed';
        boton.style.filter = 'grayscale(50%)';
        boton.classList.add('disabled');
    }
}

// Actualizar indicadores visuales de campos
function actualizarIndicadoresVisuales() {
    const campos = [
        { elemento: elementos.cedulaInput, nombre: 'Cédula' },
        { elemento: elementos.nombreInput, nombre: 'Nombre' },
        { elemento: elementos.apellidoInput, nombre: 'Apellido' },
        { elemento: elementos.telefonoInput, nombre: 'Teléfono' },
        { elemento: elementos.aliasInput, nombre: 'Alias' }
    ];
    
    campos.forEach(({ elemento, nombre }) => {
        if (elemento) {
            const valor = elemento.value.trim();
            if (valor.length > 0) {
                // Campo completo - borde verde
                elemento.style.borderColor = '#28a745';
                elemento.classList.remove('campo-incompleto');
                elemento.classList.add('campo-completo');
            } else {
                // Campo vacío - borde rojo suave
                elemento.style.borderColor = '#dc3545';
                elemento.classList.remove('campo-completo');
                elemento.classList.add('campo-incompleto');
            }
        }
    });
}

// Configurar auto-completado mejorado para campos de cliente
function configurarAutoCompletado() {
    const camposCliente = [
        { campo: elementos.cedulaInput, tipo: 'cedula' },
        { campo: elementos.nombreInput, tipo: 'nombre' },
        { campo: elementos.apellidoInput, tipo: 'apellido' },
        { campo: elementos.telefonoInput, tipo: 'telefono' },
        { campo: elementos.aliasInput, tipo: 'alias' }
    ];
    
    camposCliente.forEach(({ campo, tipo }) => {
        if (campo) {
            // Crear contenedor de sugerencias si no existe
            crearContenedorSugerencias(campo, tipo);
            
            campo.addEventListener('input', function() {
                const valor = this.value.trim();
                if (valor.length >= 2) {
                    buscarClienteAutoComplete(this, valor, tipo);
                } else {
                    ocultarSugerencias(tipo);
                }
            });
            
            campo.addEventListener('blur', function() {
                // Limpiar auto-completado cuando se pierde el foco
                setTimeout(() => ocultarSugerencias(tipo), 200);
            });
            
            // Navegación con teclado
            campo.addEventListener('keydown', function(e) {
                manejarNavegacionTeclado(e, tipo);
            });
        }
    });
}

// Buscar cliente para auto-completado mejorado
function buscarClienteAutoComplete(campo, valor, tipo) {
    // Limpiar timeout anterior
    if (timeoutAutoComplete) {
        clearTimeout(timeoutAutoComplete);
    }
    
    // Debounce de 300ms
    timeoutAutoComplete = setTimeout(() => {
        fetch(`../logica/buscar_cliente_autocomplete.php?campo=${encodeURIComponent(tipo)}&valor=${encodeURIComponent(valor)}`)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.clientes && data.clientes.length > 0) {
                    mostrarSugerenciasCliente(data.clientes, tipo, valor);
                } else {
                    ocultarSugerencias(tipo);
                }
            })
            .catch(error => {
                console.error('Error en auto-completado:', error);
                ocultarSugerencias(tipo);
            });
    }, 300);
}

// Mostrar sugerencias de clientes
function mostrarSugerenciasCliente(clientes, tipo, valorBusqueda) {
    const contenedor = document.getElementById(`sugerencias-${tipo}`);
    if (!contenedor) return;
    
    contenedor.innerHTML = '';
    
    // Limitar a máximo 5 sugerencias
    const clientesLimitados = clientes.slice(0, 5);
    
    clientesLimitados.forEach((cliente, index) => {
        const item = document.createElement('div');
        item.className = 'sugerencia-item';
        if (index === 0) item.classList.add('sugerencia-seleccionada');
        
        // Resaltar texto coincidente
        const textoMostrar = obtenerTextoSugerencia(cliente, tipo);
        const textoResaltado = resaltarCoincidencia(textoMostrar, valorBusqueda);
        
        item.innerHTML = `
            <div class="sugerencia-principal">${textoResaltado}</div>
            <div class="sugerencia-secundaria">${cliente.nombre} ${cliente.apellido} - ${cliente.telefono}</div>
        `;
        
        item.addEventListener('click', () => {
            autoRellenarCamposCliente(cliente);
            ocultarSugerencias(tipo);
            animarCampoAutoComplete(elementos.cedulaInput);
        });
        
        item.addEventListener('mouseenter', () => {
            contenedor.querySelectorAll('.sugerencia-item').forEach(s => s.classList.remove('sugerencia-seleccionada'));
            item.classList.add('sugerencia-seleccionada');
        });
        
        contenedor.appendChild(item);
    });
    
    contenedor.style.display = 'block';
}

// Obtener texto para mostrar según el tipo de campo
function obtenerTextoSugerencia(cliente, tipo) {
    switch (tipo) {
        case 'cedula': return cliente.cedula || '';
        case 'nombre': return cliente.nombre || '';
        case 'apellido': return cliente.apellido || '';
        case 'telefono': return cliente.telefono || '';
        case 'alias': return cliente.alias || '';
        default: return `${cliente.nombre} ${cliente.apellido}`;
    }
}

// Resaltar coincidencias en el texto
function resaltarCoincidencia(texto, busqueda) {
    // Validar que texto sea un string válido
    if (!busqueda || !texto || typeof texto !== 'string') {
        return texto || '';
    }
    
    const regex = new RegExp(`(${busqueda.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')})`, 'gi');
    return texto.replace(regex, '<strong>$1</strong>');
}

// Auto-rellenar campos del cliente
function autoRellenarCamposCliente(cliente) {
    // Siempre rellenar todos los campos con los datos del cliente seleccionado
    // Guardar el id_cliente del cliente seleccionado para futuras operaciones
    clienteSeleccionadoId = cliente.id_cliente || null;
    if (elementos.cedulaInput) {
        elementos.cedulaInput.value = cliente.cedula || '';
        animarCampoAutoComplete(elementos.cedulaInput);
    }
    
    if (elementos.nombreInput) {
        elementos.nombreInput.value = cliente.nombre || '';
        animarCampoAutoComplete(elementos.nombreInput);
    }
    
    if (elementos.apellidoInput) {
        elementos.apellidoInput.value = cliente.apellido || '';
        animarCampoAutoComplete(elementos.apellidoInput);
    }
    
    if (elementos.telefonoInput) {
        elementos.telefonoInput.value = cliente.telefono || '';
        animarCampoAutoComplete(elementos.telefonoInput);
    }
    
    if (elementos.aliasInput) {
        elementos.aliasInput.value = cliente.alias || '';
        animarCampoAutoComplete(elementos.aliasInput);
    }
    
    // Revalidar después del auto-completado
    setTimeout(() => {
        validarFormularioCompleto();
    }, 100);
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
    /*
    Indicador de auto-completado deshabilitado por solicitud.
    Código original conservado pero comentado.
    */
    // let indicador = document.getElementById('autocomplete-indicator');
    // if (!indicador) {
    //     indicador = document.createElement('div');
    //     indicador.id = 'autocomplete-indicator';
    //     indicador.style.cssText = `
    //         position: fixed;
    //         top: 20px;
    //         right: 20px;
    //         background: linear-gradient(135deg, #28a745, #20c997);
    //         color: white;
    //         padding: 8px 16px;
    //         border-radius: 20px;
    //         font-size: 12px;
    //         font-weight: bold;
    //          box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3);
    //         z-index: 1000;
    //         opacity: 0;
    //         transform: translateY(-10px);
    //         transition: all 0.3s ease;
    //     `;
    //     indicador.textContent = '✓ Datos auto-completados';
    //     document.body.appendChild(indicador);
    // }
    // setTimeout(() => {
    //     indicador.style.opacity = '1';
    //     indicador.style.transform = 'translateY(0)';
    // }, 100);
    // setTimeout(() => {
    //     indicador.style.opacity = '0';
    //     indicador.style.transform = 'translateY(-10px)';
    // }, 3000);
}

// Limpiar auto-completado
function limpiarAutoComplete() {
    if (timeoutAutoComplete) {
        clearTimeout(timeoutAutoComplete);
        timeoutAutoComplete = null;
    }
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

// Array para almacenar los productos de la factura
let productosFactura = [];
// Moneda actual para visualización: 'USD' o 'VES'
let monedaActual = 'USD';
// Tasa de cambio única (USD -> Bs)
let tasaCambio = parseFloat(localStorage.getItem('tasaCambio')) || 36;
// Leer preferencia guardada de moneda
const monedaGuardadaInit = localStorage.getItem('monedaActual');
if (monedaGuardadaInit === 'USD' || monedaGuardadaInit === 'VES') {
    monedaActual = monedaGuardadaInit;
}

// Cargar tasa oficial desde Configuración (lectura de BD)
async function cargarTasaDesdeConfiguracionCajeroLobby() {
    try {
        const resp = await fetch('../logica/obtener_tasa_cambio.php');
        const data = await resp.json();
        if (data && data.success && data.tasa_cambio) {
            const tasa = parseFloat(data.tasa_cambio);
            if (Number.isFinite(tasa) && tasa > 0) {
                localStorage.setItem('tasaCambio', tasa.toString());
                tasaCambio = tasa; // sincronizar variable local
                try { aplicarMonedaEnUI(); } catch(_) {}
                try { actualizarTablaFactura(); } catch(_) {}
                try { calcularTotalFactura(); } catch(_) {}
                return; // éxito
            }
        }
        throw new Error('Tasa de cambio inválida o no disponible');
    } catch (err) {
        console.warn('No se pudo cargar la tasa desde configuración (Cajero Lobby):', err);
        throw err;
    }
}

function agregarProductoAFactura(boton) {
    console.log('Agregando producto a la factura...');
    
    // Prevenir doble clic deshabilitando temporalmente el botón
    if (boton.disabled) return;
    boton.disabled = true;
    
    // Obtener los datos del producto desde los atributos data-* del botón
    const idProducto = boton.dataset.id;
    const nombreProducto = boton.dataset.nombre;
    const precioProducto = parseFloat(boton.dataset.precio) || 0;
    // Leer cantidad desde el input de la misma fila si existe
    const fila = boton.closest('tr');
    let cantidadSeleccionada = 1;
    if (fila) {
        const inputCantidad = fila.querySelector('.cantidad-input');
        if (inputCantidad) {
            const val = parseInt(inputCantidad.value, 10);
            if (!isNaN(val) && val > 0 && val <= 999) {
                cantidadSeleccionada = val;
            }
        }
    }
    
    console.log('Datos del producto:', {
        id: idProducto,
        nombre: nombreProducto,
        precio: precioProducto
    });
    
    if (!nombreProducto || precioProducto <= 0) {
        console.error('Datos del producto inválidos');
        mostrarAlerta('error', 'Error: Datos del producto inválidos');
        boton.disabled = false; // Rehabilitar el botón
        return;
    }
    
    const producto = {
        id: idProducto,
        nombre: nombreProducto,
        precio: precioProducto,
        categoria: 'General', // Valor por defecto
        cantidad: cantidadSeleccionada
    };
    
    // Verificar si el producto ya está en la factura (buscar por ID)
    const productoExistente = productosFactura.find(p => p.id === producto.id);
    
    if (productoExistente) {
        // Si ya existe, aumentar la cantidad seleccionada
        productoExistente.cantidad += cantidadSeleccionada;
        console.log('Producto existente, nueva cantidad:', productoExistente.cantidad);
    } else {
        // Si no existe, agregarlo
        productosFactura.push(producto);
        console.log('Producto nuevo agregado');
    }
    
    console.log('Array de productos actualizado:', productosFactura);
    
    // Actualizar la tabla de la factura
    actualizarTablaFactura();
    
    // Calcular y mostrar el total
    calcularTotalFactura();
    
    // Mostrar mensaje de éxito
    mostrarAlerta('success', `Producto "${producto.nombre}" agregado a la factura`);
    
    // Rehabilitar el botón después de un breve delay
    setTimeout(() => {
        boton.disabled = false;
    }, 500);
}

// Registrar nuevo cliente
function registrarCliente() {
    // Recopilar datos del cliente (permitir cédula y teléfono vacíos)
    const datosCliente = {
        id_cliente: obtenerClienteId() || null,
        cedula: elementos.cedulaInput.value.trim(),
        nombre: elementos.nombreInput.value.trim(),
        apellido: elementos.apellidoInput.value.trim(),
        telefono: elementos.telefonoInput.value.trim(),
        alias: elementos.aliasInput.value.trim()
    };

    // Validación mínima: nombre y apellido son obligatorios
    if (!validarDatosCliente(datosCliente)) {
        mostrarAlerta('error', 'Debe ingresar al menos nombre y apellido');
        resaltarCamposIncompletos();
        return;
    }
    
    // Deshabilitar botón durante el registro
    deshabilitarBoton(elementos.btnRegistrar);
    elementos.btnRegistrar.textContent = 'Registrando...';
    
    // Enviar datos al backend
    fetch('../logica/agregar_cliente.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(datosCliente)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Guardar/actualizar el id_cliente
            clienteSeleccionadoId = data.id_cliente || clienteSeleccionadoId || null;
            mostrarAlerta('success', data.message);
            // NO limpiar formulario para mantener los datos del cliente registrado
            // Los datos se mantendrán para mostrarlos en la factura
            
            // Continuar a la sección de factura inmediatamente
            setTimeout(() => {
                // Primero actualizar el resumen del cliente mientras los campos están visibles
                actualizarResumenCliente();
                // Luego mostrar la factura
                mostrarSeccionFacturaConAnimacion();
                // Finalmente ocultar el panel del cliente
                ocultarPanelClienteConAnimacion();
            }, 1500);
        } else {
            mostrarAlerta('error', data.message);
        }
    })
    .catch(error => {
        console.error('Error al registrar cliente:', error);
        mostrarAlerta('error', 'Error de conexión. Intente nuevamente.');
    })
    .finally(() => {
        // Rehabilitar botón
        habilitarBoton(elementos.btnRegistrar);
        elementos.btnRegistrar.textContent = 'Registrar';
    });
}

// Limpiar formulario de cliente
function limpiarFormularioCliente() {
    if (elementos.cedulaInput) elementos.cedulaInput.value = '';
    if (elementos.nombreInput) elementos.nombreInput.value = '';
    if (elementos.apellidoInput) elementos.apellidoInput.value = '';
    if (elementos.telefonoInput) elementos.telefonoInput.value = '';
    if (elementos.aliasInput) elementos.aliasInput.value = '';
    
    // Actualizar validación visual
    actualizarIndicadoresVisuales();
}

// Continuar con cliente existente
function continuarConCliente() {
    if (!validarTodosLosCampos()) {
        mostrarAlerta('error', 'Debe completar todos los campos del cliente para continuar');
        resaltarCamposIncompletos();
        return;
    }
    // Actualizar resumen del cliente antes de transicionar
    actualizarResumenCliente();
    mostrarSeccionFacturaConAnimacion();
    ocultarPanelClienteConAnimacion();
}

// Resaltar campos incompletos
function resaltarCamposIncompletos() {
    const campos = [
        { elemento: elementos.cedulaInput, nombre: 'Cédula' },
        { elemento: elementos.nombreInput, nombre: 'Nombre' },
        { elemento: elementos.apellidoInput, nombre: 'Apellido' },
        { elemento: elementos.telefonoInput, nombre: 'Teléfono' },
        { elemento: elementos.aliasInput, nombre: 'Alias' }
    ];
    
    campos.forEach(({ elemento, nombre }) => {
        if (elemento && !elemento.value.trim()) {
            // Animación de shake para campos vacíos
            elemento.style.animation = 'shake 0.5s ease-in-out';
            elemento.style.borderColor = '#dc3545';
            elemento.style.backgroundColor = '#fff5f5';
            
            setTimeout(() => {
                elemento.style.animation = '';
                elemento.style.backgroundColor = '';
            }, 500);
        }
    });
}

// Validar datos del cliente (actualizada)
function validarDatosCliente(datos) {
    // cédula y teléfono pueden ser vacíos; nombre y apellido son obligatorios
    return !!(datos.nombre && datos.apellido);
}

// 🔹 FUNCIONES DE ANIMACIÓN MEJORADAS ENTRE CLIENTE Y FACTURA
function ocultarPanelClienteConAnimacion() {
    if (elementos.containerCliente) {
        // Agregar clase de transición
        elementos.containerCliente.classList.add('transitioning');
        
        // Aplicar animación de salida
        elementos.containerCliente.classList.add('slide-out-left');
        
        // Después de la animación, ocultar completamente
        setTimeout(() => {
            elementos.containerCliente.style.display = 'none';
            elementos.containerCliente.classList.remove('slide-out-left', 'transitioning');
        }, 600); // Duración de la animación
    }
}

function mostrarSeccionFacturaConAnimacion() {
    if (elementos.containerFactura) {
        // NO actualizar resumen aquí ya que se hace antes de llamar esta función
        
        // Mostrar el contenedor
        elementos.containerFactura.style.display = 'flex';
        elementos.containerFactura.classList.remove('hidden');
        elementos.containerFactura.classList.add('transitioning');
        
        // Pequeño delay para asegurar que el display se aplique
        setTimeout(() => {
            // Aplicar animación de entrada
            elementos.containerFactura.classList.add('slide-in-right');
            
            // Después de un breve delay, mostrar elementos internos
            setTimeout(() => {
                elementos.containerFactura.classList.add('show');
            }, 200);
            
            // Limpiar clases después de la animación
            setTimeout(() => {
                elementos.containerFactura.classList.remove('slide-in-right', 'transitioning');
            }, 600);
            
            // Scroll suave a la factura
            elementos.containerFactura.scrollIntoView({ 
                behavior: 'smooth', 
                block: 'start' 
            });
        }, 50);
    }
}

// Actualizar la tabla de la factura
function actualizarTablaFactura() {
    console.log('Actualizando tabla de factura...');
    console.log('Productos en factura:', productosFactura);
    
    if (!elementos.tablaFacturaBody) {
        console.error('Elemento tablaFacturaBody no encontrado');
        return;
    }
    
    // Limpiar la tabla
    elementos.tablaFacturaBody.innerHTML = '';
    
    if (productosFactura.length === 0) {
        elementos.tablaFacturaBody.innerHTML = `
            <tr>
                <td colspan="6" class="text-center text-gray-500">
                    No hay productos en la factura
                </td>
            </tr>
        `;
        return;
    }
    
    // Agregar cada producto a la tabla
    productosFactura.forEach((producto, index) => {
        console.log(`Procesando producto ${index}:`, producto);
        
        const subtotalUSD = producto.precio * producto.cantidad;
        const subtotalBs = subtotalUSD * tasaCambio;
        
        console.log(`Subtotal USD: ${subtotalUSD}, Subtotal Bs: ${subtotalBs}`);
        
        const fila = document.createElement('tr');
        fila.innerHTML = `
            <td>${producto.nombre}</td>
            <td>
                <div class="cantidad-controls">
                    <button onclick="cambiarCantidadProducto(${index}, -1)" 
                            class="btn-cantidad btn-decrementar">
                        -
                    </button>
                    <input type="number" 
                           class="cantidad-numero" 
                           value="${producto.cantidad}" 
                           min="1" 
                           max="999"
                           oninput="actualizarCantidadManual(${index}, this.value)"
                           onchange="actualizarCantidadManual(${index}, this.value)"
                           onblur="validarCantidadInput(this)"
                           onclick="this.select()">
                    <button onclick="cambiarCantidadProducto(${index}, 1)" 
                            class="btn-cantidad btn-incrementar">
                        +
                    </button>
                </div>
            </td>
            <td>$${producto.precio.toFixed(2)}</td>
            <td>Bs. ${(producto.precio * tasaCambio).toFixed(2)}</td>
            <td>${monedaActual === 'USD' ? `$${subtotalUSD.toFixed(2)}` : `Bs ${subtotalBs.toFixed(2)}`}</td>
            <td>
                <button onclick="eliminarProductoFactura(${index})" 
                        class="btn-eliminar">
                    Eliminar
                </button>
            </td>
        `;
        elementos.tablaFacturaBody.appendChild(fila);
    });
    
    console.log('Tabla actualizada');

    // Aplicar clase de moneda a la tabla para ocultar columnas
    const tabla = document.querySelector('.tabla-factura');
    if (tabla) {
        tabla.classList.toggle('moneda-usd', monedaActual === 'USD');
        tabla.classList.toggle('moneda-ves', monedaActual !== 'USD');
    }
}

// Cambiar cantidad de un producto en la factura
function cambiarCantidadProducto(index, cambio) {
    if (index < 0 || index >= productosFactura.length) return;
    
    productosFactura[index].cantidad += cambio;
    
    // Si la cantidad llega a 0 o menos, eliminar el producto
    if (productosFactura[index].cantidad <= 0) {
        productosFactura.splice(index, 1);
    }
    
    // Actualizar la tabla y el total
    actualizarTablaFactura();
    calcularTotalFactura();
}

// Eliminar producto de la factura
function eliminarProductoFactura(index) {
    if (index < 0 || index >= productosFactura.length) return;
    
    const producto = productosFactura[index];
    productosFactura.splice(index, 1);
    
    // Actualizar la tabla y el total
    actualizarTablaFactura();
    calcularTotalFactura();
    
    mostrarAlerta('info', `Producto "${producto.nombre}" eliminado de la factura`);
}

// Calcular y mostrar el total de la factura
function calcularTotalFactura() {
    console.log('Calculando total de factura...');
    console.log('Productos en factura:', productosFactura);
    
    const totalUSD = productosFactura.reduce((sum, producto) => {
        console.log(`Producto: ${producto.nombre}, Precio: ${producto.precio}, Cantidad: ${producto.cantidad}`);
        return sum + (producto.precio * producto.cantidad);
    }, 0);
    
    const totalBs = totalUSD * tasaCambio; // Tasa de cambio
    
    console.log('Total USD:', totalUSD);
    console.log('Total Bs:', totalBs);
    console.log('Elemento totalText:', elementos.totalText);
    
    if (elementos.totalText) {
        const totalMostrar = monedaActual === 'USD' 
            ? `$${totalUSD.toFixed(2)} USD`
            : `Bs ${totalBs.toFixed(2)}`;
        elementos.totalText.innerHTML = `
            <div class="total-container">
                <div class="total-label">TOTAL A PAGAR</div>
                <div class="total-amounts">
                    <div class="total-moneda">${totalMostrar}</div>
                </div>
            </div>
        `;
        console.log('Total actualizado en DOM');
    } else {
        console.error('Elemento totalText no encontrado');
    }
    
    return totalUSD;
}

// Toggle de moneda y actualización de UI
function toggleMoneda() {
    monedaActual = (monedaActual === 'USD') ? 'VES' : 'USD';
    // Persistir preferencia
    localStorage.setItem('monedaActual', monedaActual);
    const btn = document.getElementById('btn-toggle-moneda');
    if (btn) {
        btn.textContent = monedaActual === 'USD' ? 'USD' : 'Bs';
    }
    aplicarMonedaEnUI();
    actualizarTablaFactura();
    calcularTotalFactura();
    // Refrescar la tabla de productos según la moneda actual
    try {
        const termino = elementos.busquedaProducto?.value?.trim();
        if (termino) {
            filtrarProductos();
        } else {
            cargarTodosLosProductos();
        }
    } catch(_) {}
}

// Aplica moneda a encabezados y tablas
function aplicarMonedaEnUI() {
    // Encabezado de la lista de productos
    const thPrecioProducto = document.getElementById('th-precio-producto');
    if (thPrecioProducto) {
        thPrecioProducto.textContent = monedaActual === 'USD' ? 'Precio ($)' : 'Precio (Bs)';
    }
    // Texto del botón de moneda
    const btn = document.getElementById('btn-toggle-moneda');
    if (btn) {
        btn.textContent = monedaActual === 'USD' ? 'USD' : 'Bs';
    }
    // Clase en tabla de factura para ocultar columnas
    const tabla = document.querySelector('.tabla-factura');
    if (tabla) {
        tabla.classList.toggle('moneda-usd', monedaActual === 'USD');
        tabla.classList.toggle('moneda-ves', monedaActual !== 'USD');
    }
}
function actualizarResumenCliente() {
    console.log('Ejecutando actualizarResumenCliente()');
    
    if (elementos.resumenCliente) {
        const cedula = elementos.cedulaInput?.value.trim() || '';
        const nombre = elementos.nombreInput?.value.trim() || '';
        const apellido = elementos.apellidoInput?.value.trim() || '';
        const telefono = elementos.telefonoInput?.value.trim() || '';
        const alias = elementos.aliasInput?.value.trim() || '';
        
        console.log('Valores obtenidos:', { cedula, nombre, apellido, telefono, alias });
        console.log('Elementos DOM:', {
            cedulaInput: elementos.cedulaInput,
            nombreInput: elementos.nombreInput,
            apellidoInput: elementos.apellidoInput,
            telefonoInput: elementos.telefonoInput,
            aliasInput: elementos.aliasInput,
            resumenCliente: elementos.resumenCliente
        });
        
        if (cedula || nombre || apellido) {
            const resumen = `
                <strong>Cédula:</strong> ${cedula}<br>
                <strong>Nombre:</strong> ${nombre} ${apellido}<br>
                <strong>Teléfono:</strong> ${telefono}<br>
                <strong>Alias:</strong> ${alias}
            `;
            elementos.resumenCliente.innerHTML = resumen;
            console.log('Resumen actualizado con datos del cliente');
        } else {
            elementos.resumenCliente.textContent = 'Ninguno';
            console.log('No hay datos del cliente, mostrando "Ninguno"');
        }
    } else {
        console.error('Elemento resumenCliente no encontrado');
    }
}

// Filtrar productos en tiempo real
function filtrarProductos() {
    if (!elementos.busquedaProducto || !elementos.productosBody) return;
    
    const filtro = elementos.busquedaProducto.value.trim();
    
    // Si no hay filtro, mostrar todos los productos
    if (filtro.length === 0) {
        const filas = elementos.productosBody.querySelectorAll('tr');
        filas.forEach(fila => {
            fila.style.display = '';
            fila.style.animation = 'fadeIn 0.3s ease';
        });
        return;
    }
    
    // Si hay menos de 2 caracteres, no buscar
    if (filtro.length < 2) {
        return;
    }
    
    // Buscar productos usando el backend
    buscarProductosAutoComplete(filtro);
}

// Nueva función para buscar productos con autocompletado
function buscarProductosAutoComplete(termino) {
    // Limpiar timeout anterior
    if (timeoutAutoComplete) {
        clearTimeout(timeoutAutoComplete);
    }
    
    // Debounce de 300ms
    timeoutAutoComplete = setTimeout(() => {
        fetch(`../logica/buscar_producto.php?q=${encodeURIComponent(termino)}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    actualizarTablaProductos(data.productos);
                    mostrarIndicadorBusquedaProductos(data.productos.length);
                } else {
                    // Si no hay resultados, mostrar mensaje
                    mostrarTablaVacia();
                }
            })
            .catch(error => {
                console.error('Error al buscar productos:', error);
                mostrarAlerta('error', 'Error al buscar productos');
            });
    }, 300);
}

// Actualizar tabla de productos con resultados de búsqueda
function actualizarTablaProductos(productos) {
    console.log('Actualizando tabla de productos con:', productos);
    
    if (!elementos.productosBody) return;
    
    // Limpiar tabla actual
    elementos.productosBody.innerHTML = '';
    
    if (productos.length === 0) {
        mostrarTablaVacia();
        return;
    }
    
    // Agregar productos encontrados
    productos.forEach(producto => {
        console.log('Procesando producto para tabla:', producto);
        
        const fila = document.createElement('tr');
        const precioUSD = parseFloat(producto.precio_venta);
        const precioMostrar = monedaActual === 'USD' 
            ? `$${precioUSD.toFixed(2)}` 
            : `Bs ${ (precioUSD * tasaCambio).toFixed(2) }`;
        fila.innerHTML = `
            <td>${producto.nombre_produc}</td>
            <td>${precioMostrar}</td>
            <td>${producto.stock_disponible !== undefined ? producto.stock_disponible : (producto.cantidad_total ?? 0)}</td>
            <td>
                <button class="btn-agregar-producto modern-btn" 
                        data-id="${producto.id_producto}" 
                        data-nombre="${producto.nombre_produc}" 
                        data-precio="${producto.precio_venta}" 
                        aria-label="Agregar ${producto.nombre_produc} a la factura">
                    Agregar
                </button>
            </td>
        `;
        
        // Animación de aparición
        fila.style.animation = 'fadeIn 0.3s ease';
        elementos.productosBody.appendChild(fila);
    });
    
    console.log('Tabla de productos actualizada');
}

// Mostrar tabla vacía cuando no hay resultados
function mostrarTablaVacia() {
    if (!elementos.productosBody) return;
    
    elementos.productosBody.innerHTML = `
        <tr>
            <td colspan="4" style="text-align: center; padding: 20px; color: #666;">
                No se encontraron productos
            </td>
        </tr>
    `;
}

// Mostrar indicador de búsqueda de productos
function mostrarIndicadorBusquedaProductos(cantidad) {
    // Crear o actualizar indicador
    let indicador = document.getElementById('indicador-busqueda-productos');
    
    if (!indicador) {
        indicador = document.createElement('div');
        indicador.id = 'indicador-busqueda-productos';
        indicador.style.cssText = `
            position: absolute;
            top: -25px;
            right: 0;
            background: #28a745;
            color: white;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            z-index: 1000;
            animation: fadeIn 0.3s ease;
        `;
        
        // Agregar al contenedor de búsqueda
        const contenedorBusqueda = elementos.busquedaProducto.parentElement;
        if (contenedorBusqueda) {
            contenedorBusqueda.style.position = 'relative';
            contenedorBusqueda.appendChild(indicador);
        }
    }
    
    indicador.textContent = `${cantidad} producto${cantidad !== 1 ? 's' : ''} encontrado${cantidad !== 1 ? 's' : ''}`;
    
    // Ocultar después de 2 segundos
    setTimeout(() => {
        if (indicador && indicador.parentElement) {
            indicador.remove();
        }
    }, 2000);
}

// Animación para botones
function animarBoton(boton) {
    boton.style.transform = 'scale(0.95)';
    boton.style.transition = 'transform 0.1s ease';
    
    setTimeout(() => {
        boton.style.transform = 'scale(1)';
    }, 100);
}

// Panel de confirmación HTML (reutilizable)
function confirmarAccion(mensaje, opciones = {}) {
    return new Promise(resolve => {
        const {
            titulo = 'Confirmación',
            textoConfirmar = 'Confirmar',
            textoCancelar = 'Cancelar'
        } = opciones;

        // Crear overlay
        const overlay = document.createElement('div');
        overlay.style.cssText = `
            position: fixed; inset: 0; background: rgba(0,0,0,0.5);
            display: flex; align-items: center; justify-content: center;
            z-index: 10000;
        `;

        // Crear panel
        const panel = document.createElement('div');
        panel.style.cssText = `
            background: #fff; border-radius: 10px; width: 90%; max-width: 420px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
            overflow: hidden; transform: translateY(-10px); opacity: 0;
            transition: all .2s ease;
        `;

        const header = document.createElement('div');
        header.textContent = titulo;
        header.style.cssText = `background: #222; color: #fff; padding: 12px 16px; font-weight: 600;`;

        const body = document.createElement('div');
        body.textContent = mensaje;
        body.style.cssText = `padding: 16px; color: #333; line-height: 1.5;`;

        const footer = document.createElement('div');
        footer.style.cssText = `display: flex; gap: 10px; padding: 12px 16px; justify-content: flex-end; background: #f7f7f7;`;

        const btnCancelar = document.createElement('button');
        btnCancelar.textContent = textoCancelar;
        btnCancelar.style.cssText = `
            padding: 8px 14px; border-radius: 6px; border: 1px solid #ccc; background: #fff; cursor: pointer;
        `;

        const btnConfirmar = document.createElement('button');
        btnConfirmar.textContent = textoConfirmar;
        btnConfirmar.style.cssText = `
            padding: 8px 14px; border-radius: 6px; border: 1px solid #28a745; background: #28a745; color: #fff; cursor: pointer;
        `;

        btnCancelar.addEventListener('click', () => {
            document.body.removeChild(overlay);
            resolve(false);
        });
        btnConfirmar.addEventListener('click', () => {
            document.body.removeChild(overlay);
            resolve(true);
        });

        footer.appendChild(btnCancelar);
        footer.appendChild(btnConfirmar);

        panel.appendChild(header);
        panel.appendChild(body);
        panel.appendChild(footer);
        overlay.appendChild(panel);
        document.body.appendChild(overlay);

        requestAnimationFrame(() => {
            panel.style.transform = 'translateY(0)';
            panel.style.opacity = '1';
        });
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
        contenedor.style.cssText = `
            position: fixed; top: 16px; right: 16px; z-index: 10001;
            display: flex; flex-direction: column; gap: 10px; align-items: flex-end;
        `;
        document.body.appendChild(contenedor);
    }

    const toast = document.createElement('div');
    toast.style.cssText = `
        color: #fff; background: ${bg}; border-left: 5px solid ${border};
        padding: 10px 12px; border-radius: 8px; box-shadow: 0 6px 14px rgba(0,0,0,0.15);
        min-width: 240px; max-width: 360px; opacity: 0; transform: translateY(-6px);
        transition: all .2s ease; font-size: 14px;
    `;
    toast.textContent = mensaje;
    contenedor.appendChild(toast);

    requestAnimationFrame(() => {
        toast.style.opacity = '1';
        toast.style.transform = 'translateY(0)';
    });

    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateY(-6px)';
        setTimeout(() => {
            if (toast.parentNode === contenedor) contenedor.removeChild(toast);
        }, 200);
    }, 3500);
}

// Reimplementar mostrarAlerta para usar toasts en lugar de banners
function mostrarAlerta(tipo = 'info', mensaje = '') {
    try {
        mostrarToast(tipo, mensaje);
    } catch (e) {
        console.warn('No se pudo mostrar el toast:', e);
    }
}

// Mostrar alertas animadas
function mostrarAlerta(tipo, mensaje) {
    /*
    Alertas visuales deshabilitadas por solicitud.
    Código original conservado pero comentado.
    */
    // const alertaAnterior = document.querySelector('.custom-alert');
    // if (alertaAnterior) {
    //     alertaAnterior.remove();
    // }
    // const alerta = document.createElement('div');
    // alerta.className = `custom-alert alert-${tipo}`;
    // alerta.textContent = mensaje;
    // alerta.style.cssText = `
    //     position: fixed;
    //     top: 20px;
    //     right: 20px;
    //     padding: 15px 20px;
    //     border-radius: 8px;
    //     color: white;
    //     font-weight: bold;
    //     z-index: 10000;
    //     max-width: 300px;
    //     box-shadow: 0 4px 12px rgba(0,0,0,0.3);
    //     animation: slideInRight 0.3s ease-out;
    // `;
    // const colores = {
    //     success: '#28a745',
    //     error: '#dc3545',
    //     warning: '#ffc107',
    //     info: '#17a2b8'
    // };
    // alerta.style.backgroundColor = colores[tipo] || colores.info;
    // document.body.appendChild(alerta);
    // setTimeout(() => {
    //     if (alerta.parentNode) {
    //         alerta.style.animation = 'slideOutRight 0.3s ease-in';
    //         setTimeout(() => alerta.remove(), 300);
    //     }
    // }, 4000);
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
        
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
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
        
        .button.disabled {
            pointer-events: none;
        }
        
        .campo-completo {
            border-color: #28a745 !important;
        }
        
        .campo-incompleto {
            border-color: #dc3545 !important;
        }
        
        .container_factura {
            transition: all 0.5s ease;
        }
        
        .container_cliente {
            transition: all 0.5s ease;
            overflow: hidden;
        }
        
        .custom-alert {
            font-family: Arial, sans-serif;
            font-size: 14px;
        }
    `;
    document.head.appendChild(estiloAnimaciones);
}

// Función para cargar todos los productos inicialmente
function cargarTodosLosProductos() {
    fetch('../logica/obtener_producto.php')
        .then(response => response.json())
        .then(data => {
            if (data.success && data.productos) {
                actualizarTablaProductos(data.productos);
            } else {
                console.error('Error al cargar productos:', data.message);
                mostrarTablaVacia('Error al cargar productos');
            }
        })
        .catch(error => {
            console.error('Error en la petición:', error);
            mostrarTablaVacia('Error de conexión');
        });
}

// ...

// Crear contenedor de sugerencias para autocompletado
function crearContenedorSugerencias(campo, tipo) {
    const contenedorId = `sugerencias-${tipo}`;
    let contenedor = document.getElementById(contenedorId);
    
    if (!contenedor) {
        contenedor = document.createElement('div');
        contenedor.id = contenedorId;
        contenedor.className = 'sugerencias-autocomplete';
        contenedor.style.cssText = `
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            display: none;
        `;
        
        // Insertar después del campo
        campo.parentNode.style.position = 'relative';
        campo.parentNode.appendChild(contenedor);
        
        // Agregar clase de animación cuando se muestre
        const observer = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                if (mutation.type === 'attributes' && mutation.attributeName === 'style') {
                    if (contenedor.style.display !== 'none' && !contenedor.classList.contains('entrando')) {
                        contenedor.classList.add('entrando');
                        setTimeout(() => {
                            contenedor.classList.remove('entrando');
                        }, 300);
                    }
                }
            });
        });
        
        observer.observe(contenedor, { attributes: true });
    }
    
    return contenedor;
}

// Manejar navegación con teclado en sugerencias
function manejarNavegacionTeclado(e, tipo) {
    const contenedor = document.getElementById(`sugerencias-${tipo}`);
    if (!contenedor || contenedor.style.display === 'none') return;
    
    const sugerencias = contenedor.querySelectorAll('.sugerencia-item');
    if (sugerencias.length === 0) return;
    
    let seleccionado = contenedor.querySelector('.sugerencia-seleccionada');
    let indiceActual = seleccionado ? Array.from(sugerencias).indexOf(seleccionado) : -1;
    
    switch (e.key) {
        case 'ArrowDown':
            e.preventDefault();
            if (seleccionado) seleccionado.classList.remove('sugerencia-seleccionada');
            indiceActual = (indiceActual + 1) % sugerencias.length;
            sugerencias[indiceActual].classList.add('sugerencia-seleccionada');
            sugerencias[indiceActual].scrollIntoView({ block: 'nearest' });
            break;
            
        case 'ArrowUp':
            e.preventDefault();
            if (seleccionado) seleccionado.classList.remove('sugerencia-seleccionada');
            indiceActual = indiceActual <= 0 ? sugerencias.length - 1 : indiceActual - 1;
            sugerencias[indiceActual].classList.add('sugerencia-seleccionada');
            sugerencias[indiceActual].scrollIntoView({ block: 'nearest' });
            break;
            
        case 'Enter':
            e.preventDefault();
            if (seleccionado) {
                seleccionado.click();
            }
            break;
            
        case 'Escape':
            e.preventDefault();
            ocultarSugerencias(tipo);
            break;
    }
}

// Ocultar sugerencias de autocompletado
function ocultarSugerencias(tipo) {
    const contenedor = document.getElementById(`sugerencias-${tipo}`);
    if (contenedor) {
        contenedor.style.display = 'none';
        contenedor.innerHTML = '';
    }
}

// Agregar estilos CSS para sugerencias de autocompletado
function agregarEstilosSugerencias() {
    const estiloId = 'estilos-sugerencias-autocomplete';
    if (document.getElementById(estiloId)) return;
    
    const estilos = document.createElement('style');
    estilos.id = estiloId;
    estilos.textContent = `
        .sugerencias-autocomplete {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 14px;
            background: #ffffff;
            border: 1px solid #e1e5e9;
            border-radius: 12px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
            backdrop-filter: blur(10px);
            max-height: 280px;
            overflow-y: auto;
            z-index: 1000;
            animation: slideDown 0.2s ease-out;
        }
        
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .sugerencia-item {
            padding: 14px 18px;
            cursor: pointer;
            border-bottom: 1px solid #f5f7fa;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            background: linear-gradient(135deg, transparent 0%, rgba(59, 130, 246, 0.02) 100%);
        }
        
        .sugerencia-item:first-child {
            border-top-left-radius: 12px;
            border-top-right-radius: 12px;
        }
        
        .sugerencia-item:last-child {
            border-bottom: none;
            border-bottom-left-radius: 12px;
            border-bottom-right-radius: 12px;
        }
        
        .sugerencia-item::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            width: 3px;
            height: 100%;
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            transform: scaleY(0);
            transition: transform 0.2s ease;
        }
        
        .sugerencia-item:hover,
        .sugerencia-item.sugerencia-seleccionada {
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            transform: translateX(2px);
            box-shadow: 0 2px 8px rgba(59, 130, 246, 0.1);
        }
        
        .sugerencia-item:hover::before,
        .sugerencia-item.sugerencia-seleccionada::before {
            transform: scaleY(1);
        }
        
        .sugerencia-principal {
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 4px;
            font-size: 14px;
            line-height: 1.4;
        }
        
        .sugerencia-principal strong {
            background: linear-gradient(135deg, #fef3c7, #fde68a);
            color: #92400e;
            padding: 2px 6px;
            border-radius: 6px;
            font-weight: 700;
            box-shadow: 0 1px 3px rgba(146, 64, 14, 0.1);
        }
        
        .sugerencia-secundaria {
            font-size: 12px;
            color: #64748b;
            opacity: 0.9;
            font-weight: 400;
            line-height: 1.3;
        }
        
        /* Scrollbar personalizado */
        .sugerencias-autocomplete::-webkit-scrollbar {
            width: 8px;
        }
        
        .sugerencias-autocomplete::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 10px;
            margin: 8px 0;
        }
        
        .sugerencias-autocomplete::-webkit-scrollbar-thumb {
            background: linear-gradient(135deg, #cbd5e1, #94a3b8);
            border-radius: 10px;
            border: 2px solid #f1f5f9;
        }
        
        .sugerencias-autocomplete::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(135deg, #94a3b8, #64748b);
        }
        
        /* Efecto de entrada suave */
        .sugerencias-autocomplete.entrando {
            animation: entradaSuave 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
        }
        
        @keyframes entradaSuave {
            0% {
                opacity: 0;
                transform: translateY(-15px) scale(0.95);
            }
            100% {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }
        
        /* Indicador de más resultados */
        .sugerencias-autocomplete::after {
            content: '';
            position: sticky;
            bottom: 0;
            height: 20px;
            background: linear-gradient(transparent, rgba(255, 255, 255, 0.9));
            pointer-events: none;
        }
    `;
    
    document.head.appendChild(estilos);
}

// Función para actualizar cantidad manualmente
function actualizarCantidadManual(index, nuevaCantidad) {
    const cantidad = parseInt(nuevaCantidad);
    
    // Validar que la cantidad sea válida
    if (isNaN(cantidad) || cantidad < 1) {
        mostrarAlerta('error', 'La cantidad debe ser un número mayor a 0');
        actualizarTablaFactura(); // Restaurar valor anterior
        return;
    }
    
    if (cantidad > 999) {
        mostrarAlerta('error', 'La cantidad no puede ser mayor a 999');
        actualizarTablaFactura(); // Restaurar valor anterior
        return;
    }
    
    // Actualizar la cantidad del producto
    productosFactura[index].cantidad = cantidad;
    
    // Actualizar subtotal de la fila en tiempo real sin re-renderizar toda la tabla
    if (elementos.tablaFacturaBody && elementos.tablaFacturaBody.children[index]) {
        const fila = elementos.tablaFacturaBody.children[index];
        const subtotalUSD = productosFactura[index].precio * cantidad;
        const subtotalBs = subtotalUSD * tasaCambio;
        const subtotalCol = fila.children && fila.children[4];
        if (subtotalCol) {
            subtotalCol.textContent = monedaActual === 'USD' 
                ? `$${subtotalUSD.toFixed(2)}` 
                : `Bs ${subtotalBs.toFixed(2)}`;
        }
    }
    // Recalcular totales generales
    calcularTotalFactura();
    
    console.log(`Cantidad actualizada manualmente: ${cantidad} para producto ${productosFactura[index].nombre}`);
}

// Función para validar el input de cantidad
function validarCantidadInput(input) {
    const valor = parseInt(input.value);
    
    if (isNaN(valor) || valor < 1) {
        input.value = 1;
        input.style.borderColor = '#ff4757';
        setTimeout(() => {
            input.style.borderColor = '';
        }, 200);
    } else if (valor > 999) {
        input.value = 999;
        input.style.borderColor = '#ff4757';
        setTimeout(() => {
            input.style.borderColor = '';
        }, 200);
    } else {
        input.style.borderColor = '#2ed573';
        setTimeout(() => {
            input.style.borderColor = '';
        }, 100);
    }
}

// Función para eliminar factura completa y regresar al cliente (sin confirmación)
function eliminarFacturaCompleta() {
        // Limpiar array de productos
        productosFactura = [];
        
        // Actualizar tabla
        actualizarTablaFactura();
        
        // Recalcular totales
        calcularTotalFactura();
        
        // Ocultar sección factura
        const containerFactura = elementos.containerFactura;
        containerFactura.classList.add('hidden');
        
        // Mostrar sección cliente
        const containerCliente = elementos.containerCliente;
        if (containerCliente) {
            containerCliente.style.display = 'flex';
            containerCliente.classList.remove('hidden');
        } else {
            console.error('No se encontró el container_cliente');
        }
        
        // Limpiar búsqueda de productos
        if (elementos.busquedaProducto) {
            elementos.busquedaProducto.value = '';
            mostrarTablaVacia();
        }
        
        // Limpiar formulario de cliente para permitir agregar nuevo cliente
        limpiarFormularioCliente();
        
        // Limpiar autocompletado
        limpiarAutoComplete();
        
        // Mostrar mensaje de confirmación
        // mostrarAlerta('success', 'Regresando a selección de cliente. Puedes agregar un nuevo cliente o buscar uno existente.');
        
        console.log('Factura eliminada completamente');
        
        // Recargar la página
        window.location.reload();
    }


// Función para transición suave de vuelta al cliente
function mostrarPanelClienteConAnimacion() {
    if (elementos.containerCliente) {
        // Mostrar el contenedor
        elementos.containerCliente.style.display = 'flex';
        elementos.containerCliente.classList.add('transitioning');
        
        // Pequeño delay para asegurar que el display se aplique
        setTimeout(() => {
            // Aplicar animación de entrada
            elementos.containerCliente.classList.add('fade-in-up');
            
            // Limpiar clases después de la animación
            setTimeout(() => {
                elementos.containerCliente.classList.remove('fade-in-up', 'transitioning');
            }, 100);
        }, 50);
    }
}

function ocultarSeccionFacturaConAnimacion() {
    if (elementos.containerFactura) {
        // Agregar clase de transición
        elementos.containerFactura.classList.add('transitioning');
        elementos.containerFactura.classList.remove('show');
        
        // Aplicar animación de salida
        elementos.containerFactura.classList.add('fade-out-down');
        
        // Después de la animación, ocultar completamente
        setTimeout(() => {
            elementos.containerFactura.style.display = 'none';
            elementos.containerFactura.classList.remove('fade-out-down', 'transitioning');
            elementos.containerFactura.classList.add('hidden');
        }, 200);
    }
}

// Función para agregar productos a cuenta del cliente (crédito)
async function agregarACuenta() {
    console.log('Agregando productos a cuenta del cliente...');
    
    // Validar que hay productos en la factura
    if (productosFactura.length === 0) {
        mostrarAlerta('error', 'No hay productos en la factura para agregar a cuenta');
        return;
    }
    
    // Validar que hay un cliente seleccionado
    const clienteId = obtenerClienteId();
    if (!clienteId) {
        mostrarAlerta('error', 'Debe seleccionar un cliente antes de agregar a cuenta');
        return;
    }
    
    const confirmado = await confirmarAccion('¿Está seguro de agregar estos productos a la cuenta del cliente?', {
        titulo: 'Agregar a cuenta',
        textoConfirmar: 'Sí, agregar',
        textoCancelar: 'Cancelar'
    });
    if (!confirmado) return;
    
    // Preparar datos para enviar
    const datosFactura = {
        tipo: 'credito',
        cliente_id: clienteId,
        productos: productosFactura.map(producto => ({
            id: producto.id,
            nombre: producto.nombre,
            cantidad: producto.cantidad,
            precio: producto.precio,
            total: producto.precio * producto.cantidad
        })),
        total_dolares: calcularTotalDolares(),
        total_bolivares: calcularTotalBolivares()
    };
    
    console.log('Datos a enviar:', datosFactura);
    
    // Deshabilitar botón mientras se procesa
    elementos.btnVerCuenta.disabled = true;
    elementos.btnVerCuenta.textContent = 'Procesando...';
    
    // Enviar datos al servidor
    fetch('../logica/guardar_factura.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(datosFactura)
    })
    .then(response => response.json())
    .then(data => {
        console.log('Respuesta del servidor:', data);
        
        if (data.success) {
            mostrarAlerta('success', `Productos agregados a cuenta exitosamente. Número de crédito: ${data.numero_factura}`);
            
            // Limpiar factura después de agregar a cuenta
            setTimeout(() => {
                limpiarFacturaCompleta();
            }, 500);
        } else {
            mostrarAlerta('error', data.message || 'Error al agregar productos a cuenta');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        mostrarAlerta('error', 'Error de conexión al agregar productos a cuenta');
    })
    .finally(() => {
        // Rehabilitar botón
        elementos.btnVerCuenta.disabled = false;
        elementos.btnVerCuenta.textContent = 'Agregar a cuenta';
    });
}

async function procesarPago() {
    console.log('Procesando pago de la factura...');
    
    // Validar que hay productos en la factura
    if (productosFactura.length === 0) {
        mostrarAlerta('error', 'No hay productos en la factura para procesar el pago');
        return;
    }
    
    // Validar que hay un cliente seleccionado
    const clienteId = obtenerClienteId();
    if (!clienteId) {
        mostrarAlerta('error', 'Debe seleccionar un cliente antes de procesar el pago');
        return;
    }
    
    const totalDolares = calcularTotalDolares();
    const confirmado = await confirmarAccion(`¿Confirma el pago de $${totalDolares.toFixed(2)}?`, {
        titulo: 'Confirmar pago',
        textoConfirmar: 'Pagar',
        textoCancelar: 'Cancelar'
    });
    if (!confirmado) return;
    
    // Preparar datos para enviar
    const datosFactura = {
        tipo: 'contado',
        cliente_id: clienteId,
        productos: productosFactura.map(producto => ({
            id: producto.id,
            nombre: producto.nombre,
            cantidad: producto.cantidad,
            precio: producto.precio,
            total: producto.precio * producto.cantidad
        })),
        total_dolares: totalDolares,
        total_bolivares: calcularTotalBolivares()
    };
    
    console.log('Datos a enviar:', datosFactura);
    
    // Deshabilitar botón mientras se procesa
    elementos.btnPagar.disabled = true;
    elementos.btnPagar.textContent = 'Procesando...';
    
    // Enviar datos al servidor
    fetch('../logica/guardar_factura.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(datosFactura)
    })
    .then(response => response.json())
    .then(data => {
        console.log('Respuesta del servidor:', data);
        
        if (data.success) {
            mostrarAlerta('success', `Pago procesado exitosamente. Número de factura: ${data.numero_factura}`);
            
            // Limpiar factura después del pago
            setTimeout(() => {
                limpiarFacturaCompleta();
            }, 500);
        } else {
            mostrarAlerta('error', data.message || 'Error al procesar el pago');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        mostrarAlerta('error', 'Error de conexión al procesar el pago');
    })
    .finally(() => {
        // Rehabilitar botón
        elementos.btnPagar.disabled = false;
        elementos.btnPagar.textContent = 'Pagar';
    });
}

// Función auxiliar para obtener el ID del cliente actual
function obtenerClienteId() {
    // Buscar el ID del cliente en los campos del formulario o en una variable global
    if (clienteSeleccionadoId && Number.isInteger(clienteSeleccionadoId)) {
        return clienteSeleccionadoId;
    }
    const cedulaCliente = elementos.cedulaInput?.value?.trim();
    if (!cedulaCliente) {
        console.error('No se encontró cédula del cliente');
        return null;
    }
    // Si no hay id almacenado, pedir al backend que resuelva el cliente por cédula de forma síncrona no es posible aquí.
    // Forzar selección/registro correcto antes de continuar.
    console.warn('id_cliente no está definido. Seleccione desde autocompletado o registre el cliente.');
    return null;
}

// Función auxiliar para calcular total en dólares
function calcularTotalDolares() {
    return productosFactura.reduce((total, producto) => {
        return total + (producto.precio * producto.cantidad);
    }, 0);
}

// Función auxiliar para calcular total en bolívares
function calcularTotalBolivares() {
    const totalDolares = calcularTotalDolares();
    return totalDolares * tasaCambio; // Tasa de cambio única
}

// Función para limpiar la factura completa después de procesar
function limpiarFacturaCompleta() {
    // Limpiar array de productos
    productosFactura = [];
    
    // Actualizar tabla
    actualizarTablaFactura();
    
    // Recalcular totales
    calcularTotalFactura();
    
    // Regresar a la selección de cliente
    eliminarFacturaCompleta();
}

// ... existing code ...