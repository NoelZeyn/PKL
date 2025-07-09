<template>
  <div class="flex h-screen bg-gray-100">
    <Sidebar :activeMenu="activeMenu" @update:activeMenu="activeMenu = $event" />
    <div class="flex-1 p-8 pt-4 bg-white">
      <HeaderBar title="Riwayat Approval" />
      <div class="my-4 border-b border-gray-300"></div>

      <div class="pb-12">
        <div class="filters space-y-4">
          <div class="relative">
            <input
              type="text"
              v-model="searchQuery"
              @input="onInputSearch"
              placeholder="Cari Level atau NID..."
              class="w-full border border-gray-300 rounded-md py-2 pl-10 pr-4 text-sm text-gray-700"
            />
            <img src="@/assets/search.svg" alt="Search" class="w-5 h-5 absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400" />
          </div>
        </div>

        <div class="bg-white rounded-lg shadow border border-gray-300 mt-8 overflow-hidden">
          <div class="flex justify-between items-center px-5 p-3 border-b border-gray-300">
            <h3 class="text-sm font-semibold text-gray-900">Riwayat Approval</h3>

            <button
              @click="downloadExcel"
              class="text-sm font-semibold text-[#074a5d] hover:text-[#0066cc] cursor-pointer"
            >
              Download Excel
            </button>
          </div>

          <table class="w-full table-fixed border-collapse border border-gray-300">
            <thead class="bg-gray-100 text-[#7d7f81]">
              <tr>
                <th class="w-15">No</th>
                <th class="p-3 border">Level Approval</th>
                <th class="p-3 border">Status</th>
                <th class="p-3 border">Tanggal</th>
                <th class="p-3 border">Nama Approver</th>
                <th class="p-3 border">ID Request</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(item, index) in paginatedList" :key="item.id_approval" class="text-[#333436]">
                <td class="p-3">{{ (currentPage - 1) * itemsPerPage + index + 1 }}</td>
                <td class="p-3">{{ item.level_approval }}</td>
                <td class="p-3">
                  <span :class="[
                    'px-3 py-1 rounded-full text-xs font-semibold',
                    statusColor(item.status),
                  ]">
                    {{ item.status }}
                  </span>
                </td>
                <td class="p-3">{{ formatTanggal(item.tanggal) }}</td>
                <td class="p-3">{{ item.request?.status_by || '-' }}</td>
                <td class="p-3">{{ item.id_request_fk }}</td>
              </tr>
              <tr v-if="paginatedList.length === 0">
                <td colspan="6" class="text-center p-4 text-gray-500">Data tidak ditemukan</td>
              </tr>
            </tbody>
          </table>

          <div class="flex justify-between items-center px-4 py-3 border-t border-gray-300 text-sm text-[#333436]">
            <button @click="prevPage" :disabled="currentPage === 1" class="px-3 py-1 rounded bg-gray-200 hover:bg-gray-300 disabled:opacity-50">Prev</button>
            <span>Halaman {{ currentPage }} dari {{ totalPages }}</span>
            <button @click="nextPage" :disabled="currentPage === totalPages" class="px-3 py-1 rounded bg-gray-200 hover:bg-gray-300 disabled:opacity-50">Next</button>
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
  name: "RiwayatApproval",
  components: { Sidebar, HeaderBar },
  data() {
    return {
      activeMenu: "laporanApproval",
      searchQuery: "",
      approvalList: [],
      currentPage: 1,
      itemsPerPage: 10,
    };
  },
  computed: {
    filteredList() {
      return this.approvalList.filter(item =>
        item.level_approval.toLowerCase().includes(this.searchQuery.toLowerCase()) ||
        item.admin?.NID?.toLowerCase().includes(this.searchQuery.toLowerCase())
      );
    },
    paginatedList() {
      const start = (this.currentPage - 1) * this.itemsPerPage;
      return this.filteredList.slice(start, start + this.itemsPerPage);
    },
    totalPages() {
      return Math.ceil(this.filteredList.length / this.itemsPerPage) || 1;
    },
  },
  created() {
    this.fetchApprovals();
  },
  methods: {
    fetchApprovals() {
      const token = localStorage.getItem("token");
      axios.get("http://localhost:8000/api/history_approval", {
        headers: { Authorization: `Bearer ${token}` },
      }).then(res => {
        this.approvalList = res.data.data;
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
    statusColor(status) {
      return {
        approved: 'bg-green-200 text-green-800',
        rejected: 'bg-red-200 text-red-800',
        pending: 'bg-yellow-200 text-yellow-800',
      }[status] || 'bg-gray-200 text-gray-800';
    },
    async downloadExcel() {
      const workbook = new ExcelJS.Workbook();
      const worksheet = workbook.addWorksheet('Riwayat Approval');

      worksheet.columns = [
        { header: 'No', key: 'no', width: 5 },
        { header: 'Level Approval', key: 'level_approval', width: 25 },
        { header: 'Status', key: 'status', width: 15 },
        { header: 'Tanggal', key: 'tanggal', width: 15 },
        { header: 'NID Approver', key: 'nid', width: 20 },
        { header: 'ID Request', key: 'id_request', width: 15 },
      ];

      this.filteredList.forEach((item, index) => {
        worksheet.addRow({
          no: index + 1,
          level_approval: item.level_approval,
          status: item.status,
          tanggal: this.formatTanggal(item.tanggal),
          nid: item.admin?.NID || '-',
          id_request: item.id_request_fk,
        });
      });

      const buffer = await workbook.xlsx.writeBuffer();
      saveAs(new Blob([buffer]), 'Riwayat_Approval.xlsx');
    }
  }
};
</script>

<style scoped>
th, td {
  padding: 12px 16px;
  text-align: center;
  font-size: 14px;
  border: 1px solid #ccc;
  word-wrap: break-word;
}
</style>
