<template>
  <div class="flex flex-col md:flex-row min-h-screen bg-gray-100">
    <Sidebar :activeMenu="activeMenu" @update:activeMenu="updateActiveMenu" />
    <div class="flex-1 p-4 sm:p-6 md:p-8 pt-4 bg-white overflow-auto">
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
              <option value="ATK Tidak Digunakan">ATK Tidak Digunakan</option>
            </select>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow border border-gray-300 mt-8 overflow-hidden">
          <div class="flex flex-wrap gap-2 justify-between items-center px-5 p-3 border-b border-gray-300">
            <h3 class="text-sm font-semibold text-gray-900">Data ATK</h3>

            <div class="flex flex-wrap gap-2">
              <router-link to="/alat-pemakaian"
                class="px-3 py-1 rounded bg-[#08607a] text-white text-sm hover:bg-[#074a5d]">
                Pemakaian Alat
              </router-link>

              <router-link to="/alat-stock"
                class="px-3 py-1 rounded bg-[#08607a] text-white text-sm hover:bg-[#074a5d]">
                Manajemen Stock
              </router-link>

              <button v-if="tingkatanOtoritas === 'admin' || tingkatanOtoritas === 'superadmin'" @click="downloadExcel"
                class="cursor-pointer px-3 py-1 rounded bg-[#08607a] text-white text-sm hover:bg-[#074a5d]">
                Download Excel
              </button>

              <router-link v-if="tingkatanOtoritas === 'admin' || tingkatanOtoritas === 'superadmin'" to="/alat-add"
                class="text-sm font-semibold text-[#074a5d] no-underline hover:text-[#0066cc] hover:no-underline">
                Tambah ATK
              </router-link>
            </div>
          </div>

          <!-- Admin/Superadmin Table -->
          <div class="overflow-x-auto">
            <table v-if="tingkatanOtoritas === 'admin' || tingkatanOtoritas === 'superadmin'"
              class="min-w-full table-auto border-collapse border border-gray-300">
              <thead class="bg-gray-100 text-[#7d7f81]">
                <tr>
                  <th class="p-3 border">Nama Barang</th>
                  <th class="p-3 border">Stock Min</th>
                  <th class="p-3 border">Stock Max</th>
                  <th class="p-3 border">Stock Sekarang</th>
                  <th class="p-3 border">Harga Satuan</th>
                  <th class="p-3 border">Rekomendasi Pembelian</th>
                  <th class="p-3 border">Aksi</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(alat, index) in paginatedAlatList" :key="alat.id" class="text-[#333436]">
                  <td class="p-3">{{ alat.nama_barang }}</td>
                  <td class="p-3">{{ alat.stock_min }}</td>
                  <td class="p-3">{{ alat.stock_max }}</td>
                  <td class="p-3">{{ alat.stock }}</td>
                  <td class="p-3">{{ formatRupiah(alat.harga_satuan) }}</td>
                  <td class="p-3">
                    <span v-if="alat.stock_min === 0 && alat.stock_max === 0 && alat.stock === 0"
                      class="text-gray-500 italic">ATK Tidak Digunakan</span>
                    <span v-else-if="alat.stock <= alat.stock_min" class="text-red-600 font-semibold">Perlu
                      Pengajuan</span>
                    <span v-else class="text-green-600">Aman</span>
                  </td>
                  <td class="p-3">
                    <div class="flex flex-wrap justify-center items-center gap-2 sm:space-x-2">
                      <!-- Tombol Informasi -->
                      <button title="Informasi" @click="navigateTo('info', alat)"
                        class="cursor-pointer hover:opacity-70">
                        <img :src="informasiIcon" alt="Informasi" class="w-5 h-5 object-contain" />
                      </button>

                      <!-- Tombol Edit (Hanya untuk Admin/Superadmin) -->
                      <button title="Edit" @click="navigateTo('edit', alat)"
                        v-if="tingkatanOtoritas === 'admin' || tingkatanOtoritas === 'superadmin'"
                        class="cursor-pointer hover:opacity-70 border-l sm:border-l pl-2 sm:pl-2 border-transparent sm:border-gray-300">
                        <img :src="updateIcon" alt="Update" class="w-5 h-5 object-contain" />
                      </button>

                      <!-- Tombol Hapus (Hanya untuk Admin/Superadmin) -->
                      <button title="Hapus" @click="confirmDelete(alat)"
                        v-if="tingkatanOtoritas === 'admin' || tingkatanOtoritas === 'superadmin'"
                        class="cursor-pointer hover:opacity-70 border-l sm:border-l pl-2 sm:pl-2 border-transparent sm:border-gray-300">
                        <img :src="deleteIcon" alt="Delete" class="w-5 h-5 object-contain" />
                      </button>
                    </div>
                  </td>

                </tr>
              </tbody>
            </table>

            <!-- Table for User/Manajer -->
            <table v-else class="min-w-full table-auto border-collapse border border-gray-300">
              <thead class="bg-gray-100 text-[#7d7f81]">
                <tr>
                  <th class="p-3 border">Nama Barang</th>
                  <th class="p-3 border">Stock Min</th>
                  <th class="p-3 border">Stock Max</th>
                  <th class="p-3 border">Stock</th>
                  <th class="p-3 border">Pusat Stock</th>
                  <th class="p-3 border">Rekomendasi</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(alat, index) in paginatedUserAlatList" :key="alat.id" class="text-[#333436]">
                  <td class="p-3">{{ alat.nama_barang }}</td>
                  <td class="p-3">{{ alat.stock_min }}</td>
                  <td class="p-3">{{ alat.stock_max }}</td>
                  <td class="p-3">{{ alat.stock }}</td>
                  <td class="p-3">{{ alat.pusat_stock }}</td>
                  <td class="p-3">
                    <span v-if="alat.stock_min === 0 && alat.stock_max === 0 && alat.stock === 0"
                      class="text-gray-500 italic">ATK Tidak Digunakan</span>
                    <span v-else-if="alat.stock <= alat.stock_min" class="text-red-600 font-semibold">Perlu
                      Pengajuan</span>
                    <span v-else class="text-green-600">Aman</span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Pagination -->
          <div class="flex justify-between items-center px-4 py-3 border-t border-gray-300 text-sm text-[#333436]">
            <button @click="prevPage" :disabled="currentPage === 1"
              class="px-3 py-1 rounded bg-gray-200 hover:bg-gray-300 disabled:opacity-50">
              Prev
            </button>
            <span>
              Halaman {{ currentPage }} dari
              {{ tingkatanOtoritas === 'admin' || tingkatanOtoritas === 'superadmin' ? totalPages : totalPagesUser }}
            </span>
            <button @click="nextPage"
              :disabled="currentPage === (tingkatanOtoritas === 'admin' || tingkatanOtoritas === 'superadmin' ? totalPages : totalPagesUser)"
              class="px-3 py-1 rounded bg-gray-200 hover:bg-gray-300 disabled:opacity-50">
              Next
            </button>
          </div>
        </div>
      </div>
    </div>

    <SuccessAlert :visible="showSuccessAlert" :message="successMessage" />
    <ModalConfirm :visible="showModal" title="Konfirmasi Hapus Data"
      message="Apakah Anda yakin ingin menghapus data ini?" @cancel="cancelDelete" @confirm="deleteAlat" />
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
// This component manages the inventory of tools (ATK) in the application.
export default {
  name: "ManajemenAlat",
  components: { Sidebar, HeaderBar, ModalConfirm, SuccessAlert },

  data() {
    return {
      activeMenu: "manajemenAlat",
      searchQuery: "",
      showModal: false,
      showSuccessAlert: false,
      rekomendasiFilter: "",
      successMessage: "",
      alatToDelete: null,
      tingkatanOtoritas: "",
      alatList: [],
      userAlatList: [],

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

          const rekomendasi = this.getRekomendasiStatus(a);

          const rekomendasiMatch = !this.rekomendasiFilter || this.rekomendasiFilter === rekomendasi;

          return searchMatch && rekomendasiMatch;
        })
        .sort((a, b) => {
          const statusOrder = { 'perlu': 1, 'aman': 2, 'ATK Tidak Digunakan': 3 };

          const aStatus = this.getRekomendasiStatus(a);
          const bStatus = this.getRekomendasiStatus(b);

          return statusOrder[aStatus] - statusOrder[bStatus];
        });
    },

    filteredUserAlatList() {
      return this.userAlatList
        .filter(a => {
          const searchMatch = !this.searchQuery ||
            a.nama_barang.toLowerCase().includes(this.searchQuery.toLowerCase()) ||
            (a.keterangan && a.keterangan.toLowerCase().includes(this.searchQuery.toLowerCase()));

          const rekomendasi = this.getRekomendasiStatus(a);

          const rekomendasiMatch = !this.rekomendasiFilter || this.rekomendasiFilter === rekomendasi;

          return searchMatch && rekomendasiMatch;
        })
        .sort((a, b) => {
          const statusOrder = { 'perlu': 1, 'aman': 2, 'ATK Tidak Digunakan': 3 };

          const aStatus = this.getRekomendasiStatus(a);
          const bStatus = this.getRekomendasiStatus(b);

          return statusOrder[aStatus] - statusOrder[bStatus];
        });
    },

    paginatedAlatList() {
      const start = (this.currentPage - 1) * this.itemsPerPage;
      return this.filteredAlatList.slice(start, start + this.itemsPerPage);
    },
    paginatedUserAlatList() {
      // if (!Array.isArray(this.userAlatList)) return [];
      const start = (this.currentPage - 1) * this.itemsPerPage;
      return this.filteredUserAlatList.slice(start, start + this.itemsPerPage);
    },


    totalPages() {
      return Math.ceil(this.filteredAlatList.length / this.itemsPerPage);
    },

    totalPagesUser() {
      return Math.ceil(this.userAlatList.length / this.itemsPerPage);
    }

  },

  created() {
    this.getUserInfo();
    this.fetchAlat();
    this.fetchUserAlat();
  },

  methods: {
    getRekomendasiStatus(alat) {
      if (alat.stock_min === 0 && alat.stock_max === 0 && alat.stock === 0) {
        return 'ATK Tidak Digunakan';
      } else if (alat.stock <= alat.stock_min) {
        return 'perlu';
      } else {
        return 'aman';
      }
    },

    async downloadExcel() {
      const workbook = new ExcelJS.Workbook();
      const worksheet = workbook.addWorksheet('Data ATK');

      worksheet.columns = [
        { header: 'No', key: 'no', width: 5 },
        { header: 'Nama Barang', key: 'nama_barang', width: 25 },
        { header: 'Stock Min', key: 'stock_min', width: 12 },
        { header: 'Stock Max', key: 'stock_max', width: 12 },
        { header: 'Stock Sekarang', key: 'stock', width: 15 },
        { header: 'Harga Satuan', key: 'harga_satuan', width: 18 },
        { header: 'Rekomendasi Pembelian', key: 'rekomendasi', width: 22 },
      ];

      worksheet.getRow(1).eachCell(cell => {
        cell.font = { bold: true, color: { argb: 'FFFFFFFF' } };
        cell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FF4F46E5' } };
        cell.alignment = { vertical: 'middle', horizontal: 'center' };
        cell.border = { top: { style: 'thin' }, left: { style: 'thin' }, bottom: { style: 'thin' }, right: { style: 'thin' } };
      });

      // GUNAKAN DATA BERDASARKAN OTORITAS
      const isAdmin = this.tingkatanOtoritas !== 'user';
      const sourceData = isAdmin ? this.filteredAlatList : this.filteredUserAlatList;

      const groupedData = {
        'Perlu Pengajuan': [],
        'Aman': [],
        'ATK Tidak Digunakan': [],
      };

      sourceData.forEach(alat => {
        const status = this.getRekomendasiStatus(alat);
        const statusText =
          status === 'perlu' ? 'Perlu Pengajuan' :
            status === 'aman' ? 'Aman' : 'ATK Tidak Digunakan';
        groupedData[statusText].push(alat);
      });

      let rowIndex = 2;
      let globalNo = 1;

      const statusOrder = ['Perlu Pengajuan', 'Aman', 'ATK Tidak Digunakan'];

      statusOrder.forEach(statusName => {
        const items = groupedData[statusName];
        if (items.length === 0) return;

        worksheet.mergeCells(`A${rowIndex}:G${rowIndex}`);
        const statusCell = worksheet.getCell(`A${rowIndex}`);
        statusCell.value = `Status: ${statusName}`;
        statusCell.font = { bold: true, color: { argb: 'FFFFFFFF' } };

        let bgColor = 'FF10B981';
        if (statusName === 'Perlu Pengajuan') bgColor = 'FFDC3545';
        if (statusName === 'ATK Tidak Digunakan') bgColor = 'FF6C757D';

        statusCell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: bgColor } };
        statusCell.alignment = { vertical: 'middle', horizontal: 'center' };
        statusCell.border = { top: { style: 'thin' }, left: { style: 'thin' }, bottom: { style: 'thin' }, right: { style: 'thin' } };
        rowIndex++;

        items.forEach(alat => {
          const rekomendasiText =
            this.getRekomendasiStatus(alat) === 'perlu' ? 'Perlu Pengajuan' :
              this.getRekomendasiStatus(alat) === 'aman' ? 'Aman' : 'ATK Tidak Digunakan';

          const row = worksheet.addRow({
            no: globalNo++,
            nama_barang: alat.nama_barang,
            stock_min: alat.stock_min,
            stock_max: alat.stock_max,
            stock: alat.stock,
            harga_satuan: alat.harga_satuan,
            rekomendasi: rekomendasiText,
          });

          row.eachCell((cell, colNumber) => {
            cell.alignment = { vertical: 'middle', horizontal: 'center' };
            cell.border = { top: { style: 'thin' }, left: { style: 'thin' }, bottom: { style: 'thin' }, right: { style: 'thin' } };

            if (colNumber === 6) {
              cell.numFmt = '"Rp"#,##0';
            }
          });

          const rekomCell = row.getCell('rekomendasi');
          if (rekomendasiText === 'Perlu Pengajuan') {
            rekomCell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FFDC3545' } };
            rekomCell.font = { color: { argb: 'FFFFFFFF' }, bold: true };
          } else if (rekomendasiText === 'Aman') {
            rekomCell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FF28A745' } };
            rekomCell.font = { color: { argb: 'FFFFFFFF' }, bold: true };
          } else {
            rekomCell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FF6C757D' } };
            rekomCell.font = { color: { argb: 'FFFFFFFF' }, bold: true };
          }

          rowIndex++;
        });

        rowIndex++;
      });

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
    async fetchUserAlat() {
      try {
        const token = localStorage.getItem("token");
        const userData = JSON.parse(localStorage.getItem("user"));
        const idPenempatan = userData.id_penempatan_fk;

        const res = await axios.get(`http://localhost:8000/api/alat-penempatan/${idPenempatan}`, {
          headers: { Authorization: `Bearer ${token}` }
        });
        this.userAlatList = res.data.data?.[0]?.barang || [];
      } catch (err) {
        console.error("Gagal mengambil data alat berdasarkan penempatan:", err);
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
    navigateTo(action, alat) {
      localStorage.setItem(`dataAlat${action}`, JSON.stringify(alat));
      this.$router.push(`/alat-${action}/${alat.id_alat}`);
    },
    confirmDelete(alat) {
      this.alatToDelete = alat;
      this.showModal = true;
    },
    cancelDelete() {
      this.alatToDelete = null;
      this.showModal = false;
    },
    async deleteAlat() {
      try {
        const token = localStorage.getItem("token");

        const res = await axios.delete(
          `http://localhost:8000/api/alat/${this.alatToDelete.id_alat}`,
          {
            headers: { Authorization: `Bearer ${token}` }
          }
        );

        // ✅ Tangani berbagai jenis status respon
        if (res.data.status === "info") {
          this.successMessage = res.data.message; // Sudah dinonaktifkan sebelumnya
        } else if (res.data.status === "success") {
          this.successMessage = res.data.message; // Berhasil hapus atau nonaktif
        }

        this.showSuccessAlert = true;
        setTimeout(() => (this.showSuccessAlert = false), 2000);

        this.fetchAlat();
      } catch (err) {
        console.error("Gagal menghapus alat:", err);
      } finally {
        this.cancelDelete();
      }
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
  padding: 8px 10px;
  /* diperkecil */
  text-align: center;
  font-size: 12px;
  /* perkecil font */
  border: 1px solid #ccc;
  word-wrap: break-word;
}
</style>
