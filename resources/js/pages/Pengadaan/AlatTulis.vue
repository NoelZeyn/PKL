<template>
    <div class="flex h-screen bg-gray-100">
        <Sidebar :activeMenu="activeMenu" @update:activeMenu="updateActiveMenu" />
        <div class="flex-1 p-8 pt-4 bg-white">
            <HeaderBar title="Manajemen ATK" class="mt-3" />
            <div class="my-4 border-b border-gray-300"></div>

            <div class="pb-12">
                <div class="filters flex flex-wrap gap-4 mb-4">
                    <!-- Search Input -->
                    <div class="relative flex-1 min-w-[200px]">
                        <input type="text" v-model="searchQuery" @input="onInputSearch"
                            placeholder="Cari Nama Barang..."
                            class="w-full border border-gray-300 rounded-md py-2 pl-10 pr-4 text-sm text-gray-700" />
                        <img src="@/assets/search.svg" alt="Search"
                            class="w-5 h-5 absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400" />
                    </div>

                    <!-- Filter Rekomendasi -->
                    <div class="relative flex-1 min-w-[200px]">
                        <select v-model="rekomendasiFilter"
                            class="w-full border border-gray-300 rounded-md py-2 pl-3 pr-4 text-sm text-gray-700">
                            <option value="">Semua Rekomendasi</option>
                            <option value="perlu">Perlu Pengajuan</option>
                            <option value="aman">Aman</option>
                        </select>
                    </div>
                </div>


                <!-- Tabel Data -->
                <div class="bg-white rounded-lg shadow border border-gray-300 mt-8 overflow-hidden">
                    <div class="flex justify-between items-center px-5 p-3 border-b border-gray-300">
                        <h3 class="text-sm font-semibold text-gray-900">
                            Data ATK
                        </h3>

                        <router-link to="/alat-add"
                            class="text-sm font-semibold text-[#074a5d] no-underline hover:text-[#0066cc] hover:no-underline">
                            Tambah ATK
                        </router-link>
                    </div>

                    <table class="w-full table-fixed border-collapse border border-gray-300">
                        <thead class="bg-gray-100 text-[#7d7f81]">
                            <tr>
                                <th class="w-14">No</th>
                                <th class="p-3 border">Nama Barang</th>
                                <!-- <th class="p-3 w-20 border">Satuan</th> -->
                                <!-- <th class="p-3 w-29 border">Keterangan</th> -->
                                <th class="p-3 border">Stock Min</th>
                                <th class="p-3 border">Stock Max</th>
                                <th class="p-3 w-25 border">Stock Sekarang</th>
                                <!-- <th class="p-3 border">Order</th> -->
                                <th class="p-3 border">Harga Satuan</th>
                                <th class="p-3 border">Rekomendasi Pembelian</th>
                                <th class="p-3 border">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(alat, index) in paginatedAlatList" :key="alat.id" class="text-[#333436]">
                                <td class="p-3">{{ (currentPage - 1) * itemsPerPage + index + 1 }}</td>
                                <td class="p-3">{{ alat.nama_barang }}</td>
                                <!-- <td class="p-3">{{ alat.satuan }}</td> -->
                                <!-- <td class="p-3">{{ alat.keterangan }}</td> -->
                                <td class="p-3">{{ alat.stock_min }}</td>
                                <td class="p-3">{{ alat.stock_max }}</td>
                                <td class="p-3">{{ alat.stock }}</td>
                                <!-- <td class="p-3">{{ alat.order }}</td> -->
                                <td class="p-3">{{ formatRupiah(alat.harga_satuan) }}</td>
                                <td class="p-3">
                                    <span v-if="alat.stock <= alat.stock_min" class="text-red-600 font-semibold">Perlu
                                        Pengajuan</span>
                                    <span v-else class="text-green-600">Aman</span>
                                </td>

                                <td class="p-3">
                                    <div class="flex items-center space-x-2 justify-center">
                                        <button title="Informasi" @click="
                                            navigateTo('info', alat)
                                            " class="cursor-pointer hover:opacity-70 border-r-1 pr-2 cursor-pointer">
                                            <img :src="informasiIcon" alt="Informasi" class="w-5 h-5 object-contain" />
                                        </button>
                                        <button title="Edit" @click="navigateTo('edit', alat)"
                                            class="cursor-pointer hover:opacity-70 border-r-1 pr-2 cursor-pointer">
                                            <img :src="updateIcon" alt="Update" class="w-5 h-5 object-contain" />
                                        </button>
                                        <button title="Hapus" @click="confirmDelete(alat)"
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

            alatList: [],
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

      const rekomendasiMatch =
        !this.rekomendasiFilter ||
        (this.rekomendasiFilter === 'perlu' && a.stock <= a.stock_min) ||
        (this.rekomendasiFilter === 'aman' && a.stock > a.stock_min);

      return searchMatch && rekomendasiMatch;
    })
    .sort((a, b) => a.stock - b.stock);
},


        paginatedAlatList() {
            const start = (this.currentPage - 1) * this.itemsPerPage;
            return this.filteredAlatList.slice(start, start + this.itemsPerPage);
        },
        totalPages() {
            return Math.ceil(this.filteredAlatList.length / this.itemsPerPage);
        },
    },

    created() {
        this.fetchAlat();
    },

    methods: {
        formatRupiah(angka) {
            if (!angka) return "-";
            return (
                "Rp. " + angka.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".")
            );
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
                await axios.delete(`http://localhost:8000/api/alat/${this.alatToDelete.id_alat}`, {
                    headers: { Authorization: `Bearer ${token}` }
                });
                this.successMessage = "Alat berhasil dihapus!";
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
    padding: 12px 16px;
    text-align: center;
    font-size: 14px;
    border: 1px solid #ccc;
    word-wrap: break-word;
}
</style>
