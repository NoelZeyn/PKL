<template>
    <div class="flex min-h-screen bg-gray-100">
        <Sidebar :activeMenu="activeMenu" @update:activeMenu="activeMenu = $event" />

        <div class="flex-1 p-6 md:p-8 bg-white overflow-y-auto">
            <HeaderBar title="Surat Berita Acara Pengajuan" class="mt-3"/>
            
            <div class="my-4 border-b border-gray-300"></div>

            <!-- Pencarian -->
            <div class="mb-6">
                <div class="relative max-w-md">
                    <input type="text" v-model="searchQuery" @input="onInputSearch" placeholder="Cari Nama Barang ..."
                        class="w-full border border-gray-300 rounded-md py-2 pl-10 pr-4 text-sm text-gray-700 focus:ring-2 focus:ring-[#08607a] focus:outline-none" />
                    <img src="@/assets/search.svg" alt="Search"
                        class="w-5 h-5 absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400" />
                </div>
            </div>

            <!-- Container Tabel -->
            <div class="bg-white rounded-lg shadow border border-gray-300">
                <!-- Filter dan Aksi -->
                <div class="bg-white border-b border-gray-300 px-5 py-4">
                    <div class="flex flex-col xl:flex-row xl:items-center xl:justify-between gap-4">
                        <!-- Judul -->
                        <h3 class="text-sm font-semibold text-gray-900 whitespace-nowrap">
                            Riwayat Pengajuan Barang
                        </h3>


                        <!-- Dropdown + Tombol -->
                        <div class="ml-[50px] grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            <!-- Dropdown Penempatan -->
                            <div class="flex flex-col sm:flex-row sm:items-center gap-2">
                                <label class="text-sm font-medium text-gray-700 whitespace-nowrap">Penempatan:</label>
                                <select v-model="selectedPenempatanId" @change="fetchUsersByPenempatan"
                                    class="text-gray-500 w-full border border-gray-300 px-3 py-2 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-[#08607a]">
                                    <option disabled value="">Pilih Penempatan</option>
                                    <option v-for="penempatan in penempatanList" :key="penempatan.id"
                                        :value="penempatan.id">
                                        {{ penempatan.nama_penempatan }}
                                    </option>
                                </select>
                            </div>

                            <!-- Dropdown User -->
                            <div class="flex flex-col sm:flex-row sm:items-center gap-2">
                                <label class="text-sm font-medium text-gray-700 whitespace-nowrap">User:</label>
                                <select v-model="selectedUserId"
                                    class="text-gray-500 w-full border border-gray-300 px-3 py-2 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-[#08607a]">
                                    <option disabled value="">-- Pilih User --</option>
                                    <option v-for="user in userList" :key="user.id" :value="user.id">
                                        {{ user.data_diri?.nama_lengkap || user.NID }}
                                    </option>
                                </select>
                            </div>

                            <!-- Tombol Download -->
                            <div class="flex items-center justify-start sm:justify-start">
                                <button @click="extractPDFByUser"
                                    class="cursor-pointer flex items-center gap-2 px-4 py-2 bg-[#08607a] hover:bg-[#065666] text-white text-sm rounded-lg shadow transition duration-200 whitespace-nowrap">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                                    </svg>
                                    Download PDF
                                </button>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Tabel -->
                <div class="overflow-x-auto">
                    <table class="min-w-full table-auto border-collapse border border-gray-300 text-sm">
                        <thead class="bg-gray-100 text-[#7d7f81]">
                            <tr>
                                <th class="w-16 p-3 border">No</th>
                                <th class="p-3 border">Nama Barang</th>
                                <th class="p-3 border">Jumlah</th>
                                <th class="p-3 border">Keterangan</th>
                                <th class="p-3 border">Tanggal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(item, index) in paginatedData" :key="index"
                                class="text-[#333436] hover:bg-gray-50 transition">
                                <td class="p-3 text-center">{{ (currentPage - 1) * itemsPerPage + index + 1 }}</td>
                                <td class="p-3">{{ item.alat?.nama_barang || '-' }}</td>
                                <td class="p-3 text-center">{{ item.jumlah }}</td>
                                <td class="p-3">{{ item.keterangan || '-' }}</td>
                                <td class="p-3 text-center">{{ formatTanggal(item.tanggal_permintaan) }}</td>
                            </tr>
                            <tr v-if="paginatedData.length === 0">
                                <td colspan="5" class="text-center p-4 text-gray-500">Pilih User untuk melihat data</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div
                    class="flex justify-between items-center px-5 py-4 border-t border-gray-300 text-sm text-[#333436]">
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
</template>

<script>
import Sidebar from "@/components/Sidebar.vue";
import HeaderBar from "@/components/HeaderBar.vue";
import axios from "axios";

export default {
    name: "suratBeritaAcara",
    components: { Sidebar, HeaderBar },
    data() {
        return {
            activeMenu: "suratBeritaAcara",
            requestList: [],
            userList: [],
            penempatanList: [],
            selectedPenempatanId: "",
            selectedUserId: "",
            searchQuery: "",
            currentPage: 1,
            itemsPerPage: 10,
        };
    },
    computed: {
        filteredData() {
            return this.requestList.filter(item =>
                item.alat?.nama_barang?.toLowerCase().includes(this.searchQuery.toLowerCase())
            );
        },
        paginatedData() {
            const start = (this.currentPage - 1) * this.itemsPerPage;
            return this.filteredData.slice(start, start + this.itemsPerPage);
        },
        totalPages() {
            return Math.max(1, Math.ceil(this.filteredData.length / this.itemsPerPage));
        },
        totalJumlah() {
            return this.filteredData.reduce((sum, item) => sum + Number(item.jumlah || 0), 0);
        }
    },
    created() {
        this.fetchPenempatanList();
    },
    watch: {
        selectedUserId(newUserId) {
            if (newUserId) {
                this.fetchRequestsByUser(newUserId);
            } else {
                this.requestList = [];
            }
        }
    },
    methods: {
        async fetchRequestsByUser(userId) {
            const token = localStorage.getItem("token");
            try {
                const res = await axios.get(`http://localhost:8000/api/berita-acara/user/${userId}`, {
                    headers: { Authorization: `Bearer ${token}` },
                });
                this.requestList = res.data.data;
            } catch (err) {
                console.error("Gagal mengambil data permintaan pengguna:", err);
                this.requestList = [];
            }
        },

        async fetchPenempatanList() {
            const token = localStorage.getItem("token");
            try {
                const res = await axios.get("http://localhost:8000/api/penempatan", {
                    headers: { Authorization: `Bearer ${token}` },
                });
                this.penempatanList = res.data.data;
            } catch (err) {
                console.error("Gagal mengambil daftar penempatan:", err);
            }
        },

        async fetchUsersByPenempatan() {
            if (!this.selectedPenempatanId) return;

            const token = localStorage.getItem("token");
            try {
                const res = await axios.get(
                    `http://localhost:8000/api/list-user/${this.selectedPenempatanId}`,
                    {
                        headers: { Authorization: `Bearer ${token}` },
                    }
                );
                this.userList = res.data.data.filter(user => user.access === "active");
                this.selectedUserId = ""; // reset user saat ganti penempatan
                this.requestList = [];
            } catch (err) {
                console.error("Gagal mengambil daftar user:", err);
                this.userList = [];
            }
        },

        formatTanggal(dateString) {
            const date = new Date(dateString);
            return date.toLocaleDateString("id-ID", {
                day: "2-digit",
                month: "2-digit",
                year: "numeric",
            });
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

        async extractPDFByUser() {
            if (!this.selectedUserId) {
                alert("Pilih user terlebih dahulu!");
                return;
            }

            const token = localStorage.getItem("token");
            try {
                const res = await axios.get(`http://localhost:8000/api/export-pdf/${this.selectedUserId}`, {
                    headers: {
                        Authorization: `Bearer ${token}`,
                        Accept: "application/pdf",
                    },
                    responseType: "blob",
                });

                const url = window.URL.createObjectURL(new Blob([res.data], { type: "application/pdf" }));
                const link = document.createElement("a");
                link.href = url;
                link.setAttribute("download", `berita-acara-${this.selectedUserId}.pdf`);
                document.body.appendChild(link);
                link.click();
                link.remove();
            } catch (err) {
                console.error("Gagal mengunduh PDF:", err);
            }
        }
    }
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
