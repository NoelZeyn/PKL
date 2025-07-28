<template>
    <div class="flex flex-col md:flex-row min-h-screen bg-gray-100">
        <!-- Sidebar -->
        <Sidebar :activeMenu="activeMenu" @update:activeMenu="activeMenu = $event" />

        <!-- Main Content -->
        <div class="flex-1 p-4 md:p-8 pt-6 flex flex-col bg-white">
            <HeaderBar title="Form Pengajuan ATK Baru" />
            <div class="border-b border-gray-300 mb-4"></div>

            <div class="bg-white p-4 md:p-6 rounded-2xl shadow w-full max-w-5xl mx-auto">
                <div class="flex flex-col gap-6">

                    <!-- Form Items -->
                    <div v-for="(item, index) in formData.items" :key="index"
                        class="border border-gray-200 p-4 rounded-lg shadow-sm space-y-4">
                        <div class="flex justify-between items-center mb-2">
                            <h4 class="font-semibold text-sm text-[#333]">Pengajuan ATK {{ index + 1 }}</h4>
                            <button v-if="formData.items.length > 1" @click="removeItem(index)"
                                class="text-red-500 text-xs hover:underline cursor-pointer">
                                Hapus
                            </button>
                        </div>

                        <!-- Nama Barang -->
                        <div class="flex flex-col md:flex-row md:items-center gap-2 md:gap-5">
                            <label class="w-full md:w-[150px] font-semibold text-sm text-black">Nama Barang</label>
                            <input type="text" v-model="item.nama_barang" placeholder="Masukkan nama barang"
                                class="w-full p-2 border border-gray-300 rounded-lg text-sm" required />
                        </div>

                        <!-- Satuan -->
                        <div class="flex flex-col md:flex-row md:items-center gap-2 md:gap-5">
                            <label class="w-full md:w-[150px] font-semibold text-sm text-black">Satuan</label>
                            <input type="text" v-model="item.satuan" placeholder="Contoh: pcs, box"
                                class="w-full p-2 border border-gray-300 rounded-lg text-sm" required />
                        </div>

                        <!-- Kategori -->
                        <div class="flex flex-col md:flex-row md:items-center gap-2 md:gap-5">
                            <label class="w-full md:w-[150px] font-semibold text-sm text-black">Kategori</label>
                            <select v-model="item.id_kategori_fk"
                                class="w-full p-2 border border-gray-300 rounded-lg text-sm cursor-pointer" required>
                                <option disabled value="">Pilih Kategori</option>
                                <option v-for="kategori in kategoriList" :key="kategori.id_kategori"
                                    :value="kategori.id_kategori">
                                    {{ kategori.nama_kategori }}
                                </option>
                            </select>
                        </div>

                        <!-- Harga Estimasi -->
                        <div class="flex flex-col md:flex-row md:items-center gap-2 md:gap-5">
                            <label class="w-full md:w-[150px] font-semibold text-sm text-black">Harga Estimasi</label>
                            <input type="number" v-model.number="item.harga_estimasi" placeholder="Masukkan harga"
                                class="w-full p-2 border border-gray-300 rounded-lg text-sm" />
                        </div>

                        <!-- Keterangan -->
                        <div class="flex flex-col md:flex-row gap-2 md:gap-5">
                            <label class="w-full md:w-[150px] font-semibold text-sm text-black">Keterangan</label>
                            <textarea v-model="item.keterangan" placeholder="Contoh: ATK untuk rapat bulanan"
                                class="w-full p-2 border border-gray-300 rounded-lg text-sm resize-y"></textarea>
                        </div>
                    </div>

                    <!-- Tambah Item -->
                    <button @click="addItem"
                        class="mt-2 w-fit bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg cursor-pointer">
                        + Tambah Pengajuan ATK
                    </button>

                    <!-- Alert Sukses -->
                    <SuccessAlert :visible="showSuccessAlert" :message="successMessage" />

                    <!-- Navigasi -->
                    <div class="flex flex-col md:flex-row justify-between items-center gap-4 mt-6">
                        <router-link to="/pengajuan" class="w-full md:w-auto">
                            <button
                                class="cursor-pointer w-full md:w-auto bg-[#074a5d] text-white px-4 py-2 rounded-lg hover:bg-[#063843] transition">
                                Kembali
                            </button>
                        </router-link>

                        <button @click="submitForm"
                            class="cursor-pointer w-full md:w-auto bg-[#074a5d] text-white px-4 py-2 rounded-lg hover:bg-[#063843] transition">
                            Simpan Semua Pengajuan
                        </button>
                    </div>
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
    name: "FormPengajuanATKBaru",
    components: { Sidebar, HeaderBar, SuccessAlert },
    data() {
        return {
            activeMenu: "pengajuan",
            kategoriList: [],
            formData: {
                items: [
                    {
                        nama_barang: "",
                        satuan: "",
                        harga_estimasi: "",
                        keterangan: "",
                        id_kategori_fk: ""
                    }
                ]
            },
            errors: [],
            showSuccessAlert: false,
            successMessage: "",
        };
    },
    mounted() {
        this.fetchKategori();
    },
    methods: {
        async fetchKategori() {
            const token = localStorage.getItem("token");
            try {
                const response = await axios.get("http://localhost:8000/api/kategori_pengadaan", {
                    headers: {
                        Authorization: `Bearer ${token}`,
                        Accept: "application/json",
                    },
                });
                this.kategoriList = response.data.data;
            } catch (error) {
                console.error("Error fetching kategori:", error);
            }
        },
        addItem() {
            this.formData.items.push({
                nama_barang: "",
                satuan: "",
                harga_estimasi: "",
                keterangan: "",
                id_kategori_fk: ""
            });
        },
        removeItem(index) {
            this.formData.items.splice(index, 1);
        },
        async submitForm() {
            this.errors = [];

            const isInvalid = this.formData.items.some((item, index) => {
                if (
                    !item.nama_barang ||
                    !item.satuan ||
                    !item.id_kategori_fk ||
                    item.harga_estimasi === "" ||
                    item.harga_estimasi === null
                ) {
                    this.errors.push(`Form pengajuan ATK ke-${index + 1} belum lengkap.`);
                    return true;
                }
                return false;
            });

            if (isInvalid) {
                alert(this.errors.join('\n'));
                return;
            }

            const token = localStorage.getItem("token");
            try {
                await axios.post("http://localhost:8000/api/pengajuan-baru", this.formData, {
                    headers: { Authorization: `Bearer ${token}` },
                });
                this.successMessage = "Pengajuan ATK berhasil disimpan.";
                this.showSuccessAlert = true;
                setTimeout(() => {
                    this.showSuccessAlert = false;
                    this.$router.push("/pengajuan");
                }, 2000);
            } catch (error) {
                console.error(error);
                alert("Gagal menyimpan pengajuan.");
            }
        }

    },
};
</script>
