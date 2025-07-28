<template>
  <div class="flex flex-col lg:flex-row min-h-screen bg-gray-100">
    <!-- Sidebar -->
    <Sidebar :activeMenu="activeMenu" @update:activeMenu="updateActiveMenu" />

    <!-- Main Content -->
    <div class="flex-1 p-4 md:p-8 bg-white">
      <HeaderBar title="Tambah Akun Baru" />
      <div class="border-b border-gray-300 my-4"></div>

      <div class="bg-white p-4 md:p-6 rounded-2xl shadow max-w-3xl mx-auto">
        <form @submit.prevent="submitForm" class="space-y-6">
          <!-- NID -->
          <div class="flex flex-col md:flex-row md:items-center gap-2 md:gap-4">
            <label class="md:min-w-[150px] font-semibold text-sm text-black">NID</label>
            <input v-model="form.nid" type="text" class="w-full border border-gray-300 rounded-lg p-2 text-sm"
              placeholder="Masukkan NID" required />
          </div>

          <!-- Password -->
          <div class="flex flex-col md:flex-row md:items-center gap-2 md:gap-4">
            <label class="md:min-w-[150px] font-semibold text-sm text-black">Password</label>
            <input v-model="form.password" type="password" class="w-full border border-gray-300 rounded-lg p-2 text-sm"
              placeholder="Masukkan Password" required />
          </div>

          <!-- Bidang -->
          <div class="flex flex-col md:flex-row md:items-center gap-2 md:gap-4">
            <label class="md:min-w-[150px] font-semibold text-sm text-black">Bidang</label>
            <select v-model="form.id_bidang_fk" @change="handleBidangChange"
              class="w-full border border-gray-300 rounded-lg p-2 text-sm" required>
              <option disabled value="">Pilih Bidang</option>
              <option v-for="item in bidangList" :key="item.id" :value="item.id">
                {{ item.nama_bidang }}
              </option>
            </select>
          </div>

          <!-- Penempatan -->
          <div v-if="form.id_bidang_fk" class="flex flex-col md:flex-row md:items-center gap-2 md:gap-4">
            <label class="md:min-w-[150px] font-semibold text-sm text-black">Penempatan</label>
            <select v-model="form.id_penempatan_fk" class="w-full border border-gray-300 rounded-lg p-2 text-sm"
              required>
              <option disabled value="">Pilih Penempatan</option>
              <option v-for="item in penempatanList" :key="item.id" :value="item.id">
                {{ item.nama_penempatan }}
              </option>
            </select>
          </div>

          <!-- Tingkatan -->
          <div class="flex flex-col md:flex-row md:items-center gap-2 md:gap-4">
            <label class="md:min-w-[150px] font-semibold text-sm text-black">Tingkatan Otoritas</label>
            <select v-model="form.tingkatan_otoritas" class="w-full border border-gray-300 rounded-lg p-2 text-sm"
              required>
              <option disabled value="">Pilih Tingkatan</option>
              <option value="admin">Admin</option>
              <option value="superadmin">Super Admin</option>
              <option value="user">User</option>
              <option value="asman">Asman</option>
              <option value="manajer">Manajer</option>
              <option value="anggaran">Anggaran</option>
              <option value="user_review">User Review</option>
            </select>
          </div>

          <!-- Alert -->
          <SuccessAlert :visible="showSuccessAlert" :message="successMessage" />

          <!-- Action Buttons -->
          <div class="flex flex-col sm:flex-row justify-between items-stretch sm:items-center gap-3 pt-4">
            <router-link to="/manajemen-akun">
              <button type="button"
                class="w-full sm:w-auto bg-[#074a5d] text-white px-4 py-2 rounded-lg hover:bg-[#063843] transition">
                Kembali
              </button>
            </router-link>
            <button type="submit"
              class="w-full sm:w-auto bg-[#074a5d] text-white px-4 py-2 rounded-lg hover:bg-[#063843] transition">
              Simpan Akun
            </button>
          </div>
        </form>
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
  name: "TambahAkun",
  components: { Sidebar, HeaderBar, SuccessAlert },
  data() {
    return {
      activeMenu: "manajemenAkun",
      form: {
        nid: "",
        password: "",
        id_bidang_fk: "", // Tambahkan ini agar logika lengkap
        id_penempatan_fk: "",
        tingkatan_otoritas: ""
      },
      bidangList: [],
      penempatanList: [],
      showSuccessAlert: false,
      successMessage: "",
      errors: {}
    };
  },

  created() {
    this.fetchBidangList();
  },

  methods: {
    updateActiveMenu(menu) {
      this.activeMenu = menu;
    },
    async fetchBidangList() {
      try {
        const apiUrl = import.meta.env.VITE_APP_URL || "http://localhost:8000/api";
        const response = await axios.get(`${apiUrl}/bidang`);
        this.bidangList = response.data.data;
      } catch (error) {
        console.error("Gagal memuat data bidang:", error);
      }
    },
    async handleBidangChange() {
      this.form.id_penempatan_fk = "";
      if (!this.form.id_bidang_fk) return;
      try {
        const apiUrl = import.meta.env.VITE_APP_URL || "http://localhost:8000/api";
        const response = await axios.get(`${apiUrl}/penempatan/by-bidang/${this.form.id_bidang_fk}`);
        this.penempatanList = response.data.data;
      } catch (error) {
        console.error("Gagal memuat penempatan:", error);
      }
    },
    async submitForm() {
      try {
        const token = localStorage.getItem("token");
        const res = await axios.post("http://localhost:8000/api/account", this.form, {
          headers: { Authorization: `Bearer ${token}` }
        });

        this.successMessage = "Akun berhasil ditambahkan!";
        this.showSuccessAlert = true;
        this.errors = {};
        this.form = {
          nid: "",
          password: "",
          id_bidang_fk: "",
          id_penempatan_fk: "",
          tingkatan_otoritas: ""
        };
        setTimeout(() => this.$router.push("/manajemen-akun"), 2000);
      } catch (error) {
        if (error.response?.data?.errors) {
          this.errors = error.response.data.errors;
        }
        console.error("Gagal menambahkan akun:", error);
      }
    }
  }
};
</script>

<style scoped>
label {
  font-size: 13px;
}
</style>
