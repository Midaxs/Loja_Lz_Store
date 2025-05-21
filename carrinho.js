window.addEventListener('beforeunload', function () {
    var carrinho = JSON.stringify(window.carrinho || {});
    navigator.sendBeacon('salvar_carrinho_ajax.php', new URLSearchParams({
        carrinho: carrinho
    }));
});