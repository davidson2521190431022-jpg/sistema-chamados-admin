document.addEventListener('DOMContentLoaded', function () {
    const botaoNotificacoes = document.getElementById('botaoNotificacoes');
    const listaNotificacoes = document.getElementById('listaNotificacoes');

    if (botaoNotificacoes && listaNotificacoes) {
        botaoNotificacoes.addEventListener('click', function (e) {
            e.stopPropagation();
            listaNotificacoes.classList.toggle('active');
        });

        document.addEventListener('click', function (e) {
            if (!listaNotificacoes.contains(e.target) && !botaoNotificacoes.contains(e.target)) {
                listaNotificacoes.classList.remove('active');
            }
        });
    }
});