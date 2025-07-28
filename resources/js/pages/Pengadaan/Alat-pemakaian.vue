<template>
  <div class="flex flex-col md:flex-row min-h-screen bg-gray-100">
    <Sidebar :activeMenu="activeMenu" @update:activeMenu="activeMenu = $event" />

    <div class="flex-1 p-4 sm:p-6 md:p-8 pt-7 flex flex-col bg-white overflow-auto">
      <HeaderBar title="Form Pemakaian Alat" />
      <div class="border-b border-gray-300 mb-4"></div>

      <div class="bg-white p-4 sm:p-6 rounded-2xl shadow">
        <div class="flex flex-col gap-6">

          <div v-for="(item, index) in formData" :key="index" class="border border-gray-200 p-4 rounded-lg shadow-sm">
            <div class="flex justify-between items-center mb-3 flex-wrap gap-2">
              <h4 class="font-semibold text-sm text-[#333]">Pemakaian Alat {{ index + 1 }}</h4>
              <button v-if="formData.length > 1" @click="removeForm(index)"
                class="text-red-500 text-xs hover:underline cursor-pointer">
                Hapus
              </button>
            </div>

            <!-- Field: NID -->
            <div class="flex flex-col md:flex-row md:items-center gap-2 md:gap-5 mb-3">
              <label class="md:min-w-[150px] font-semibold text-sm text-black">NID Pemakai</label>
              <input type="text" v-model="item.NID" placeholder="Masukkan NID Pemakai"
                class="w-full p-2 border border-gray-300 rounded-lg text-sm" required />
            </div>

            <!-- Field: Nama Barang -->
            <div class="flex flex-col md:flex-row md:items-center gap-2 md:gap-5 mb-3">
              <label class="md:min-w-[150px] font-semibold text-sm text-black">Nama Barang</label>
              <select v-model="item.id_alat" @change="updateStock(index)"
                class="w-full p-2 border border-gray-300 rounded-lg text-sm cursor-pointer" required>
                <option disabled value="">Pilih Barang</option>
                <option v-for="alat in alatList" :key="alat.id_alat" :value="alat.id_alat">
                  {{ alat.nama_barang }}
                </option>
              </select>
            </div>

            <!-- Field: Stock -->
            <div class="flex flex-col md:flex-row md:items-center gap-2 md:gap-5 mb-3">
              <label class="md:min-w-[150px] font-semibold text-sm text-black">Stock Sekarang</label>
              <input type="text" :value="item.stock" placeholder="Stock Barang" disabled
                class="w-full p-2 border border-gray-300 rounded-lg text-sm text-gray-500" />
            </div>

            <!-- Field: Jumlah -->
            <div class="flex flex-col md:flex-row md:items-center gap-2 md:gap-5 mb-3">
              <label class="md:min-w-[150px] font-semibold text-sm text-black">Jumlah Terpakai</label>
              <input type="number" v-model.number="item.jumlah" min="1"
                class="w-full p-2 border border-gray-300 rounded-lg text-sm" />
            </div>

            <!-- Field: Keterangan -->
            <div class="flex flex-col md:flex-row md:items-start gap-2 md:gap-5">
              <label class="md:min-w-[150px] font-semibold text-sm text-black">Keterangan</label>
              <textarea v-model="item.keterangan" placeholder="Contoh: Keperluan meeting"
                class="w-full p-2 border border-gray-300 rounded-lg text-sm"></textarea>
            </div>
          </div>

          <!-- Button: Tambah -->
          <button @click="addForm"
            class="mt-4 w-fit bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg cursor-pointer">
            + Tambah Pemakaian Alat
          </button>

          <SuccessAlert :visible="showSuccessAlert" :message="successMessage" />

          <!-- Footer Actions -->
          <div class="flex flex-col sm:flex-row justify-between gap-4 items-center mt-6">
            <router-link to="/manajemen-alat" class="w-full sm:w-auto">
              <button
                class="cursor-pointer w-full sm:w-auto bg-[#074a5d] text-white px-4 py-2 rounded-lg hover:bg-[#063843] transition">
                Kembali
              </button>
            </router-link>
            <button @click="submitForm"
              class="cursor-pointer w-full sm:w-auto bg-[#074a5d] text-white px-4 py-2 rounded-lg hover:bg-[#063843] transition">
              Simpan Semua Pemakaian
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
  name: "FormPemakaianAlat",
  components: { Sidebar, HeaderBar, SuccessAlert },
  data() {
    return {
      activeMenu: "manajemenAlat",
      alatList: [],
      formData: [
        { NID: "", id_alat: "", jumlah: "", keterangan: "", stock: "" }
      ],
      showSuccessAlert: false,
      successMessage: "",
      tingkatanOtoritas: "",
      userPenempatanId: ""
    };
  },
  mounted() {
    this.getUserInfo();
  },
  methods: {
    async getUserInfo() {
      try {
        const token = localStorage.getItem("token");
        const res = await axios.post("http://localhost:8000/api/me", {}, {
          headers: { Authorization: `Bearer ${token}` }
        });

        this.tingkatanOtoritas = res.data.tingkatan_otoritas;
        this.userPenempatanId = res.data.id_penempatan_fk;

        if (this.tingkatanOtoritas === 'admin' || this.tingkatanOtoritas === 'superadmin') {
          this.fetchAlatAll();
        } else {
          this.fetchAlatByPenempatan(this.userPenempatanId);
        }
      } catch (err) {
        console.error("Gagal mendapatkan info user:", err);
      }
    },

    fetchAlatAll() {
      const token = localStorage.getItem("token");
      axios.get("http://localhost:8000/api/alat", {
        headers: { Authorization: `Bearer ${token}` },
      }).then(res => {
        this.alatList = res.data.data;
      });
    },

    fetchAlatByPenempatan(penempatanId) {
      const token = localStorage.getItem("token");
      axios.get(`http://localhost:8000/api/alat-penempatan/${penempatanId}`, {
        headers: { Authorization: `Bearer ${token}` },
      }).then(res => {
        // Ambil array barang dari response
        this.alatList = res.data.data[0]?.barang || [];
      });
    },

    addForm() {
      this.formData.push({ NID: "", id_alat: "", jumlah: "", keterangan: "", stock: "" });
    },

    removeForm(index) {
      this.formData.splice(index, 1);
    },

    updateStock(index) {
      const selectedAlatId = this.formData[index].id_alat;
      const selectedAlat = this.alatList.find(alat => alat.id_alat === selectedAlatId);

      if (!selectedAlat) {
        this.formData[index].stock = "";
        return;
      }

      if (this.tingkatanOtoritas === 'admin' || this.tingkatanOtoritas === 'superadmin') {
        this.formData[index].stock = selectedAlat.stock ?? "";
      } else {
        const detailPenempatan = selectedAlat.terbagi_ke?.find(p => p.id_penempatan === this.userPenempatanId);
        this.formData[index].stock = detailPenempatan?.stock ?? "";
      }
    },

    async submitForm() {
      const token = localStorage.getItem("token");

      try {
        await axios.post("http://localhost:8000/api/history_pemakaian_multi",
          {
            pemakaian: this.formData,
            tingkatan_otoritas: this.tingkatanOtoritas,
            id_penempatan_fk: this.userPenempatanId
          },
          { headers: { Authorization: `Bearer ${token}` } }
        );

        this.successMessage = "Semua pemakaian berhasil dicatat!";
        this.showSuccessAlert = true;

        setTimeout(() => {
          this.showSuccessAlert = false;
          this.$router.push("/manajemen-alat");
        }, 2000);

      } catch (error) {
        console.error(error);
        alert("Gagal mencatat pemakaian.");
      }
    },
  },
};
</script>
