<template>
  <div class="flex flex-wrap h-screen bg-gray-100">
    <!-- Sidebar -->
    <Sidebar :activeMenu="activeMenu" @update:activeMenu="updateActiveMenu" />

    <!-- Main Content -->
    <div class="flex-1 p-8 pt-4 bg-white">
      <HeaderBar title="Dashboard" class="mt-3" />
      <div class="my-4 border-b border-gray-300"></div>

      <!-- Header Section -->
      <div class="flex flex-wrap justify-between items-center mt-[-10px] pt-5 gap-2">
        <div>
          <h3 class="text-lg font-semibold text-gray-800">
            Selamat Datang, {{ user.NID }} {{ user.tingkatan_otoritas }} 👋
          </h3>
        </div>
        <div
          class="inline-flex items-center gap-2 px-3 py-1 rounded-lg border text-sm font-bold text-gray-800 bg-white border-gray-300">
          <img class="w-5 h-5" :src="iconKalender" alt="icon" />
          <span>{{ formattedDate }}</span>
        </div>
      </div>

      <!-- Menu Grid -->
      <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 mt-6 mb-10">
        <div v-for="menu in quickMenus" :key="menu.title"
          class="flex items-center gap-3 p-5 rounded-xl bg-white border border-gray-200 shadow-lg hover:bg-blue-50 active:bg-blue-700 active:text-white transition-all cursor-pointer"
          @click="navigateTo(menu.path)">
          <div class="flex items-center justify-center">
            <img :src="menu.icon" class="w-6 h-6 object-cover" :alt="menu.title" />
          </div>
          <p class="font-semibold text-blue-900">{{ menu.title }}</p>
        </div>
      </div>

      <!-- Grafik Anggaran ATK -->
      <div class="max-w-6xl mx-auto bg-white rounded-2xl shadow-xl p-8">
        <div class="flex justify-between items-center mb-4">
          <h1 class="text-3xl font-bold text-[#08607a]">Perbandingan Anggaran ATK</h1>
          <div class="flex gap-2">
            <button @click="downloadChart"
              class="px-3 py-2 bg-[#08607a] hover:bg-[#065666] text-white rounded-md text-sm cursor-pointer">
              Download Gambar Grafik
            </button>
            <button @click="downloadExcel"
              class="px-3 py-2 bg-[#08607a] hover:bg-[#065666] text-white rounded-md text-sm cursor-pointer">
              Download Excel
            </button>
          </div>
        </div>

        <p class="text-gray-600 mb-6 text-sm">
          Grafik perbandingan harga satuan, total, dan estimasi kebutuhan ATK.
        </p>

        <div class="bg-gray-50 rounded-xl p-4 shadow-inner">
          <canvas id="anggaranChart" ref="chartCanvas" class="w-full h-96"></canvas>
        </div>
      </div>
      <!-- Grafik Total Pengajuan per Bidang -->
      <div class="max-w-6xl mx-auto bg-white rounded-2xl shadow-xl p-8 mt-12">
        <div class="flex justify-between items-center mb-4">
          <h1 class="text-3xl font-bold text-[#08607a]">Total Pengajuan per Bidang</h1>
          <button @click="downloadBidangChart"
            class="px-3 py-2 bg-[#08607a] hover:bg-[#065666] text-white rounded-md text-sm cursor-pointer">
            Download Gambar Grafik
          </button>
          <button @click="downloadRekapBidangExcel"
            class="flex items-center gap-2 px-4 cursor-pointer py-2 bg-[#08607a] hover:bg-[#065666] text-white text-sm rounded-lg shadow transition duration-200">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
              stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round"
                d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5m0 0l5-5m-5 5V4" />
            </svg>
            Download Excel
          </button>
        </div>

        <p class="text-gray-600 mb-6 text-sm">
          Grafik ini menunjukkan total pengajuan harga per bidang berdasarkan seluruh data yang telah disetujui.
        </p>

        <div class="bg-gray-50 rounded-xl p-4 shadow-inner">
          <canvas ref="bidangChartCanvas" class="w-full h-96"></canvas>
        </div>
      </div>


    </div>
  </div>
</template>

<script>
import Sidebar from "@/components/Sidebar.vue";
import HeaderBar from "@/components/HeaderBar.vue";
import iconKalender from "@/assets/kalender.svg";
import iconLaporan from "@/assets/laporan.svg";
import iconStetoskop from "@/assets/stetoskop.svg";
import iconPasien from "@/assets/pasien.svg";
import iconPosyandu from "@/assets/posko.svg";

import Chart from 'chart.js/auto';
import axios from 'axios';
import ExcelJS from 'exceljs';
import { saveAs } from 'file-saver';

export default {
  name: "DashboardGabungan",
  components: {
    Sidebar,
    HeaderBar,
  },
  data() {
    return {
      activeMenu: "dashboard",
      user: {},
      chartInstance: null,
      chartCanvas: null,
      bidangChartInstance: null,
      bidangChartCanvas: null,
      iconKalender: iconKalender,
      quickMenus: [
        { icon: iconPosyandu, title: "Grafik", path: "/grafik" },
        { icon: iconStetoskop, title: "Laporan Pengajuan", path: "/laporan-pengajuan" },
        { icon: iconPasien, title: "Manage Alat", path: "/manajemen-alat" },
        { icon: iconLaporan, title: "Pengajuan", path: "/pengajuan" },
      ],
    };
  },
  computed: {
    formattedDate() {
      const today = new Date();
      return `${today.toLocaleDateString("id-ID", { month: "long", day: "numeric" }).split(' ').reverse().join(' ')}`;
    },
  },
  mounted() {
    const storedUser = localStorage.getItem("user");
    if (storedUser) {
      this.user = JSON.parse(storedUser);
    }
    this.fetchDataAndRenderChart();
    this.fetchPengajuanAdminChart();
  },
  methods: {
    updateActiveMenu(menu) {
      this.activeMenu = menu;
    },
    navigateTo(path) {
      if (path) window.location.href = path;
    },
    async downloadRekapBidangExcel() {
      try {
        const token = localStorage.getItem("token");
        const res = await axios.get("http://localhost:8000/api/admin", {
          headers: { Authorization: `Bearer ${token}` },
        });

        const data = res.data.data || [];
        const workbook = new ExcelJS.Workbook();
        const worksheet = workbook.addWorksheet("Rekap per Bidang");

        worksheet.columns = [
          { header: "Bidang", key: "bidang", width: 15 },
          { header: "Nama Barang", key: "nama_barang", width: 30 },
          { header: "Jumlah", key: "jumlah", width: 10 },
          { header: "Harga Satuan", key: "harga_satuan", width: 18 },
          { header: "Total Harga", key: "total_harga", width: 18 },
          { header: "Keterangan", key: "keterangan", width: 40 },
          { header: "Status", key: "status", width: 15 },
        ];

        worksheet.getRow(1).eachCell(cell => {
          cell.font = { bold: true, color: { argb: "FFFFFFFF" } };
          cell.fill = { type: "pattern", pattern: "solid", fgColor: { argb: "FF4F46E5" } };
          cell.alignment = { vertical: "middle", horizontal: "center" };
          cell.border = {
            top: { style: "thin" },
            left: { style: "thin" },
            bottom: { style: "thin" },
            right: { style: "thin" },
          };
        });

        let rowIndex = 2;
        const formatIDR = (value) =>
          new Intl.NumberFormat("id-ID", {
            style: "currency",
            currency: "IDR",
            minimumFractionDigits: 0,
          }).format(value);

        for (const group of data) {
          const bidang = group.nama_bidang || "-";
          const barangList = group.barang || [];
          let subtotal = 0;
          let subtotalJumlah = 0;

          worksheet.mergeCells(`A${rowIndex}:G${rowIndex}`);
          const cell = worksheet.getCell(`A${rowIndex}`);
          cell.value = `Bidang: ${bidang}`;
          cell.font = { bold: true, color: { argb: "FFFFFFFF" } };
          cell.fill = { type: "pattern", pattern: "solid", fgColor: { argb: "FF10B981" } };
          cell.alignment = { vertical: "middle", horizontal: "left" };
          rowIndex++;

          for (const item of barangList) {
            worksheet.addRow({
              bidang,
              nama_barang: item.nama_barang || "-",
              jumlah: item.jumlah || 0,
              harga_satuan: formatIDR(item.harga_satuan || 0),
              total_harga: formatIDR(item.total_harga || 0),
              keterangan: item.keterangan || "-",
              status: item.status || "-",
            });

            subtotal += item.total_harga || 0;
            subtotalJumlah += item.jumlah || 0;
            rowIndex++;
          }

          const subtotalRow = worksheet.addRow({
            nama_barang: "Subtotal",
            jumlah: subtotalJumlah,
            total_harga: formatIDR(subtotal),
          });

          subtotalRow.eachCell(cell => {
            cell.font = { bold: true };
            cell.fill = { type: "pattern", pattern: "solid", fgColor: { argb: "FFE5E7EB" } };
            cell.alignment = { vertical: "middle", horizontal: "center" };
            cell.border = {
              top: { style: "thin" },
              left: { style: "thin" },
              bottom: { style: "thin" },
              right: { style: "thin" },
            };
          });

          rowIndex++;
        }

        const buffer = await workbook.xlsx.writeBuffer();
        const filename = `Rekap-Pengajuan-PerBidang-${new Date().toISOString().slice(0, 10)}.xlsx`;
        saveAs(new Blob([buffer]), filename);
      } catch (error) {
        console.error("Gagal export Excel rekap bidang:", error);
      }
    },
    async downloadExcel() {
      try {
        const token = localStorage.getItem("token");
        const res = await axios.get("http://localhost:8000/api/alat", {
          headers: { Authorization: `Bearer ${token}` }
        });

        const dataAlat = res.data.data || [];
        const workbook = new ExcelJS.Workbook();
        const worksheet = workbook.addWorksheet('Perbandingan Anggaran ATK');

        worksheet.columns = [
          { header: 'No', key: 'no', width: 5 },
          { header: 'Nama Barang', key: 'nama_barang', width: 25 },
          { header: 'Harga Satuan', key: 'harga_satuan', width: 18 },
          { header: 'Stock', key: 'stock', width: 10 },
          { header: 'Harga Total', key: 'harga_total', width: 18 },
          { header: 'Estimasi Satuan', key: 'estimasi_satuan', width: 20 },
          { header: 'Estimasi Total', key: 'estimasi_total', width: 20 },
        ];

        worksheet.getRow(1).eachCell(cell => {
          cell.font = { bold: true, color: { argb: 'FFFFFFFF' } };
          cell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FF4F46E5' } };
          cell.alignment = { vertical: 'middle', horizontal: 'center' };
          cell.border = { top: { style: 'thin' }, left: { style: 'thin' }, bottom: { style: 'thin' }, right: { style: 'thin' } };
        });

        const groupedByCategory = {};
        dataAlat.forEach(item => {
          const kategori = item.kategori?.nama_kategori || 'Tanpa Kategori';
          if (!groupedByCategory[kategori]) groupedByCategory[kategori] = [];
          groupedByCategory[kategori].push(item);
        });

        let rowIndex = 2, globalNo = 1, grandTotalHarga = 0, grandTotalEstimasi = 0;

        for (const [kategoriName, items] of Object.entries(groupedByCategory)) {
          worksheet.mergeCells(`A${rowIndex}:G${rowIndex}`);
          const kategoriCell = worksheet.getCell(`A${rowIndex}`);
          kategoriCell.value = `Kategori: ${kategoriName}`;
          kategoriCell.font = { bold: true, color: { argb: 'FFFFFFFF' } };
          kategoriCell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FF10B981' } };
          kategoriCell.alignment = { vertical: 'middle', horizontal: 'center' };
          rowIndex++;

          let totalKategoriHarga = 0, totalKategoriEstimasi = 0;

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

          worksheet.addRow({
            no: '',
            nama_barang: 'Subtotal',
            harga_total: totalKategoriHarga,
            estimasi_total: totalKategoriEstimasi,
          }).eachCell(cell => cell.font = { bold: true });

          grandTotalHarga += totalKategoriHarga;
          grandTotalEstimasi += totalKategoriEstimasi;
          rowIndex++;
        }

        worksheet.addRow({
          no: '',
          nama_barang: 'TOTAL',
          harga_total: grandTotalHarga,
          estimasi_total: grandTotalEstimasi,
        }).eachCell(cell => {
          cell.font = { bold: true, color: { argb: 'FFFFFFFF' } };
          cell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FFEF4444' } };
        });

        const buffer = await workbook.xlsx.writeBuffer();
        saveAs(new Blob([buffer]), `Laporan-Anggaran-ATK-${new Date().toISOString().slice(0, 10)}.xlsx`);
      } catch (error) {
        console.error('Gagal membuat laporan Excel:', error);
      }
    },
    downloadChart() {
      if (this.chartInstance) {
        const link = document.createElement('a');
        link.href = this.chartInstance.toBase64Image();
        link.download = `Grafik-ATK-${new Date().toISOString().slice(0, 10)}.png`;
        link.click();
      }
    },
    downloadBidangChart() {
      if (this.bidangChartInstance) {
        const link = document.createElement("a");
        link.href = this.bidangChartInstance.toBase64Image();
        link.download = `Total-Pengajuan-Per-Bidang-${new Date().toISOString().slice(0, 10)}.png`;
        link.click();
      }
    },
    async fetchDataAndRenderChart() {
      try {
        const token = localStorage.getItem("token");
        const res = await axios.get("http://localhost:8000/api/alat", {
          headers: { Authorization: `Bearer ${token}` }
        });

        const dataAlat = res.data.data || [];
        const labels = dataAlat.map(item => item.nama_barang);
        const hargaSatuan = dataAlat.map(item => item.harga_satuan);
        const hargaTotal = dataAlat.map(item => item.harga_satuan * item.stock);
        const estimasiSatuan = dataAlat.map(item => item.harga_estimasi);
        const estimasiTotal = dataAlat.map(item => item.harga_estimasi * item.stock);

        const ctx = this.$refs.chartCanvas.getContext("2d");
        this.chartInstance = new Chart(ctx, {
          type: "bar",
          data: {
            labels,
            datasets: [
              { label: "Harga Satuan", data: hargaSatuan, backgroundColor: "#4f46e5" },
              { label: "Harga Total", data: hargaTotal, backgroundColor: "#10b981" },
              { label: "Estimasi Satuan", data: estimasiSatuan, backgroundColor: "#f59e0b" },
              { label: "Estimasi Total", data: estimasiTotal, backgroundColor: "#ef4444" },
            ],
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
              legend: { position: "bottom" },
              tooltip: {
                callbacks: {
                  label: (ctx) => `${ctx.dataset.label}: Rp ${ctx.raw.toLocaleString("id-ID")}`,
                },
              },
            },
            scales: {
              y: {
                beginAtZero: true,
                ticks: {
                  callback: (value) => "Rp " + value.toLocaleString("id-ID"),
                },
              },
            },
          },
        });
      } catch (error) {
        console.error("Gagal mengambil data alat:", error);
      }
    },
    async fetchPengajuanAdminChart() {
      try {
        const token = localStorage.getItem("token");
        const res = await axios.get("http://localhost:8000/api/admin", {
          headers: { Authorization: `Bearer ${token}` }
        });

        const dataGroupedByBidang = res.data.data || [];
        const labels = [];
        const totalHargaPerBidang = [];
        const totalJumlahOrderPerBidang = [];

        dataGroupedByBidang.forEach(group => {
          labels.push(group.nama_bidang);
          let totalHarga = 0, totalJumlah = 0;

          group.barang.forEach(item => {
            totalHarga += item.total_harga || 0;
            totalJumlah += item.jumlah || 0;
          });

          totalHargaPerBidang.push(totalHarga);
          totalJumlahOrderPerBidang.push(totalJumlah);
        });

        const ctx = this.$refs.bidangChartCanvas.getContext("2d");

        this.bidangChartInstance = new Chart(ctx, {
          type: "bar",
          data: {
            labels,
            datasets: [
              {
                label: "Total Harga (Rp)",
                data: totalHargaPerBidang,
                backgroundColor: "#0ea5e9",
                borderRadius: 6,
                barThickness: 40,
                yAxisID: 'y',
              },
              {
                label: "Total Jumlah Barang",
                data: totalJumlahOrderPerBidang,
                backgroundColor: "#f59e0b",
                borderRadius: 6,
                barThickness: 40,
                yAxisID: 'y1',
              }
            ]
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
              mode: 'index',
              intersect: false,
            },
            plugins: {
              legend: { position: 'top' },
              tooltip: {
                callbacks: {
                  label: (ctx) => {
                    return ctx.dataset.label.includes("Harga")
                      ? `${ctx.dataset.label}: Rp ${ctx.raw.toLocaleString('id-ID')}`
                      : `${ctx.dataset.label}: ${ctx.raw}`;
                  }
                }
              }
            },
            scales: {
              y: {
                type: 'linear',
                position: 'left',
                beginAtZero: true,
                title: { display: true, text: 'Total Harga (Rp)' },
                ticks: {
                  callback: value => 'Rp ' + value.toLocaleString('id-ID')
                }
              },
              y1: {
                type: 'linear',
                position: 'right',
                beginAtZero: true,
                title: { display: true, text: 'Jumlah Order' },
                grid: {
                  drawOnChartArea: false,
                },
                ticks: {
                  precision: 0
                }
              }
            }
          }
        });

      } catch (error) {
        console.error("Gagal memuat data pengajuan admin:", error);
      }
    }
  }
};
</script>



<style scoped>
canvas {
  max-height: 400px;
}
</style>
