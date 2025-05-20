const wrapper = document.querySelector('.wrapper');
const loginLink = document.querySelector('.login-link');
const registerLink = document.querySelector('.register-link');
const btnPopup = document.querySelector('.btnLogin-popup');
const iconClose = document.querySelector('.icon-close');

registerLink.addEventListener('click', () => {
    wrapper.classList.add('active');
});

loginLink.addEventListener('click', () => {
    wrapper.classList.remove('active');
});

btnPopup.addEventListener('click', () => {
    wrapper.classList.add('active-popup');
});

iconClose.addEventListener('click', () => {
    wrapper.classList.remove('active-popup');
});

document.addEventListener('DOMContentLoaded', () => {
    const toggleButtons = document.querySelectorAll('.toggle-password');

    toggleButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            const targetId = btn.getAttribute('data-target');
            const passwordInput = document.getElementById(targetId);

            if (passwordInput) {
                const isPassword = passwordInput.type === 'password';
                passwordInput.type = isPassword ? 'text' : 'password';
                const icon = btn.querySelector('ion-icon');
                icon.setAttribute('name', isPassword ? 'eye-outline' : 'eye-off-outline');
            }
        });
    });
});

const wraper = document.querySelector('.wrapper');
const contactLink = document.querySelector('.contact-link');
const contactForm = document.querySelector('.form-box.contacto');
const loginForm = document.querySelector('.form-box.login');
const registerForm = document.querySelector('.form-box.register');
const closeIcon = document.querySelector('.icon-close');
const closeContactIcon = document.querySelector('.icon-close-contacto');

contactLink.addEventListener('click', () => {
    wrapper.classList.add('active-popup');
    contactForm.style.display = 'block';
    loginForm.style.display = 'none';
    registerForm.style.display = 'none';
});

closeIcon.addEventListener('click', () => {
    wrapper.classList.remove('active-popup');
    contactForm.style.display = 'none';
    loginForm.style.display = 'block'; // vuelve al login por defecto
});

btnPopup.addEventListener('click', () => {
    wrapper.classList.add('active-popup');
    loginForm.style.display = 'block';
    registerForm.style.display = 'none';
    contactForm.style.display = 'none';
});

// Mostrar formulario de registro
registerLink.addEventListener('click', () => {
    loginForm.style.display = 'none';
    registerForm.style.display = 'block';
    contactForm.style.display = 'none';
});

// Volver al formulario de login desde el registro
loginLink.addEventListener('click', () => {
    registerForm.style.display = 'none';
    loginForm.style.display = 'block';
    contactForm.style.display = 'none';
});

// Mostrar contacto (WhatsApp)
contactLink.addEventListener('click', () => {
    wrapper.classList.add('active-popup');
    contactForm.style.display = 'block';
    loginForm.style.display = 'none';
    registerForm.style.display = 'none';
});

// Cerrar login o registro
closeIcon.addEventListener('click', () => {
    wrapper.classList.remove('active-popup');
    loginForm.style.display = 'none';
    registerForm.style.display = 'none';
});

// Cerrar formulario de contacto sin abrir login
closeContactIcon.addEventListener('click', () => {
    wrapper.classList.remove('active-popup');
    contactForm.style.display = 'none';
});
