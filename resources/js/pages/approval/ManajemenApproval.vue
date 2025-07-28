    <template>
        <div class="flex flex-col md:flex-row min-h-screen bg-gray-100">
            <Sidebar :activeMenu="activeMenu" @update:activeMenu="updateActiveMenu" />
            <div class="flex-1 p-4 sm:p-6 md:p-8 pt-4 bg-white overflow-auto">
                <HeaderBar title="Data Approval" class="mt-3" />
                <div class="my-4 border-b border-gray-300"></div>

                <div class="pb-12">
                    <div v-if="isAsman || isManajer" class="filters space-y-4">
                        <div class="relative">
                            <input type="text" v-model="searchQuery" @input="onInputSearch"
                                placeholder="Data List Pengajuan : Cari Nama Barang atau Nama Pemohon..."
                                class="w-full border border-gray-300 rounded-md py-2 pl-10 pr-4 text-sm text-gray-700" />
                            <img src="@/assets/search.svg" alt="Search"
                                class="w-5 h-5 absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400" />
                        </div>
                    </div>
                    <div v-if="isSuperAdmin || isAdmin" class="filters space-y-4">
                        <div class="relative">
                            <input type="text" v-model="searchQuery" @input="onInputSearch"
                                placeholder="Pencarian nama barang, catatan, status, nama kategori pengajuan"
                                class="w-full border border-gray-300 rounded-md py-2 pl-10 pr-4 text-sm text-gray-700" />
                            <img src="@/assets/search.svg" alt="Search"
                                class="w-5 h-5 absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400" />
                        </div>
                    </div>

                    <div v-if="isAsman || isManajer || isAnggaran"
                        class="bg-white rounded-lg shadow border border-gray-300 mt-8 overflow-hidden">
                        <div class="flex justify-between items-center px-5 p-3 border-b border-gray-300">
                            <h3 class="text-sm font-semibold text-gray-900">
                                Data List Pengajuan {{ tingkatanOtoritas }}
                            </h3>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full table-fixed border-collapse border border-gray-300">
                                <thead class="bg-gray-100 text-[#7d7f81]">
                                    <tr>
                                        <!-- <th class="p-3 border">Nama Penempatan</th> -->
                                        <th v-if="tingkatanOtoritas !== 'anggaran'" class="p-3 border">
                                            Nama Barang
                                        </th>
                                        <th v-if="tingkatanOtoritas === 'anggaran'" class="p-3 border">
                                            Nama Bidang
                                        </th>
                                        <th v-if="tingkatanOtoritas !== 'anggaran'" class="p-3 border">
                                            Jumlah Order
                                        </th>
                                        <th v-if="tingkatanOtoritas !== 'anggaran'" class="p-3 border">
                                            Harga Satuan
                                        </th>
                                        <th class="p-3 border">Total Harga</th>
                                        <th class="p-3 border">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <template v-if="isAnggaran">
                                        <tr v-for="(pengajuan, index) in paginatedPengajuanList" :key="index"
                                            class="text-[#333436]">
                                            <td class="p-3">
                                                {{ pengajuan.nama_bidang || "-" }}
                                            </td>
                                            <td class="p-3">
                                                {{
                                                    formatRupiah(
                                                        pengajuan.total_harga_barang
                                                    )
                                                }}
                                            </td>
                                            <td class="p-3">
                                                <div class="flex items-center justify-center space-x-3">
                                                    <button @click="
                                                        approvePengajuan(
                                                            pengajuan.id_bidang_fk,
                                                            null
                                                        )
                                                        " title="Setujui"
                                                        class="cursor-pointer hover:bg-green-600 text-white rounded-md w-8 h-8 flex items-center justify-center shadow hover:shadow-lg transform hover:scale-105 transition duration-150">
                                                        <span class="text-sm font-bold">✔</span>
                                                    </button>
                                                    <button @click="
                                                        rejectPengajuan(
                                                            pengajuan.id_bidang_fk,
                                                            null
                                                        )
                                                        " title="Tolak"
                                                        class="cursor-pointer hover:bg-red-600 text-white rounded-md w-8 h-8 flex items-center justify-center shadow hover:shadow-lg transform hover:scale-105 transition duration-150">
                                                        <span class="text-sm font-bold">✖</span>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    </template>

                                    <template v-else>
                                        <template v-for="(pengajuan, index) in paginatedPengajuanList"
                                            :key="pengajuan.id_penempatan_fk">
                                            <tr v-for="(item, idx) in pengajuan.barang" :key="idx"
                                                class="text-[#333436]">
                                                <td class="p-3">
                                                    {{ item.nama_barang || "-" }}
                                                </td>
                                                <td class="p-3">
                                                    {{ item.total_jumlah || "-" }}
                                                </td>
                                                <td class="p-3">
                                                    {{
                                                        formatRupiah(item.harga_satuan)
                                                    }}
                                                </td>
                                                <td class="p-3">
                                                    {{ formatRupiah(item.total_harga) }}
                                                </td>
                                                <td class="p-3">
                                                    <div class="flex items-center justify-center space-x-3">
                                                        <button @click="
                                                            approvePengajuan(
                                                                isManajer
                                                                    ? pengajuan.id_bidang_fk
                                                                    : pengajuan.id_penempatan_fk,
                                                                item.id_alat
                                                            )
                                                            " title="Setujui"
                                                            class="cursor-pointer hover:bg-green-600 text-white rounded-md w-8 h-8 flex items-center justify-center shadow hover:shadow-lg transform hover:scale-105 transition duration-150">
                                                            <span class="text-sm font-bold">✔</span>
                                                        </button>
                                                        <button @click="
                                                            rejectPengajuan(
                                                                isManajer
                                                                    ? pengajuan.id_bidang_fk
                                                                    : pengajuan.id_penempatan_fk,
                                                                item.id_alat
                                                            )
                                                            " title="Tolak"
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
                    </div>

                    <!-- table untuk pengajuan jenis atk baru -->
                    <div v-if="isSuperAdmin || isAdmin"
                        class="bg-white rounded-lg shadow border border-gray-300 mt-8 overflow-hidden">
                        <div class="flex justify-between items-center px-5 p-3 border-b border-gray-300">
                            <h3 class="text-sm font-semibold text-gray-900">
                                Data List Pengajuan ATK Baru
                            </h3>
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
                                        <th class="p-3 border">Aksi</th>
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
                                        <td class="p-3">
                                            <div class="flex flex-col items-center space-y-2">
                                                <!-- Label Delete -->
                                                <!-- <span v-if="tingkatanOtoritas === 'admin' || tingkatanOtoritas === 'superadmin'" class="text-xs text-gray-600">Delete</span> -->

                                                <!-- Tombol Delete -->
                                                <!-- <button v-if="
                                                tingkatanOtoritas === 'admin' ||
                                                tingkatanOtoritas ===
                                                'superadmin'
                                            " @click="confirmDelete(item)" title="Hapus"
                                                class="cursor-pointer hover:bg-white text-white rounded-md w-6 h-6 flex items-center justify-center shadow hover:shadow-lg transform hover:scale-105 transition duration-150">
                                                <img :src="deleteIcon" alt="Delete" class="w-4 h-4 object-contain" />
                                            </button> -->

                                                <!-- Tombol Approve & Reject -->
                                                <div class="flex space-x-2 pt-2">
                                                    <button @click="approvePengajuan(item.id, null)" title="Setujui"
                                                        class="cursor-pointer hover:bg-green-600 text-white rounded-md w-6 h-6 flex items-center justify-center shadow hover:shadow-lg transform hover:scale-105 transition duration-150">
                                                        <span class="text-sm font-bold">✔</span>
                                                    </button>

                                                    <button @click="rejectPengajuan(item.id, null)" title="Tolak"
                                                        class="cursor-pointer hover:bg-red-600 text-white rounded-md w-6 h-6 flex items-center justify-center shadow hover:shadow-lg transform hover:scale-105 transition duration-150">
                                                        <span class="text-sm font-bold">✖</span>
                                                    </button>
                                                </div>
                                            </div>
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

                    <div v-if="isAdmin || isSuperAdmin"
                        class="bg-white rounded-lg shadow border border-gray-300 mt-8 overflow-hidden">
                        <div class="flex justify-between items-center px-5 p-3 border-b border-gray-300">
                            <h3 class="text-sm font-semibold text-gray-900">
                                Data Rekapitulasi Pengajuan per Bidang
                            </h3>
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
                                        <th class="p-3 border">Aksi</th>
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
                                                <td class="p-3 border">{{ barang.status || '-' }}</td>
                                                <td class="p-3 border text-center">
                                                    <template
                                                        v-if="['approved', 'purchasing', 'on_the_way'].includes(barang.status)">
                                                        <select v-model="barang.selectedStatus"
                                                            @change="updateStatus(group.id_bidang_fk, barang.id_alat, barang.selectedStatus)"
                                                            class="border rounded text-sm px-2 py-1">
                                                            <option disabled value="">Ubah status...</option>
                                                            <option v-if="barang.status === 'approved'"
                                                                value="purchasing">
                                                                Purchasing</option>
                                                            <option v-if="barang.status === 'purchasing'"
                                                                value="on_the_way">On the Way</option>
                                                            <option v-if="barang.status === 'on_the_way'" value="done">
                                                                Done
                                                            </option>
                                                        </select>
                                                    </template>
                                                    <template v-else>
                                                        <span>
                                                            No More Action
                                                        </span>
                                                    </template>
                                                </td>
                                            </tr>
                                        </template>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>
            </div>

            <SuccessAlert :visible="showSuccessAlert" :message="successMessage" />
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
            activeMenu: "manajemenApproval",
            searchQuery: "",
            tingkatanOtoritas: "",
            showModal: false,
            showSuccessAlert: false,
            successMessage: "",
            requestToDelete: null,
            requestList: [],
            pengajuanList: [],
            PengajuanBaruList: [],
            dataGroupedByBidang: [],

            informasiIcon,
            updateIcon,
            deleteIcon,

            showRejectModal: false,
            rejectReason: "",
            rejectTarget: null,

            currentPage: 1,
            currentPagePengajuanBaru: 1,

            itemsPerPage: 10,
        };
    },

    computed: {
        filteredPengajuanList() {
            if (!this.searchQuery) return this.pengajuanList;

            const query = this.searchQuery.toLowerCase();

            return this.pengajuanList
                .map((pengajuan) => {
                    const filteredBarang = pengajuan.barang?.filter((item) =>
                        item.nama_barang?.toLowerCase().includes(query)
                    );

                    if (filteredBarang?.length) {
                        return {
                            ...pengajuan,
                            barang: filteredBarang,
                        };
                    }
                    return null;
                })
                .filter((item) => item !== null);
        },

        paginatedPengajuanList() {
            const start = (this.currentPage - 1) * this.itemsPerPage;
            return this.filteredPengajuanList.slice(start, start + this.itemsPerPage);
        },

        totalPages() {
            return Math.ceil(this.filteredPengajuanList.length / this.itemsPerPage);
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
            const start = (this.currentPagePengajuanBaru - 1) * this.itemsPerPage;
            return this.filteredPengajuanBaruList.slice(start, start + this.itemsPerPage);
        },

        totalPagesPengajuanBaru() {
            return Math.ceil(this.filteredPengajuanBaruList.length / this.itemsPerPage);
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
        isAdmin() {
            return this.tingkatanOtoritas === "admin";
        },
        isSuperAdmin() {
            return this.tingkatanOtoritas === "superadmin";
        },
    },

    async created() {
        await this.getUserInfo();
        this.fetchPengajuan();
        this.fetchPengajuanBaru();
        this.fetchPengajuanAdminTable();
    },

    methods: {
        async updateStatus(id_bidang_fk, id_alat, newStatus) {
            if (newStatus === 'batalkan') {
                // reset pilihan (optional)
                return;
            }

            try {
                await axios.patch(`/api/update-status`, {
                    id_bidang_fk,
                    id_alat,
                    status: newStatus,
                });

                // update status di frontend
                const group = this.dataGroupedByBidang.find(g => g.id_bidang_fk === id_bidang_fk);
                if (group) {
                    const item = group.barang.find(b => b.id_alat === id_alat);
                    if (item) {
                        item.status = newStatus;
                    }
                }

                this.$toast?.success?.('Status berhasil diubah.');
            } catch (error) {
                console.error(error);
                this.$toast?.error?.('Gagal mengubah status.');
            }
        },
        async getUserInfo() {
            try {
                const token = localStorage.getItem("token");
                const res = await axios.post(
                    "http://localhost:8000/api/me",
                    {},
                    { headers: { Authorization: `Bearer ${token}` } }
                );
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

        async fetchPengajuanAdminTable() {
            try {
                const token = localStorage.getItem("token");
                const res = await axios.get(
                    "http://localhost:8000/api/admin",
                    { headers: { Authorization: `Bearer ${token}` } }
                );
                this.dataGroupedByBidang = res.data.data || [];
            } catch (error) {
                console.error("Gagal mengambil data rekap admin:", error);
            }
        },

        async approvePengajuan(id_fk, id_alat) {
            try {
                const token = localStorage.getItem("token");
                let url = "";
                let payload = { id_alat };

                if (this.isAdmin || this.isSuperAdmin) {
                    url = `http://localhost:8000/api/pengajuan-baru/approve/${id_fk}`;
                    payload = {};
                } else if (this.isAsman) {
                    url = "http://localhost:8000/api/asman/approve";
                    payload.id_penempatan_fk = id_fk;
                } else if (this.isManajer) {
                    url = "http://localhost:8000/api/manajer/approve";
                    payload.id_bidang_fk = id_fk;
                } else if (this.isAnggaran) {
                    url = "http://localhost:8000/api/anggaran/approve";
                    payload.id_bidang_fk = id_fk;
                } else {
                    return;
                }

                const res = await axios.patch(url, payload, {
                    headers: { Authorization: `Bearer ${token}` },
                });

                this.successMessage = res.data.message;
                this.showSuccessAlert = true;

                // refresh semua data
                this.fetchPengajuan();
                this.fetchPengajuanBaru();
                this.fetchPengajuanAdminTable();

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

            if (this.isAdmin || this.isSuperAdmin) {
                url = `http://localhost:8000/api/pengajuan-baru/reject/${id_fk}`;
                payload = { keterangan };
            } else if (this.isAsman) {
                url = "http://localhost:8000/api/asman/reject";
                payload.id_penempatan_fk = id_fk;
            } else if (this.isManajer) {
                url = "http://localhost:8000/api/manajer/reject";
                payload.id_bidang_fk = id_fk;
            } else if (this.isAnggaran) {
                url = "http://localhost:8000/api/anggaran/reject";
                payload.id_bidang_fk = id_fk;
            } else {
                this.showRejectModal = false;
                return;
            }

            axios
                .patch(url, payload, {
                    headers: { Authorization: `Bearer ${token}` },
                })
                .then((res) => {
                    this.successMessage = res.data.message;
                    this.showSuccessAlert = true;

                    // refresh semua data
                    this.fetchPengajuan();
                    this.fetchPengajuanBaru();
                    this.fetchPengajuanAdminTable();

                    setTimeout(() => (this.showSuccessAlert = false), 2000);
                })
                .catch((err) => {
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

        nextPage() {
            if (this.currentPage < this.totalPages) this.currentPage++;
        },

        prevPage() {
            if (this.currentPage > 1) this.currentPage--;
        },

        nextPagePengajuanBaru() {
            if (this.currentPagePengajuanBaru < this.totalPagesPengajuanBaru)
                this.currentPagePengajuanBaru++;
        },

        prevPagePengajuanBaru() {
            if (this.currentPagePengajuanBaru > 1) this.currentPagePengajuanBaru--;
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
