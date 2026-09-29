import Alpine from 'alpinejs';
import ApexCharts from 'apexcharts';
import Swal from 'sweetalert2';

window.Alpine = Alpine;
window.ApexCharts = ApexCharts;
window.Swal = Swal;

window.showToast = function showToast(icon, title) {
    return Swal.fire({
        toast: true,
        position: 'top-end',
        icon,
        title,
        showConfirmButton: false,
        timer: 2400,
        timerProgressBar: true,
    });
};

Alpine.start();
