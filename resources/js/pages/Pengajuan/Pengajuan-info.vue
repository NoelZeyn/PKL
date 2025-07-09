<template>
    <div class="flex h-screen bg-gray-100">
        <Sidebar :activeMenu="activeMenu" @update:activeMenu="activeMenu = $event" />

        <div class="flex-1 p-8 pt-7 flex flex-col bg-white">
            <HeaderBar title="Informasi Pengajuan" />
            <div class="border-b border-gray-300"></div>

            <div class="bg-white p-6 rounded-2xl shadow mt-8">
                <h3 class="text-[15px] text-[#074a5d] font-semibold mb-4">Informasi Pengajuan</h3>
                <div class="h-[1px] w-[calc(100%+47px)] bg-gray-300 my-4 -ml-6"></div>

                <div class="flex flex-col gap-4 mx-9">
                    <h4 class="text-[15px] font-medium text-black text-center pb-3">Form Informasi Pengajuan</h4>

                    <div class="flex items-center gap-5">
                        <label class="min-w-[150px] font-semibold text-sm text-black">NID Pengajuan</label>
                        <input type="text" :value="formData.user?.NID || '-'" disabled
                            class="w-full p-2 border border-gray-300 rounded-lg bg-gray-100 text-sm text-gray-700" />
                    </div>

                    <div class="flex items-center gap-5">
                        <label class="min-w-[150px] font-semibold text-sm text-black">Nama Barang</label>
                        <input type="text" :value="formData.alat?.nama_barang || '-'" disabled
                            class="w-full p-2 border border-gray-300 rounded-lg bg-gray-100 text-sm text-gray-700" />
                    </div>

                    <div class="flex items-center gap-5">
                        <label class="min-w-[150px] font-semibold text-sm text-black">Stock Minimal</label>
                        <input type="text" :value="formData.alat?.stock_min ?? '-'" disabled
                            class="w-full p-2 border border-gray-300 rounded-lg bg-gray-100 text-sm text-gray-700" />
                    </div>

                    <div class="flex items-center gap-5">
                        <label class="min-w-[150px] font-semibold text-sm text-black">Stock Maksimal</label>
                        <input type="text" :value="formData.alat?.stock_max ?? '-'" disabled
                            class="w-full p-2 border border-gray-300 rounded-lg bg-gray-100 text-sm text-gray-700" />
                    </div>

                    <div class="flex items-center gap-5">
                        <label class="min-w-[150px] font-semibold text-sm text-black">Satuan</label>
                        <input type="text" :value="formData.alat?.satuan ?? '-'" disabled
                            class="w-full p-2 border border-gray-300 rounded-lg bg-gray-100 text-sm text-gray-700" />
                    </div>

                    <div class="flex items-center gap-5">
                        <label class="min-w-[150px] font-semibold text-sm text-black">Harga Satuan</label>
                        <input type="text" :value="formatRupiah(formData.alat?.harga_satuan)" disabled
                            class="w-full p-2 border border-gray-300 rounded-lg bg-gray-100 text-sm text-gray-700" />
                    </div>

                    <div class="flex items-center gap-5">
                        <label class="min-w-[150px] font-semibold text-sm text-black">Tanggal Permintaan</label>
                        <input type="text" :value="formatTanggal(formData.tanggal_permintaan)" disabled
                            class="w-full p-2 border border-gray-300 rounded-lg bg-gray-100 text-sm text-gray-700" />
                    </div>

                    <div class="flex items-center gap-5">
                        <label class="min-w-[150px] font-semibold text-sm text-black">Jumlah Pengajuan</label>
                        <input type="text" :value="formData.jumlah ?? '-'" disabled
                            class="w-full p-2 border border-gray-300 rounded-lg bg-gray-100 text-sm text-gray-700" />
                    </div>
                    
                    <div class="flex items-center gap-5">
                        <label class="min-w-[150px] font-semibold text-sm text-black">Total Harga</label>
                        <input type="text" :value="formatRupiah(formData.total)" disabled
                        class="w-full p-2 border border-gray-300 rounded-lg bg-gray-100 text-sm text-gray-700" />
                    </div>
                    <div class="flex items-center gap-5">
                        <label class="min-w-[150px] font-semibold text-sm text-black">Keterangan</label>
                        <input type="text" :value="formData.keterangan" disabled
                        class="w-full p-2 border border-gray-300 rounded-lg bg-gray-100 text-sm text-gray-700" />
                    </div>
                    <div class="flex items-center gap-5">
                        <label class="min-w-[150px] font-semibold text-sm text-black">Status Diperbarui Oleh</label>
                        <input type="text" :value="formData.status_by ?? '-'" disabled
                            class="w-full p-2 border border-gray-300 rounded-lg bg-gray-100 text-sm text-gray-700" />
                    </div>

                    <div class="flex items-center gap-5">
                        <label class="min-w-[150px] font-semibold text-sm text-black">Status Pengajuan</label>
                        <span :class="['px-3 py-1 rounded-full text-xs font-semibold', statusBadge(formData.status)]">
                            {{ statusLabel(formData.status) }}
                        </span>
                    </div>

                    <div class="flex justify-between items-center mt-6">
                        <router-link to="/manajemen-alat">
                            <button
                                class="bg-[#074a5d] cursor-pointer text-white px-4 py-2 rounded-lg hover:bg-[#063843] transition">
                                Kembali
                            </button>
                        </router-link>
                    </div>
                </div>

            </div>
            <br />
        </div>
    </div>
</template>

<script>
import Sidebar from "@/components/Sidebar.vue";
import HeaderBar from "@/components/HeaderBar.vue";
import axios from "axios";

export default {
    name: "ManajemenPengajuanInfo",
    components: {
        Sidebar,
        HeaderBar,
    },
    data() {
        return {
            activeMenu: "pengajuan",
            kategoriList: [],
            formData: {
                NID: "",
                nama_barang: "",
                id_kategori_fk: "",
                keterangan: "",
                stock_min: "",
                stock_max: "",
                satuan: "",
                harga_satuan: "",
                harga_estimasi: "",
                tanggal_permintaan: "",
                status: "",
                keterangan: "",
                status_by: "",
                jumlah: "",
                total: "",
            },

        };
    },
    mounted() {
        this.fetchPengajuan();
    },
    methods: {
        formatRupiah(angka) {
            if (!angka) return '-';
            return 'Rp. ' + angka.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        },

        formatTanggal(dateString) {
            if (!dateString) return '-';
            const date = new Date(dateString);
            return date.toLocaleDateString('id-ID', { day: '2-digit', month: '2-digit', year: 'numeric' });
        },

        statusBadge(status) {
            const map = {
                draft: 'bg-yellow-200 text-yellow-800',
                waiting_approval_1: 'bg-yellow-200 text-yellow-800',
                waiting_approval_2: 'bg-yellow-200 text-yellow-800',
                waiting_approval_3: 'bg-yellow-200 text-yellow-800',
                approved: 'bg-green-200 text-green-800',
                rejected: 'bg-red-200 text-red-800',
            };
            return map[status] || 'bg-gray-200 text-gray-800';
        },

        statusLabel(status) {
            const map = {
                draft: 'Draft',
                waiting_approval_1: 'Approval 1',
                waiting_approval_2: 'Approval 2',
                waiting_approval_3: 'Approval 3',
                approved: 'Disetujui',
                rejected: 'Ditolak',
            };
            return map[status] || status;
        },
        fetchPengajuan() {
            const token = localStorage.getItem("token");
            const id = this.$route.params.id; // Ambil ID dari route params
            axios
                .get(`http://localhost:8000/api/request/${id}`, {
                    headers: {
                        Authorization: `Bearer ${token}`,
                        Accept: "application/json",
                    },
                })
                .then((response) => {
                    this.formData = response.data.data;
                })

                .catch((error) => {
                    console.error("Error fetching alat:", error);
                });
        },
    },
};
</script>
