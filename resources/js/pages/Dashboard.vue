<template>
        <div class="flex flex-col md:flex-row min-h-screen bg-gray-100">
    <!-- Sidebar -->
    <Sidebar :activeMenu="activeMenu" @update:activeMenu="updateActiveMenu" />

    <!-- Main Content -->
    <div class="flex-1 p-4 sm:p-6 lg:p-8 bg-white">
      <!-- Header -->
      <HeaderBar title="Dashboard" class="mt-3" />
      <div class="my-4 border-b border-gray-300"></div>

      <!-- Greeting & Date -->
      <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-3 pt-5">
        <h3 class="text-base sm:text-lg font-semibold text-gray-800">
          Selamat Datang, {{ user.NID }} {{ user.tingkatan_otoritas }} 👋
        </h3>
        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-lg border text-sm font-bold text-gray-800 bg-white border-gray-300">
          <img class="w-5 h-5" :src="iconKalender" alt="icon" />
          <span>{{ formattedDate }}</span>
        </div>
      </div>

      <!-- Quick Menu Grid -->
      <div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 mt-6 mb-10">
        <div v-for="menu in quickMenus" :key="menu.title"
          class="flex items-center gap-3 p-4 sm:p-5 rounded-xl bg-white border border-gray-200 shadow hover:bg-blue-50 active:bg-blue-700 active:text-white transition cursor-pointer"
          @click="navigateTo(menu.path)">
          <img :src="menu.icon" class="w-6 h-6 object-cover" :alt="menu.title" />
          <p class="font-semibold text-blue-900 text-sm sm:text-base">{{ menu.title }}</p>
        </div>
      </div>

      <!-- Anggaran ATK Chart -->
      <div class="w-full max-w-full xl:max-w-6xl mx-auto bg-white rounded-2xl shadow-xl p-6 sm:p-8">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-4">
          <h1 class="text-2xl sm:text-3xl font-bold text-[#08607a]">Perbandingan Anggaran ATK</h1>
          <div class="flex flex-wrap gap-2">
            <button @click="downloadChart" class="px-3 py-2 bg-[#08607a] hover:bg-[#065666] text-white rounded-md text-sm">
              Download Gambar Grafik
            </button>
            <button @click="downloadExcel" class="px-3 py-2 bg-[#08607a] hover:bg-[#065666] text-white rounded-md text-sm">
              Download Excel
            </button>
          </div>
        </div>
        <p class="text-gray-600 mb-4 text-sm">
          Grafik perbandingan harga satuan, total, dan estimasi kebutuhan ATK.
        </p>
        <div class="bg-gray-50 rounded-xl p-4 shadow-inner overflow-x-auto">
          <canvas id="anggaranChart" ref="chartCanvas" class="w-full h-72 sm:h-96"></canvas>
        </div>
      </div>

      <!-- Total Pengajuan per Bidang -->
      <div v-if="tingkatanOtoritas !== 'user' && tingkatanOtoritas !== 'asman'"
        class="w-full max-w-full xl:max-w-6xl mx-auto bg-white rounded-2xl shadow-xl p-6 sm:p-8 mt-10">
        <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-4 mb-4">
          <h1 class="text-2xl sm:text-3xl font-bold text-[#08607a]">Total Pengajuan per Bidang</h1>
          <div class="flex flex-wrap gap-2">
            <button @click="downloadBidangChart"
              class="px-3 py-2 bg-[#08607a] hover:bg-[#065666] text-white rounded-md text-sm">
              Download Gambar Grafik
            </button>
            <button @click="downloadRekapBidangExcel"
              class="flex items-center gap-2 px-4 py-2 bg-[#08607a] hover:bg-[#065666] text-white text-sm rounded-lg shadow">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round"
                  d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5m0 0l5-5m-5 5V4" />
              </svg>
              Download Excel
            </button>
          </div>
        </div>
        <p class="text-gray-600 mb-4 text-sm">
          Grafik ini menunjukkan total pengajuan harga per bidang berdasarkan seluruh data yang telah disetujui.
        </p>
        <div class="flex items-center gap-3 mb-4 flex-wrap">
          <label for="tahun" class="text-sm font-medium text-gray-700">Pilih Tahun:</label>
          <select id="tahun" v-model="selectedYear" @change="fetchPengajuanAdminChart"
            class="border border-gray-300 rounded px-3 py-2 text-sm">
            <option v-for="year in yearOptions" :key="year" :value="year">{{ year }}</option>
          </select>
        </div>
        <div class="overflow-x-auto">
          <canvas ref="chartPengajuans" class="w-full h-72 sm:h-96"></canvas>
        </div>
      </div>

      <!-- Grafik Status per Semester -->
      <div v-if="tingkatanOtoritas !== 'user' && tingkatanOtoritas !== 'asman'"
        class="w-full max-w-full xl:max-w-6xl mx-auto bg-white rounded-2xl shadow-xl p-6 sm:p-8 mt-10">
        <div class="flex flex-col md:flex-row justify-between items-center gap-4 mb-4">
          <h3 class="text-sm sm:text-base md:text-lg font-semibold text-gray-900">
            Grafik Pengajuan Berdasarkan Status per Semester
          </h3>
          <div class="flex flex-wrap gap-2">
            <button @click="downloadChartPerSemester"
              class="px-3 py-2 bg-[#08607a] hover:bg-[#065666] text-white rounded-md text-sm">
              Download Gambar Grafik
            </button>
            <button @click="downloadExcelPerSemester"
              class="flex items-center gap-2 px-4 py-2 bg-[#08607a] hover:bg-[#065666] text-white text-sm rounded-lg shadow">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round"
                  d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5m0 0l5-5m-5 5V4" />
              </svg>
              Download Excel
            </button>
          </div>
        </div>
        <div class="flex items-center gap-4 mb-4 flex-wrap">
          <label for="tahun" class="text-sm font-medium text-gray-700">Pilih Tahun :</label>
          <select v-model="selectedYear" @change="fetchAllRequest" class="border rounded-md px-2 py-1 text-sm">
            <option v-for="year in availableYears" :key="year" :value="year">{{ year }}</option>
          </select>
        </div>
        <div class="overflow-x-auto">
          <canvas ref="chartPengajuan" class="w-full h-72 sm:h-96 my-4"></canvas>
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

      tingkatanOtoritas: "",
      chartInstance: null,
      chartCanvas: null,
      iconKalender: iconKalender,
      quickMenus: [
        { icon: iconPosyandu, title: "Grafik", path: "/grafik" },
        { icon: iconStetoskop, title: "Laporan Pengajuan", path: "/laporan-pengajuan" },
        { icon: iconPasien, title: "Manage Alat", path: "/manajemen-alat" },
        { icon: iconLaporan, title: "Pengajuan", path: "/pengajuan" },
      ],

      selectedYear: new Date().getFullYear(),
      availableYears: Array.from({ length: 20 }, (_, i) => new Date().getFullYear() - i),
      yearOptions: [],
      requestListAll: [],

      "data": {
        "Semester 1": [],
        "Semester 2": [],
      }
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
    const currentYear = new Date().getFullYear();
    for (let i = 0; i < 20; i++) {
      this.yearOptions.push(currentYear - i);
    }
    this.fetchDataAndRenderChart();
    this.getUserInfo();
  },
  methods: {
    async getUserInfo() {
      try {
        const token = localStorage.getItem("token");
        const res = await axios.post("http://localhost:8000/api/me", {}, {
          headers: { Authorization: `Bearer ${token}` }
        });

        this.tingkatanOtoritas = res.data.tingkatan_otoritas;
        this.userPenempatanId = res.data.id_penempatan_fk;

        if (this.tingkatanOtoritas !== 'user' && this.tingkatanOtoritas !== 'asman') {
          this.fetchAllRequest();
          this.fetchPengajuanAdminChart();
        } else {
        }
      } catch (err) {
        console.error("Gagal mendapatkan info user:", err);
      }
    },
    renderChartPerSemester() {
      const semesterStatusData = this.requestListAll || {
        "Semester 1": {},
        "Semester 2": {},
      };

      const statusKeys = [
        "waiting_approval_1",
        "waiting_approval_2",
        "waiting_approval_3",
        "approved",
        "rejected",
        "purchasing",
        "on_the_way",
        "done",
      ];

      const statusLabelMap = {
        waiting_approval_1: "Approval 1",
        waiting_approval_2: "Approval 2",
        waiting_approval_3: "Approval 3",
        approved: "Approved",
        rejected: "Rejected",
        purchasing: "Purchasing",
        on_the_way: "On The Way",
        done: "Done",
      };

      const colorMap = {
        waiting_approval_1: "#c084fc",
        waiting_approval_2: "#f472b6",
        waiting_approval_3: "#fb923c",
        approved: "#34d399",
        rejected: "#f87171",
        purchasing: "#facc15",
        on_the_way: "#60a5fa",
        done: "#a3a3a3",
      };

      const labels = ["Semester 1", "Semester 2"];

      const datasets = statusKeys.map(statusKey => ({
        label: statusLabelMap[statusKey],
        data: labels.map(label => semesterStatusData[label]?.[statusKey] || 0),
        backgroundColor: colorMap[statusKey],
      }));

      const ctx = this.$refs.chartPengajuan?.getContext("2d");
      if (!ctx) {
        console.error("Canvas chartPengajuan tidak ditemukan");
        return;
      }

      if (this.chartPengajuanInstance) {
        this.chartPengajuanInstance.destroy();
      }

      this.chartPengajuanInstance = new Chart(ctx, {
        type: "bar",
        data: {
          labels,
          datasets,
        },
        options: {
          responsive: true,
          plugins: {
            legend: {
              position: "bottom",
            },
            tooltip: {
              callbacks: {
                label: ctx => `${ctx.dataset.label}: ${ctx.raw} pengajuan`
              }
            }
          },
          scales: {
            y: {
              beginAtZero: true,
              title: {
                display: true,
                text: 'Jumlah Pengajuan'
              }
            },
            x: {
              title: {
                display: true,
                text: 'Semester'
              }
            }
          },
        },
      });
    },
    downloadChartPerSemester() {
      if (this.chartPengajuanInstance) {
        const link = document.createElement("a");
        link.href = this.chartPengajuanInstance.toBase64Image();
        link.download = `Pengajuan-PerSemester-${new Date().toISOString().slice(0, 10)}.png`;
        link.click();
      } else {
        console.warn("Chart belum dirender.");
      }
    },
    async downloadExcelPerSemester() {
      const workbook = new ExcelJS.Workbook();
      const worksheet = workbook.addWorksheet("Pengajuan per Semester");

      worksheet.columns = [
        { header: "Semester", key: "semester", width: 15 },
        { header: "Status", key: "status", width: 25 },
        { header: "Jumlah", key: "jumlah", width: 10 },
      ];

      const statusLabelMap = {
        waiting_approval_1: "Approval 1",
        waiting_approval_2: "Approval 2",
        waiting_approval_3: "Approval 3",
        approved: "Approved",
        rejected: "Rejected",
        purchasing: "Purchasing",
        on_the_way: "On The Way",
        done: "Done",
      };

      const statusColorMap = {
        waiting_approval_1: "FFC084FC",
        waiting_approval_2: "FFF472B6",
        waiting_approval_3: "FFFB923C",
        approved: "FF34D399",
        rejected: "FFF87171",
        purchasing: "FFFACC15",
        on_the_way: "FF60A5FA",
        done: "FFA3A3A3",
      };

      // langsung ambil dari object this.requestListAll
      const semesterStatusData = this.requestListAll;

      for (const [semester, statuses] of Object.entries(semesterStatusData)) {
        // Lewati jika bukan object (misalnya array kosong)
        if (typeof statuses !== "object" || statuses === null) continue;

        for (const [statusKey, jumlah] of Object.entries(statuses)) {
          if (statusKey === "status") continue; // skip kunci `status` tambahan jika ada

          const label = statusLabelMap[statusKey] || statusKey;
          const row = worksheet.addRow({
            semester,
            status: label,
            jumlah: jumlah || 0
          });

          const fillColor = statusColorMap[statusKey] || "FFE5E7EB";

          row.eachCell(cell => {
            cell.fill = {
              type: "pattern",
              pattern: "solid",
              fgColor: { argb: fillColor }
            };
            cell.border = {
              top: { style: "thin" },
              left: { style: "thin" },
              bottom: { style: "thin" },
              right: { style: "thin" },
            };
            cell.alignment = { vertical: "middle", horizontal: "center" };
          });
        }

        worksheet.addRow({}); // spasi antar semester
      }

      worksheet.getRow(1).font = { bold: true };

      const buffer = await workbook.xlsx.writeBuffer();
      const filename = `Pengajuan-PerSemester-${this.selectedYear}-${new Date().toISOString().slice(0, 10)}.xlsx`;
      saveAs(new Blob([buffer]), filename);
    },
    async fetchAllRequest() {
      try {
        const token = localStorage.getItem("token");

        const res = await axios.get("http://localhost:8000/api/requestSemester", {
          headers: { Authorization: `Bearer ${token}` },
          params: { tahun: this.selectedYear }
        });

        this.requestListAll = res.data.data; 

        this.renderChartPerSemester();
      } catch (err) {
        console.error("Gagal mengambil SEMUA data request:", err);
      }
    },
    updateActiveMenu(menu) {
      this.activeMenu = menu;
    },
    navigateTo(path) {
      if (path) window.location.href = path;
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

    downloadBidangChart() {
      if (this.chartPengajuansInstance) {
        const link = document.createElement("a");
        link.href = this.chartPengajuansInstance.toBase64Image();
        link.download = `Total-Pengajuan-Per-Bidang-${this.selectedYear}.png`;
        link.click();
      }
    },
    async downloadRekapBidangExcel() {
      try {
        const token = localStorage.getItem("token");
        const res = await axios.get("http://localhost:8000/api/adminSemester", {
          headers: { Authorization: `Bearer ${token}` },
          params: { tahun: this.selectedYear }
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
        let grandTotal = 0; // <-- Ini akan menampung total keseluruhan

        const formatIDR = (value) =>
          new Intl.NumberFormat("id-ID", {
            style: "currency",
            currency: "IDR",
            minimumFractionDigits: 0,
          }).format(value);

        for (const semester in data) {
          const groups = data[semester] || [];

          worksheet.mergeCells(`A${rowIndex}:G${rowIndex}`);
          worksheet.getCell(`A${rowIndex}`).value = `Semester: ${semester}`;
          worksheet.getCell(`A${rowIndex}`).font = { bold: true };
          rowIndex++;

          for (const group of groups) {
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
            grandTotal += subtotal; // Tambahkan subtotal ke grand total
          }
        }

        // Tambahkan grand total di akhir sheet
        worksheet.mergeCells(`A${rowIndex}:B${rowIndex}`);
        const grandTotalCell = worksheet.getCell(`A${rowIndex}`);
        grandTotalCell.value = "TOTAL KESELURUHAN";
        grandTotalCell.font = { bold: true };
        grandTotalCell.alignment = { vertical: "middle", horizontal: "right" };

        const grandTotalValueCell = worksheet.getCell(`C${rowIndex}`);
        grandTotalValueCell.value = formatIDR(grandTotal);
        grandTotalValueCell.font = { bold: true };
        grandTotalValueCell.alignment = { vertical: "middle", horizontal: "center" };

        const buffer = await workbook.xlsx.writeBuffer();
const filename = `Rekap-Pengajuan-PerBidang-${this.selectedYear}-${new Date().toISOString().slice(0, 10)}.xlsx`;

        saveAs(new Blob([buffer]), filename);
      } catch (error) {
        console.error("Gagal export Excel rekap bidang:", error);
      }
    },
    async fetchPengajuanAdminChart() {
      try {
        const token = localStorage.getItem("token");
        const res = await axios.get("http://localhost:8000/api/adminSemester", {
          headers: { Authorization: `Bearer ${token}` },
          params: { tahun: this.selectedYear }
        });

        const semesterData = res.data.data || {};

        const allBidang = new Set();
        const totalHargaByBidang = {
          "Semester 1": {},
          "Semester 2": {},
        };

        // Ambil total harga per bidang di masing-masing semester
        for (const semester in semesterData) {
          const groups = semesterData[semester];
          groups.forEach(group => {
            const bidang = group.nama_bidang || "Tidak Diketahui";
            allBidang.add(bidang);

            let total = 0;
            group.barang.forEach(item => {
              total += item.total_harga || 0;
            });

            totalHargaByBidang[semester][bidang] = total;
          });
        }

        const labels = Array.from(allBidang); // Semua nama bidang
        const dataSemester1 = labels.map(bidang => totalHargaByBidang["Semester 1"][bidang] || 0);
        const dataSemester2 = labels.map(bidang => totalHargaByBidang["Semester 2"][bidang] || 0);

        const ctx = this.$refs.chartPengajuans?.getContext("2d");
        if (!ctx) {
          console.error("Canvas chartPengajuans tidak ditemukan");
          return;
        }

        if (this.chartPengajuansInstance) {
          this.chartPengajuansInstance.destroy();
        }

        this.chartPengajuansInstance = new Chart(ctx, {
          type: "bar",
          data: {
            labels,
            datasets: [
              {
                label: "Semester 1",
                data: dataSemester1,
                backgroundColor: "#60a5fa",
              },
              {
                label: "Semester 2",
                data: dataSemester2,
                backgroundColor: "#34d399",
              }
            ]
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
              legend: { position: "top" },
              tooltip: {
                callbacks: {
                  label: ctx => `Rp ${ctx.raw.toLocaleString("id-ID")}`
                }
              }
            },
            scales: {
              y: {
                beginAtZero: true,
                title: { display: true, text: "Total Harga (Rp)" },
                ticks: {
                  callback: value => 'Rp ' + value.toLocaleString("id-ID")
                }
              },
              x: {
                title: { display: true, text: "Nama Bidang" }
              }
            }
          }
        });
      } catch (error) {
        console.error("Gagal memuat data pengajuan admin:", error);
      }
    },
  }
};
</script>



<style scoped>
canvas {
  max-height: 400px;
}
</style>
