<template>
  <div class="flex h-screen bg-gray-100">
    <Sidebar :activeMenu="activeMenu" @update:activeMenu="activeMenu = $event" />
    <div class="flex-1 p-8 pt-7 flex flex-col bg-white">
      <HeaderBar title="Form Pemakaian Alat" />
      <div class="border-b border-gray-300 mb-4"></div>

      <div class="bg-white p-6 rounded-2xl shadow">
        <div class="flex flex-col gap-6 mx-9">

          <div v-for="(item, index) in formData" :key="index" class="border border-gray-200 p-4 rounded-lg shadow-sm">
            <div class="flex justify-between items-center mb-3">
              <h4 class="font-semibold text-sm text-[#333]">Pemakaian Alat {{ index + 1 }}</h4>
              <button v-if="formData.length > 1" @click="removeForm(index)"
                class="text-red-500 text-xs hover:underline cursor-pointer">Hapus</button>
            </div>

            <div class="flex items-center gap-5 mb-3">
              <label class="min-w-[150px] font-semibold text-sm text-black">NID Pemakai</label>
              <input type="text" v-model="item.NID" placeholder="Masukkan NID Pemakai"
                class="w-full p-2 border border-gray-300 rounded-lg text-sm" required />
            </div>

            <div class="flex items-center gap-5 mb-3">
              <label class="min-w-[150px] font-semibold text-sm text-black">Nama Barang</label>
              <select v-model="item.id_alat" class="w-full p-2 border border-gray-300 rounded-lg text-sm cursor-pointer" required>
                <option disabled value="">Pilih Barang</option>
                <option v-for="alat in alatList" :key="alat.id_alat" :value="alat.id_alat">
                  {{ alat.nama_barang }}
                </option>
              </select>
            </div>

            <div class="flex items-center gap-5 mb-3">
              <label class="min-w-[150px] font-semibold text-sm text-black">Jumlah Terpakai</label>
              <input type="number" v-model.number="item.jumlah" min="1"
                class="w-full p-2 border border-gray-300 rounded-lg text-sm" />
            </div>

            <div class="flex items-center gap-5">
              <label class="min-w-[150px] font-semibold text-sm text-black">Keterangan</label>
              <textarea v-model="item.keterangan" placeholder="Contoh: Keperluan meeting"
                class="w-full p-2 border border-gray-300 rounded-lg text-sm"></textarea>
            </div>
          </div>

          <button @click="addForm" class="mt-4 w-fit bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg cursor-pointer">
            + Tambah Pemakaian Alat
          </button>

          <SuccessAlert :visible="showSuccessAlert" :message="successMessage" />

          <div class="flex justify-between items-center mt-6">
            <router-link to="/manajemen-alat">
              <button class="bg-[#074a5d] text-white px-4 py-2 rounded-lg hover:bg-[#063843] transition cursor-pointer">
                Kembali
              </button>
            </router-link>
            <button @click="submitForm"
              class="bg-[#074a5d] text-white px-4 py-2 rounded-lg hover:bg-[#063843] transition cursor-pointer">
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
      activeMenu: "pemakaianAlat",
      alatList: [],
      formData: [
        { NID: "", id_alat: "", jumlah: "", keterangan: "" }
      ],
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
      }).then(res => {
        this.alatList = res.data.data;
      });
    },
    addForm() {
      this.formData.push({ NID: "", id_alat: "", jumlah: "", keterangan: "" });
    },
    removeForm(index) {
      this.formData.splice(index, 1);
    },
    async submitForm() {
      const token = localStorage.getItem("token");

      try {
        await axios.post("http://localhost:8000/api/history_pemakaian_multi", 
          { pemakaian: this.formData }, 
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
