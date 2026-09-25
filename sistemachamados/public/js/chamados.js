document.addEventListener('DOMContentLoaded', function () {
    const filtroStatus = document.getElementById('filtroStatus');
    const tabelaChamados = document.getElementById('tabelaChamados');

    if (filtroStatus && tabelaChamados) {
        filtroStatus.addEventListener('change', function () {
            const statusSelecionado = this.value.toLowerCase();
            const linhas = tabelaChamados.querySelectorAll('tbody tr');

            linhas.forEach(linha => {
                const statusLinha = linha.getAttribute('data-status').toLowerCase();
                
                if (statusSelecionado === '' || statusLinha === statusSelecionado) {
                    linha.style.display = '';
                } else {
                    linha.style.display = 'none';
                }
            });
        });
    }
});