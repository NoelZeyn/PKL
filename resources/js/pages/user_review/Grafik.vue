<template>
  <div class="flex flex-col md:flex-row min-h-screen bg-gray-100">
    <!-- Sidebar -->
    <Sidebar :activeMenu="activeMenu" @update:activeMenu="activeMenu = $event" />

    <!-- Main Content -->
    <div class="flex-1 px-4 sm:px-6 lg:px-8 py-6 bg-white">
      <div class="max-w-6xl mx-auto bg-white rounded-2xl shadow-xl px-4 sm:px-6 py-6 space-y-12">

        <!-- Grafik Status Barang per Semester -->
        <div v-if="tingkatanOtoritas === 'user' || tingkatanOtoritas === 'asman'">
          <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-4 mb-4">
            <h3 class="text-base font-semibold text-gray-900">
              Grafik Pengajuan Status Barang Per Semester
            </h3>
            <div class="flex flex-wrap gap-2">
              <button @click="downloadBarangStatusChart"
                class="px-3 py-2 bg-[#08607a] hover:bg-[#065666] text-white rounded-md text-sm">
                Download Gambar Grafik
              </button>
              <button @click="downloadBarangStatusExcel"
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

          <div class="mb-4">
            <label for="tahun" class="block text-sm font-medium text-gray-700 mb-1">Pilih Tahun :</label>
            <select v-model="selectedYear" @change="fetchRequestByPenempatan"
              class="w-full sm:w-64 border rounded-md px-3 py-2 text-sm">
              <option v-for="year in availableYears" :key="year" :value="year">{{ year }}</option>
            </select>
          </div>

          <div class="bg-white rounded-lg shadow border border-gray-300 p-5 overflow-x-auto">
            <canvas id="chartBarangStatusPerSemester" class="w-full h-96 my-4"></canvas>
          </div>
        </div>

        <!-- Grafik Total per Bidang -->
        <div v-if="tingkatanOtoritas !== 'user' && tingkatanOtoritas !== 'asman'">
          <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-4 mb-4">
            <h1 class="text-xl font-bold text-[#08607a]">Total Pengajuan per Bidang</h1>
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
              <button @click="downloadRekapBidangExcelML"
                class="flex items-center gap-2 px-4 py-2 bg-[#08607a] hover:bg-[#065666] text-white text-sm rounded-lg shadow">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                  stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round"
                    d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5m0 0l5-5m-5 5V4" />
                </svg>
                Download Rekap Prediksi ML
              </button>
            </div>
          </div>

          <p class="text-gray-600 mb-6 text-sm">
            Grafik ini menunjukkan total pengajuan harga per bidang berdasarkan seluruh data yang telah disetujui.
          </p>

          <div class="flex flex-col sm:flex-row items-start sm:items-center gap-2 mb-4">
            <label for="tahun" class="text-sm font-medium text-gray-700">Pilih Tahun:</label>
            <select id="tahun" v-model="selectedYear" @change="fetchPengajuanAdminChart"
              class="w-full sm:w-64 border rounded-md px-3 py-2 text-sm">
              <option v-for="year in yearOptions" :key="year" :value="year">{{ year }}</option>
            </select>
          </div>

          <div class="overflow-x-auto">
            <canvas ref="chartPengajuans" class="w-full h-96"></canvas>
          </div>
        </div>

        <!-- Grafik Status per Semester -->
        <div v-if="tingkatanOtoritas !== 'user' && tingkatanOtoritas !== 'asman'"
          class="mt-8 bg-white rounded-lg shadow border border-gray-300 p-5">
          <div class="flex flex-col md:flex-row justify-between md:items-center mb-4 gap-4">
            <h3 class="text-sm font-semibold text-gray-900">
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

          <div class="mb-4">
            <label for="tahun" class="block text-sm font-medium text-gray-700 mb-1">Pilih Tahun :</label>
            <select v-model="selectedYear" @change="fetchAllRequest"
              class="w-full sm:w-64 border rounded-md px-3 py-2 text-sm">
              <option v-for="year in availableYears" :key="year" :value="year">{{ year }}</option>
            </select>
          </div>

          <div class="overflow-x-auto">
            <canvas ref="chartPengajuan" class="w-full h-96 my-4"></canvas>
          </div>
        </div>

      </div>
    </div>
  </div>
</template>


<script>
import Sidebar from '@/components/Sidebar.vue';
import Chart from 'chart.js/auto';
import axios from 'axios';
import ExcelJS from 'exceljs';
import { saveAs } from 'file-saver';

export default {
  name: 'GrafikAnggaran',
  components: { Sidebar },

  data() {
    return {
      activeMenu: 'grafik',
      tingkatanOtoritas: "",
      bidangChartInstance: null,
      bidangChartCanvas: null,
      chartPengajuansInstance: null,
      selectedYear: new Date().getFullYear(),
      availableYears: Array.from({ length: 20 }, (_, i) => new Date().getFullYear() - i),
      yearOptions: [],
      requestListAll: [],
      requestListByPenempatan: [],
      "data": {
        "Semester 1": [],
        "Semester 2": [],
      }
    };
  },

  mounted() {
    const currentYear = new Date().getFullYear();
    for (let i = 0; i < 20; i++) {
      this.yearOptions.push(currentYear - i);
    }
    this.getUserInfo();
  },

  methods: {
    async downloadRekapBidangExcelML() {
      try {
        const token = localStorage.getItem("token");
        const res = await axios.get("http://localhost:8000/api/request", {
          headers: { Authorization: `Bearer ${token}` }
        });

        // Ambil hanya data dengan status "done"
        const allowedStatus = ["approved", "purchasing", "on_the_way", "done"];
        const data = (res.data.data || []).filter(item => allowedStatus.includes(item.status));


        const workbook = new ExcelJS.Workbook();
        const worksheet = workbook.addWorksheet("Dataset ML");

        const bidangList = ["HAR", "EQA", "OP", "BS", "LK3"];

        worksheet.columns = [
          { header: "Barang", key: "barang", width: 35 },
          { header: "Jumlah", key: "jumlah", width: 10 },
          { header: "Semester", key: "semester", width: 10 },
          { header: "Tahun", key: "tahun", width: 10 },
          ...bidangList.map(b => ({ header: b, key: b.toLowerCase(), width: 8 }))
        ];

        worksheet.getRow(1).eachCell(cell => {
          cell.font = { bold: true, color: { argb: "FFFFFFFF" } };
          cell.fill = { type: "pattern", pattern: "solid", fgColor: { argb: "FF4F46E5" } };
          cell.alignment = { vertical: "middle", horizontal: "center" };
        });

        const grouped = {}; // key: barang-semester-tahun

        for (const item of data) {
          const barang = item.alat?.nama_barang || "-";
          const jumlah = item.jumlah || 0;
          const tanggal = item.tanggal_permintaan || item.created_at;
          const tahun = new Date(tanggal).getFullYear();
          const bulan = new Date(tanggal).getMonth() + 1;
          const semester = bulan <= 6 ? "Ganjil" : "Genap";
          const bidang = item.user?.penempatan?.bidang?.nama_bidang?.toUpperCase() || "-";

          const key = `${barang}__${semester}__${tahun}`;

          if (!grouped[key]) {
            grouped[key] = {
              barang,
              semester,
              tahun,
              jumlah: 0,
              HAR: false,
              EQA: false,
              OP: false,
              BS: false,
              LK3: false
            };
          }

          grouped[key].jumlah += jumlah;

          if (bidangList.includes(bidang)) {
            grouped[key][bidang] = true;
          }
        }

        for (const key in grouped) {
          const row = grouped[key];
          worksheet.addRow({
            barang: row.barang,
            jumlah: row.jumlah,
            semester: row.semester,
            tahun: row.tahun,
            har: row.HAR,
            eqa: row.EQA,
            op: row.OP,
            bs: row.BS,
            lk3: row.LK3
          });
        }

        const buffer = await workbook.xlsx.writeBuffer();
        const filename = `Dataset-ML-Pengajuan-${new Date().toISOString().slice(0, 10)}.xlsx`;
        saveAs(new Blob([buffer]), filename);
      } catch (error) {
        console.error("Gagal export dataset ML:", error);
      }
    },
    async getUserInfo() {
      try {
        const token = localStorage.getItem("token");
        const res = await axios.post("http://localhost:8000/api/me", {}, {
          headers: { Authorization: `Bearer ${token}` }
        });

        this.tingkatanOtoritas = res.data.tingkatan_otoritas;
        this.userPenempatanId = res.data.id_penempatan_fk;

        if (this.tingkatanOtoritas === 'user' || this.tingkatanOtoritas === 'asman') {
          this.fetchRequestByPenempatan();
        } else {
          this.fetchAllRequest();
          this.fetchPengajuanAdminChart();
        }
      } catch (err) {
        console.error("Gagal mendapatkan info user:", err);
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

    async renderChartBarangStatusPerSemester() {
      const semesterData = {
        "Semester 1": {},
        "Semester 2": {}
      };

      const validStatuses = [
        "approved", "rejected", "purchasing", "on_the_way", "done",
        "waiting_approval_1", "waiting_approval_2", "waiting_approval_3"
      ];

      for (const semester of ["Semester 1", "Semester 2"]) {
        const dataList = this.requestListByPenempatan[semester] || [];

        dataList.forEach(req => {
          const barang = req.alat?.nama_barang || "Tanpa Nama";
          const status = req.status;
          if (!validStatuses.includes(status)) return;

          if (!semesterData[semester][barang]) {
            semesterData[semester][barang] = {};
            validStatuses.forEach(s => semesterData[semester][barang][s] = 0);
          }

          semesterData[semester][barang][status]++;
        });
      }

      const allBarang = new Set();
      Object.values(semesterData).forEach(barangs => {
        Object.keys(barangs).forEach(nama => allBarang.add(nama));
      });

      const labels = Array.from(allBarang);

      const statusColorMap = {
        waiting_approval_1: "#c084fc",
        waiting_approval_2: "#f472b6",
        waiting_approval_3: "#fb923c",
        approved: "#34d399",
        rejected: "#f87171",
        purchasing: "#facc15",
        on_the_way: "#60a5fa",
        done: "#a3a3a3",
      };

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

      const datasets = [];

      for (const status of validStatuses) {
        const dataset = {
          label: statusLabelMap[status],
          backgroundColor: statusColorMap[status] + "CC",
          borderColor: statusColorMap[status],
          borderWidth: 1,
          data: [],
        };

        labels.forEach(barang => {
          let total = 0;
          for (const semester of ["Semester 1", "Semester 2"]) {
            total += semesterData[semester][barang]?.[status] || 0;
          }
          dataset.data.push(total);
        });

        datasets.push(dataset);
      }

      const ctx = document.getElementById("chartBarangStatusPerSemester").getContext("2d");

      if (this.chartBarangStatusSemesterByPenempatanInstance) {
        this.chartBarangStatusSemesterByPenempatanInstance.destroy();
      }

      this.chartBarangStatusSemesterByPenempatanInstance = new Chart(ctx, {
        type: "bar",
        data: {
          labels,
          datasets,
        },
        options: {
          responsive: true,
          plugins: {
            legend: { position: "top" },
          },
          scales: {
            x: { stacked: true, title: { display: true, text: 'Nama Barang' } },
            y: { stacked: true, beginAtZero: true, title: { display: true, text: 'Jumlah Pengajuan' } }
          }
        }
      });
    },
    async downloadBarangStatusExcel() {
      const ExcelJS = (await import('exceljs')).default;
      const { saveAs } = await import('file-saver');

      try {
        const workbook = new ExcelJS.Workbook();
        const worksheet = workbook.addWorksheet("Pengajuan Status Barang");

        const statusLabelMap = {
          waiting_approval_1: "Approval 1",
          waiting_approval_2: "Approval 2",
          waiting_approval_3: "Approval 3",
          approved: "Approved",
          rejected: "Rejected",
          purchasing: "Purchasing",
          on_the_way: "On The Way",
          done: "Done"
        };

        const statusColorMap = {
          waiting_approval_1: "c084fc",
          waiting_approval_2: "f472b6",
          waiting_approval_3: "fb923c",
          approved: "34d399",
          rejected: "f87171",
          purchasing: "facc15",
          on_the_way: "60a5fa",
          done: "a3a3a3"
        };

        worksheet.columns = [
          { header: "Nama Barang", key: "barang", width: 30 },
          { header: "Semester", key: "semester", width: 15 },
          { header: "Status", key: "status", width: 25 },
          { header: "Jumlah", key: "jumlah", width: 10 },
        ];

        worksheet.getRow(1).eachCell(cell => {
          cell.font = { bold: true, color: { argb: "FFFFFFFF" } };
          cell.fill = { type: "pattern", pattern: "solid", fgColor: { argb: "FF4F46E5" } };
          cell.alignment = { vertical: "middle", horizontal: "center" };
          cell.border = {
            top: { style: "thin" },
            left: { style: "thin" },
            bottom: { style: "thin" },
            right: { style: "thin" }
          };
        });

        const dataPerSemester = {
          "Semester 1": {},
          "Semester 2": {}
        };

        const validStatuses = Object.keys(statusLabelMap);

        Object.entries(this.requestListByPenempatan).forEach(([semester, reqList]) => {
          reqList.forEach(req => {
            const barang = req.alat?.nama_barang || "Tanpa Nama";
            const status = req.status;

            if (!validStatuses.includes(status)) return;

            const key = `${barang}|||${status}`;
            if (!dataPerSemester[semester][key]) {
              dataPerSemester[semester][key] = 0;
            }

            dataPerSemester[semester][key]++;
          });
        });

        Object.entries(dataPerSemester).forEach(([semester, records]) => {
          const startRow = worksheet.lastRow.number + 2;
          worksheet.mergeCells(`A${startRow}:D${startRow}`);
          const titleCell = worksheet.getCell(`A${startRow}`);
          titleCell.value = semester;
          titleCell.font = { bold: true, color: { argb: "FFFFFFFF" } };
          titleCell.fill = {
            type: "pattern",
            pattern: "solid",
            fgColor: { argb: semester === "Semester 1" ? "FF10B981" : "FFF59E0B" }
          };
          titleCell.alignment = { vertical: "middle", horizontal: "left" };

          Object.entries(records).forEach(([key, jumlah]) => {
            const [barang, status] = key.split("|||");
            const row = worksheet.addRow({
              barang,
              semester,
              status: statusLabelMap[status],
              jumlah,
            });

            row.eachCell((cell, colNumber) => {
              cell.border = {
                top: { style: "thin" },
                left: { style: "thin" },
                bottom: { style: "thin" },
                right: { style: "thin" }
              };

              if (colNumber === 3) {
                const hex = statusColorMap[status];
                if (hex) {
                  cell.fill = {
                    type: "pattern",
                    pattern: "solid",
                    fgColor: { argb: "FF" + hex }
                  };
                  cell.font = { bold: true };
                  cell.alignment = { horizontal: "center" };
                }
              }
            });
          });
        });

        const buffer = await workbook.xlsx.writeBuffer();
        saveAs(new Blob([buffer]), `Rekap-Pengajuan-Barang-Semester-${new Date().toISOString().slice(0, 10)}.xlsx`);
      } catch (error) {
        console.error("Gagal export Excel status barang per semester:", error);
      }
    },

    downloadBarangStatusChart() {
      if (this.chartBarangStatusSemesterByPenempatanInstance) {
        const link = document.createElement('a');
        link.href = this.chartBarangStatusSemesterByPenempatanInstance.toBase64Image();
        link.download = `Grafik-Barang-Semester-${new Date().toISOString().slice(0, 10)}.png`;
        link.click();
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
      const filename = `Rekap-Pengajuan-PerBidang-${this.selectedYear}-${new Date().toISOString().slice(0, 10)}.xlsx`;
      saveAs(new Blob([buffer]), filename);
    },
    async fetchAllRequest() {
      try {
        const token = localStorage.getItem("token");

        const res = await axios.get("http://localhost:8000/api/requestSemester", {
          headers: { Authorization: `Bearer ${token}` },
          params: { tahun: this.selectedYear }
        });

        this.requestListAll = res.data.data; // bukan array, tapi object per semester

        this.renderChartPerSemester(); // pakai semua data

      } catch (err) {
        console.error("Gagal mengambil SEMUA data request:", err);
      }
    },
    async fetchRequestByPenempatan() {
      try {
        const token = localStorage.getItem("token");

        const res = await axios.get('http://localhost:8000/api/request/filter', {
          headers: { Authorization: `Bearer ${token}` },
          params: {
            tahun: this.selectedYear,
          }
        });

        this.requestListByPenempatan = res.data.data; // format: { Semester 1: [...], Semester 2: [...] }

        this.renderChartBarangStatusPerSemester();

      } catch (err) {
        console.error("Gagal mengambil data request by penempatan:", err);
      }
    }

  },
};
</script>


<style scoped>
canvas {
  max-height: 400px;
}
</style>
