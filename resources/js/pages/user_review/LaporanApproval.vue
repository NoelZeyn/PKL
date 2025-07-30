<template>
        <div class="flex flex-col md:flex-row min-h-screen bg-gray-100">
    <Sidebar :activeMenu="activeMenu" @update:activeMenu="activeMenu = $event" />

    <div class="flex-1 p-4 lg:p-8 bg-white">
      <HeaderBar title="Riwayat Approval" />
      <div class="my-4 border-b border-gray-300"></div>

      <div class="pb-12">
        <!-- Filter Section -->
        <div class="filters flex flex-col lg:flex-row lg:items-center lg:space-x-4 space-y-4 lg:space-y-0">
          <!-- Search Input -->
          <div class="relative w-full">
            <input type="text" v-model="searchQuery" @input="onInputSearch"
              placeholder="Cari Level, Nama Approver, atau Status..."
              class="w-full border border-gray-300 rounded-md py-2 pl-10 pr-4 text-sm text-gray-700" />
            <img src="@/assets/search.svg" alt="Search"
              class="w-5 h-5 absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400" />
          </div>

          <!-- Status Dropdown -->
          <div class="w-full lg:w-60">
            <select v-model="selectedStatus" @change="onInputSearch"
              class="w-full border border-gray-300 rounded-md py-2 px-3 text-sm text-gray-700">
              <option value="">Semua Status</option>
              <option value="approved">Approved</option>
              <option value="rejected">Rejected</option>
              <option value="pending">Pending</option>
            </select>
          </div>
        </div>

        <!-- Table Section -->
        <div class="bg-white rounded-lg shadow border border-gray-300 mt-8 overflow-auto">
          <div
            class="flex flex-col sm:flex-row justify-between items-start sm:items-center px-5 p-3 border-b border-gray-300 gap-2">
            <h3 class="text-sm font-semibold text-gray-900">Riwayat Approval</h3>
            <button @click="downloadExcel"
              class="flex items-center gap-2 px-4 py-2 bg-[#08607a] hover:bg-[#065666] text-white text-sm rounded-lg shadow transition duration-200">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round"
                  d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5m0 0l5-5m-5 5V4" />
              </svg>
              Download Excel
            </button>
          </div>

          <div class="w-full overflow-x-auto">
            <table class="min-w-full table-auto border-collapse border border-gray-300 text-sm">
              <thead class="bg-gray-100 text-[#7d7f81]">
                <tr>
                  <th class="p-3 border">No</th>
                  <th class="p-3 border">Level Approval</th>
                  <th class="p-3 border">Status</th>
                  <th class="p-3 border">Catatan</th>
                  <th class="p-3 border">Tanggal</th>
                  <th class="p-3 border">Nama Approver</th>
                  <th class="p-3 border">ID Request</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(item, index) in paginatedList" :key="item.id_approval" class="text-[#333436] text-center">
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
                  <td class="p-3">{{ item.catatan }}</td>
                  <td class="p-3">{{ formatTanggal(item.tanggal) }}</td>
                  <td class="p-3">{{ item.request?.status_by || '-' }}</td>
                  <td class="p-3">{{ item.id_request_fk }}</td>
                </tr>
                <tr v-if="paginatedList.length === 0">
                  <td colspan="7" class="text-center p-4 text-gray-500">Data tidak ditemukan</td>
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
            <span>Halaman {{ currentPage }} dari
              {{ totalPages }}</span>
            <button @click="nextPage" :disabled="currentPage === totalPages"
              class="px-3 py-1 rounded bg-gray-200 hover:bg-gray-300 disabled:opacity-50">
              Next
            </button>
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
      selectedStatus: "",
      approvalList: [],
      currentPage: 1,
      itemsPerPage: 10,
    };
  },
  computed: {
    filteredList() {
      const priority = {
        done: 1,
        on_the_way: 2,
        purchasing: 3,
        approved: 4,
        pending: 5,
        rejected: 6,
      };

      return this.approvalList
        .filter(item => {
          const matchesSearch = item.level_approval?.toLowerCase().includes(this.searchQuery.toLowerCase())
            || item.request?.status_by?.toLowerCase().includes(this.searchQuery.toLowerCase())
            || item.status?.toLowerCase().includes(this.searchQuery.toLowerCase());

          const matchesStatus = this.selectedStatus === '' || item.status === this.selectedStatus;

          return matchesSearch && matchesStatus;
        })
        .sort((a, b) => {
          return (priority[a.status] || 999) - (priority[b.status] || 999);
        });
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
        purchasing: 'bg-blue-200 text-blue-800',
        on_the_way: 'bg-indigo-200 text-indigo-800',
        done: 'bg-lime-200 text-lime-800',
      }[status] || 'bg-gray-200 text-gray-800';
    },
    async downloadExcel() {
      const workbook = new ExcelJS.Workbook();
      const worksheet = workbook.addWorksheet('Riwayat Approval');

      worksheet.columns = [
        { header: 'No', key: 'no', width: 5 },
        { header: 'Level Approval', key: 'level_approval', width: 25 },
        { header: 'Status', key: 'status', width: 15 },
        { header: 'Catatan', key: 'catatan', width: 30 },
        { header: 'Tanggal', key: 'tanggal', width: 15 },
        { header: 'Nama Approver', key: 'status_by', width: 20 },
        { header: 'ID Request', key: 'id_request', width: 15 },
      ];

      // Header styling
      worksheet.getRow(1).eachCell(cell => {
        cell.font = { bold: true, color: { argb: "FFFFFFFF" } };
        cell.fill = {
          type: "pattern",
          pattern: "solid",
          fgColor: { argb: "FF4F46E5" }, // Indigo
        };
        cell.alignment = { vertical: "middle", horizontal: "center" };
        cell.border = {
          top: { style: "thin" },
          left: { style: "thin" },
          bottom: { style: "thin" },
          right: { style: "thin" },
        };
      });

      this.filteredList.forEach((item, index) => {
        const row = worksheet.addRow({
          no: index + 1,
          level_approval: item.level_approval,
          status: item.status,
          catatan: item.catatan,
          tanggal: this.formatTanggal(item.tanggal),
          status_by: item.request?.status_by || '-',
          id_request: item.id_request_fk,
        });

        // Dynamic row background color based on status
        const statusColorMap = {
          approved: "FFDCFCE7", // light green
          rejected: "FFFEE2E2", // light red
          pending: "FFFEF9C3", // light yellow
          purchasing: "FFE0F2FE", // light blue
          on_the_way: "FFEDE9FE", // light indigo
          done: "FFF0FDF4", // very light green
        };

        const bgColor = statusColorMap[item.status] || "FFFFFFFF"; // default white

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
      const filename = `Riwayat-Approval-${new Date().toISOString().slice(0, 10)}.xlsx`;
      saveAs(new Blob([buffer]), filename);
    },

  }
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
