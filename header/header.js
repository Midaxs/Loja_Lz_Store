document.addEventListener('DOMContentLoaded', function() {
    // Categorias
    const btnCategoria = document.getElementById('btn-categoria');
    const menuCategorias = document.getElementById('menu-categorias');
    if (btnCategoria && menuCategorias) {
        btnCategoria.addEventListener('click', function(e) {
            e.stopPropagation();
            menuCategorias.style.display = (menuCategorias.style.display === 'block') ? 'none' : 'block';
        });
    }

    // Dropdown logout
    const usuarioDropdown = document.querySelector('.usuario-dropdown');
    if (usuarioDropdown) {
        const usuarioNome = usuarioDropdown.querySelector('.usuario-nome');
        const dropdown = usuarioDropdown.querySelector('.dropdown-logout');
        usuarioNome.addEventListener('click', function(e) {
            e.stopPropagation();
            dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
        });
        dropdown.addEventListener('click', function(e) {
            e.stopPropagation();
        });
    }

    // Fecha ambos ao clicar fora
    document.addEventListener('click', function(event) {
        // Fecha menu categorias se aberto e clique fora dele
        if (menuCategorias && menuCategorias.style.display === 'block' &&
            !menuCategorias.contains(event.target) && !btnCategoria.contains(event.target)) {
            menuCategorias.style.display = 'none';
        }
        // Fecha dropdown logout se aberto e clique fora dele
        if (usuarioDropdown) {
            const usuarioNome = usuarioDropdown.querySelector('.usuario-nome');
            const dropdown = usuarioDropdown.querySelector('.dropdown-logout');
            if (dropdown.style.display === 'block' &&
                !dropdown.contains(event.target) && !usuarioNome.contains(event.target)) {
                dropdown.style.display = 'none';
            }
        }
    });

    // Histórico de pesquisa
    const campoBusca = document.getElementById('campo-busca');
    const sugestoes = document.getElementById('search-suggestions');

    function salvarHistoricoBusca(termo) {
        if (!termo.trim()) return;
        let historico = JSON.parse(localStorage.getItem('historicoBusca') || '[]');
        historico = historico.filter(item => item.toLowerCase() !== termo.toLowerCase());
        historico.unshift(termo);
        if (historico.length > 8) historico = historico.slice(0, 8);
        localStorage.setItem('historicoBusca', JSON.stringify(historico));
    }

    function mostrarSugestoes() {
        let historico = JSON.parse(localStorage.getItem('historicoBusca') || '[]');
        const filtro = campoBusca.value.trim().toLowerCase();
        let filtrados = filtro
            ? historico.filter(item => item.toLowerCase().includes(filtro))
            : historico;

        if (filtrados.length === 0 || !filtro) {
            sugestoes.style.display = 'none';
            return;
        }
        sugestoes.innerHTML = '';
        filtrados.forEach(item => {
            const div = document.createElement('div');
            div.style.display = 'flex';
            div.style.alignItems = 'center';
            div.innerHTML = `
                <span style="display:inline-block;width:20px;height:20px;margin-right:8px;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#888" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                </span>
                <span>${highlightTerm(item, filtro)}</span>
            `;
            div.onclick = () => {
                campoBusca.value = item;
                sugestoes.style.display = 'none';
            };
            sugestoes.appendChild(div);
        });
        sugestoes.style.display = 'block';
    }

    // Função para destacar o termo digitado na sugestão
    function highlightTerm(text, term) {
        if (!term) return text;
        const re = new RegExp('(' + term.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + ')', 'gi');
        return text.replace(re, '<b>$1</b>');
    }

    if (campoBusca && sugestoes) {
        campoBusca.addEventListener('input', mostrarSugestoes);
        campoBusca.addEventListener('focus', mostrarSugestoes);
        campoBusca.form.addEventListener('submit', function() {
            salvarHistoricoBusca(campoBusca.value);
            sugestoes.style.display = 'none';
        });
        document.addEventListener('click', function(e) {
            if (!sugestoes.contains(e.target) && e.target !== campoBusca) {
                sugestoes.style.display = 'none';
            }
        });
    }
});