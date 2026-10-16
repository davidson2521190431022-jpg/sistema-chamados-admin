document.addEventListener('DOMContentLoaded', function () {
    const botaoNotificacoes = document.getElementById('botaoNotificacoes');
    const listaNotificacoes = document.getElementById('listaNotificacoes');

    if (!botaoNotificacoes || !listaNotificacoes) {
        return;
    }

    botaoNotificacoes.addEventListener('click', function (evento) {
        evento.stopPropagation();

        const aberta = listaNotificacoes.classList.toggle('active');

        botaoNotificacoes.setAttribute(
            'aria-expanded',
            aberta ? 'true' : 'false'
        );
    });

    document.addEventListener('click', function (evento) {
        if (
            !listaNotificacoes.contains(evento.target) &&
            !botaoNotificacoes.contains(evento.target)
        ) {
            listaNotificacoes.classList.remove('active');
            botaoNotificacoes.setAttribute('aria-expanded', 'false');
        }
    });

    document.addEventListener('keydown', function (evento) {
        if (evento.key === 'Escape') {
            listaNotificacoes.classList.remove('active');
            botaoNotificacoes.setAttribute('aria-expanded', 'false');
        }
    });
});
