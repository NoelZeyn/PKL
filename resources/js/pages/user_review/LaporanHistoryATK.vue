<template>
  <div class="flex min-h-screen bg-gray-100">
    <Sidebar :activeMenu="activeMenu" @update:activeMenu="activeMenu = $event" />
    <div class="flex-1 p-4 sm:p-6 md:p-8 pt-4 bg-white overflow-auto">
      <HeaderBar title="Riwayat Manajemen ATK" />
      <div class="my-4 border-b border-gray-300"></div>

      <div class="pb-12">
        <div class="filters space-y-4">
          <div class="relative">
            <input type="text" v-model="searchQuery" @input="onInputSearch" placeholder="Cari Barang atau Admin..."
              class="w-full border border-gray-300 rounded-md py-2 pl-10 pr-4 text-sm text-gray-700" />
            <img src="@/assets/search.svg" alt="Search"
              class="w-5 h-5 absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400" />
          </div>
        </div>

        <div class="bg-white rounded-lg shadow border border-gray-300 mt-8 overflow-hidden">
          <div class="flex justify-between items-center px-5 p-3 border-b border-gray-300">
            <h3 class="text-sm font-semibold text-gray-900">Riwayat Perubahan Data ATK</h3>

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
          <div class="overflow-x-auto">

            <table class="min-w-full table-fixed border-collapse border border-gray-300">
              <thead class="bg-gray-100 text-[#7d7f81]">
                <tr>
                  <th class="w-16">No</th>
                  <th class="p-3 border">Nama Barang</th>
                  <th class="p-3 border">Admin</th>
                  <th class="p-3 border">Aksi</th>
                  <th class="p-3 border">Deskripsi</th>
                  <th class="p-3 border">Tanggal</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(item, index) in paginatedHistory" :key="index" class="text-[#333436]">
                  <td class="p-3">{{ (currentPage - 1) * itemsPerPage + index + 1 }}</td>
                  <td class="p-3">{{ item.nama_barang }}</td>
                  <td class="p-3">{{ item.nama_admin }}</td>
                  <td class="p-3 capitalize">{{ item.jenis_aksi }}</td>
                  <td class="p-3">{{ item.deskripsi || '-' }}</td>
                  <td class="p-3">{{ formatTanggal(item.tanggal) }}</td>
                </tr>
                <tr v-if="paginatedHistory.length === 0">
                  <td colspan="6" class="text-center p-4 text-gray-500">Data tidak ditemukan</td>
                </tr>
              </tbody>
            </table>
          </div>

          <div class="flex justify-between items-center px-4 py-3 border-t border-gray-300 text-sm text-[#333436]">
            <button @click="prevPage" :disabled="currentPage === 1"
              class="px-3 py-1 rounded bg-gray-200 hover:bg-gray-300 disabled:opacity-50">Prev</button>
            <span>Halaman {{ currentPage }} dari {{ totalPages }}</span>
            <button @click="nextPage" :disabled="currentPage === totalPages"
              class="px-3 py-1 rounded bg-gray-200 hover:bg-gray-300 disabled:opacity-50">Next</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import Sidebar from "@/components/Sidebar.vue";
import HeaderBar from "@/components/HeaderBar.vue";
import axios from "axios";
import ExcelJS from 'exceljs';
import { saveAs } from 'file-saver';

export default {
  name: "HistoryDataATK",
  components: { Sidebar, HeaderBar },

  data() {
    return {
      activeMenu: "laporanHistoryATK",
      searchQuery: "",
      historyList: [],
      currentPage: 1,
      itemsPerPage: 10,
    };
  },

  computed: {
    filteredHistory() {
      return this.historyList.filter(item =>
        (item.nama_barang || '').toLowerCase().includes(this.searchQuery.toLowerCase()) ||
        (item.nama_admin || '').toLowerCase().includes(this.searchQuery.toLowerCase())
      );
    },
    paginatedHistory() {
      const start = (this.currentPage - 1) * this.itemsPerPage;
      return this.filteredHistory.slice(start, start + this.itemsPerPage);
    },
    totalPages() {
      return Math.ceil(this.filteredHistory.length / this.itemsPerPage) || 1;
    },
  },

  created() {
    this.fetchHistory();
  },

  methods: {
    fetchHistory() {
      const token = localStorage.getItem("token");
      axios.get("http://localhost:8000/api/history-atk", {
        headers: { Authorization: `Bearer ${token}` },
      }).then(res => {
        this.historyList = res.data.data.map(item => ({
          nama_barang: item.alat?.nama_barang || '-',
          nama_admin: item.admin?.data_diri?.nama_lengkap || '-',
          jenis_aksi: item.jenis_aksi || '-',
          deskripsi: item.deskripsi || '-',
          tanggal: item.tanggal,
        }));
      }).catch(err => console.error(err));
    },

    formatTanggal(dateString) {
      const date = new Date(dateString);
      return date.toLocaleDateString("id-ID", { day: '2-digit', month: '2-digit', year: 'numeric' });
    },

    nextPage() {
      if (this.currentPage < this.totalPages) this.currentPage++;
    },
    prevPage() {
      if (this.currentPage > 1) this.currentPage--;
    },
    onInputSearch() {
      this.currentPage = 1;
    },

    async downloadExcel() {
      const workbook = new ExcelJS.Workbook();
      const worksheet = workbook.addWorksheet('Riwayat Manajemen ATK');

      worksheet.columns = [
        { header: 'No', key: 'no', width: 5 },
        { header: 'Nama Barang', key: 'nama_barang', width: 25 },
        { header: 'Admin', key: 'nama_admin', width: 20 },
        { header: 'Aksi', key: 'aksi', width: 15 },
        { header: 'Deskripsi', key: 'deskripsi', width: 40 },
        { header: 'Tanggal', key: 'tanggal', width: 15 },
      ];

      // Header Styling
      worksheet.getRow(1).eachCell(cell => {
        cell.font = { bold: true, color: { argb: "FFFFFFFF" } };
        cell.fill = {
          type: "pattern",
          pattern: "solid",
          fgColor: { argb: "FF4F46E5" }, // ungu
        };
        cell.alignment = { vertical: "middle", horizontal: "center" };
        cell.border = {
          top: { style: "thin" },
          left: { style: "thin" },
          bottom: { style: "thin" },
          right: { style: "thin" },
        };
      });

      this.filteredHistory.forEach((item, index) => {
        const row = worksheet.addRow({
          no: index + 1,
          nama_barang: item.nama_barang,
          nama_admin: item.nama_admin,
          aksi: item.jenis_aksi,
          deskripsi: item.deskripsi,
          tanggal: this.formatTanggal(item.tanggal),
        });

        let bgColor = "FFFFFFFF"; // default putih
        if (item.jenis_aksi === "tambah") bgColor = "FFDCFCE7";        // hijau muda
        else if (item.jenis_aksi === "hapus" || item.jenis_aksi === "nonaktif") bgColor = "FFFEE2E2"; // merah muda
        else if (item.jenis_aksi === "edit") bgColor = "FFFEF9C3"; // kuning muda

        row.eachCell(cell => {
          cell.fill = {
            type: "pattern",
            pattern: "solid",
            fgColor: { argb: bgColor },
          };
          cell.alignment = { vertical: "middle", horizontal: "center", wrapText: true };
          cell.border = {
            top: { style: "thin" },
            left: { style: "thin" },
            bottom: { style: "thin" },
            right: { style: "thin" },
          };
        });
      });

      const buffer = await workbook.xlsx.writeBuffer();
      const filename = `History-Data-ATK-${new Date().toISOString().slice(0, 10)}.xlsx`;
      saveAs(new Blob([buffer]), filename);
    },
  },
};
</script>

<style scoped>
th,
td {
  padding: 8px 10px;
  text-align: center;
  font-size: 12px;
  border: 1px solid #ccc;
  word-wrap: break-word;
}
</style>
