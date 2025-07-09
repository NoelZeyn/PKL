<template>
  <div class="flex min-h-screen bg-gray-100">
    <Sidebar :activeMenu="activeMenu" @update:activeMenu="activeMenu = $event" />

    <div class="flex-1 p-8">
      <div class="max-w-6xl mx-auto bg-white rounded-2xl shadow-xl p-8">
        <div class="flex justify-between items-center mb-4">
          <h1 class="text-3xl font-bold text-[#08607a]">Perbandingan Anggaran ATK</h1>
          <button @click="downloadChart"
            class="px-3 py-2 bg-[#08607a] hover:bg-[#065666] text-white rounded-md text-sm cursor-pointer">
            Download Gambar Grafik
          </button>

          <button @click="downloadExcel"
            class="flex items-center gap-2 px-4 py-2 bg-[#08607a] hover:bg-[#065666] text-white text-sm rounded-lg shadow transition duration-200 cursor-pointer">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
              stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round"
                d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5m0 0l5-5m-5 5V4" />
            </svg>
            Download Excel
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
import ExcelJS from 'exceljs';
import { saveAs } from 'file-saver';

export default {
  name: 'GrafikAnggaran',
  components: { Sidebar },

  setup() {
    const activeMenu = ref('grafik');
    const chartInstance = ref(null);
    const chartCanvas = ref(null);

    const downloadExcel = async () => {
      try {
        const token = localStorage.getItem('token');
        const res = await axios.get('http://localhost:8000/api/alat', {
          headers: { Authorization: `Bearer ${token}` }
        });

        const dataAlat = res.data.data || [];
        const workbook = new ExcelJS.Workbook();
        const worksheet = workbook.addWorksheet('Perbandingan Anggaran ATK');

        // Header Kolom
        worksheet.columns = [
          { header: 'No', key: 'no', width: 5 },
          { header: 'Nama Barang', key: 'nama_barang', width: 25 },
          { header: 'Harga Satuan', key: 'harga_satuan', width: 18 },
          { header: 'Stock', key: 'stock', width: 10 },
          { header: 'Harga Total', key: 'harga_total', width: 18 },
          { header: 'Estimasi Satuan', key: 'estimasi_satuan', width: 20 },
          { header: 'Estimasi Total', key: 'estimasi_total', width: 20 },
        ];

        // Style Header
        worksheet.getRow(1).eachCell(cell => {
          cell.font = { bold: true, color: { argb: 'FFFFFFFF' } };
          cell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FF4F46E5' } };
          cell.alignment = { vertical: 'middle', horizontal: 'center' };
          cell.border = { top: { style: 'thin' }, left: { style: 'thin' }, bottom: { style: 'thin' }, right: { style: 'thin' } };
        });

        // Group data berdasarkan nama_kategori
        const groupedByCategory = {};
        dataAlat.forEach(item => {
          const kategori = item.kategori?.nama_kategori || 'Tanpa Kategori';
          if (!groupedByCategory[kategori]) groupedByCategory[kategori] = [];
          groupedByCategory[kategori].push(item);
        });

        let rowIndex = 2;
        let globalNo = 1;
        let grandTotalHarga = 0;
        let grandTotalEstimasi = 0;

        for (const [kategoriName, items] of Object.entries(groupedByCategory)) {
          // Baris Kategori
          worksheet.mergeCells(`A${rowIndex}:G${rowIndex}`);
          const kategoriCell = worksheet.getCell(`A${rowIndex}`);
          kategoriCell.value = `Kategori: ${kategoriName}`;
          kategoriCell.font = { bold: true, color: { argb: 'FFFFFFFF' } };
          kategoriCell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FF10B981' } };
          kategoriCell.alignment = { vertical: 'middle', horizontal: 'center' };
          rowIndex++;

          let totalKategoriHarga = 0;
          let totalKategoriEstimasi = 0;

          items.forEach(item => {
            const hargaSatuan = item.harga_satuan || 0;
            const stock = item.stock || 0;
            const hargaTotal = hargaSatuan * stock;
            const estimasiSatuan = item.harga_estimasi || 0;
            const estimasiTotal = estimasiSatuan * stock;

            totalKategoriHarga += hargaTotal;
            totalKategoriEstimasi += estimasiTotal;

            worksheet.addRow({
              no: globalNo++,
              nama_barang: item.nama_barang,
              harga_satuan: hargaSatuan,
              stock: stock,
              harga_total: hargaTotal,
              estimasi_satuan: estimasiSatuan,
              estimasi_total: estimasiTotal,
            });

            rowIndex++;
          });

          // Total per Kategori (Opsional, bisa dihapus kalau tidak mau)
          const totalRow = worksheet.addRow({
            no: '',
            nama_barang: 'Subtotal',
            harga_satuan: '',
            stock: '',
            harga_total: totalKategoriHarga,
            estimasi_satuan: '',
            estimasi_total: totalKategoriEstimasi,
          });

          totalRow.eachCell(cell => {
            cell.font = { bold: true };
            cell.alignment = { vertical: 'middle', horizontal: 'center' };
          });

          grandTotalHarga += totalKategoriHarga;
          grandTotalEstimasi += totalKategoriEstimasi;

          rowIndex++;
        }

        // Baris TOTAL Akhir
        const totalAkhirRow = worksheet.addRow({
          no: '',
          nama_barang: 'TOTAL',
          harga_satuan: '',
          stock: '',
          harga_total: grandTotalHarga,
          estimasi_satuan: '',
          estimasi_total: grandTotalEstimasi,
        });

        totalAkhirRow.eachCell(cell => {
          cell.font = { bold: true, color: { argb: 'FFFFFFFF' } };
          cell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FFEF4444' } };
          cell.alignment = { vertical: 'middle', horizontal: 'center' };
        });

        // Style Seluruh Baris
        worksheet.eachRow((row, rowNumber) => {
          if (rowNumber !== 1) {
            row.eachCell((cell, colNumber) => {
              cell.border = { top: { style: 'thin' }, left: { style: 'thin' }, bottom: { style: 'thin' }, right: { style: 'thin' } };
              cell.alignment = { vertical: 'middle', horizontal: 'center' };

              if ([3, 5, 6, 7].includes(colNumber)) {
                cell.numFmt = '"Rp"#,##0';
              }

              if (colNumber === 1) {
                cell.numFmt = '0';
              }
            });
          }
        });

        const buffer = await workbook.xlsx.writeBuffer();
        saveAs(new Blob([buffer]), `Laporan-Anggaran-ATK-${new Date().toISOString().slice(0, 10)}.xlsx`);
      } catch (error) {
        console.error('Gagal membuat laporan Excel:', error);
      }
    };



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
        const estimasiSatuan = dataAlat.map(item => item.harga_estimasi);
        const estimasiTotal = dataAlat.map(item => item.harga_estimasi * item.stock);

        const ctx = chartCanvas.value.getContext('2d');

        chartInstance.value = new Chart(ctx, {
          type: 'bar',
          data: {
            labels: labels,
            datasets: [
              { label: 'Harga Satuan', data: hargaSatuan, backgroundColor: '#4f46e5' },
              { label: 'Harga Total', data: hargaTotal, backgroundColor: '#10b981' },
              { label: 'Estimasi Satuan', data: estimasiSatuan, backgroundColor: '#f59e0b' },
              { label: 'Estimasi Total', data: estimasiTotal, backgroundColor: '#ef4444' },
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

    return { activeMenu, chartCanvas, downloadChart, downloadExcel };
  }
};
</script>


<style scoped>
canvas {
  max-height: 400px;
}
</style>
