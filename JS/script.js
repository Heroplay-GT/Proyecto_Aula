const wrappper = document.querySelector('.wrapper');
const registerLink = document.querySelector('.register-link');
const loginLink = document.querySelector('.login-link');

registerLink.onclick = () => {
    wrappper.classList.add('active');
}

loginLink.onclick = () => {
    wrappper.classList.remove('active');
}