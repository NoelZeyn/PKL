<template>
  <div class="flex h-screen bg-gray-100">
    <Sidebar :activeMenu="activeMenu" @update:activeMenu="activeMenu = $event" />
    <div class="flex-1 p-8 pt-7 flex flex-col bg-white">
      <HeaderBar title="Manajemen Stock Alat" />
      <div class="border-b border-gray-300 mb-4"></div>

      <div class="bg-white p-6 rounded-2xl shadow">
        <div class="flex flex-col gap-4 mx-9">

          <div class="flex items-center gap-5">
            <label class="min-w-[150px] font-semibold text-sm text-black">Nama Barang</label>
            <select v-model="selectedAlatId" @change="onAlatChange"
              class="w-full p-2 border border-gray-300 rounded-lg text-sm" required>
              <option disabled value="">Pilih Barang</option>
              <option v-for="item in alatList" :key="item.id_alat" :value="item.id_alat">
                {{ item.nama_barang }}
              </option>
            </select>
          </div>

          <div class="flex items-center gap-5">
            <label class="min-w-[150px] font-semibold text-sm text-black">Stock Minimal</label>
            <input type="number" v-model.number="formData.stock_min" min="0"
              class="w-full p-2 border border-gray-300 rounded-lg text-sm" />
          </div>

          <div class="flex items-center gap-5">
            <label class="min-w-[150px] font-semibold text-sm text-black">Stock Maximal</label>
            <input type="number" v-model.number="formData.stock_max" min="0"
              class="w-full p-2 border border-gray-300 rounded-lg text-sm" />
          </div>
          <div class="flex items-center gap-5">
            <label class="min-w-[150px] font-semibold text-sm text-black">Stock Sekarang</label>
            <input type="number" v-model.number="formData.stock" min="0"
              class="w-full p-2 border border-gray-300 rounded-lg text-sm" />
          </div>

          <SuccessAlert :visible="showSuccessAlert" :message="successMessage" />

          <div class="flex justify-between items-center mt-6">
            <router-link to="/manajemen-alat">
              <button class="bg-[#074a5d] cursor-pointer text-white px-4 py-2 rounded-lg hover:bg-[#063843] transition">
                Kembali
              </button>
            </router-link>
            <button @click="submitForm"
              class="bg-[#074a5d] text-white px-4 py-2 rounded-lg hover:bg-[#063843] transition cursor-pointer">
              Simpan Perubahan Stock
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
  name: "ManajemenStockAlat",
  components: {
    Sidebar, HeaderBar, SuccessAlert
  },
  data() {
    return {
      activeMenu: "manajemenAlat",
      alatList: [],
      selectedAlatId: "",
      formData: {
        stock_min: 0,
        stock_max: 0,
      },
      showSuccessAlert: false,
      successMessage: "",
    };
  },
  mounted() {
    this.fetchAlat();
  },
  methods: {
    fetchAlat() {
      const token = localStorage.getItem("token");
      axios.get("http://localhost:8000/api/alat", {
        headers: { Authorization: `Bearer ${token}` },
      }).then((res) => {
        this.alatList = res.data.data;
      });
    },
    onAlatChange() {
      const selectedAlat = this.alatList.find(item => item.id_alat === this.selectedAlatId);
      if (selectedAlat) {
        this.formData.stock_min = selectedAlat.stock_min;
        this.formData.stock_max = selectedAlat.stock_max;
        this.formData.stock = selectedAlat.stock;
      } else {
        this.formData.stock_min = 0;
        this.formData.stock_max = 0;
        this.formData.stock = 0;
      }
    },
    async submitForm() {
      if (!this.selectedAlatId) {
        alert("Pilih barang terlebih dahulu.");
        return;
      }

      const token = localStorage.getItem("token");

      // Ambil seluruh data alat yang dipilih
      const selected = this.alatList.find(a => a.id_alat === this.selectedAlatId);
      if (!selected) {
        alert("Data alat tidak ditemukan.");
        return;
      }

      // Kirim ulang semua data, hanya ubah stock_min dan stock_max
      const payload = {
        id_kategori_fk: selected.id_kategori_fk,
        nama_barang: selected.nama_barang,
        harga_satuan: selected.harga_satuan,
        stock: selected.stock,
        satuan: selected.satuan,
        stock_min: this.formData.stock_min,
        stock_max: this.formData.stock_max,
        stock: this.formData.stock, // Gunakan stock yang ada di form
      };

      try {
        await axios.put(`http://localhost:8000/api/alat/${this.selectedAlatId}`, payload, {
          headers: {
            Authorization: `Bearer ${token}`,
            Accept: "application/json",
          },
        });

        this.successMessage = "Stock berhasil diperbarui.";
        this.showSuccessAlert = true;

        setTimeout(() => {
          this.showSuccessAlert = false;
          this.$router.push("/manajemen-alat");
        }, 2500);
      } catch (error) {
        console.error(error);
        alert("Gagal memperbarui stock.");
      }
    }

  },
};
</script>
