import axios from 'axios';
import Chart from 'chart.js/auto';

/**
 * O Bootstrap (v4) e o jQuery já são servidos pelo AdminLTE em
 * layouts/app.blade.php. Importar o Bootstrap 5 aqui carregava duas versões
 * incompatíveis na mesma página.
 *
 * O Chart.js é empacotado (não vem mais de CDN) para funcionar em redes
 * corporativas restritas.
 */

window.axios = axios;
window.Chart = Chart;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
