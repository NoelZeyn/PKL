<template>
  <div class="flex min-h-screen bg-gray-100">
    <Sidebar :activeMenu="activeMenu" @update:activeMenu="activeMenu = $event" />

    <div class="flex-1 p-8">
      <div class="max-w-6xl mx-auto bg-white rounded-2xl shadow-xl p-8">
        <div class="flex justify-between items-center mb-4">
          <h1 class="text-3xl font-bold text-[#08607a]">Perbandingan Anggaran ATK</h1>
          <button @click="downloadChart"
            class="px-3 py-2 bg-[#08607a] hover:bg-[#065666] text-white rounded-md text-sm cursor-pointer">
            Download Grafik
          </button>
        </div>

        <p class="text-gray-600 mb-6 text-sm">
          Grafik perbandingan harga satuan, total, dan estimasi kebutuhan ATK.
        </p>

        <div class="bg-gray-50 rounded-xl p-4 shadow-inner">
          <canvas id="anggaranChart" ref="chartCanvas" class="w-full h-96"></canvas>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { onMounted, ref } from 'vue';
import Sidebar from '@/components/Sidebar.vue';
import Chart from 'chart.js/auto';
import axios from 'axios';

export default {
  name: 'GrafikAnggaran',
  components: { Sidebar },

  setup() {
    const activeMenu = ref('grafik');
    const chartInstance = ref(null);
    const chartCanvas = ref(null);

    const fetchDataAndRenderChart = async () => {
      try {
        const token = localStorage.getItem('token');
        const res = await axios.get('http://localhost:8000/api/alat', {
          headers: { Authorization: `Bearer ${token}` }
        });

        const dataAlat = res.data.data || [];
        const labels = dataAlat.map(item => item.nama_barang);
        const hargaSatuan = dataAlat.map(item => item.harga_satuan);
        const hargaTotal = dataAlat.map(item => item.harga_satuan * item.stock);
        const hargaEstimasiSatuan = dataAlat.map(item => item.harga_estimasi);
        const hargaEstimasiTotal = dataAlat.map(item => item.harga_estimasi * item.stock);

        const ctx = chartCanvas.value.getContext('2d');

        chartInstance.value = new Chart(ctx, {
          type: 'bar',
          data: {
            labels: labels,
            datasets: [
              { label: 'Harga Satuan', data: hargaSatuan, backgroundColor: '#4f46e5' },
              { label: 'Harga Total', data: hargaTotal, backgroundColor: '#10b981' },
              { label: 'Estimasi Satuan', data: hargaEstimasiSatuan, backgroundColor: '#f59e0b' },
              { label: 'Estimasi Total', data: hargaEstimasiTotal, backgroundColor: '#ef4444' },
            ]
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
              legend: { position: 'bottom', labels: { color: '#374151', font: { size: 12 } } },
              tooltip: {
                callbacks: {
                  label: ctx => `${ctx.dataset.label}: Rp ${ctx.raw.toLocaleString('id-ID')}`
                }
              }
            },
            scales: {
              y: {
                beginAtZero: true,
                ticks: {
                  callback: value => 'Rp ' + value.toLocaleString('id-ID'),
                  color: '#6b7280'
                },
                grid: { color: '#e5e7eb' }
              },
              x: {
                ticks: { color: '#6b7280' },
                grid: { display: false }
              }
            }
          }
        });

      } catch (error) {
        console.error('Gagal mengambil data alat:', error);
      }
    };

    const downloadChart = () => {
      if (chartInstance.value) {
        const link = document.createElement('a');
        link.href = chartInstance.value.toBase64Image();
        link.download = `Grafik-ATK-${new Date().toISOString().slice(0, 10)}.png`;
        link.click();
      }
    };

    onMounted(() => {
      fetchDataAndRenderChart();
    });

    return { activeMenu, chartCanvas, downloadChart };
  }
};
</script>

<style scoped>
canvas {
  max-height: 400px;
}
</style>
