<template>
  <div class="flex h-screen bg-gray-100">
    <Sidebar :activeMenu="activeMenu" @update:activeMenu="updateActiveMenu" />
    <div class="flex-1 p-8 pt-4 bg-white">
      <HeaderBar title="Manajemen ATK" class="mt-3" />
      <div class="my-4 border-b border-gray-300"></div>

      <div class="pb-12">
        <div class="filters flex flex-wrap gap-4 mb-4">
          <div class="relative flex-1 min-w-[200px]">
            <input type="text" v-model="searchQuery" @input="onInputSearch" placeholder="Cari Nama Barang..."
              class="w-full border border-gray-300 rounded-md py-2 pl-10 pr-4 text-sm text-gray-700" />
            <img src="@/assets/search.svg" alt="Search"
              class="w-5 h-5 absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400" />
          </div>

          <div class="relative flex-1 min-w-[200px]">
            <select v-model="rekomendasiFilter"
              class="w-full border border-gray-300 rounded-md py-2 pl-3 pr-4 text-sm text-gray-700">
              <option value="">Semua Rekomendasi</option>
              <option value="perlu">Perlu Pengajuan</option>
              <option value="aman">Aman</option>
            </select>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow border border-gray-300 mt-8 overflow-hidden">
          <div class="flex justify-between items-center px-5 p-3 border-b border-gray-300">
            <h3 class="text-sm font-semibold text-gray-900">Data ATK</h3>

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

          <table class="w-full table-fixed border-collapse border border-gray-300">
            <thead class="bg-gray-100 text-[#7d7f81]">
              <tr>
                <th class="w-14">No</th>
                <th class="p-3 border">Nama Barang</th>
                <th class="p-3 border">Stock Min</th>
                <th class="p-3 border">Stock Max</th>
                <th class="p-3 w-25 border">Stock Sekarang</th>
                <th class="p-3 border">Harga Satuan</th>
                <th class="p-3 border">Rekomendasi Pembelian</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(alat, index) in paginatedAlatList" :key="alat.id" class="text-[#333436]">
                <td class="p-3">{{ (currentPage - 1) * itemsPerPage + index + 1 }}</td>
                <td class="p-3">{{ alat.nama_barang }}</td>
                <td class="p-3">{{ alat.stock_min }}</td>
                <td class="p-3">{{ alat.stock_max }}</td>
                <td class="p-3">{{ alat.stock }}</td>
                <td class="p-3">{{ formatRupiah(alat.harga_satuan) }}</td>
                <td class="p-3">
                  <span v-if="alat.stock <= alat.stock_min" class="text-red-600 font-semibold">Perlu Pengajuan</span>
                  <span v-else class="text-green-600">Aman</span>
                </td>
              </tr>
            </tbody>
          </table>

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
import ModalConfirm from "@/components/ModalConfirm.vue";
import SuccessAlert from "@/components/SuccessAlert.vue";
import informasiIcon from "@/assets/Informasi.svg";
import updateIcon from "@/assets/Edit.svg";
import deleteIcon from "@/assets/Delete.svg";
import axios from "axios";
import ExcelJS from 'exceljs';
import { saveAs } from 'file-saver';
export default {
  name: "ManajemenAlat",
  components: { Sidebar, HeaderBar, ModalConfirm, SuccessAlert },

  data() {
    return {
      activeMenu: "laporanATK",
      searchQuery: "",
      rekomendasiFilter: "",
      successMessage: "",
      tingkatanOtoritas: "",
      alatList: [],
      informasiIcon,
      updateIcon,
      deleteIcon,
      currentPage: 1,
      itemsPerPage: 10,
    };
  },

  computed: {
    filteredAlatList() {
      return this.alatList
        .filter(a => {
          const searchMatch = !this.searchQuery ||
            a.nama_barang.toLowerCase().includes(this.searchQuery.toLowerCase()) ||
            (a.keterangan && a.keterangan.toLowerCase().includes(this.searchQuery.toLowerCase()));
          const rekomendasiMatch = !this.rekomendasiFilter ||
            (this.rekomendasiFilter === 'perlu' && a.stock <= a.stock_min) ||
            (this.rekomendasiFilter === 'aman' && a.stock > a.stock_min);
          return searchMatch && rekomendasiMatch;
        })
        .sort((a, b) => a.stock - b.stock);
    },
    paginatedAlatList() {
      const start = (this.currentPage - 1) * this.itemsPerPage;
      return this.filteredAlatList.slice(start, start + this.itemsPerPage);
    },
    totalPages() {
      return Math.ceil(this.filteredAlatList.length / this.itemsPerPage);
    },
  },

  created() {
    this.getUserInfo();
    this.fetchAlat();
  },

  methods: {
    async downloadExcel() {
      const workbook = new ExcelJS.Workbook();
      const worksheet = workbook.addWorksheet('Data ATK');

      // Define Columns
      worksheet.columns = [
        { header: 'No', key: 'no', width: 5 },
        { header: 'Nama Barang', key: 'nama_barang', width: 25 },
        { header: 'Stock Min', key: 'stock_min', width: 12 },
        { header: 'Stock Max', key: 'stock_max', width: 12 },
        { header: 'Stock Sekarang', key: 'stock', width: 15 },
        { header: 'Harga Satuan', key: 'harga_satuan', width: 18 },
        { header: 'Rekomendasi Pembelian', key: 'rekomendasi', width: 22 },
      ];

      // Style Header
      worksheet.getRow(1).eachCell(cell => {
        cell.font = { bold: true, color: { argb: 'FFFFFFFF' } };
        cell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FF4F46E5' } };
        cell.alignment = { vertical: 'middle', horizontal: 'center' };
        cell.border = { top: { style: 'thin' }, left: { style: 'thin' }, bottom: { style: 'thin' }, right: { style: 'thin' } };
      });

      // Group Data by Kategori
      const groupedData = {};
      this.filteredAlatList.forEach(alat => {
        const kategori = alat.kategori?.nama_kategori || 'Tanpa Kategori';
        if (!groupedData[kategori]) groupedData[kategori] = [];
        groupedData[kategori].push(alat);
      });

      let rowIndex = 2;
      let globalNo = 1;

      for (const [kategoriName, items] of Object.entries(groupedData)) {
        // Row Kategori
        worksheet.mergeCells(`A${rowIndex}:G${rowIndex}`);
        const kategoriCell = worksheet.getCell(`A${rowIndex}`);
        kategoriCell.value = `Kategori: ${kategoriName}`;
        kategoriCell.font = { bold: true, color: { argb: 'FFFFFFFF' } };
        kategoriCell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FF10B981' } }; // Hijau
        kategoriCell.alignment = { vertical: 'middle', horizontal: 'center' };
        kategoriCell.border = { top: { style: 'thin' }, left: { style: 'thin' }, bottom: { style: 'thin' }, right: { style: 'thin' } };
        rowIndex++;

        items.forEach(alat => {
          const rekomendasiText = alat.stock <= alat.stock_min ? 'Perlu Pengajuan' : 'Aman';
          const row = worksheet.addRow({
            no: globalNo++,
            nama_barang: alat.nama_barang,
            stock_min: alat.stock_min,
            stock_max: alat.stock_max,
            stock: alat.stock,
            harga_satuan: alat.harga_satuan,  // Tetap angka
            rekomendasi: rekomendasiText,
          });

          row.eachCell((cell, colNumber) => {
            cell.alignment = { vertical: 'middle', horizontal: 'center' };
            cell.border = { top: { style: 'thin' }, left: { style: 'thin' }, bottom: { style: 'thin' }, right: { style: 'thin' } };

            if (colNumber === 6) {
              cell.numFmt = '"Rp"#,##0'; // Format Harga
            }
          });

          const rekomendasiCell = row.getCell('rekomendasi');
          if (rekomendasiText === 'Perlu Pengajuan') {
            rekomendasiCell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FFDC3545' } }; // Merah
            rekomendasiCell.font = { color: { argb: 'FFFFFFFF' }, bold: true };
          } else {
            rekomendasiCell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FF28A745' } }; // Hijau
            rekomendasiCell.font = { color: { argb: 'FFFFFFFF' }, bold: true };
          }

          rowIndex++;
        });

        rowIndex++;
      }

      const buffer = await workbook.xlsx.writeBuffer();
      saveAs(new Blob([buffer]), `Data-ATK-${new Date().toISOString().slice(0, 10)}.xlsx`);
    },



    formatRupiah(angka) {
      if (!angka) return "-";
      return "Rp. " + angka.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    },
    async getUserInfo() {
      try {
        const token = localStorage.getItem("token");
        const res = await axios.post("http://localhost:8000/api/me", {}, {
          headers: { Authorization: `Bearer ${token}` }
        });
        this.tingkatanOtoritas = res.data.tingkatan_otoritas;
      } catch (err) {
        console.error("Gagal mengambil data user:", err);
      }
    },
    async fetchAlat() {
      try {
        const token = localStorage.getItem("token");
        const res = await axios.get("http://localhost:8000/api/alat", {
          headers: { Authorization: `Bearer ${token}` }
        });
        this.alatList = res.data.data;
      } catch (err) {
        console.error("Gagal mengambil data alat:", err);
      }
    },
    updateActiveMenu(menu) {
      this.activeMenu = menu;
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
  },
};
</script>

<style scoped>
th,
td {
  padding: 12px 16px;
  text-align: center;
  font-size: 14px;
  border: 1px solid #ccc;
  word-wrap: break-word;
}
</style>
