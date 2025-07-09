<template>
    <div class="flex h-screen bg-gray-100">
        <Sidebar :activeMenu="activeMenu" @update:activeMenu="updateActiveMenu" />
        <div class="flex-1 p-8 pt-4 bg-white">
            <HeaderBar title="Manajemen Akun" class="mt-3" />
            <div class="my-4 border-b border-gray-300"></div>

            <div class="pb-12">
                <div class="filters space-y-4">
                    <div class="relative">
                        <input type="text" v-model="searchQuery" @input="onInputSearch"
                            placeholder="Cari NID atau Tingkatan..."
                            class="w-full border border-gray-300 rounded-md py-2 pl-10 pr-4 text-sm text-gray-700" />
                        <img src="@/assets/search.svg" alt="Search"
                            class="w-5 h-5 absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400" />
                    </div>
                </div>

                <!-- Tabel Data -->
                <div class="bg-white rounded-lg shadow border border-gray-300 mt-8 overflow-hidden">
                    <div class="flex justify-between items-center px-5 p-3 border-b border-gray-300">
                        <h3 class="text-sm font-semibold text-gray-900">
                            Data Akun
                        </h3>

                        <router-link to="/akun-add"
                            class="text-sm font-semibold text-[#074a5d] no-underline hover:text-[#0066cc] hover:no-underline">
                            Tambah Akun
                        </router-link>
                    </div>

                    <table class="w-full table-fixed border-collapse border border-gray-300">
                        <thead class="bg-gray-100 text-[#7d7f81]">
                            <tr>
                                <th class="w-16">No</th>
                                <th class="p-3 border">NID</th>
                                <th class="p-3 border">Tingkatan Otoritas</th>
                                <th class="p-3 border">Access</th>
                                <th class="p-3 border">Password Changed</th>
                                <th class="p-3 border">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(akun, index) in paginatedAccountList" :key="akun.id" class="text-[#333436]">
                                <td class="p-3">{{ (currentPage - 1) * itemsPerPage + index + 1 }}</td>
                                <td class="p-3">{{ akun.NID }}</td>
                                <td class="p-3">{{ akun.tingkatan_otoritas }}</td>
                                <td class="p-3">
                                    <select v-model="akun.access" @change="updateAccess(akun)"
                                        class="border border-gray-300 rounded px-2 py-1 text-xs">
                                        <option value="active">Active</option>
                                        <option value="inactive">Inactive</option>
                                        <option value="pending">Pending</option>
                                    </select>
                                </td>

                                <td class="p-3">{{ akun.password_changed_at }}</td>
                                <td class="p-3">
                                    <div class="flex items-center space-x-2 justify-center">
                                        <!-- <button title="Informasi" @click="
                                            navigateTo('informasi', akun)
                                            " class="cursor-pointer hover:opacity-70 border-r-1 pr-2 cursor-pointer">
                                            <img :src="informasiIcon" alt="Informasi" class="w-5 h-5 object-contain" />
                                        </button> -->
                                        <button title="Hapus" @click="confirmDelete(akun)"
                                            class="cursor-pointer hover:opacity-70 cursor-pointer">
                                            <img :src="deleteIcon" alt="Delete" class="w-5 h-5 object-contain" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- Pagination Controls -->
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
            message="Apakah Anda yakin ingin menghapus data ini?" @cancel="cancelDelete" @confirm="deleteSupplier" />
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
    name: "ManajemenAkun",
    components: { Sidebar, HeaderBar, ModalConfirm, SuccessAlert },

    data() {
        return {
            activeMenu: "manajemenAkun",
            searchQuery: "",
            showModal: false,
            showSuccessAlert: false,
            successMessage: "",
            akunToDelete: null,

            accountList: [],
            informasiIcon,
            updateIcon,
            deleteIcon,

            currentPage: 1,
            itemsPerPage: 10,
        };
    },

    computed: {
        filteredAccountList() {
            return this.accountList.filter(
                a =>
                    !this.searchQuery ||
                    a.NID.toLowerCase().includes(this.searchQuery.toLowerCase()) ||
                    a.tingkatan_otoritas.toLowerCase().includes(this.searchQuery.toLowerCase())
            );
        },
        paginatedAccountList() {
            const start = (this.currentPage - 1) * this.itemsPerPage;
            return this.filteredAccountList.slice(start, start + this.itemsPerPage);
        },
        totalPages() {
            return Math.ceil(this.filteredAccountList.length / this.itemsPerPage);
        },
    },

    created() {
        this.fetchAccounts();
    },

    methods: {
        async updateAccess(akun) {
            try {
                const token = localStorage.getItem("token");
                await axios.put(`http://localhost:8000/api/account/${akun.id}`, {
                    access: akun.access
                }, {
                    headers: { Authorization: `Bearer ${token}` }
                });
                this.successMessage = "Akses akun berhasil diperbarui!";
                this.showSuccessAlert = true;
                setTimeout(() => (this.showSuccessAlert = false), 2000);
            } catch (err) {
                console.error("Gagal memperbarui akses akun:", err);
            }
        },
        async fetchAccounts() {
            try {
                const token = localStorage.getItem("token");
                const res = await axios.get("http://localhost:8000/api/account", {
                    headers: { Authorization: `Bearer ${token}` }
                });
                this.accountList = res.data.data;
            } catch (err) {
                console.error("Gagal mengambil data akun:", err);
            }
        },
        updateActiveMenu(menu) {
            this.activeMenu = menu;
        },
        navigateTo(action, akun) {
            localStorage.setItem(`dataAkun${action}`, JSON.stringify(akun));
            this.$router.push(`/akun-${action}/${akun.id}`);
        },
        confirmDelete(akun) {
            this.akunToDelete = akun;
            this.showModal = true;
        },
        cancelDelete() {
            this.akunToDelete = null;
            this.showModal = false;
        },
        async deleteAccount() {
            try {
                const token = localStorage.getItem("token");
                await axios.delete(`http://localhost:8000/api/admin/${this.akunToDelete.id}`, {
                    headers: { Authorization: `Bearer ${token}` }
                });
                this.successMessage = "Akun berhasil dihapus!";
                this.showSuccessAlert = true;
                setTimeout(() => (this.showSuccessAlert = false), 2000);
                this.fetchAccounts();
            } catch (err) {
                console.error("Gagal menghapus akun:", err);
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
    padding: 12px 16px;
    text-align: center;
    font-size: 14px;
    border: 1px solid #ccc;
    word-wrap: break-word;
}
</style>
