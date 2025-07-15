<template>
  <div class="flex h-screen bg-gray-100">
    <Sidebar :activeMenu="activeMenu" @update:activeMenu="activeMenu = $event" />

    <div class="flex-1 p-8 bg-white">
      <div class="max-w-6xl mx-auto bg-white rounded-2xl shadow-xl p-8 space-y-12">
        <!-- Grafik Anggaran ATK -->
        <div>
          <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-4 mb-4">
            <h1 class="text-3xl font-bold text-[#08607a]">Perbandingan Anggaran ATK</h1>
            <div class="flex flex-wrap gap-2">
              <button @click="downloadChart"
                class="cursor-pointer px-3 py-2 bg-[#08607a] hover:bg-[#065666] text-white rounded-md text-sm">
                Download Gambar Grafik
              </button>
              <button @click="downloadExcel"
                class="cursor-pointer flex items-center gap-2 px-4 py-2 bg-[#08607a] hover:bg-[#065666] text-white text-sm rounded-lg shadow">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                  stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round"
                    d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5m0 0l5-5m-5 5V4" />
                </svg>
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

        <!-- Grafik Status Barang per Semester -->
        <div>
          <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-4 mb-4">
            <h3 class="text-sm font-semibold text-gray-900">Grafik Pengajuan Status Barang Per Semester</h3>
            <div class="flex gap-2">
              <button @click="downloadBarangStatusChart"
                class="cursor-pointer px-3 py-2 bg-[#08607a] hover:bg-[#065666] text-white rounded-md text-sm">
                Download Gambar Grafik
              </button>
              <button @click="downloadBarangStatusExcel"
                class="cursor-pointer flex items-center gap-2 px-4 py-2 bg-[#08607a] hover:bg-[#065666] text-white text-sm rounded-lg shadow">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                  stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round"
                    d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5m0 0l5-5m-5 5V4" />
                </svg>
                Download Excel
              </button>
            </div>
          </div>

          <div class="bg-white rounded-lg shadow border border-gray-300 p-5">
            <canvas id="chartBarangStatusPerSemester" class="w-full h-96 my-4"></canvas>
          </div>
        </div>


        <!-- Grafik Total per Bidang -->
        <div>
          <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-4 mb-4">
            <h1 class="text-3xl font-bold text-[#08607a]">Total Pengajuan per Bidang</h1>
            <div class="flex flex-wrap gap-2">
              <button @click="downloadBidangChart"
                class="cursor-pointer px-3 py-2 bg-[#08607a] hover:bg-[#065666] text-white rounded-md text-sm">
                Download Gambar Grafik
              </button>
              <button @click="downloadRekapBidangExcel"
                class="cursor-pointer flex items-center gap-2 px-4 py-2 bg-[#08607a] hover:bg-[#065666] text-white text-sm rounded-lg shadow">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                  stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round"
                    d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5m0 0l5-5m-5 5V4" />
                </svg>
                Download Excel
              </button>
            </div>
          </div>

          <p class="text-gray-600 mb-6 text-sm">
            Grafik ini menunjukkan total pengajuan harga per bidang berdasarkan seluruh data yang telah disetujui.
          </p>

          <div class="bg-gray-50 rounded-xl p-4 shadow-inner">
            <canvas ref="bidangChartCanvas" class="w-full h-96"></canvas>
          </div>
        </div>
        <div class="mt-8 bg-white rounded-lg shadow border border-gray-300 p-5">
          <div class="flex justify-between items-center mb-4">
            <h3 class="text-sm font-semibold text-gray-900">
              Grafik Pengajuan Berdasarkan Status per Semester
            </h3>
            <div class="flex gap-2">

              <button @click="downloadChartPerSemester"
                class="cursor-pointer px-3 py-2 bg-[#08607a] hover:bg-[#065666] text-white rounded-md text-sm">
                Download Gambar Grafik
              </button>
              <button @click="downloadExcelPerSemester"
                class="cursor-pointer flex items-center gap-2 px-4 py-2 bg-[#08607a] hover:bg-[#065666] text-white text-sm rounded-lg shadow">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                  stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round"
                    d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5m0 0l5-5m-5 5V4" />
                </svg>
                Download Excel
              </button>
            </div>
          </div>
          <canvas ref="chartPengajuan" class="w-full h-96 my-4"></canvas>
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
      chartInstance: null,
      chartCanvas: null,
      bidangChartInstance: null,
      bidangChartCanvas: null,

      requestList: [],
    };
  },

  mounted() {
    this.fetchDataAndRenderChart();
    this.fetchRequest();
    this.fetchPengajuanAdminChart();
  },

  methods: {
    downloadBidangChart() {
      if (this.bidangChartInstance) {
        const link = document.createElement("a");
        link.href = this.bidangChartInstance.toBase64Image();
        link.download = `Total-Pengajuan-Per-Bidang-${new Date().toISOString().slice(0, 10)}.png`;
        link.click();
      }
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
    },
    async downloadExcel() {
      try {
        const token = localStorage.getItem('token');
        const res = await axios.get('http://localhost:8000/api/alat', {
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
          { header: 'Rekomendasi', key: 'rekomendasi', width: 22 },
        ];

        worksheet.getRow(1).eachCell(cell => {
          cell.font = { bold: true, color: { argb: 'FFFFFFFF' } };
          cell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FF4F46E5' } };
          cell.alignment = { vertical: 'middle', horizontal: 'center' };
          cell.border = {
            top: { style: 'thin' }, left: { style: 'thin' },
            bottom: { style: 'thin' }, right: { style: 'thin' }
          };
        });

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
          worksheet.mergeCells(`A${rowIndex}:H${rowIndex}`);
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

            let rekomendasi = 'Aman';
            if (item.stock_min === 0 && item.stock_max === 0 && stock === 0) {
              rekomendasi = 'ATK Tidak Digunakan';
            } else if (stock < item.stock_min) {
              rekomendasi = 'Perlu Pengajuan';
            }

            const row = worksheet.addRow({
              no: globalNo++,
              nama_barang: item.nama_barang,
              harga_satuan: hargaSatuan,
              stock: stock,
              harga_total: hargaTotal,
              estimasi_satuan: estimasiSatuan,
              estimasi_total: estimasiTotal,
              rekomendasi: rekomendasi
            });

            row.eachCell((cell, colNumber) => {
              cell.alignment = { vertical: 'middle', horizontal: 'center' };
              cell.border = {
                top: { style: 'thin' }, left: { style: 'thin' },
                bottom: { style: 'thin' }, right: { style: 'thin' }
              };
              if ([3, 5, 6, 7].includes(colNumber)) {
                cell.numFmt = '"Rp"#,##0';
              }
            });

            const rekomCell = row.getCell('rekomendasi');
            if (rekomendasi === 'Perlu Pengajuan') {
              rekomCell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FFDC3545' } };
              rekomCell.font = { bold: true, color: { argb: 'FFFFFFFF' } };
            } else if (rekomendasi === 'Aman') {
              rekomCell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FF28A745' } };
              rekomCell.font = { bold: true, color: { argb: 'FFFFFFFF' } };
            } else {
              rekomCell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FF6C757D' } };
              rekomCell.font = { bold: true, color: { argb: 'FFFFFFFF' } };
            }

            rowIndex++;
          });

          const totalRow = worksheet.addRow({
            no: '',
            nama_barang: 'Subtotal',
            harga_satuan: '',
            stock: '',
            harga_total: totalKategoriHarga,
            estimasi_satuan: '',
            estimasi_total: totalKategoriEstimasi,
            rekomendasi: '',
          });

          totalRow.eachCell(cell => {
            cell.font = { bold: true };
            cell.alignment = { vertical: 'middle', horizontal: 'center' };
          });

          grandTotalHarga += totalKategoriHarga;
          grandTotalEstimasi += totalKategoriEstimasi;
          rowIndex++;
        }

        const totalAkhirRow = worksheet.addRow({
          no: '',
          nama_barang: 'TOTAL',
          harga_satuan: '',
          stock: '',
          harga_total: grandTotalHarga,
          estimasi_satuan: '',
          estimasi_total: grandTotalEstimasi,
          rekomendasi: '',
        });

        totalAkhirRow.eachCell(cell => {
          cell.font = { bold: true, color: { argb: 'FFFFFFFF' } };
          cell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FFEF4444' } };
          cell.alignment = { vertical: 'middle', horizontal: 'center' };
        });

        const buffer = await workbook.xlsx.writeBuffer();
        saveAs(new Blob([buffer]), `Laporan-Anggaran-ATK-${new Date().toISOString().slice(0, 10)}.xlsx`);
      } catch (error) {
        console.error('Gagal membuat laporan Excel:', error);
      }
    },

    async fetchDataAndRenderChart() {
      try {
        const token = localStorage.getItem('token');
        const res = await axios.get('http://localhost:8000/api/alat', {
          headers: { Authorization: `Bearer ${token}` }
        });

        const dataAlat = res.data.data || [];

        const labels = dataAlat.map(item => item.nama_barang);
        const hargaSatuan = dataAlat.map(item => item.harga_satuan || 0);
        const hargaTotal = dataAlat.map(item => (item.harga_satuan || 0) * (item.stock || 0));
        const estimasiSatuan = dataAlat.map(item => item.harga_estimasi || 0);
        const estimasiTotal = dataAlat.map(item => (item.harga_estimasi || 0) * (item.stock || 0));

        if (this.chartInstance) {
          this.chartInstance.destroy();
        }

        const ctx = this.$refs.chartCanvas.getContext('2d');

        this.chartInstance = new Chart(ctx, {
          type: 'bar',
          data: {
            labels: labels,
            datasets: [
              { label: 'Harga Satuan', data: hargaSatuan, backgroundColor: '#6366f1' },
              { label: 'Harga Total', data: hargaTotal, backgroundColor: '#10b981' },
              { label: 'Estimasi Satuan', data: estimasiSatuan, backgroundColor: '#fbbf24' },
              { label: 'Estimasi Total', data: estimasiTotal, backgroundColor: '#ef4444' },
            ],
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
              mode: 'index',
              intersect: false,
            },
            plugins: {
              legend: {
                position: 'bottom',
                labels: {
                  color: '#374151',
                  font: { size: 13, family: 'Arial' },
                },
              },
              tooltip: {
                callbacks: {
                  label: ctx => `${ctx.dataset.label}: Rp ${Number(ctx.raw).toLocaleString('id-ID')}`,
                },
              },
              title: {
                display: true,
                text: 'Perbandingan Anggaran ATK',
                font: { size: 16, weight: 'bold' },
                color: '#111827',
              },
            },
            scales: {
              x: {
                ticks: {
                  color: '#374151',
                  maxRotation: 60,
                  minRotation: 45,
                  autoSkip: false,
                  font: { size: 11 },
                },
                grid: { display: false },
              },
              y: {
                beginAtZero: true,
                ticks: {
                  callback: val => 'Rp ' + val.toLocaleString('id-ID'),
                  color: '#6b7280',
                  font: { size: 12 },
                },
                grid: { color: '#e5e7eb' },
                title: {
                  display: true,
                  text: 'Jumlah (Rupiah)',
                  color: '#374151',
                  font: { size: 13, weight: 'bold' },
                },
              },
            },
          },
        });
      } catch (error) {
        console.error('Gagal mengambil data alat:', error);
      }
    },
    async renderChartBarangStatusPerSemester() {
      const semesterData = {};

      this.requestList.forEach(req => {
        const tanggal = new Date(req.tanggal_permintaan);
        const semester = tanggal.getMonth() + 1 <= 6 ? "Semester 1" : "Semester 2";
        const barang = req.alat?.nama_barang || "Tanpa Nama";
        const status = req.status;

        const validStatuses = [
          "approved", "rejected", "purchasing", "on_the_way", "done",
          "waiting_approval_1", "waiting_approval_2", "waiting_approval_3"
        ];

        if (!validStatuses.includes(status)) return;

        const key = semester === "Semester 2" ? `${barang} (S2)` : barang;


        if (!semesterData[key]) {
          // Inisialisasi semua status ke 0
          semesterData[key] = {};
          validStatuses.forEach(s => {
            semesterData[key][s] = 0;
          });
        }

        semesterData[key][status]++;
      });

      const labels = Object.keys(semesterData).sort((a, b) => {
        const [semA, namaA] = a.split(" - ");
        const [semB, namaB] = b.split(" - ");

        if (semA === semB) return namaA.localeCompare(namaB);
        return semA === "Semester 1" ? -1 : 1;
      });

      const statusList = [
        "waiting_approval_1",
        "waiting_approval_2",
        "waiting_approval_3",
        "approved",
        "rejected",
        "purchasing",
        "on_the_way",
        "done"
      ];

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
        waiting_approval_1: "#c084fc", // ungu
        waiting_approval_2: "#f472b6", // pink
        waiting_approval_3: "#fb923c", // oranye
        approved: "#34d399",           // hijau
        rejected: "#f87171",           // merah
        purchasing: "#facc15",         // kuning
        on_the_way: "#60a5fa",         // biru
        done: "#a3a3a3",               // abu
      };


      const datasets = statusList.map(status => ({
        label: statusLabelMap[status],
        backgroundColor: statusColorMap[status] + "CC", // transparan
        borderColor: statusColorMap[status],
        borderWidth: 1,
        data: labels.map(label => semesterData[label][status] || 0),
      }));

      const ctx = document.getElementById("chartBarangStatusPerSemester").getContext("2d");

      if (this.chartBarangStatusSemesterInstance) {
        this.chartBarangStatusSemesterInstance.destroy();
      }

      this.chartBarangStatusSemesterInstance = new Chart(ctx, {
        type: "bar",
        data: {
          labels,
          datasets,
        },
        options: {
          plugins: {
            legend: { position: "top" },
          },
          responsive: true,
          scales: {
            x: {
              stacked: true,
              title: {
                display: true,
                text: 'Nama Barang',
              }
            },
            y: {
              stacked: true,
              beginAtZero: true,
              title: {
                display: true,
                text: 'Jumlah Pengajuan',
              }
            }
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

        // Warna status
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

        // Header style
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

        // Grup data
        this.requestList.forEach(req => {
          const tanggal = new Date(req.tanggal_permintaan);
          const semester = tanggal.getMonth() + 1 <= 6 ? "Semester 1" : "Semester 2";
          const barang = req.alat?.nama_barang || "Tanpa Nama";
          const status = req.status;

          if (!validStatuses.includes(status)) return;

          const key = `${barang}|||${status}`;
          if (!dataPerSemester[semester][key]) {
            dataPerSemester[semester][key] = 0;
          }

          dataPerSemester[semester][key]++;
        });

        // Tulis ke Excel per semester
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
                // Warnai kolom status
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
      if (this.chartBarangStatusSemesterInstance) {
        const link = document.createElement('a');
        link.href = this.chartBarangStatusSemesterInstance.toBase64Image();
        link.download = `Grafik-Barang-Semester-${new Date().toISOString().slice(0, 10)}.png`;
        link.click();
      }
    },
    renderChartPerSemester() {
      const semesterStatusData = {
        "Semester 1": {
          approved: 0, rejected: 0, purchasing: 0, on_the_way: 0, done: 0,
          waiting_approval_1: 0, waiting_approval_2: 0, waiting_approval_3: 0
        },
        "Semester 2": {
          approved: 0, rejected: 0, purchasing: 0, on_the_way: 0, done: 0,
          waiting_approval_1: 0, waiting_approval_2: 0, waiting_approval_3: 0
        },
      };

      this.requestList.forEach(req => {
        const status = req.status;
        const date = new Date(req.tanggal_permintaan);
        const month = date.getMonth() + 1;
        const semester = month <= 6 ? "Semester 1" : "Semester 2";

        if (semesterStatusData[semester][status] !== undefined) {
          semesterStatusData[semester][status]++;
        }
      });

      const labels = Object.keys(semesterStatusData);

      const datasets = [
        {
          label: "Approval 1",
          data: labels.map(label => semesterStatusData[label].waiting_approval_1),
          backgroundColor: "#c084fc",
        },
        {
          label: "Approval 2",
          data: labels.map(label => semesterStatusData[label].waiting_approval_2),
          backgroundColor: "#f472b6",
        },
        {
          label: "Approval 3",
          data: labels.map(label => semesterStatusData[label].waiting_approval_3),
          backgroundColor: "#fb923c",
        },
        {
          label: "Approved",
          data: labels.map(label => semesterStatusData[label].approved),
          backgroundColor: "#34d399",
        },
        {
          label: "Rejected",
          data: labels.map(label => semesterStatusData[label].rejected),
          backgroundColor: "#f87171",
        },
        {
          label: "Purchasing",
          data: labels.map(label => semesterStatusData[label].purchasing),
          backgroundColor: "#facc15",
        },
        {
          label: "On The Way",
          data: labels.map(label => semesterStatusData[label].on_the_way),
          backgroundColor: "#60a5fa",
        },
        {
          label: "Done",
          data: labels.map(label => semesterStatusData[label].done),
          backgroundColor: "#a3a3a3",
        },
      ];

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
              stepSize: 1,
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

      const semesterStatusData = {
        "Semester 1": {},
        "Semester 2": {}
      };

      this.requestList.forEach(req => {
        const status = req.status;
        const date = new Date(req.tanggal_permintaan);
        const month = date.getMonth() + 1;
        const semester = month <= 6 ? "Semester 1" : "Semester 2";

        if (!semesterStatusData[semester][status]) {
          semesterStatusData[semester][status] = 0;
        }
        semesterStatusData[semester][status]++;
      });

      for (const [semester, statuses] of Object.entries(semesterStatusData)) {
        for (const [statusKey, jumlah] of Object.entries(statuses)) {
          const row = worksheet.addRow({
            semester,
            status: statusLabelMap[statusKey],
            jumlah
          });

          const fillColor = statusColorMap[statusKey];
          row.getCell("status").fill = {
            type: "pattern",
            pattern: "solid",
            fgColor: { argb: fillColor }
          };
        }
        worksheet.addRow({});
      }

      worksheet.getRow(1).font = { bold: true };
      const buffer = await workbook.xlsx.writeBuffer();
      const filename = `Pengajuan-PerSemester-${new Date().toISOString().slice(0, 10)}.xlsx`;
      saveAs(new Blob([buffer]), filename);
    },
    async fetchRequest() {
      try {
        const token = localStorage.getItem("token");
        const res = await axios.get(
          "http://localhost:8000/api/request",
          {
            headers: { Authorization: `Bearer ${token}` },
          }
        );
        this.requestList = res.data.data;
        this.renderChartPerSemester();
        this.renderChartBarangStatusPerSemester();

      } catch (err) {
        console.error("Gagal mengambil data request:", err);
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
  },
};
</script>


<style scoped>
canvas {
  max-height: 400px;
}
</style>
