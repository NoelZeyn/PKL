<template>
    <div class="flex h-screen bg-gray-100">
        <Sidebar :activeMenu="activeMenu" @update:activeMenu="activeMenu = $event" />
        <div class="flex-1 p-8 pt-7 flex flex-col bg-white">
            <HeaderBar title="Form Pengajuan ATK Baru" />
            <div class="border-b border-gray-300 mb-4"></div>

            <div class="bg-white p-6 rounded-2xl shadow">
                <div class="flex flex-col gap-6 mx-9">

                    <div v-for="(item, index) in formData.items" :key="index"
                        class="border border-gray-200 p-4 rounded-lg shadow-sm">
                        <div class="flex justify-between items-center mb-3">
                            <h4 class="font-semibold text-sm text-[#333]">Pengajuan ATK {{ index + 1 }}</h4>
                            <button v-if="formData.items.length > 1" @click="removeItem(index)"
                                class="text-red-500 text-xs hover:underline cursor-pointer">Hapus</button>
                        </div>

                        <div class="flex items-center gap-5 mb-3">
                            <label class="min-w-[150px] font-semibold text-sm text-black">Nama Barang</label>
                            <input type="text" v-model="item.nama_barang" placeholder="Masukkan nama barang"
                                class="w-full p-2 border border-gray-300 rounded-lg text-sm" required />
                        </div>

                        <div class="flex items-center gap-5 mb-3">
                            <label class="min-w-[150px] font-semibold text-sm text-black">Satuan</label>
                            <input type="text" v-model="item.satuan" placeholder="Contoh: pcs, box"
                                class="w-full p-2 border border-gray-300 rounded-lg text-sm" required />
                        </div>

                        <div class="flex items-center gap-5 mb-3">
                            <label class="min-w-[150px] font-semibold text-sm text-black">Kategori</label>
                            <select v-model="item.id_kategori_fk"
                                class="cursor-pointer w-full p-2 border border-gray-300 rounded-lg text-sm" required>
                                <option disabled value="">Pilih Kategori</option>
                                <option v-for="kategori in kategoriList" :key="kategori.id_kategori"
                                    :value="kategori.id_kategori">
                                    {{ kategori.nama_kategori }}
                                </option>
                            </select>
                        </div>

                        <div class="flex items-center gap-5 mb-3">
                            <label class="min-w-[150px] font-semibold text-sm text-black">Harga Estimasi</label>
                            <input type="number" v-model.number="item.harga_estimasi" placeholder="Masukkan harga"
                                class="w-full p-2 border border-gray-300 rounded-lg text-sm" />
                        </div>

                        <div class="flex items-center gap-5">
                            <label class="min-w-[150px] font-semibold text-sm text-black">Keterangan</label>
                            <textarea v-model="item.keterangan" placeholder="Contoh: ATK untuk rapat bulanan"
                                class="w-full p-2 border border-gray-300 rounded-lg text-sm"></textarea>
                        </div>
                    </div>

                    <button @click="addItem"
                        class="mt-4 w-fit bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg cursor-pointer">
                        + Tambah Pengajuan ATK
                    </button>

                    <SuccessAlert :visible="showSuccessAlert" :message="successMessage" />

                    <div class="flex justify-between items-center mt-6">
                        <router-link to="/pengajuan">
                            <button
                                class="bg-[#074a5d] text-white px-4 py-2 rounded-lg hover:bg-[#063843] transition cursor-pointer">
                                Kembali
                            </button>
                        </router-link>
                        <button @click="submitForm"
                            class="bg-[#074a5d] text-white px-4 py-2 rounded-lg hover:bg-[#063843] transition cursor-pointer">
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
