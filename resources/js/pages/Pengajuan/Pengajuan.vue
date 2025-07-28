<template>
    <div class="flex flex-col md:flex-row min-h-screen bg-gray-100">
        <Sidebar :activeMenu="activeMenu" @update:activeMenu="updateActiveMenu" />
        
    <div class="flex-1 p-4 sm:p-6 md:p-8 pt-4 bg-white overflow-auto">
      <HeaderBar title="Data Pengajuan" class="mt-3" />
            <div class="my-4 border-b border-gray-300"></div>

            <div class="pb-12">
                <div class="filters space-y-4">
                    <div class="relative">
                        <input type="text" v-model="searchQuery" @input="onInputSearch"
                            placeholder="Data List Pengajuan : Cari Nama Barang atau Nama Pemohon..."
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
                        <router-link to="/pengajuan-atk-add"
                            class="text-sm font-semibold text-[#074a5d] no-underline hover:text-[#0066cc] hover:no-underline">
                            Tambah Pengajuan ATK Baru
                        </router-link>
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

                <!-- table untuk semua -->
                <div class="bg-white rounded-lg shadow border border-gray-300 mt-8 overflow-hidden">
                    <div class="flex justify-between items-center px-5 p-3 border-b border-gray-300">
                        <h3 class="text-sm font-semibold text-gray-900">
                            Data List Pengajuan
                        </h3>
                        <router-link v-if="tingkatanOtoritas === 'admin' || tingkatanOtoritas === 'superadmin'"
                            to="/pengajuan-setting" class="text-sm font-semibold text-purple-700 hover:text-purple-900">
                            ⚙️ Pengaturan Pengajuan
                        </router-link>
                        <router-link v-if="showAddButton" to="/pengajuan-add"
                            class="text-sm font-semibold text-[#074a5d] no-underline hover:text-[#0066cc] hover:no-underline">
                            Tambah Pengajuan
                        </router-link>

                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full table-fixed border-collapse border border-gray-300">
                            <thead class="bg-gray-100 text-[#7d7f81]">
                                <tr>
                                    <th class="w-15 p-3 border">ID Req</th>
                                    <th class="p-3 border">Nama Barang</th>
                                    <th class="p-3 border">Pemohon</th>
                                    <th class="w-30 p-3 border">Tgl Permintaan</th>
                                    <th class="w-33 p-3 border">Status</th>
                                    <th class="p-3 border">Jumlah</th>
                                    <th class="w-22 p-3 border">Harga Satuan</th>
                                    <th class="p-3 border">Total</th>
                                    <th class="p-3 border">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(request, index) in paginatedRequestList" :key="request.id_request"
                                    class="text-[#333436]">
                                    <td class="p-3">
                                        {{ request.id_request || "-" }}
                                    </td>
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
                                        <br /><br />
                                        <div v-if="request.status_by !== null">
                                            {{ request.status_by || "-" }}

                                        </div>
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
                                        <div class="flex flex-wrap items-center justify-center">
                                            <button title="Informasi" @click="navigateTo('info', request)" v-if="
                                                tingkatanOtoritas !==
                                                'user_review'
                                            " class="cursor-pointer hover:opacity-70">
                                                <img :src="informasiIcon" alt="Informasi"
                                                    class="w-5 h-5 object-contain" />
                                            </button>
                                            <button title="Hapus" @click="confirmDelete(request)" v-if="
                                                tingkatanOtoritas === 'admin' ||
                                                tingkatanOtoritas ===
                                                'superadmin'
                                            " class="cursor-pointer hover:opacity-70 border-l-1 pl-2">
                                                <img :src="deleteIcon" alt="Delete"
                                                    class="cursor-pointer hover:opacity-70" />
                                            </button>
                                        </div>
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
import ModalReject from "@/components/ModalReject.vue";
import informasiIcon from "@/assets/Informasi.svg";
import updateIcon from "@/assets/Edit.svg";
import deleteIcon from "@/assets/Delete.svg";
import axios from "axios";

export default {
    name: "ManajemenPengajuan",
    components: { Sidebar, HeaderBar, ModalConfirm, SuccessAlert, ModalReject },

    data() {
        return {
            activeMenu: "pengajuan",
            searchQuery: "",
            tingkatanOtoritas: "",
            showAddButton: false,
            showModal: false,
            showSuccessAlert: false,
            successMessage: "",
            requestToDelete: null,
            requestList: [],
            PengajuanBaruList: [],
            informasiIcon,
            updateIcon,
            deleteIcon,
            showRejectModal: false,
            rejectReason: "",
            rejectTarget: null, // { id_fk, id_alat }
            currentPage: 1,
            currentPagePengajuanBaru: 1,

            itemsPerPage: 10,
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
                    r.user?.data_diri?.nama_lengkap
                        .toLowerCase()
                        .includes(this.searchQuery.toLowerCase())
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

    async created() {
        await this.getUserInfo();
        this.fetchRequest();
        this.fetchPengajuanBaru();
        this.cekStatusPengajuan();
    },

    methods: {
        async cekStatusPengajuan() {
            try {
                const res = await axios.get("http://localhost:8000/api/pengaturan-pengajuan");
                this.showAddButton = res.data.is_open;
            } catch (err) {
                console.error("Gagal cek pengaturan pengajuan", err);
                this.showAddButton = true; // fallback default
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

        navigateTo(action, request) {
            localStorage.setItem(
                `dataRequest${action}`,
                JSON.stringify(request)
            );
            this.$router.push(`/pengajuan-${action}/${request.id_request}`);
        },

        confirmDelete(item) {
            if (item.id_request) {
                this.requestToDelete = item;
                this.showModal = true;
            }
        },

        cancelDelete() {
            this.requestToDelete = null;
            this.showModal = false;
        },

        async deleteRequest() {
            try {
                const token = localStorage.getItem("token");

                if (this.requestToDelete) {
                    await axios.delete(
                        `http://localhost:8000/api/request/${this.requestToDelete.id_request}`,
                        {
                            headers: { Authorization: `Bearer ${token}` },
                        }
                    );
                    this.successMessage = "Pengajuan berhasil dihapus!";
                    this.fetchRequest();
                }
                this.showSuccessAlert = true;
                setTimeout(() => (this.showSuccessAlert = false), 2000);
            } catch (err) {
                console.error("Gagal menghapus pengajuan:", err);
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
            this.currentPagePengajuanBaru = 1;
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
    text-align: center;
    font-size: 12px;
    border: 1px solid #ccc;
    word-wrap: break-word;
}
</style>
