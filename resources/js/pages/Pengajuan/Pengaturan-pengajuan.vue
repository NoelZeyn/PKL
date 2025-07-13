<template>
    <div class="flex h-screen bg-gray-100">
        <Sidebar :activeMenu="activeMenu" @update:activeMenu="activeMenu = $event" />

        <div class="flex-1 p-8 pt-7 bg-white">
            <HeaderBar title="Pengaturan Pengajuan" />
            <div class="border-b border-gray-300 mb-6"></div>

            <div class="max-w-2xl mx-auto bg-white p-6 rounded-2xl shadow">
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <label class="text-sm font-medium text-gray-700">Status Pengajuan</label>
                        <select v-model="isOpen" class="border border-gray-300 rounded px-3 py-1 text-sm">
                            <option :value="true">Dibuka</option>
                            <option :value="false">Ditutup</option>
                        </select>
                    </div>

                    <div>
                        <label class="text-sm font-medium text-gray-700 block mb-1">Tanggal Mulai</label>
                        <input type="date" v-model="tanggalMulai"
                            class="w-full border border-gray-300 rounded px-3 py-2 text-sm" />
                    </div>

                    <div>
                        <label class="text-sm font-medium text-gray-700 block mb-1">Tanggal Selesai</label>
                        <input type="date" v-model="tanggalSelesai"
                            class="w-full border border-gray-300 rounded px-3 py-2 text-sm" />
                    </div>

                    <button @click="simpan"
                        class="cursor-pointer mt-4 bg-blue-600 text-white text-sm px-4 py-2 rounded hover:bg-blue-700">
                        Simpan Pengaturan
                    </button>

                    <SuccessAlert :visible="showSuccessAlert" :message="successMessage" />
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import Sidebar from "@/components/Sidebar.vue";
import HeaderBar from "@/components/HeaderBar.vue";
import SuccessAlert from "@/components/SuccessAlert.vue";
import axios from "axios";

export default {
    name: "PengaturanPengajuan",
    components: { Sidebar, HeaderBar, SuccessAlert },

    data() {
        return {
            activeMenu: "pengaturan-pengajuan",
            isOpen: false,
            tanggalMulai: "",
            tanggalSelesai: "",
            showSuccessAlert: false,
            successMessage: "",
        };
    },

    mounted() {
        this.getPengaturan();
    },

    methods: {
        async getPengaturan() {
            const token = localStorage.getItem("token");
            try {
                const res = await axios.get("http://localhost:8000/api/pengaturan-pengajuan", {
                    headers: { Authorization: `Bearer ${token}` },
                });

                const data = res.data;
                this.isOpen = data.is_open;
                this.tanggalMulai = data.tanggal_mulai || "";
                this.tanggalSelesai = data.tanggal_selesai || "";
            } catch (err) {
                console.error("Gagal mengambil data pengaturan:", err);
            }
        },

        async simpan() {
            const token = localStorage.getItem("token");
            try {
                await axios.post(
                    "http://localhost:8000/api/pengaturan-pengajuan",
                    {
                        is_open: this.isOpen,
                        tanggal_mulai: this.tanggalMulai,
                        tanggal_selesai: this.tanggalSelesai,
                    },
                    { headers: { Authorization: `Bearer ${token}` } }
                );

                this.successMessage = "Pengaturan berhasil disimpan.";
                this.showSuccessAlert = true;
                setTimeout(() => {
                    this.showSuccessAlert = false;
                    this.$router.push("/pengajuan");
                }, 2000);
            } catch (err) {
                console.error("Gagal menyimpan pengaturan:", err);
            }
        },
    },
};
</script>

<style scoped>
label {
    font-weight: 500;
}
</style>
