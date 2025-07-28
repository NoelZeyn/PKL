<template>
  <div class="flex flex-col md:flex-row min-h-screen bg-gray-100">
    <Sidebar :activeMenu="activeMenu" @update:activeMenu="activeMenu = $event" />

    <div class="flex-1 p-4 sm:p-6 md:p-8 pt-7 flex flex-col bg-white overflow-auto">
      <HeaderBar title="Tambah ATK" />
      <div class="border-b border-gray-300"></div>

      <div class="bg-white p-4 sm:p-6 rounded-2xl shadow mt-6">
        <h3 class="text-[15px] text-[#074a5d] font-semibold mb-4">Tambah ATK</h3>
        <div class="h-[1px] w-full bg-gray-300 my-4"></div>

        <div class="flex flex-col gap-6 sm:gap-5">
          <h4 class="text-[15px] font-medium text-black text-center pb-3">Form Tambah ATK</h4>

          <!-- Nama ATK -->
          <div class="flex flex-col gap-1 w-full">
            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-2 sm:gap-5">
              <label class="sm:min-w-[150px] font-semibold text-sm text-black">Nama ATK</label>
              <input type="text" v-model="formData.nama_barang" placeholder="Nama ATK"
                :class="['w-full p-2 border rounded-lg bg-gray-100 text-sm', errors.nama_barang ? 'border-red-500' : 'border-gray-300']" />
            </div>
            <span v-if="errors.nama_barang" class="text-red-500 text-xs sm:ml-[150px] -mt-1">{{ errors.nama_barang
              }}</span>
          </div>

          <!-- Kategori -->
          <div class="flex flex-col gap-1 w-full">
            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-2 sm:gap-5">
              <label class="sm:min-w-[150px] font-semibold text-sm text-black">Kategori ATK</label>
              <select v-model="formData.id_kategori_fk"
                :class="['w-full p-2 border rounded-lg bg-gray-100 text-sm', errors.id_kategori_fk ? 'border-red-500' : 'border-gray-300']">
                <option disabled value="">Pilih Kategori ATK</option>
                <option v-for="item in kategoriList" :key="item.id_kategori" :value="item.id_kategori">
                  {{ item.nama_kategori }}
                </option>
              </select>
            </div>
            <span v-if="errors.id_kategori_fk" class="text-red-500 text-xs sm:ml-[150px] -mt-1">{{ errors.id_kategori_fk
              }}</span>
          </div>

          <!-- Keterangan -->
          <div class="flex flex-col gap-1 w-full">
            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-2 sm:gap-5">
              <label class="sm:min-w-[150px] font-semibold text-sm text-black">Keterangan</label>
              <textarea v-model="formData.keterangan" placeholder="Keterangan ATK"
                class="w-full p-2 border border-gray-300 rounded-lg bg-gray-100 text-sm resize-y"></textarea>
            </div>
          </div>

          <!-- Input Lainnya -->
          <template v-for="field in fields" :key="field.key">
            <div class="flex flex-col gap-1 w-full">
              <div class="flex flex-col sm:flex-row items-start sm:items-center gap-2 sm:gap-5">
                <label class="sm:min-w-[150px] font-semibold text-sm text-black">{{ field.label }}</label>
                <input :type="field.type" v-model="formData[field.key]" :placeholder="field.placeholder"
                  :class="['w-full p-2 border rounded-lg bg-gray-100 text-sm', errors[field.key] ? 'border-red-500' : 'border-gray-300']" />
              </div>
              <span v-if="errors[field.key]" class="text-red-500 text-xs sm:ml-[150px] -mt-1">{{ errors[field.key]
                }}</span>
            </div>
          </template>

          <!-- Alert dan Tombol -->
          <SuccessAlert :visible="showSuccessAlert" :message="successMessage" />

          <div class="flex flex-col sm:flex-row justify-between gap-4 mt-6">
            <router-link to="/manajemen-alat" class="w-full sm:w-auto">
              <button
                class="cursor-pointer w-full sm:w-auto bg-[#074a5d] text-white px-4 py-2 rounded-lg hover:bg-[#063843] transition">
                Kembali
              </button>
            </router-link>

            <button @click="submitATK"
              class="cursor-pointer w-full sm:w-auto bg-[#074a5d] text-white px-4 py-2 rounded-lg hover:bg-[#063843] transition">
              Tambahkan ATK
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
        id_kategori_fk: "",
        keterangan: "",
        stock_min: "",
        stock_max: "",
        stock: "",
        satuan: "",
        harga_satuan: "",
        harga_estimasi: "",
      },
      errors: {},
      showSuccessAlert: false,
      successMessage: "",
      fields: [
        {
          key: "stock_min",
          label: "Stock Minimal",
          type: "number",
          placeholder: "Stock Min ATK",
        },
        {
          key: "stock_max",
          label: "Stock Maximal",
          type: "number",
          placeholder: "Stock Max ATK",
        },
        {
          key: "stock",
          label: "Stock Sekarang",
          type: "number",
          placeholder: "Stock",
        },
        {
          key: "satuan",
          label: "Satuan",
          type: "text",
          placeholder: "Satuan ATK",
        },
        {
          key: "harga_satuan",
          label: "Harga Satuan",
          type: "number",
          placeholder: "Harga Satuan ATK, Contoh: 10000",
        },
        {
          key: "harga_estimasi",
          label: "Harga Estimasi Satuan",
          type: "number",
          placeholder: "Harga Estimasi Satuan ATK, Contoh: 10000",
        },
      ],
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
      let valid = true;
      this.errors = {};

      if (!this.formData.nama_barang.trim()) {
        this.errors.nama_barang = "Nama ATK wajib diisi.";
        valid = false;
      }

      if (!this.formData.id_kategori_fk) {
        this.errors.id_kategori_fk = "Kategori ATK wajib dipilih.";
        valid = false;
      }

      if (this.formData.stock_min === "" || this.formData.stock_min < 0) {
        this.errors.stock_min = "Stock minimal wajib diisi dan tidak boleh negatif.";
        valid = false;
      }

      if (this.formData.stock_max === "" || this.formData.stock_max < 0) {
        this.errors.stock_max = "Stock maksimal wajib diisi dan tidak boleh negatif.";
        valid = false;
      }

      if (this.formData.stock === "" || this.formData.stock < 0) {
        this.errors.stock = "Stock sekarang wajib diisi dan tidak boleh negatif.";
        valid = false;
      }

      if (!this.formData.satuan.trim()) {
        this.errors.satuan = "Satuan wajib diisi.";
        valid = false;
      }

      if (this.formData.harga_satuan === "" || this.formData.harga_satuan < 0) {
        this.errors.harga_satuan = "Harga satuan wajib diisi dan tidak boleh negatif.";
        valid = false;
      }

      if (this.formData.harga_estimasi === "" || this.formData.harga_estimasi < 0) {
        this.errors.harga_estimasi = "Harga estimasi wajib diisi dan tidak boleh negatif.";
        valid = false;
      }

      return valid;
    },
    async submitATK() {
      if (!this.validateForm()) return;

      const token = localStorage.getItem("token");
      try {
        await axios.post("http://localhost:8000/api/alat", this.formData, {
          headers: {
            Authorization: `Bearer ${token}`,
            Accept: "application/json",
          },
        });
        this.successMessage = "Alat berhasil ditambahkan";
        this.showSuccessAlert = true;
        setTimeout(() => {
          this.showSuccessAlert = false;
          this.$router.push("/manajemen-alat");
        }, 2500);
      } catch (error) {
        console.error("Gagal menambahkan alat:", error);
      }
    },
  },
};
</script>
