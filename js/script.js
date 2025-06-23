document.addEventListener('DOMContentLoaded', function() {
    console.log('script.js cargado');
    // Validación de login en el cliente
    var loginForm = document.querySelector('form[method="post"].login-form');
    if (loginForm) {
        var usuario = loginForm.querySelector('[name="usuario"]');
        var password = loginForm.querySelector('[name="password"]');
        var usuarioError = document.createElement('div');
        usuarioError.className = 'input-error';
        usuarioError.style.color = '#e53935';
        usuarioError.style.fontSize = '0.93em';
        usuarioError.style.marginTop = '3px';
        var passwordError = usuarioError.cloneNode();
        // Insertar los divs de error debajo de los inputs si no existen
        if (!usuario.nextElementSibling || !usuario.nextElementSibling.classList.contains('input-error')) {
            usuario.parentNode.appendChild(usuarioError);
        } else {
            usuarioError = usuario.nextElementSibling;
        }
        if (!password.nextElementSibling || !password.nextElementSibling.classList.contains('input-error')) {
            password.parentNode.appendChild(passwordError);
        } else {
            passwordError = password.nextElementSibling;
        }
        // Mostrar errores de credenciales incorrectas si vienen por la URL
        const params = new URLSearchParams(window.location.search);
        if (params.get('error') === 'usuario') {
            usuarioError.textContent = 'Usuario no registrado';
        } else if (params.get('error') === 'contrasena') {
            passwordError.textContent = 'Contraseña incorrecta.';
        }
        loginForm.addEventListener('submit', function(e) {
            let errores = false;
            usuarioError.textContent = '';
            passwordError.textContent = '';
            if (!usuario.value.trim()) {
                usuarioError.textContent = 'El usuario es obligatorio.';
                errores = true;
            } else if (usuario.value.trim().length < 4) {
                usuarioError.textContent = 'Debe tener al menos 4 caracteres.';
                errores = true;
            } else if (usuario.value.trim().length > 15) {
                usuarioError.textContent = 'No puede tener más de 15 caracteres.';
                errores = true;
            }
            if (!password.value.trim()) {
                passwordError.textContent = 'La contraseña es obligatoria.';
                errores = true;
            } else if (password.value.trim().length < 8) {
                passwordError.textContent = 'Debe tener al menos 8 caracteres.';
                errores = true;
            } else if (password.value.trim().length > 15) {
                passwordError.textContent = 'No puede tener más de 15 caracteres.';
                errores = true;
            }
            if (errores) {
                e.preventDefault();
            }
        });
    }
    // Limitar la cantidad de caracteres permitidos en los campos de login
    if (loginForm) {
        var usuario = loginForm.querySelector('[name="usuario"]');
        var password = loginForm.querySelector('[name="password"]');
        if (usuario) {
            usuario.addEventListener('input', function() {
                if (usuario.value.length > 15) {
                    usuario.value = usuario.value.slice(0, 15);
                }
            });
        }
        if (password) {
            password.addEventListener('input', function() {
                if (password.value.length > 15) {
                    password.value = password.value.slice(0, 15);
                }
            });
        }
    }

    // Limitar la cantidad de caracteres permitidos en los campos de nombre y descripción
    document.querySelectorAll('input[name="nombre"]').forEach(function(input) {
        input.addEventListener('input', function() {
            if (input.value.length > 15) {
                input.value = input.value.slice(0, 15);
            }
        });
    });
    document.querySelectorAll('textarea[name="descripcion"]').forEach(function(textarea) {
        textarea.addEventListener('input', function() {
            if (textarea.value.length > 15) {
                textarea.value = textarea.value.slice(0, 15);
            }
        });
    });

    // Validaciones para crear categorías y productos
    document.querySelectorAll('form').forEach(function(form) {
        // Evitar alert en el login
        if (form.classList.contains('login-form')) return;
        form.addEventListener('submit', function(e) {
            let valid = true;
            let msg = [];
            form.querySelectorAll('input[required], textarea[required]').forEach(function(input) {
                if (!input.value.trim()) {
                    valid = false;
                    msg.push('El campo "' + (input.getAttribute('name') || input.id) + '" es obligatorio.');
                } else if (input.value.trim().length > 15) {
                    valid = false;
                    msg.push('El campo "' + (input.getAttribute('name') || input.id) + '" no puede tener más de 15 caracteres.');
                }
            });
            // Validar precio
            let precio = form.querySelector('[name="precio"]');
            if (precio) {
                let val = precio.value;
                // Solo permite números, un punto y máximo dos decimales
                if (!/^\d+(\.\d{1,2})?$/.test(val)) {
                    valid = false;
                    msg.push('El precio debe tener el formato 0.00 (máximo dos decimales).');
                }
                let num = parseFloat(val);
                if (isNaN(num) || num <= 0) {
                    valid = false;
                    msg.push('El precio debe ser un número positivo.');
                }
            }
            // Mostrar errores visuales y nunca alert
            let errorDiv = form.querySelector('.input-error-global');
            if (!valid) {
                if (!errorDiv) {
                    errorDiv = document.createElement('div');
                    errorDiv.className = 'input-error-global';
                    errorDiv.style.color = '#e53935';
                    errorDiv.style.fontSize = '0.95em';
                    errorDiv.style.margin = '8px 0 10px 0';
                    form.insertBefore(errorDiv, form.firstChild.nextSibling);
                }
                errorDiv.innerHTML = msg.map(m => `<div>${m}</div>`).join('');
                e.preventDefault();
            } else if (errorDiv) {
                errorDiv.innerHTML = '';
            }
        });

        // 3. Deshabilitar botón de submit tras primer clic
        form.addEventListener('submit', function(e) {
            let btn = form.querySelector('[type="submit"]');
            if (btn) {
                btn.disabled = true;
                setTimeout(function() { btn.disabled = false; }, 3000); // Rehabilita tras 3s por si hay error
            }
        });
    });

    // 4. Previsualización de imágenes
    document.querySelectorAll('input[type="file"][accept^="image/"]').forEach(function(input) {
        input.addEventListener('change', function(e) {
            const file = input.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(ev) {
                    let preview = input.closest('form').querySelector('.img-preview');
                    if (!preview) {
                        preview = document.createElement('img');
                        preview.className = 'img-preview';
                        preview.style.maxWidth = '120px';
                        preview.style.maxHeight = '120px';
                        preview.style.display = 'block';
                        preview.style.marginTop = '10px';
                        input.parentNode.appendChild(preview);
                    }
                    preview.src = ev.target.result;
                };
                reader.readAsDataURL(file);
            }
        });
    });

    // Validación y restricción para campo precio: solo números, un punto, y máximo dos decimales
    document.querySelectorAll('input[name="precio"]').forEach(function(input) {
        input.addEventListener('input', function(e) {
            let val = input.value;
            // Solo permite números y un punto
            val = val.replace(/[^\d.]/g, '');
            // Solo un punto
            let parts = val.split('.');
            if (parts.length > 2) {
                val = parts[0] + '.' + parts.slice(1).join('').replace(/\./g, '');
            }
            // Limitar a dos decimales
            let punto = val.indexOf('.');
            if (punto !== -1) {
                val = val.substring(0, punto + 1) + val.substring(punto + 1).replace(/\./g, '');
                let decimales = val.split('.')[1];
                if (decimales && decimales.length > 2) {
                    val = val.split('.')[0] + '.' + decimales.slice(0, 2);
                }
            }
            input.value = val;
        });
    });

    // Confirmación personalizada para acciones críticas
    function mostrarConfirmacionPersonalizada(mensaje, callback) {
        // Si ya existe el modal, elimínalo
        let modalExistente = document.getElementById('modalConfirmacionPersonalizada');
        if (modalExistente) modalExistente.remove();
        // Crea el modal
        let modal = document.createElement('div');
        modal.id = 'modalConfirmacionPersonalizada';
        modal.style.position = 'fixed';
        modal.style.top = '0';
        modal.style.left = '0';
        modal.style.width = '100vw';
        modal.style.height = '100vh';
        modal.style.background = 'rgba(0,0,0,0.4)';
        modal.style.display = 'flex';
        modal.style.alignItems = 'center';
        modal.style.justifyContent = 'center';
        modal.style.zIndex = '9999';
        modal.innerHTML = `
            <div style="background:#fff;padding:2rem 2.5rem;border-radius:8px;box-shadow:0 2px 12px #0002;text-align:center;max-width:90vw;">
                <p style="margin-bottom:1.5rem;font-size:1.1rem;">${mensaje}</p>
                <button id="btnConfirmarAccion" class="btn btn-danger me-2">Sí</button>
                <button id="btnCancelarAccion" class="btn btn-secondary">No</button>
            </div>
        `;
        document.body.appendChild(modal);
        document.getElementById('btnConfirmarAccion').onclick = function() {
            modal.remove();
            callback(true);
        };
        document.getElementById('btnCancelarAccion').onclick = function() {
            modal.remove();
            callback(false);
        };
    }

    // Usar el modal personalizado en botones de eliminar o con data-confirm
    document.querySelectorAll('a.btn-danger, button.btn-danger').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            if (btn.hasAttribute('data-confirm')) {
                e.preventDefault();
                mostrarConfirmacionPersonalizada(btn.getAttribute('data-confirm'), function(confirmado) {
                    if (confirmado) {
                        // Si es un enlace, redirige
                        if (btn.tagName === 'A') {
                            window.location.href = btn.href;
                        } else {
                            // Si es un botón, intenta enviar el formulario
                            let form = btn.closest('form');
                            if (form) form.submit();
                        }
                    }
                });
            }
        });
    });
});