<template>
    <div class="flex h-screen bg-gray-100">
        <Sidebar :activeMenu="activeMenu" @update:activeMenu="activeMenu = $event" />

        <div class="flex-1 p-8 pt-7 flex flex-col bg-white">
            <HeaderBar title="Tambah ATK" />
            <div class="border-b border-gray-300"></div>

            <div class="bg-white p-6 rounded-2xl shadow mt-8">
                <h3 class="text-[15px] text-[#074a5d] font-semibold mb-4">Tambah ATK</h3>
                <div class="h-[1px] w-[calc(100%+47px)] bg-gray-300 my-4 -ml-6"></div>

                <div class="flex flex-col gap-4 mx-9">
                    <h4 class="text-[15px] font-medium text-black text-center pb-3">Form Tambah ATK</h4>

                    <div class="flex items-center gap-5">
                        <label class="min-w-[150px] font-semibold text-sm text-black">Nama ATK</label>
                        <input type="text" v-model="formData.nama_barang" placeholder="Nama ATK"
                            class="w-full p-2 border border-gray-300 rounded-lg bg-gray-100 text-sm" />
                    </div>

                    <div class="flex items-center gap-5">
                        <label class="min-w-[150px] font-semibold text-sm text-black">Kategori ATK</label>
                        <select v-model="formData.id_kategori_fk" required
                            class="w-full p-2 border border-gray-300 rounded-lg bg-gray-100 text-sm">
                            <option disabled value="">Pilih Kategori ATK</option>
                            <option v-for="item in kategoriList" :key="item.id_kategori" :value="item.id_kategori">
                                {{ item.nama_kategori }}
                            </option>
                        </select>
                    </div>

                    <div class="flex items-center gap-5">
                        <label class="min-w-[150px] font-semibold text-sm text-black">Keterangan</label>
                        <textarea v-model="formData.keterangan" placeholder="Keterangan ATK"
                            class="w-full p-2 border border-gray-300 rounded-lg bg-gray-100 text-sm resize-y"></textarea>
                    </div>

                    <div class="flex items-center gap-5">
                        <label class="min-w-[150px] font-semibold text-sm text-black">Stock Minimal</label>
                        <input type="number" v-model="formData.stock_min" placeholder="Stock Min ATK"
                            class="w-full p-2 border border-gray-300 rounded-lg bg-gray-100 text-sm" />
                    </div>

                    <div class="flex items-center gap-5">
                        <label class="min-w-[150px] font-semibold text-sm text-black">Stock Maximal</label>
                        <input type="number" v-model="formData.stock_max" placeholder="Stock Max ATK"
                            class="w-full p-2 border border-gray-300 rounded-lg bg-gray-100 text-sm" />
                    </div>

                    <div class="flex items-center gap-5">
                        <label class="min-w-[150px] font-semibold text-sm text-black">Satuan</label>
                        <input type="text" v-model="formData.satuan" placeholder="Satuan ATK"
                            class="w-full p-2 border border-gray-300 rounded-lg bg-gray-100 text-sm" />
                    </div>

                    <div class="flex items-center gap-5">
                        <label class="min-w-[150px] font-semibold text-sm text-black">Harga Satuan</label>
                        <input type="number" v-model="formData.harga_satuan" placeholder="Harga Satuan ATK"
                            class="w-full p-2 border border-gray-300 rounded-lg bg-gray-100 text-sm" />
                    </div>

                    <div class="flex items-center gap-5">
                        <label class="min-w-[150px] font-semibold text-sm text-black">Harga Estimasi Satuan</label>
                        <input type="number" v-model="formData.harga_estimasi" placeholder="Harga Estimasi Satuan ATK"
                            class="w-full p-2 border border-gray-300 rounded-lg bg-gray-100 text-sm" />
                    </div>

                    <SuccessAlert :visible="showSuccessAlert" :message="successMessage" />

                    <div class="flex justify-between items-center mt-6">
                        <router-link to="/manajemen-alat">
                            <button
                                class="bg-[#074a5d] cursor-pointer text-white px-4 py-2 rounded-lg hover:bg-[#063843] transition">
                                Kembali
                            </button>
                        </router-link>

                        <button @click="submitATK"
                            class="bg-[#074a5d] cursor-pointer text-white px-4 py-2 rounded-lg hover:bg-[#063843] transition">
                            Tambahkan ATK
                        </button>
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
import SuccessAlert from "@/components/SuccessAlert.vue";
import axios from "axios";

export default {
    name: "ATKAdd",
    components: {
        Sidebar,
        HeaderBar,
        SuccessAlert,
    },
    data() {
        return {
            activeMenu: "manajemenAlat",
            kategoriList: [],
            formData: {
                nama_barang: "",
                id_kategori_fk: "", // ✅ Pastikan ini v-model di select
                keterangan: "",
                stock_min: "",
                stock_max: "",
                satuan: "",
                harga_satuan: "",
                harga_estimasi: "",
            },
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
        validateForm() {
            if (!this.formData.nama_barang.trim()) {
                alert("Nama ATK wajib diisi.");
                return false;
            }
            if (!this.formData.id_kategori_fk) {
                alert("Kategori wajib diisi.");
                return false;
            }
            return true;
        },
        submitATK() {
            if (!this.validateForm()) return;
            const token = localStorage.getItem("token");
            axios
                .post("http://localhost:8000/api/alat", this.formData, {
                    headers: {
                        Authorization: `Bearer ${token}`,
                        Accept: "application/json",
                    },
                })
                .then(() => {
                    this.successMessage = "Alat berhasil ditambahkan";
                    this.showSuccessAlert = true;
                    setTimeout(() => {
                        this.showSuccessAlert = false;
                        this.$router.push("/manajemen-alat");
                    }, 2500);
                })
                .catch(() => {
                    alert("Gagal menambahkan alat.");
                });
        },
    },
};
</script>
