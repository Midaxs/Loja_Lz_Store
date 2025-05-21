document.addEventListener('DOMContentLoaded', function() {
    const btnCategoria = document.getElementById('btn-categoria');
    const menuCategorias = document.getElementById('menu-categorias');

    btnCategoria.addEventListener('click', function(e) {
        e.stopPropagation();
        if (menuCategorias.style.display === 'none' || menuCategorias.style.display === '') {
            menuCategorias.style.display = 'block';
        } else {
            menuCategorias.style.display = 'none';
        }
    });

    // Fecha o menu ao clicar fora dele
    document.addEventListener('click', function(event) {
        if (!btnCategoria.contains(event.target) && !menuCategorias.contains(event.target)) {
            menuCategorias.style.display = 'none';
        }
    });
});