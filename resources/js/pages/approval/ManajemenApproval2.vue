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
                                <!-- <th class="w-14">No</th> -->
                                <th class="p-3 border">Nama Barang</th>
                                <th class="p-3 border">Pemohon</th>
                                <th class="p-3 w-28 border">Tgl Permintaan</th>
                                <th class="w-40 border">Status</th>
                                <th class="w-22 border">Jumlah</th>
                                <th class="w-22 p-3 border">Harga Satuan</th>
                                <th class="w-22 border">Total</th>
                                <th class="p-3 border">Decision</th>
                                <th class="w-18 border">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(request, index) in paginatedRequestList" :key="request.id_request"
                                class="text-[#333436]">
                                <!-- <td class="p-3">
                                    {{
                                        (currentPage - 1) * itemsPerPage +
                                        index +
                                        1
                                    }}
                                </td> -->
                                <td class="p-3">
                                    {{ request.alat?.nama_barang || "-" }}
                                </td>
                                <td class="p-3">
                                    {{ request.user?.NID || "-" }}
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
                                    <div v-if="tingkatanOtoritas !== 'user_review'"
                                        class="flex items-center justify-center space-x-2">
                                        <button @click="approveRequest(request)" title="Setujui"
                                            class="cursor-pointer bg-green-500 hover:bg-green-600 text-white rounded-full w-8 h-8 flex items-center justify-center text-base shadow">
                                            ✔
                                        </button>
                                        <button @click="rejectRequest(request)" title="Tolak"
                                            class="cursor-pointer bg-red-500 hover:bg-red-600 text-white rounded-full w-8 h-8 flex items-center justify-center text-base shadow">
                                            ✖
                                        </button>
                                    </div>
                                </td>

                                <td class="p-3">
                                    <div class="flex items-center justify-center space-x-2">
                                        <button v-if="tingkatanOtoritas !== 'user_review'" title="Informasi"
                                            @click="navigateTo('info', request)" class="hover:opacity-70">
                                            <img :src="informasiIcon" alt="Informasi" class="w-5 h-5 object-contain" />
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
import informasiIcon from "@/assets/Informasi.svg";
import updateIcon from "@/assets/Edit.svg";
import deleteIcon from "@/assets/Delete.svg";
import axios from "axios";

export default {
    name: "ManajemenPengajuan",
    components: { Sidebar, HeaderBar, ModalConfirm, SuccessAlert },

    data() {
        return {
            activeMenu: "manajemenApproval2",
            searchQuery: "",
            tingkatanOtoritas: "",
            showModal: false,
            showSuccessAlert: false,
            successMessage: "",
            requestToDelete: null,
            requestList: [],
            informasiIcon,
            updateIcon,
            deleteIcon,
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
                    r.user?.NID?.toLowerCase().includes(
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
        async approveRequest(request) {
            await this.updateApproval(request, 'waiting_approval_3');
        },

        async rejectRequest(request) {
            await this.updateApproval(request, 'rejected');
        },

        async updateApproval(request, newStatus) {
            try {
                const token = localStorage.getItem("token");
                const resUser = await axios.post("http://localhost:8000/api/me", {}, {
                    headers: { Authorization: `Bearer ${token}` }
                });

                const NID = resUser.data.NID;

                const payload = {
                    id_inventoris_fk: request.id_inventoris_fk,
                    NID: NID,
                    jumlah: request.jumlah,
                    tanggal_permintaan: request.tanggal_permintaan,
                    status: newStatus,
                    total: request.total,
                };

                await axios.put(`http://localhost:8000/api/editApproval2/${request.id_request}`, payload, {
                    headers: { Authorization: `Bearer ${token}` }
                });

                this.successMessage = (newStatus === 'waiting_approval_3') ? 'Pengajuan berhasil disetujui!' : 'Pengajuan berhasil ditolak!';
                this.showSuccessAlert = true;
                setTimeout(() => (this.showSuccessAlert = false), 2000);
                this.fetchRequest();

            } catch (err) {
                console.error('Gagal memperbarui pengajuan:', err);
            }
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
                    "http://localhost:8000/api/approval2",
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
                await axios.delete(
                    `http://localhost:8000/api/request/${this.requestToDelete.id_request}`,
                    {
                        headers: { Authorization: `Bearer ${token}` },
                    }
                );
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
