<template>
  <div class="flex flex-col md:flex-row min-h-screen bg-gray-100">
    <!-- Sidebar -->
    <Sidebar :activeMenu="activeMenu" @update:activeMenu="activeMenu = $event" />

    <!-- Main Content -->
    <div class="flex-1 p-4 md:p-8 pt-6 flex flex-col bg-white">
      <HeaderBar title="Manajemen Stock Alat" />
      <div class="border-b border-gray-300 mb-4"></div>

      <div class="bg-white p-4 md:p-6 rounded-2xl shadow w-full max-w-4xl mx-auto">
        <div class="flex flex-col gap-4">

          <!-- Nama Barang -->
          <div class="flex flex-col md:flex-row items-start md:items-center gap-2 md:gap-5">
            <label class="w-full md:w-[150px] font-semibold text-sm text-black">Nama Barang</label>
            <select v-model="selectedAlatId" @change="onAlatChange"
              class="w-full p-2 border border-gray-300 rounded-lg text-sm" required>
              <option disabled value="">Pilih Barang</option>
              <option v-for="item in alatList" :key="item.id_alat" :value="item.id_alat">
                {{ item.nama_barang }}
              </option>
            </select>
          </div>

          <!-- Stock Minimal -->
          <div class="flex flex-col md:flex-row items-start md:items-center gap-2 md:gap-5">
            <label class="w-full md:w-[150px] font-semibold text-sm text-black">Stock Minimal</label>
            <input type="number" v-model.number="formData.stock_min" min="0"
              class="w-full p-2 border border-gray-300 rounded-lg text-sm" />
          </div>

          <!-- Stock Maximal -->
          <div class="flex flex-col md:flex-row items-start md:items-center gap-2 md:gap-5">
            <label class="w-full md:w-[150px] font-semibold text-sm text-black">Stock Maximal</label>
            <input type="number" v-model.number="formData.stock_max" min="0"
              class="w-full p-2 border border-gray-300 rounded-lg text-sm" />
          </div>

          <!-- Stock Sekarang -->
          <div class="flex flex-col md:flex-row items-start md:items-center gap-2 md:gap-5">
            <label class="w-full md:w-[150px] font-semibold text-sm text-black">Stock Sekarang</label>
            <input type="number" v-model.number="formData.stock" min="0"
              class="w-full p-2 border border-gray-300 rounded-lg text-sm" />
          </div>

          <!-- Alert -->
          <SuccessAlert :visible="showSuccessAlert" :message="successMessage" />

          <!-- Tombol -->
          <div class="flex flex-col md:flex-row justify-between items-center mt-6 gap-3">
            <router-link to="/manajemen-alat" class="w-full md:w-auto">
              <button
                class="cursor-pointer w-full md:w-auto bg-[#074a5d] text-white px-4 py-2 rounded-lg hover:bg-[#063843] transition">
                Kembali
              </button>
            </router-link>
            <button @click="submitForm"
              class="cursor-pointer w-full md:w-auto bg-[#074a5d] text-white px-4 py-2 rounded-lg hover:bg-[#063843] transition">
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
    Sidebar,
    HeaderBar,
    SuccessAlert,
  },
  data() {
    return {
      activeMenu: "manajemenAlat",
      alatList: [],
      selectedAlatId: "",
      formData: {
        stock_min: 0,
        stock_max: 0,
        stock: 0,
      },
      showSuccessAlert: false,
      successMessage: "",
      tingkatanOtoritas: "",
      userPenempatanId: "",
    };
  },
  mounted() {
    this.getUserInfo(); // hanya panggil ini, fetchAlat akan dipicu dari dalamnya
  },
  methods: {
    async getUserInfo() {
      try {
        const token = localStorage.getItem("token");
        const res = await axios.post(
          "http://localhost:8000/api/me",
          {},
          {
            headers: { Authorization: `Bearer ${token}` },
          }
        );

        this.tingkatanOtoritas = res.data.tingkatan_otoritas;
        this.userPenempatanId = res.data.id_penempatan_fk;

        if (
          this.tingkatanOtoritas === "admin" ||
          this.tingkatanOtoritas === "superadmin"
        ) {
          this.fetchAlatAdmin();
        } else {
          this.fetchAlatUser();
        }
      } catch (err) {
        console.error("Gagal mendapatkan info user:", err);
      }
    },

    fetchAlatAdmin() {
      const token = localStorage.getItem("token");
      axios
        .get("http://localhost:8000/api/alat", {
          headers: { Authorization: `Bearer ${token}` },
        })
        .then((res) => {
          this.alatList = res.data.data;
        });
    },

    fetchAlatUser() {
      const token = localStorage.getItem("token");
      axios
        .get(
          `http://localhost:8000/api/alat-penempatan/${this.userPenempatanId}`,
          {
            headers: { Authorization: `Bearer ${token}` },
          }
        )
        .then((res) => {
          this.alatList = res.data.data[0]?.barang || [];
        });
    },

    onAlatChange() {
      const selectedAlat = this.alatList.find(
        (item) => item.id_alat === this.selectedAlatId
      );
      if (selectedAlat) {
        if (
          this.tingkatanOtoritas === "admin" ||
          this.tingkatanOtoritas === "superadmin"
        ) {
          this.formData.stock_min = selectedAlat.stock_min;
          this.formData.stock_max = selectedAlat.stock_max;
          this.formData.stock = selectedAlat.stock;
        } else {
          const penempatan = selectedAlat.terbagi_ke?.find(
            (p) => p.id_penempatan === this.userPenempatanId
          );
          if (penempatan) {
            this.formData.stock_min = penempatan.stock_min;
            this.formData.stock_max = penempatan.stock_max;
            this.formData.stock = penempatan.stock;
          } else {
            this.formData.stock_min = 0;
            this.formData.stock_max = 0;
            this.formData.stock = 0;
          }
        }
      }
    },

    async submitForm() {
      if (!this.selectedAlatId) {
        alert("Pilih barang terlebih dahulu.");
        return;
      }

      if (
        this.tingkatanOtoritas === "admin" ||
        this.tingkatanOtoritas === "superadmin"
      ) {
        this.submitFormAdmin();
      } else {
        this.submitFormUser();
      }
    },

    async submitFormAdmin() {
      const token = localStorage.getItem("token");
      const selected = this.alatList.find(
        (a) => a.id_alat === this.selectedAlatId
      );
      if (!selected) {
        alert("Data alat tidak ditemukan.");
        return;
      }

      const payload = {
        id_kategori_fk: selected.id_kategori_fk,
        nama_barang: selected.nama_barang,
        harga_satuan: selected.harga_satuan,
        satuan: selected.satuan,
        keterangan: selected.keterangan,
        stock_min: this.formData.stock_min,
        stock_max: this.formData.stock_max,
        stock: this.formData.stock,
      };

      try {
        await axios.put(
          `http://localhost:8000/api/alat/${this.selectedAlatId}`,
          payload,
          {
            headers: {
              Authorization: `Bearer ${token}`,
              Accept: "application/json",
            },
          }
        );

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
    },

    async submitFormUser() {
      const token = localStorage.getItem("token");
      const selected = this.alatList.find(
        (a) => a.id_alat === this.selectedAlatId
      );
      if (!selected) {
        alert("Data alat tidak ditemukan.");
        return;
      }

      const terbagiKe = selected.terbagi_ke || [];
      const currentEntry = terbagiKe.find(
        (p) => p.id_penempatan === this.userPenempatanId
      );
      if (!currentEntry) {
        alert("Data penempatan tidak ditemukan di alat.");
        return;
      }

      const payload = {
        stock_min: this.formData.stock_min,
        stock_max: this.formData.stock_max,
        stock: this.formData.stock,
      };

      try {
        await axios.put(
          `http://localhost:8000/api/alat-penempatan/${this.selectedAlatId}/${this.userPenempatanId}`,
          payload,
          {
            headers: {
              Authorization: `Bearer ${token}`,
              Accept: "application/json",
            },
          }
        );

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
    },
  },
};
</script>
