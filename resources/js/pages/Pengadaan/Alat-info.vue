<template>
    <div class="flex flex-col md:flex-row min-h-screen bg-gray-100">
        <Sidebar :activeMenu="activeMenu" @update:activeMenu="activeMenu = $event" />

        <div class="flex-1 p-4 sm:p-6 md:p-8 pt-7 flex flex-col bg-white overflow-auto">
            <HeaderBar title="Informasi ATK" />
            <div class="border-b border-gray-300"></div>

            <div class="bg-white p-4 sm:p-6 rounded-2xl shadow mt-6">
                <h3 class="text-[15px] text-[#074a5d] font-semibold mb-4">Informasi ATK</h3>
                <div class="h-[1px] w-full bg-gray-300 my-4"></div>

                <div class="flex flex-col gap-6">
                    <h4 class="text-[15px] font-medium text-black text-center pb-3">Form Informasi ATK</h4>

                    <!-- Field -->
                    <div class="flex flex-col gap-4">
                        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-2 sm:gap-5">
                            <label class="sm:min-w-[150px] font-semibold text-sm text-black">Nama ATK</label>
                            <input type="text" v-model="formData.nama_barang" placeholder="Nama ATK" disabled
                                class="w-full p-2 border border-gray-300 rounded-lg bg-gray-100 text-sm text-gray-700" />
                        </div>

                        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-2 sm:gap-5">
                            <label class="sm:min-w-[150px] font-semibold text-sm text-black">Kategori ATK</label>
                            <select v-model="formData.id_kategori_fk" disabled
                                class="w-full p-2 border border-gray-300 rounded-lg bg-gray-100 text-sm text-gray-700">
                                <option disabled value="">Pilih Kategori ATK</option>
                                <option v-for="item in kategoriList" :key="item.id_kategori" :value="item.id_kategori">
                                    {{ item.nama_kategori }}
                                </option>
                            </select>
                        </div>

                        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-2 sm:gap-5">
                            <label class="sm:min-w-[150px] font-semibold text-sm text-black">Keterangan</label>
                            <textarea v-model="formData.keterangan" placeholder="Keterangan ATK" disabled
                                class="w-full p-2 border border-gray-300 rounded-lg bg-gray-100 text-sm resize-y text-gray-700"></textarea>
                        </div>

                        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-2 sm:gap-5">
                            <label class="sm:min-w-[150px] font-semibold text-sm text-black">Stock Minimal</label>
                            <input type="number" v-model="formData.stock_min" placeholder="Stock Min ATK" disabled
                                class="w-full p-2 border border-gray-300 rounded-lg bg-gray-100 text-sm text-gray-700" />
                        </div>

                        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-2 sm:gap-5">
                            <label class="sm:min-w-[150px] font-semibold text-sm text-black">Stock Maximal</label>
                            <input type="number" v-model="formData.stock_max" placeholder="Stock Max ATK" disabled
                                class="w-full p-2 border border-gray-300 rounded-lg bg-gray-100 text-sm text-gray-700" />
                        </div>

                        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-2 sm:gap-5">
                            <label class="sm:min-w-[150px] font-semibold text-sm text-black">Satuan</label>
                            <input type="text" v-model="formData.satuan" placeholder="Satuan ATK" disabled
                                class="w-full p-2 border border-gray-300 rounded-lg bg-gray-100 text-sm text-gray-700" />
                        </div>

                        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-2 sm:gap-5">
                            <label class="sm:min-w-[150px] font-semibold text-sm text-black">Harga Satuan</label>
                            <input type="number" v-model="formData.harga_satuan" placeholder="Harga Satuan ATK" disabled
                                class="w-full p-2 border border-gray-300 rounded-lg bg-gray-100 text-sm text-gray-700" />
                        </div>

                        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-2 sm:gap-5">
                            <label class="sm:min-w-[150px] font-semibold text-sm text-black">Harga Estimasi
                                Satuan</label>
                            <input type="number" v-model="formData.harga_estimasi"
                                placeholder="Harga Estimasi Satuan ATK" disabled
                                class="w-full p-2 border border-gray-300 rounded-lg bg-gray-100 text-sm text-gray-700" />
                        </div>
                    </div>

                    <!-- Tombol -->
                    <div class="flex justify-start mt-6">
                        <router-link to="/manajemen-alat">
                            <button class="bg-[#074a5d] text-white px-4 py-2 rounded-lg hover:bg-[#063843] transition">
                                Kembali
                            </button>
                        </router-link>
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
    name: "ATKEdit",
    components: {
        Sidebar,
        HeaderBar,
    },
    data() {
        return {
            activeMenu: "manajemenAlat",
            kategoriList: [],
            formData: {
                nama_barang: "",
                id_kategori_fk: "",
                keterangan: "",
                stock_min: "",
                stock_max: "",
                satuan: "",
                harga_satuan: "",
                harga_estimasi: "",
            },
        };
    },
    mounted() {
        this.fetchKategori();
        this.fetchAlat();
    },
    methods: {
        async fetchKategori() {
            const token = localStorage.getItem("token");
            try {
                const response = await axios.get(`http://localhost:8000/api/kategori_pengadaan`, {
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
        fetchAlat() {
            const token = localStorage.getItem("token");
            const id = this.$route.params.id; // Ambil ID dari route params
            axios
                .get(`http://localhost:8000/api/alat/${id}`, {
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
