document.addEventListener('DOMContentLoaded', function () {
    const alertas = document.querySelectorAll('.alert-auto-close');
    
    if (alertas.length > 0) {
        setTimeout(function () {
            alertas.forEach(alerta => {
                alerta.style.transition = 'opacity 0.5s ease';
                alerta.style.opacity = '0';
                setTimeout(() => alerta.remove(), 500);
            });
        }, 4000);
    }
});