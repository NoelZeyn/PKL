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

                <div v-if="isAsman || isManajer || isAnggaran"
                    class="bg-white rounded-lg shadow border border-gray-300 mt-8 overflow-hidden">
                    <div class="flex justify-between items-center px-5 p-3 border-b border-gray-300">
                        <h3 class="text-sm font-semibold text-gray-900">Data List Pengajuan {{ tingkatanOtoritas }}</h3>
                    </div>

                    <table class="w-full table-fixed border-collapse border border-gray-300">
                        <thead class="bg-gray-100 text-[#7d7f81]">
                            <tr>
                                <!-- <th class="p-3 border">Nama Penempatan</th> -->
                                <th v-if="tingkatanOtoritas !== 'anggaran'" class="p-3 border">Nama Barang</th>
                                <th v-if="tingkatanOtoritas === 'anggaran'" class="p-3 border">Nama Bidang</th>
                                <th v-if="tingkatanOtoritas !== 'anggaran'" class="p-3 border">Jumlah</th>
                                <th v-if="tingkatanOtoritas !== 'anggaran'" class="p-3 border">Harga Satuan</th>
                                <th class="p-3 border">Total Harga</th>
                                <th class="p-3 border">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template v-if="isAnggaran">
                                <tr v-for="(pengajuan, index) in pengajuanList" :key="index" class="text-[#333436]">
                                    <td class="p-3">{{ pengajuan.nama_bidang || "-" }}</td>
                                    <td class="p-3">{{ formatRupiah(pengajuan.total_harga_barang) }}</td>
                                    <td class="p-3">
                                        <div class="flex items-center justify-center space-x-3">
                                            <button @click="approvePengajuan(pengajuan.id_penempatan_fk, null)"
                                                title="Setujui"
                                                class="cursor-pointer hover:bg-green-600 text-white rounded-md w-8 h-8 flex items-center justify-center shadow hover:shadow-lg transform hover:scale-105 transition duration-150">
                                                <span class="text-sm font-bold">✔</span>
                                            </button>
                                            <button @click="rejectPengajuan(pengajuan.id_penempatan_fk, null)"
                                                title="Tolak"
                                                class="cursor-pointer hover:bg-red-600 text-white rounded-md w-8 h-8 flex items-center justify-center shadow hover:shadow-lg transform hover:scale-105 transition duration-150">
                                                <span class="text-sm font-bold">✖</span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </template>

                            <template v-else>
                                <template v-for="(pengajuan, index) in pengajuanList" :key="pengajuan.id_penempatan_fk">
                                    <tr v-for="(item, idx) in pengajuan.barang" :key="idx" class="text-[#333436]">
                                        <td class="p-3">{{ item.nama_barang || "-" }}</td>
                                        <td class="p-3">{{ item.total_jumlah || '-' }}</td>
                                        <td class="p-3">{{ formatRupiah(item.harga_satuan) }}</td>
                                        <td class="p-3">{{ formatRupiah(item.total_harga) }}</td>
                                        <td class="p-3">
                                            <div class="flex items-center justify-center space-x-3">
                                                <button
                                                    @click="approvePengajuan(isManajer ? pengajuan.id_bidang_fk : pengajuan.id_penempatan_fk, item.id_alat)"
                                                    title="Setujui"
                                                    class="cursor-pointer hover:bg-green-600 text-white rounded-md w-8 h-8 flex items-center justify-center shadow hover:shadow-lg transform hover:scale-105 transition duration-150">
                                                    <span class="text-sm font-bold">✔</span>
                                                </button>
                                                <button
                                                    @click="rejectPengajuan(isManajer ? pengajuan.id_bidang_fk : pengajuan.id_penempatan_fk, item.id_alat)"
                                                    title="Tolak"
                                                    class="cursor-pointer hover:bg-red-600 text-white rounded-md w-8 h-8 flex items-center justify-center shadow hover:shadow-lg transform hover:scale-105 transition duration-150">
                                                    <span class="text-sm font-bold">✖</span>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                </template>
                            </template>

                        </tbody>
                    </table>
                </div>

                <div class="bg-white rounded-lg shadow border border-gray-300 mt-8 overflow-hidden">
                    <div class="flex justify-between items-center px-5 p-3 border-b border-gray-300">
                        <h3 class="text-sm font-semibold text-gray-900">Data List Pengajuan</h3>
                        <router-link to="/pengajuan-add"
                            class="text-sm font-semibold text-[#074a5d] no-underline hover:text-[#0066cc] hover:no-underline">
                            Tambah Pengajuan
                        </router-link>
                    </div>

                    <table class="w-full table-fixed border-collapse border border-gray-300">
                        <thead class="bg-gray-100 text-[#7d7f81]">
                            <tr>
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
                                <td class="p-3">{{ request.alat?.nama_barang || "-" }}</td>
                                <td class="p-3">{{ request.user?.data_diri?.nama_lengkap || "-" }}</td>
                                <td class="p-3">{{ formatTanggal(request.tanggal_permintaan) }}</td>
                                <td class="p-3">
                                    <span
                                        :class="['px-4 py-1 rounded-full text-xs font-semibold', formatStatus(request.status).color]">
                                        {{ formatStatus(request.status).label }}
                                    </span>
                                    <br /><br />
                                    {{ request.status_by || "-" }}
                                </td>
                                <td class="p-3">{{ request.jumlah }}</td>
                                <td class="p-3">{{ formatRupiah(request.alat?.harga_satuan) }}</td>
                                <td class="p-3">{{ formatRupiah(request.total) }}</td>
                                <td class="p-3">
                                    <div class="flex items-center space-x-2 justify-center">
                                        <button title="Informasi" @click="navigateTo('info', request)"
                                            v-if="tingkatanOtoritas !== 'user_review'"
                                            class="cursor-pointer hover:opacity-70 border-r-1 pr-2">
                                            <img :src="informasiIcon" alt="Informasi" class="w-5 h-5 object-contain" />
                                        </button>
                                        <button title="Hapus" @click="confirmDelete(request)"
                                            v-if="tingkatanOtoritas === 'admin' || tingkatanOtoritas === 'superadmin'">
                                            <img :src="deleteIcon" alt="Delete"
                                                class="cursor-pointer hover:opacity-70" />
                                        </button>
                                    </div>
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
                        <span>Halaman {{ currentPage }} dari {{ totalPages }}</span>
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
        <ModalReject :visible="showRejectModal" :reason="rejectReason" @cancel="showRejectModal = false"
            @confirm="confirmReject" />
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
            showModal: false,
            showSuccessAlert: false,
            successMessage: "",
            requestToDelete: null,
            requestList: [],
            pengajuanList: [],
            informasiIcon,
            updateIcon,
            deleteIcon,
            showRejectModal: false,
            rejectReason: "",
            rejectTarget: null, // { id_fk, id_alat }
            currentPage: 1,
            itemsPerPage: 10,
        };
    },

    computed: {
        filteredRequestList() {
            return this.requestList.filter(r =>
                !this.searchQuery ||
                r.alat?.nama_barang?.toLowerCase().includes(this.searchQuery.toLowerCase()) ||
                r.user?.data_diri?.nama_lengkap.toLowerCase().includes(this.searchQuery.toLowerCase())
            );
        },
        paginatedRequestList() {
            const start = (this.currentPage - 1) * this.itemsPerPage;
            return this.filteredRequestList.slice(start, start + this.itemsPerPage);
        },
        totalPages() {
            return Math.ceil(this.filteredRequestList.length / this.itemsPerPage);
        },
        isAsman() {
            return this.tingkatanOtoritas === "asman";
        },
        isManajer() {
            return this.tingkatanOtoritas === "manajer";
        },
        isAnggaran() {
            return this.tingkatanOtoritas === "anggaran";
        },
    },

    async created() {
        await this.getUserInfo();
        this.fetchPengajuan();
        this.fetchRequest();
    },

    methods: {
        async getUserInfo() {
            try {
                const token = localStorage.getItem("token");
                const res = await axios.post("http://localhost:8000/api/me", {}, {
                    headers: { Authorization: `Bearer ${token}` },
                });
                this.tingkatanOtoritas = res.data.tingkatan_otoritas;
            } catch (err) {
                console.error("Gagal mengambil data user:", err);
            }
        },

        async fetchPengajuan() {
            try {
                const token = localStorage.getItem("token");
                let url = "";

                if (this.isAsman) url = "http://localhost:8000/api/asman";
                else if (this.isManajer) url = "http://localhost:8000/api/manajer";
                else if (this.isAnggaran) url = "http://localhost:8000/api/anggaran";
                else return;

                const res = await axios.get(url, {
                    headers: { Authorization: `Bearer ${token}` },
                });

                this.pengajuanList = res.data.data;
            } catch (err) {
                console.error("Gagal mengambil data pengajuan:", err);
            }
        },

        async fetchRequest() {
            try {
                const token = localStorage.getItem("token");
                const res = await axios.get("http://localhost:8000/api/request", {
                    headers: { Authorization: `Bearer ${token}` },
                });
                this.requestList = res.data.data;
            } catch (err) {
                console.error("Gagal mengambil data request:", err);
            }
        },

        async approvePengajuan(id_fk, id_alat) {
            try {
                const token = localStorage.getItem("token");
                let url = "";
                let payload = { id_alat };

                if (this.isAsman) {
                    url = "http://localhost:8000/api/asman/approve";
                    payload.id_penempatan_fk = id_fk;
                } else if (this.isManajer) {
                    url = "http://localhost:8000/api/manajer/approve";
                    payload.id_bidang_fk = id_fk;
                } else if (this.isAnggaran) {
                    url = "http://localhost:8000/api/anggaran/approve";
                    payload.id_penempatan_fk = id_fk;
                } else {
                    return;
                }

                const res = await axios.patch(url, payload, {
                    headers: { Authorization: `Bearer ${token}` },
                });

                this.successMessage = res.data.message;
                this.showSuccessAlert = true;
                this.fetchPengajuan();
                this.fetchRequest();
                setTimeout(() => (this.showSuccessAlert = false), 2000);
            } catch (err) {
                console.error("Gagal menyetujui:", err);
            }
        },

        rejectPengajuan(id_fk, id_alat) {
            this.rejectTarget = { id_fk, id_alat };
            this.rejectReason = "";
            this.showRejectModal = true;
        },

        confirmReject(keterangan) {
            if (!this.rejectTarget) return;

            const { id_fk, id_alat } = this.rejectTarget;
            const token = localStorage.getItem("token");
            let url = "";
            let payload = { id_alat, keterangan };

            if (this.isAsman) {
                url = "http://localhost:8000/api/asman/reject";
                payload.id_penempatan_fk = id_fk;
            } else if (this.isManajer) {
                url = "http://localhost:8000/api/manajer/reject";
                payload.id_bidang_fk = id_fk;
            } else if (this.isAnggaran) {
                url = "http://localhost:8000/api/anggaran/reject";
                payload.id_penempatan_fk = id_fk;
            } else {
                this.showRejectModal = false;
                return;
            }

            axios.patch(url, payload, {
                headers: { Authorization: `Bearer ${token}` },
            })
                .then(res => {
                    this.successMessage = res.data.message;
                    this.showSuccessAlert = true;
                    this.fetchPengajuan();
                    this.fetchRequest();
                    setTimeout(() => (this.showSuccessAlert = false), 2000);
                })
                .catch(err => {
                    console.error("Gagal menolak:", err);
                })
                .finally(() => {
                    this.showRejectModal = false;
                    this.rejectTarget = null;
                });
        },

        updateActiveMenu(menu) {
            this.activeMenu = menu;
        },

        navigateTo(action, request) {
            localStorage.setItem(`dataRequest${action}`, JSON.stringify(request));
            this.$router.push(`/pengajuan-${action}/${request.id_request}`);
        },

        confirmDelete(request) {
            this.requestToDelete = request;
            this.showModal = true;
        },

        cancelDelete() {
            this.requestToDelete = null;
            this.showModal = false;
        },

        async deleteRequest() {
            try {
                const token = localStorage.getItem("token");
                await axios.delete(`http://localhost:8000/api/request/${this.requestToDelete.id_request}`, {
                    headers: { Authorization: `Bearer ${token}` },
                });
                this.successMessage = "Pengajuan berhasil dihapus!";
                this.showSuccessAlert = true;
                this.fetchRequest();
                setTimeout(() => this.showSuccessAlert = false, 2000);
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
        },

        formatRupiah(angka) {
            if (!angka) return "-";
            return "Rp. " + angka.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
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
                draft: { label: "Draft", color: "bg-yellow-200 text-yellow-800" },
                waiting_approval_1: { label: "Approval 1", color: "bg-yellow-200 text-yellow-800" },
                waiting_approval_2: { label: "Approval 2", color: "bg-yellow-200 text-yellow-800" },
                waiting_approval_3: { label: "Approval 3", color: "bg-yellow-200 text-yellow-800" },
                approved: { label: "Disetujui", color: "bg-green-200 text-green-800" },
                rejected: { label: "Ditolak", color: "bg-red-200 text-red-800" },
            };

            return statusMap[status] || { label: status, color: "bg-gray-200 text-gray-800" };
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
