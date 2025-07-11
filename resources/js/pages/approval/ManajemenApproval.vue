<template>
    <div class="flex h-screen bg-gray-100">
        <Sidebar :activeMenu="activeMenu" @update:activeMenu="updateActiveMenu" />
        <div class="flex-1 p-8 pt-4 bg-white">
            <HeaderBar title="Data List Pengajuan Level 2" class="mt-3" />
            <div class="my-4 border-b border-gray-300"></div>

            <div class="pb-12">
                <div class="filters space-y-4">
                    <div class="relative">
                        <input type="text" v-model="searchQuery" @input="onInputSearch"
                            placeholder="Cari Nama Barang atau NID Pemohon..."
                            class="w-full border border-gray-300 rounded-md py-2 pl-10 pr-4 text-sm text-gray-700" />
                        <img src="@/assets/search.svg" alt="Search"
                            class="w-5 h-5 absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400" />
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow border border-gray-300 mt-8 overflow-hidden">
                    <div class="flex justify-between items-center px-5 p-3 border-b border-gray-300">
                        <h3 class="text-sm font-semibold text-gray-900">
                            Data List Pengajuan Level 2
                        </h3>
                    </div>

                    <table class="w-full table-fixed border-collapse border border-gray-300">
                        <thead class="bg-gray-100 text-[#7d7f81]">
                            <tr>
                                <th class="p-3 border">Nama Barang</th>
                                <th class="p-3 border">Pemohon</th>
                                <th class="w-30 p-3 border">Penempatan</th>
                                <th class="p-3 border">Tgl Permintaan</th>
                                <th class="p-3 border">Status</th>
                                <th class="p-3 border">Jumlah</th>
                                <!-- <th class=" p-3 border">Harga Satuan</th> -->
                                <th class=" border">Total</th>
                                <th class="border">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(request, index) in paginatedRequestList" :key="request.id_request"
                                class="text-[#333436]">
                                <td class="p-3">{{ request.alat?.nama_barang || "-" }}</td>
                                <td class="p-3">{{ request.user?.data_diri?.nama_lengkap || "-" }}</td>
                                <td class="p-3">{{ request.user?.penempatan?.nama_penempatan || "-" }}</td>
                                <td class="p-3">{{ formatTanggal(request.tanggal_permintaan) }}</td>
                                <td class="p-3">
                                    <span :class="['font-semibold', formatStatus(request.status).color]">
                                        {{ formatStatus(request.status).label }}
                                    </span>
                                </td>

                                <td class="p-3">{{ request.jumlah }}</td>
                                <!-- <td class="p-3">{{ formatRupiah(request.alat?.harga_satuan) }}</td> -->
                                <td class="p-3">{{ formatRupiah(request.total) }}</td>
                                <td class="p-3">
                                    <div class="flex items-center justify-center space-x-2">
                                        <button v-if="tingkatanOtoritas !== 'user_review'" title="Informasi"
                                            @click="navigateTo('info', request)" class="hover:opacity-70">
                                            <img :src="informasiIcon" alt="Informasi" class="w-5 h-5 object-contain" />
                                        </button>
                                    </div>
                                    <div v-if="tingkatanOtoritas === 'admin' || tingkatanOtoritas === 'superadmin' || tingkatanOtoritas === 'anggaran'"
                                        class="flex items-center justify-center space-x-2">
                                        <button @click="approveRequest(request)" title="Setujui"
                                            class="cursor-pointer bg-green-500 hover:bg-green-600 text-white rounded-full w-8 h-8 flex items-center justify-center text-base shadow">✔</button>
                                        <button @click="openRejectModal(request)" title="Tolak"
                                            class="cursor-pointer bg-red-500 hover:bg-red-600 text-white rounded-full w-8 h-8 flex items-center justify-center text-base shadow">✖</button>
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

        <ModalConfirm :visible="showModal" title="Konfirmasi Hapus Data"
        message="Apakah Anda yakin ingin menghapus pengajuan ini?" @cancel="cancelDelete"
        @confirm="deleteRequest" />
        <SuccessAlert :visible="showSuccessAlert" :message="successMessage" />
        <ModalReject :visible="showRejectModal" :reason="rejectReason" @cancel="showRejectModal = false"
            @confirm="confirmReject" />
    </div>
</template>

<script>
import Sidebar from "@/components/Sidebar.vue";
import HeaderBar from "@/components/HeaderBar.vue";
import ModalConfirm from "@/components/ModalConfirm.vue";
import ModalReject from "@/components/ModalReject.vue";
import SuccessAlert from "@/components/SuccessAlert.vue";
import informasiIcon from "@/assets/Informasi.svg";
import axios from "axios";

export default {
    name: "ManajemenApproval2",
    components: { Sidebar, HeaderBar, ModalConfirm, SuccessAlert, ModalReject },
    data() {
        return {
            activeMenu: "manajemenApproval",
            searchQuery: "",
            tingkatanOtoritas: "",
            showModal: false,
            showSuccessAlert: false,
            successMessage: "",
            showRejectModal: false,
            rejectReason: "",
            selectedRequest: null,
            requestToDelete: null,
            requestList: [],
            informasiIcon,
            currentPage: 1,
            itemsPerPage: 10,
        };
    },
    computed: {
        filteredRequestList() {
            return this.requestList.filter(r =>
                !this.searchQuery ||
                r.alat?.nama_barang?.toLowerCase().includes(this.searchQuery.toLowerCase()) ||
                r.user?.NID?.toLowerCase().includes(this.searchQuery.toLowerCase())
            );
        },
        paginatedRequestList() {
            const start = (this.currentPage - 1) * this.itemsPerPage;
            return this.filteredRequestList.slice(start, start + this.itemsPerPage);
        },
        totalPages() {
            return Math.ceil(this.filteredRequestList.length / this.itemsPerPage);
        },
    },
    created() {
        this.getUserInfo();
    },
    methods: {
        // Dapatkan status approval berdasarkan role
        getApprovalStatusByRole() {
            const roleToStatus = {
                admin: 'waiting_approval_1',
                superadmin: 'waiting_approval_2',
                anggaran: 'waiting_approval_3',
            };
            return roleToStatus[this.tingkatanOtoritas] || null;
        },

        // Dapatkan endpoint GET berdasarkan role
        getApprovalApiByRole() {
            const roleToApi = {
                admin: 'approval1',
                superadmin: 'approval2',
                anggaran: 'approval3',
            };
            return roleToApi[this.tingkatanOtoritas] || null;
        },

        // Dapatkan endpoint PUT berdasarkan role
        getEditApprovalApiByRole() {
            const roleToEditApi = {
                admin: 'editApproval1',
                superadmin: 'editApproval2',
                anggaran: 'editApproval3',
            };
            return roleToEditApi[this.tingkatanOtoritas] || null;
        },

        async getUserInfo() {
            try {
                const token = localStorage.getItem("token");
                const res = await axios.post("http://localhost:8000/api/me", {}, {
                    headers: { Authorization: `Bearer ${token}` },
                });
                this.tingkatanOtoritas = res.data.tingkatan_otoritas;
                this.fetchRequest();
            } catch (err) {
                console.error("Gagal mengambil data user:", err);
            }
        },

        async fetchRequest() {
            try {
                const token = localStorage.getItem("token");
                const approvalApi = this.getApprovalApiByRole();

                if (!approvalApi) {
                    console.error('API tidak ditemukan untuk role ini.');
                    return;
                }

                const res = await axios.get(`http://localhost:8000/api/${approvalApi}`, {
                    headers: { Authorization: `Bearer ${token}` },
                });
                this.requestList = res.data.data;
            } catch (err) {
                console.error("Gagal mengambil data request:", err);
            }
        },

        approveRequest(request) {
            const status = this.getApprovalStatusByRole();
            if (!status) {
                alert('Anda tidak memiliki otoritas untuk melakukan approval.');
                return;
            }
            this.updateApproval(request, status);
        },

        openRejectModal(request) {
            this.selectedRequest = request;
            this.rejectReason = "";
            this.showRejectModal = true;
        },

        async confirmReject(reason) {
            if (!this.selectedRequest) return;
            await this.updateApproval(this.selectedRequest, 'rejected', reason);
            this.showRejectModal = false;
            this.selectedRequest = null;
        },

        async updateApproval(request, newStatus, catatan = "") {
            try {
                const token = localStorage.getItem("token");
                const resUser = await axios.post("http://localhost:8000/api/me", {}, {
                    headers: { Authorization: `Bearer ${token}` },
                });

                const NID = resUser.data.NID;
                const editApi = this.getEditApprovalApiByRole();

                if (!editApi) {
                    alert('API tidak ditemukan untuk role ini.');
                    return;
                }

                const payload = {
                    id_inventoris_fk: request.id_inventoris_fk,
                    NID,
                    jumlah: request.jumlah,
                    tanggal_permintaan: request.tanggal_permintaan,
                    status: newStatus,
                    total: request.total,
                    catatan: catatan,
                };

                await axios.put(`http://localhost:8000/api/${editApi}/${request.id_request}`, payload, {
                    headers: { Authorization: `Bearer ${token}` },
                });

                this.successMessage = newStatus.includes('waiting_approval') ? 'Pengajuan berhasil disetujui!' : 'Pengajuan berhasil ditolak!';
                this.showSuccessAlert = true;
                setTimeout(() => (this.showSuccessAlert = false), 2000);
                this.fetchRequest();

            } catch (err) {
                console.error("Gagal memperbarui pengajuan:", err);
            }
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
                setTimeout(() => (this.showSuccessAlert = false), 2000);
                this.fetchRequest();
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

        formatStatus(status) {
            const statusMap = {
                draft: { label: "Draft", color: "text-yellow-600" },
                waiting_approval_1: { label: "Approval 1", color: "text-yellow-600" },
                waiting_approval_2: { label: "Approval 2", color: "text-yellow-600" },
                waiting_approval_3: { label: "Approval 3", color: "text-yellow-600" },
                approved: { label: "Disetujui", color: "text-green-600" },
                rejected: { label: "Ditolak", color: "text-red-600" },
            };
            return statusMap[status] || { label: status, color: "text-gray-600" };
        },

        formatTanggal(dateString) {
            if (!dateString) return "-";
            const date = new Date(dateString);
            return date.toLocaleDateString("id-ID", { day: "2-digit", month: "2-digit", year: "numeric" });
        },

        formatRupiah(angka) {
            if (!angka) return "-";
            return "Rp. " + angka.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
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
