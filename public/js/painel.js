document.addEventListener('DOMContentLoaded', function () {
    const botaoMenu = document.getElementById('botaoMenuMobile');
    const sidebar = document.querySelector('.sidebar');

    if (botaoMenu && sidebar) {
        botaoMenu.addEventListener('click', function () {
            sidebar.classList.toggle('active');
        });

        document.addEventListener('click', function (e) {
            if (!sidebar.contains(e.target) && !botaoMenu.contains(e.target)) {
                sidebar.classList.remove('active');
            }
        });
    }
});