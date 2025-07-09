<template>
  <div class="flex h-screen bg-gray-100">
    <Sidebar :activeMenu="activeMenu" @update:activeMenu="activeMenu = $event" />
    <div class="flex-1 p-8 pt-7 flex flex-col bg-white">
      <HeaderBar title="Form Pemakaian Alat" />
      <div class="border-b border-gray-300 mb-4"></div>

      <div class="bg-white p-6 rounded-2xl shadow">
        <div class="flex flex-col gap-4 mx-9">

          <div class="flex items-center gap-5">
            <label class="min-w-[150px] font-semibold text-sm text-black">NID Pemakai</label>
            <input type="text" v-model="formData.NID" placeholder="Masukkan NID Pemakai"
              class="w-full p-2 border border-gray-300 rounded-lg text-sm" required />
          </div>

          <div class="flex items-center gap-5">
            <label class="min-w-[150px] font-semibold text-sm text-black">Nama Barang</label>
            <select v-model="formData.id_alat" class="w-full p-2 border border-gray-300 rounded-lg text-sm" required>
              <option disabled value="">Pilih Barang</option>
              <option v-for="item in alatList" :key="item.id_alat" :value="item.id_alat">
                {{ item.nama_barang }}
              </option>
            </select>
          </div>

          <div class="flex items-center gap-5">
            <label class="min-w-[150px] font-semibold text-sm text-black">Jumlah Terpakai</label>
            <input type="number" v-model.number="formData.jumlah" min="1" required
              class="w-full p-2 border border-gray-300 rounded-lg text-sm" />
          </div>

          <div class="flex items-center gap-5">
            <label class="min-w-[150px] font-semibold text-sm text-black">Keterangan (Opsional)</label>
            <textarea v-model="formData.keterangan" placeholder="Contoh: Keperluan meeting"
              class="w-full p-2 border border-gray-300 rounded-lg text-sm"> </textarea>
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
              Simpan Pemakaian
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
  components: {
    Sidebar, HeaderBar,
    SuccessAlert
  },
  data() {
    return {
      activeMenu: "pemakaianAlat",
      alatList: [],
      formData: {
        NID: "",
        id_alat: "",
        jumlah: "",
        keterangan: "",
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
    async submitForm() {
      const token = localStorage.getItem("token");

      const payload = {
        NID: this.formData.NID,
        id_alat: this.formData.id_alat,
        jumlah: this.formData.jumlah,
        keterangan: this.formData.keterangan,
      };

      try {
        await axios.post("http://localhost:8000/api/history_pemakaian", payload, {
          headers: {
            Authorization: `Bearer ${token}`,
            Accept: "application/json",
          },
        }).then((response) => {
          this.successMessage = "History Pemakaian berhasil ditambahkan";
          this.showSuccessAlert = true;
          setTimeout(() => {
            this.showSuccessAlert = false;
            this.$router.push({
              path: `/manajemen-alat`,
            });
          }, 2500);
        });
      } catch (error) {
        console.error(error);
        alert("Gagal mencatat pemakaian.");
      }
    },
  },
};
</script>
