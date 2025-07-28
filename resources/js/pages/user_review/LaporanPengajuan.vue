<template>
    <div class="flex h-screen bg-gray-100">
        <Sidebar :activeMenu="activeMenu" @update:activeMenu="updateActiveMenu" />
        <div class="flex-1 p-4 sm:p-6 md:p-8 pt-4 bg-white">
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
                            Data List Pengajuan ATK Baru
                        </h3>
                        <button @click="downloadExcels"
                            class="flex items-center gap-2 px-4 cursor-pointer py-2 bg-[#08607a] hover:bg-[#065666] text-white text-sm rounded-lg shadow transition duration-200">
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
                                    <th class="w-20 p-3 border">Nama Barang</th>
                                    <th class="w-30 p-3 border">
                                        Tanggal Pengajuan
                                    </th>
                                    <th class="w-25 p-3 border">Status</th>
                                    <th class="w-30 p-3 border">
                                        Catatan Approval
                                    </th>
                                    <th class="w-20 p-3 border">Satuan</th>
                                    <th class="p-3 border">Kategori</th>
                                    <th class="p-3 border">Harga Estimasi</th>
                                    <!-- <th class="p-3 border">Aksi</th> -->
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(item, index) in paginatedPengajuanBaruList" :key="item.id"
                                    class="text-[#333436]">
                                    <td class="p-3">{{ item.nama_barang }}</td>
                                    <td class="p-3">
                                        {{ formatTanggal(item.created_at) }}
                                    </td>
                                    <td class="p-3">
                                        <span :class="[
                                            'px-4 py-1 rounded-full text-xs font-semibold',
                                            formatStatus(item.status).color,
                                        ]">
                                            {{ formatStatus(item.status).label }}
                                        </span>
                                    </td>
                                    <td class="p-3">{{ item.catatan || "-" }}</td>
                                    <td class="p-3">{{ item.satuan }}</td>
                                    <td class="p-3">
                                        {{ item.kategori?.nama_kategori || "-" }}
                                    </td>
                                    <td class="p-3">
                                        {{ formatRupiah(item.harga_estimasi) }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <!-- Pagination untuk PengajuanBaru -->
                    <div
                        class="flex justify-between items-center px-4 py-3 border-t border-gray-300 text-sm text-[#333436]">
                        <button @click="prevPagePengajuanBaru" :disabled="currentPagePengajuanBaru === 1"
                            class="px-3 py-1 rounded bg-gray-200 hover:bg-gray-300 disabled:opacity-50">
                            Prev
                        </button>
                        <span>Halaman {{ currentPagePengajuanBaru }} dari {{ totalPagesPengajuanBaru }}</span>
                        <button @click="nextPagePengajuanBaru"
                            :disabled="currentPagePengajuanBaru === totalPagesPengajuanBaru"
                            class="px-3 py-1 rounded bg-gray-200 hover:bg-gray-300 disabled:opacity-50">
                            Next
                        </button>
                    </div>

                </div>
                <div class="bg-white rounded-lg shadow border border-gray-300 mt-8 overflow-hidden">
                    <div class="flex justify-between items-center px-5 p-3 border-b border-gray-300">
                        <h3 class="text-sm font-semibold text-gray-900">
                            Data Rekapitulasi Pengajuan per Bidang
                        </h3>
                        <div class="flex items-center gap-2 px-5 py-3">
                            <label for="tahun" class="text-sm font-semibold text-gray-700">Tahun:</label>
                            <select v-model="selectedYear" @change="fetchPengajuanAdminTable"
                                class="border rounded px-3 py-1 text-sm">
                                <option v-for="year in availableYears" :key="year" :value="year">{{ year }}</option>
                            </select>
                        </div>
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
                    <div class="overflow-x-auto">
                        <table class="min-w-full table-auto border-collapse border border-gray-300">
                            <thead class="bg-gray-100 text-[#7d7f81]">
                                <tr>
                                    <th class="w-40 p-3 border">Bidang</th>
                                    <th class="w-40 p-3 border">Nama Barang</th>
                                    <th class="w-20 p-3 border">Jumlah Order</th>
                                    <th class="w-24 p-3 border">Harga Satuan</th>
                                    <th class="w-28 p-3 border">Total Harga</th>
                                    <th class="p-3 border">Keterangan Barang</th>
                                    <th class="w-24 p-3 border">Status Pengadaan</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- bidang dlu baru di loop barang -->
                                <template v-for="(group, groupIndex) in dataGroupedByBidang" :key="groupIndex">
                                    <template v-for="(barang, index) in group.barang" :key="index">
                                        <tr class="text-[#333436]">
                                            <!-- Tampilkan nama bidang hanya di baris pertama dari group -->
                                            <td class="p-3 border text-center min-h-[4rem]" v-if="index === 0"
                                                :rowspan="group.barang.length">
                                                <div class="h-full flex items-center justify-center">
                                                    {{ group.nama_bidang }}
                                                </div>
                                            </td>

                                            <td class="p-3 border">{{ barang.nama_barang }}</td>
                                            <td class="p-3 border text-center">{{ barang.jumlah }}</td>
                                            <td class="p-3 border">{{ formatRupiah(barang.harga_satuan) }}</td>
                                            <td class="p-3 border">{{ formatRupiah(barang.total_harga) }}</td>
                                            <td class="p-3 border">{{ barang.keterangan || '-' }}</td>
                                            <td class="p-3 border"> {{ barang.status || '-' }}
                                                <!-- <div class="flex space-x-2 justify-center">
                                                        <button
                                                            @click="approvePengajuan(group.id_bidang_fk, barang.id_alat)"
                                                            class="cursor-pointer px-2 py-1 hover:bg-green-700 text-white text-xs rounded">
                                                            ✔
                                                        </button>
                                                        <button @click="rejectPengajuan(group.id_bidang_fk, barang.id_alat)"
                                                            class="cursor-pointer px-2 py-1 hover:bg-red-700 text-white text-xs rounded">
                                                            ✖
                                                        </button>
                                                    </div> -->
                                            </td>
                                        </tr>
                                    </template>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow border border-gray-300 mt-8 overflow-hidden">
                    <div class="flex justify-between items-center px-5 p-3 border-b border-gray-300">
                        <h3 class="text-sm font-semibold text-gray-900">
                            Data List Pengajuan
                        </h3>
                        <div class="flex items-center gap-2 px-5 py-3">
                            <label for="tahun" class="text-sm font-semibold text-gray-700">Tahun:</label>
                            <select v-model="selectedYear" @change="fetchRequest" class="border rounded px-3 py-1 text-sm">
                                <option v-for="year in availableYears" :key="year" :value="year">{{ year }}</option>
                            </select>
                        </div>

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
                    <div class="overflow-x-auto">
                        <table class="min-w-full table-fixed border-collapse border border-gray-300">
                            <thead class="bg-gray-100 text-[#7d7f81]">
                                <tr>
                                    <th class="w-14">No</th>
                                    <th class="w-14">ID Req</th>
                                    <th class="p-3 border">Nama Barang</th>
                                    <th class="p-3 border">Pemohon</th>
                                    <th class="p-3 border">Tgl Permintaan</th>
                                    <th class="w-33 border">Status</th>
                                    <th class="w-30 border">Ket. Status</th>
                                    <th class="border">Jumlah Order</th>
                                    <!-- <th class="p-3 border">Harga Satuan</th> -->
                                    <th class="w-25 p-3 border">Total</th>
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
                                    <td class="p-3">{{ request.id_request }}</td>
                                    <td class="p-3">
                                        {{ request.alat?.nama_barang || "-" }}
                                    </td>
                                    <td class="p-3">
                                        {{
                                            request.user?.data_diri?.nama_lengkap ||
                                            request.user?.NID || '-'
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
                                    <td class="p-3">
                                        {{ request.keterangan || "-" }}
                                    </td>

                                    <td class="p-3">{{ request.jumlah }}</td>
                                    <!-- <td class="p-3">
                                    {{
                                        formatRupiah(request.alat?.harga_satuan)
                                        }}
                                </td> -->
                                    <td class="p-3">
                                        {{ formatRupiah(request.total) }}
                                    </td>
                                    <td class="p-3">
                                        {{ request.alat?.keterangan || '-' }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
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

        <!-- <SuccessAlert :visible="showSuccessAlert" :message="successMessage" />
        <ModalConfirm :visible="showModal" title="Konfirmasi Hapus Data"
            message="Apakah Anda yakin ingin menghapus pengajuan ini?" @cancel="cancelDelete"
            @confirm="deleteRequest" /> -->
    </div>
</template>

<script>
import Sidebar from "@/components/Sidebar.vue";
import HeaderBar from "@/components/HeaderBar.vue";
import ModalConfirm from "@/components/ModalConfirm.vue";
import SuccessAlert from "@/components/SuccessAlert.vue";
import ExcelJS from "exceljs";
import Chart from "chart.js/auto";
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
            PengajuanBaruList: [],
            selectedYear: new Date().getFullYear(),
            availableYears: Array.from({ length: 10 }, (_, i) => new Date().getFullYear() - i),
            dataGroupedByBidang: [],
            currentPage: 1,
            itemsPerPage: 10,
            currentPagePengajuanBaru: 1,
            itemsPengajuanBaruPerPage: 5,
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

        filteredPengajuanBaruList() {
            if (!this.searchQuery) return this.PengajuanBaruList;

            const query = this.searchQuery.toLowerCase();

            return this.PengajuanBaruList.filter(item =>
                item.nama_barang?.toLowerCase().includes(query) ||
                item.catatan?.toLowerCase().includes(query) ||
                item.status?.toLowerCase().includes(query) ||
                item.kategori?.nama_kategori?.toLowerCase().includes(query)
            );
        },

        paginatedPengajuanBaruList() {
            const start = (this.currentPagePengajuanBaru - 1) * this.itemsPengajuanBaruPerPage;
            return this.filteredPengajuanBaruList.slice(start, start + this.itemsPengajuanBaruPerPage);
        },

        totalPagesPengajuanBaru() {
            return Math.ceil(this.filteredPengajuanBaruList.length / this.itemsPengajuanBaruPerPage);
        },
    },

    created() {
        const currentYear = new Date().getFullYear();
        this.availableYears = Array.from({ length: 20 }, (_, i) => currentYear - i);
        this.selectedYear = currentYear;
        this.fetchRequest();
        this.getUserInfo();
        this.fetchPengajuanBaru();
        this.fetchPengajuanAdminTable();
    },

    methods: {
        async downloadRekapBidangExcel() {
            try {
                const token = localStorage.getItem("token");
                const res = await axios.get("http://localhost:8000/api/adminTahun", {
                    headers: { Authorization: `Bearer ${token}` },
                    params: { tahun: this.selectedYear },
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

                // Styling header
                worksheet.getRow(1).eachCell(cell => {
                    cell.font = { bold: true, color: { argb: "FFFFFFFF" } };
                    cell.fill = {
                        type: "pattern",
                        pattern: "solid",
                        fgColor: { argb: "FF4F46E5" },
                    };
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

                    // Merge cell judul bidang
                    worksheet.mergeCells(`A${rowIndex}:G${rowIndex}`);
                    const cell = worksheet.getCell(`A${rowIndex}`);
                    cell.value = `Bidang: ${bidang}`;
                    cell.font = { bold: true, color: { argb: "FFFFFFFF" } };
                    cell.fill = {
                        type: "pattern",
                        pattern: "solid",
                        fgColor: { argb: "FF10B981" },
                    };
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

                    // Tambahkan baris subtotal
                    const subtotalRow = worksheet.addRow({
                        nama_barang: "Subtotal",
                        jumlah: subtotalJumlah,
                        total_harga: formatIDR(subtotal),
                    });

                    subtotalRow.eachCell(cell => {
                        cell.font = { bold: true };
                        cell.fill = {
                            type: "pattern",
                            pattern: "solid",
                            fgColor: { argb: "FFE5E7EB" },
                        };
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
        async fetchPengajuanBaru() {
            try {
                const token = localStorage.getItem("token");
                const res = await axios.get(
                    "http://localhost:8000/api/pengajuan-baru",
                    {
                        headers: { Authorization: `Bearer ${token}` },
                    }
                );
                this.PengajuanBaruList = res.data.data || [];
            } catch (error) {
                console.error("Gagal mengambil data pengajuan baru:", error);
            }
        },
        async downloadExcels() {
            try {
                const workbook = new ExcelJS.Workbook();
                const worksheet = workbook.addWorksheet("Pengajuan ATK Baru");

                // Header Kolom
                worksheet.columns = [
                    { header: "No", key: "no", width: 5 },
                    { header: "Nama Barang", key: "nama_barang", width: 25 },
                    { header: "Tanggal Pengajuan", key: "tanggal", width: 20 },
                    { header: "Status", key: "status", width: 15 },
                    { header: "Catatan Approval", key: "catatan", width: 25 },
                    { header: "Satuan", key: "satuan", width: 15 },
                    { header: "Kategori", key: "kategori", width: 20 },
                    { header: "Harga Estimasi", key: "harga_estimasi", width: 20 },
                ];

                // Styling Header
                worksheet.getRow(1).eachCell(cell => {
                    cell.font = { bold: true, color: { argb: "FFFFFFFF" } };
                    cell.fill = {
                        type: "pattern",
                        pattern: "solid",
                        fgColor: { argb: "FF4F46E5" },
                    };
                    cell.alignment = { vertical: "middle", horizontal: "center" };
                    cell.border = {
                        top: { style: "thin" },
                        left: { style: "thin" },
                        bottom: { style: "thin" },
                        right: { style: "thin" },
                    };
                });

                // Data
                this.filteredPengajuanBaruList.forEach((item, index) => {
                    const rowData = {
                        no: index + 1,
                        nama_barang: item.nama_barang || "-",
                        tanggal: this.formatTanggal(item.created_at),
                        status: this.formatStatus(item.status).label,
                        catatan: item.catatan || "-",
                        satuan: item.satuan || "-",
                        kategori: item.kategori?.nama_kategori || "-",
                        harga_estimasi: item.harga_estimasi || 0,
                    };

                    const row = worksheet.addRow(rowData);

                    // Warna baris berdasarkan status
                    let bgColor = "FFFFFFFF";
                    switch (item.status) {
                        case "approved":
                            bgColor = "FFDCFCE7"; // hijau muda
                            break;
                        case "rejected":
                            bgColor = "FFFEE2E2"; // merah muda
                            break;
                        case "waiting_approval_1":
                        case "waiting_approval_2":
                        case "waiting_approval_3":
                            bgColor = "FFFEF9C3"; // kuning muda
                            break;
                        default:
                            bgColor = "FFF3F4F6"; // abu muda
                            break;
                    }

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

                    // Format harga estimasi ke format rupiah
                    row.getCell("harga_estimasi").numFmt = '"Rp"#,##0';
                });

                const buffer = await workbook.xlsx.writeBuffer();
                const filename = `Pengajuan-Baru-${new Date().toISOString().slice(0, 10)}.xlsx`;
                saveAs(new Blob([buffer]), filename);
            } catch (error) {
                console.error("Gagal export Excel pengajuan baru:", error);
            }
        },
        async downloadExcel() {
            try {
                const workbook = new ExcelJS.Workbook();
                const worksheet = workbook.addWorksheet("Data Pengajuan");

                // Header Kolom
                worksheet.columns = [
                    { header: "No", key: "no", width: 5 },
                    { header: "ID Request", key: "id_request", width: 15 },
                    { header: "Nama Barang", key: "nama_barang", width: 25 },
                    { header: "Pemohon", key: "pemohon", width: 25 },
                    { header: "Tgl Permintaan", key: "tanggal", width: 15 },
                    { header: "Status", key: "status", width: 15 },
                    { header: "Catatan", key: "catatan", width: 25 },
                    { header: "Status By", key: "status_by", width: 20 },
                    { header: "Jumlah Order", key: "jumlah", width: 15 },
                    { header: "Total", key: "total", width: 15 },
                    { header: "Keterangan", key: "keterangan", width: 25 },
                ];

                // Header styling
                worksheet.getRow(1).eachCell(cell => {
                    cell.font = { bold: true, color: { argb: "FFFFFFFF" } };
                    cell.fill = {
                        type: "pattern",
                        pattern: "solid",
                        fgColor: { argb: "FF4F46E5" },
                    };
                    cell.alignment = { vertical: "middle", horizontal: "center" };
                    cell.border = {
                        top: { style: "thin" },
                        left: { style: "thin" },
                        bottom: { style: "thin" },
                        right: { style: "thin" },
                    };
                });

                // Isi Data + Warna berdasarkan status
                this.filteredRequestList.forEach((item, index) => {
                    const rowData = {
                        no: index + 1,
                        id_request: item.id_request || "-",
                        nama_barang: item.alat?.nama_barang || "-",
                        pemohon: item.user?.data_diri?.nama_lengkap || item.user?.NID || '-',
                        tanggal: this.formatTanggal(item.tanggal_permintaan),
                        status: this.formatStatus(item.status).label,
                        status_by: item.status_by || "-",
                        catatan: item.keterangan || "-",
                        jumlah: item.jumlah,
                        total: item.total || 0,
                        keterangan: item.alat?.keterangan || "-",
                    };

                    const row = worksheet.addRow(rowData);

                    // Styling baris berdasarkan status
                    let bgColor = "FFFFFFFF"; // default putih
                    switch (item.status) {
                        case "approved":
                            bgColor = "FFDCFCE7"; // hijau muda
                            break;
                        case "rejected":
                            bgColor = "FFFEE2E2"; // merah muda
                            break;
                        case "waiting_approval_1":
                        case "waiting_approval_2":
                        case "waiting_approval_3":
                            bgColor = "FFFEF9C3"; // kuning muda
                            break;
                        default:
                            bgColor = "FFF3F4F6"; // abu muda
                            break;
                    }

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

                    // Format kolom total ke bentuk Rupiah
                    row.getCell("total").numFmt = '"Rp"#,##0';
                });

                const buffer = await workbook.xlsx.writeBuffer();
                const filename = `Data-Pengajuan-${new Date().toISOString().slice(0, 10)}.xlsx`;
                saveAs(new Blob([buffer]), filename);
            } catch (error) {
                console.error("Gagal export Excel:", error);
            }
        },
        async fetchPengajuanAdminTable() {
            try {
                const token = localStorage.getItem("token");
                const res = await axios.get("http://localhost:8000/api/adminTahun", {
                    headers: { Authorization: `Bearer ${token}` },
                    params: { tahun: this.selectedYear }
                });
                this.dataGroupedByBidang = res.data.data || [];
            } catch (error) {
                console.error("Gagal mengambil data rekap admin:", error);
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
            // format date DD/MM/YYYY
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

                const res = await axios.get("http://localhost:8000/api/requestTahun", {
                    // kalau gk jadi tahun, pakai /api/request saja, hapus params dan template select tahun
                    headers: { Authorization: `Bearer ${token}` },
                    params: { tahun: this.selectedYear }
                });

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
                purchasing: {
                    label: "Pembelian",
                    color: "bg-blue-200 text-blue-800",
                },
                on_the_way: {
                    label: "Perjalanan",
                    color: "bg-blue-200 text-blue-800",
                },
                done: {
                    label: "Selesai",
                    color: "bg-gray-300 text-gray-900",
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
    padding: 8px 10px;
    /* diperkecil */
    text-align: center;
    font-size: 12px;
    /* perkecil font */
    border: 1px solid #ccc;
    word-wrap: break-word;
}
</style>
