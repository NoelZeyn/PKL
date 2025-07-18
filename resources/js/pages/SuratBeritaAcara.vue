<template>
    <div class="flex h-screen bg-gray-100">
        <Sidebar :activeMenu="activeMenu" @update:activeMenu="activeMenu = $event" />

        <div class="flex-1 p-8 pt-4 bg-white">
            <HeaderBar title="Surat Berita Acara Pengajuan" />
            <div class="my-4 border-b border-gray-300"></div>

            <div class="pb-12">
                <div class="filters space-y-4">
                    <div class="relative">
                        <input type="text" v-model="searchQuery" @input="onInputSearch"
                            placeholder="Cari Nama Barang ..."
                            class="w-full border border-gray-300 rounded-md py-2 pl-10 pr-4 text-sm text-gray-700" />
                        <img src="@/assets/search.svg" alt="Search"
                            class="w-5 h-5 absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400" />
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow border border-gray-300 mt-8 overflow-hidden">
                    <div class="flex justify-between items-center px-5 p-3 border-b border-gray-300">
                        <h3 class="text-sm font-semibold text-gray-900">Riwayat Pengajuan Barang</h3>

                        <button @click="extractPDF"
                            class="flex items-center gap-2 px-4 py-2 bg-[#08607a] hover:bg-[#065666] text-white text-sm rounded-lg shadow transition duration-200 cursor-pointer">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                            </svg>
                            Download PDF
                        </button>
                    </div>

                    <table class="w-full table-fixed border-collapse border border-gray-300">
                        <thead class="bg-gray-100 text-[#7d7f81]">
                            <tr>
                                <th class="w-16">No</th>
                                <th class="p-3 border">Nama Barang</th>
                                <th class="p-3 border">Jumlah</th>
                                <th class="p-3 border">Keterangan</th>
                                <th class="p-3 border">Tanggal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(item, index) in paginatedData" :key="index" class="text-[#333436]">
                                <td class="p-3">{{ (currentPage - 1) * itemsPerPage + index + 1 }}</td>
                                <td class="p-3">{{ item.alat?.nama_barang || '-' }}</td>
                                <td class="p-3">{{ item.jumlah }}</td>
                                <td class="p-3">{{ item.keterangan || '-' }}</td>
                                <td class="p-3">{{ formatTanggal(item.tanggal_permintaan) }}</td>
                            </tr>
                            <tr v-if="paginatedData.length === 0">
                                <td colspan="5" class="text-center p-4 text-gray-500">Data tidak ditemukan</td>
                            </tr>
                        </tbody>
                    </table>

                    <div
                        class="flex justify-between items-center px-4 py-3 border-t border-gray-300 text-sm text-[#333436]">
                        <button @click="prevPage" :disabled="currentPage === 1"
                            class="px-3 py-1 rounded bg-gray-200 hover:bg-gray-300 disabled:opacity-50">Prev</button>
                        <span>Halaman {{ currentPage }} dari {{ totalPages }}</span>
                        <button @click="nextPage" :disabled="currentPage === totalPages"
                            class="px-3 py-1 rounded bg-gray-200 hover:bg-gray-300 disabled:opacity-50">Next</button>
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

export default {
    name: "suratBeritaAcara",
    components: { Sidebar, HeaderBar },
    data() {
        return {
            activeMenu: "suratBeritaAcara",
            requestList: [],
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
            return Math.ceil(this.filteredData.length / this.itemsPerPage) || 1;
        },
    },
    created() {
        this.fetchRequests();
    },
    methods: {
        fetchRequests() {
            const token = localStorage.getItem("token");
            axios
                .get("http://localhost:8000/api/berita-acara", {
                    headers: { Authorization: `Bearer ${token}` },
                })
                .then(res => {
                    this.requestList = res.data.data;
                })
                .catch(err => {
                    console.error("Gagal mengambil data:", err);
                });
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
        async extractPDF() {
            const token = localStorage.getItem("token");
            try {
                const res = await axios.get("http://localhost:8000/api/export-pdf", {
                    headers: {
                        Authorization: `Bearer ${token}`,
                        Accept: "application/pdf",
                    },
                    responseType: "blob",
                });

                const url = window.URL.createObjectURL(new Blob([res.data], { type: "application/pdf" }));
                const link = document.createElement('a');
                link.href = url;
                link.setAttribute("download", `berita-acara-${new Date().toISOString().slice(0, 10)}.pdf`);
                document.body.appendChild(link);
                link.click();
                link.remove();
            } catch (err) {
                console.error("Gagal download PDF:", err);
            }
        }



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
