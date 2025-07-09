<template>
    <div class="flex h-screen bg-gray-100">
        <Sidebar :activeMenu="activeMenu" @update:activeMenu="updateActiveMenu" />
        <div class="flex-1 p-8 pt-4 bg-white">
            <HeaderBar title="Data Pengajuan" class="mt-3" />
            <div class="my-4 border-b border-gray-300"></div>

            <div class="pb-12">
                <div class="filters space-y-4">
                    <div class="relative">
                        <input type="text" v-model="searchQuery" @input="onInputSearch"
                            placeholder="Cari Nama Barang atau Nama Pemohon..."
                            class="w-full border border-gray-300 rounded-md py-2 pl-10 pr-4 text-sm text-gray-700" />
                        <img src="@/assets/search.svg" alt="Search"
                            class="w-5 h-5 absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400" />
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow border border-gray-300 mt-8 overflow-hidden">
                    <div class="flex justify-between items-center px-5 p-3 border-b border-gray-300">
                        <h3 class="text-sm font-semibold text-gray-900">
                            Data List Pengajuan
                        </h3>
                        <button @click="downloadExcel"
                            class="flex items-center gap-2 px-4 cursor-pointer py-2 bg-[#08607a] hover:bg-[#065666] text-white text-sm rounded-lg shadow transition duration-200">
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
                                <th class="p-3 border">Pemohon</th>
                                <th class="p-3 w-28 border">Tgl Permintaan</th>
                                <th class="w-50 border">Status</th>
                                <th class="w-22 border">Jumlah</th>
                                <th class="w-40 p-3 border">Harga Satuan</th>
                                <th class="p-3 border">Total</th>
                                <th class="p-3 border">Keterangan</th>
                                <!-- <th class="p-3 border">Aksi</th> -->
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(request, index) in paginatedRequestList" :key="request.id_request"
                                class="text-[#333436]">
                                <td class="p-3">
                                    {{
                                        (currentPage - 1) * itemsPerPage +
                                        index +
                                        1
                                    }}
                                </td>
                                <td class="p-3">
                                    {{ request.alat?.nama_barang || "-" }}
                                </td>
                                <td class="p-3">
                                    {{
                                        request.user?.data_diri?.nama_lengkap ||
                                        "-"
                                    }}
                                </td>
                                <td class="p-3">
                                    {{
                                        formatTanggal(
                                            request.tanggal_permintaan
                                        )
                                    }}
                                </td>
                                <td class="p-3">
                                    <span :class="[
                                        'px-4 py-1 rounded-full text-xs font-semibold',
                                        formatStatus(request.status).color,
                                    ]">
                                        {{ formatStatus(request.status).label }}
                                    </span>
                                    <br><br>
                                    {{ request.status_by || "-" }}
                                </td>

                                <td class="p-3">{{ request.jumlah }}</td>
                                <td class="p-3">
                                    {{
                                        formatRupiah(request.alat?.harga_satuan)
                                    }}
                                </td>
                                <td class="p-3">
                                    {{ formatRupiah(request.total) }}
                                </td>
                                <td class="p-3">
                                    {{ request.keterangan || "-" }}
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <div
                        class="flex justify-between items-center px-4 py-3 border-t border-gray-300 text-sm text-[#333436]">
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

        <SuccessAlert :visible="showSuccessAlert" :message="successMessage" />
        <ModalConfirm :visible="showModal" title="Konfirmasi Hapus Data"
            message="Apakah Anda yakin ingin menghapus pengajuan ini?" @cancel="cancelDelete"
            @confirm="deleteRequest" />
    </div>
</template>

<script>
import Sidebar from "@/components/Sidebar.vue";
import HeaderBar from "@/components/HeaderBar.vue";
import ModalConfirm from "@/components/ModalConfirm.vue";
import SuccessAlert from "@/components/SuccessAlert.vue";
import ExcelJS from "exceljs";
import { saveAs } from "file-saver";
import axios from "axios";

export default {
    name: "ManajemenPengajuan",
    components: { Sidebar, HeaderBar, ModalConfirm, SuccessAlert },

    data() {
        return {
            activeMenu: "laporanPengajuan",
            searchQuery: "",
            tingkatanOtoritas: "",
            requestToDelete: null,
            requestList: [],
            currentPage: 1,
            itemsPerPage: 10,
        };
    },

    computed: {
        filteredRequestList() {
            return this.requestList.filter(
                (r) =>
                    !this.searchQuery ||
                    r.alat?.nama_barang
                        ?.toLowerCase()
                        .includes(this.searchQuery.toLowerCase()) ||
                    r.user?.data_diri?.nama_lengkap.toLowerCase().includes(
                        this.searchQuery.toLowerCase()
                    )
            );
        },
        paginatedRequestList() {
            const start = (this.currentPage - 1) * this.itemsPerPage;
            return this.filteredRequestList.slice(
                start,
                start + this.itemsPerPage
            );
        },
        totalPages() {
            return Math.ceil(
                this.filteredRequestList.length / this.itemsPerPage
            );
        },
    },

    created() {
        this.fetchRequest();
        this.getUserInfo();
    },

    methods: {
        async downloadExcel() {
            try {
                const workbook = new ExcelJS.Workbook();
                const worksheet = workbook.addWorksheet("Data Pengajuan");

                // Header kolom
                worksheet.columns = [
                    { header: "No", key: "no", width: 5 },
                    { header: "Nama Barang", key: "nama_barang", width: 25 },
                    { header: "Pemohon", key: "pemohon", width: 25 },
                    { header: "Tgl Permintaan", key: "tanggal", width: 15 },
                    { header: "Status", key: "status", width: 15 },
                    { header: "Jumlah", key: "jumlah", width: 10 },
                    { header: "Harga Satuan", key: "harga_satuan", width: 15 },
                    { header: "Total", key: "total", width: 15 },
                ];

                // Styling Header
                worksheet.getRow(1).eachCell(cell => {
                    cell.font = { bold: true, color: { argb: "FFFFFFFF" } };
                    cell.fill = { type: "pattern", pattern: "solid", fgColor: { argb: "FF4F46E5" } };
                    cell.alignment = { vertical: "middle", horizontal: "center" };
                    cell.border = { top: { style: "thin" }, left: { style: "thin" }, bottom: { style: "thin" }, right: { style: "thin" } };
                });

                // Isi Data
                this.filteredRequestList.forEach((item, index) => {
                    worksheet.addRow({
                        no: index + 1,
                        nama_barang: item.alat?.nama_barang || "-",
                        pemohon: item.user?.data_diri?.nama_lengkap || "-",
                        tanggal: this.formatTanggal(item.tanggal_permintaan),
                        status: this.formatStatus(item.status).label,
                        jumlah: item.jumlah,
                        harga_satuan: item.alat?.harga_satuan || 0,
                        total: item.total || 0,
                    });
                });

                // Format angka Rupiah
                worksheet.eachRow((row, rowNumber) => {
                    if (rowNumber > 1) {
                        row.getCell(7).numFmt = '"Rp"#,##0';
                        row.getCell(8).numFmt = '"Rp"#,##0';
                    }
                });

                const buffer = await workbook.xlsx.writeBuffer();
                saveAs(new Blob([buffer]), `Data-Pengajuan-${new Date().toISOString().slice(0, 10)}.xlsx`);
            } catch (error) {
                console.error("Gagal export Excel:", error);
            }
        },
        async getUserInfo() {
            try {
                const token = localStorage.getItem("token");
                const res = await axios.post(
                    "http://localhost:8000/api/me",
                    {},
                    {
                        headers: { Authorization: `Bearer ${token}` },
                    }
                );
                this.tingkatanOtoritas = res.data.tingkatan_otoritas;
            } catch (err) {
                console.error("Gagal mengambil data user:", err);
            }
        },
        formatRupiah(angka) {
            if (!angka) return "-";
            return (
                "Rp. " + angka.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".")
            );
        },
        formatTanggal(dateString) {
            if (!dateString) return "-";
            const date = new Date(dateString);
            return date.toLocaleDateString("id-ID", {
                day: "2-digit",
                month: "2-digit",
                year: "numeric",
            });
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
            } catch (err) {
                console.error("Gagal mengambil data request:", err);
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
        formatStatus(status) {
            const statusMap = {
                draft: {
                    label: "Draft",
                    color: "bg-yellow-200 text-yellow-800",
                },
                waiting_approval_1: {
                    label: "Approval 1",
                    color: "bg-yellow-200 text-yellow-800",
                },
                waiting_approval_2: {
                    label: "Approval 2",
                    color: "bg-yellow-200 text-yellow-800",
                },
                waiting_approval_3: {
                    label: "Approval 3",
                    color: "bg-yellow-200 text-yellow-800",
                },
                approved: {
                    label: "Disetujui",
                    color: "bg-green-200 text-green-800",
                },
                rejected: {
                    label: "Ditolak",
                    color: "bg-red-200 text-red-800",
                },
            };

            return (
                statusMap[status] || {
                    label: status,
                    color: "bg-gray-200 text-gray-800",
                }
            );
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
