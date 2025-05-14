// Funcion para activar el link cuando el cursor pase por encima del link
var lista = document.querySelectorAll('.nav li');

function activarLink() {
    lista.forEach((item) => {
        item.classList.remove('active');
    });

    this.classList.add('active');
}

lista.forEach((item) => {
    item.addEventListener('mouseover', activarLink);
});


