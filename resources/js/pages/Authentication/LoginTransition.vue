<template>
  <div class="flex flex-col items-center justify-center min-h-screen bg-gray-100 p-6">
    <div class="bg-white p-8 rounded-2xl shadow-lg max-w-md w-full text-center">
      <h1 class="text-2xl font-bold text-[#08607a] mb-4">
        {{ loadingMessage }}
      </h1>
      <p class="text-gray-500 text-sm mb-6">
        Mohon tunggu sebentar, kami sedang memeriksa status akun Anda.
      </p>
      <div v-if="isLoading" class="w-12 h-12 border-4 border-blue-300 border-t-blue-600 rounded-full animate-spin mx-auto"></div>
    </div>
  </div>
</template>

<script>
import axios from "axios";

export default {
  name: "LoginTransition",
  data() {
    return {
      isLoading: true,
      loadingMessage: 'Proses Login...'
    };
  },
  mounted() {
    this.checkStatus();
  },
  methods: {
    async checkStatus() {
      try {
        const apiUrl = import.meta.env.VITE_APP_URL || "http://localhost:8000/api";
        const token = localStorage.getItem("token");

        if (!token) {
          this.$router.push('/login');
          return;
        }

        const response = await axios.post(`${apiUrl}/me`, {}, {
          headers: {
            Authorization: `Bearer ${token}`,
          },
        });

        const user = response.data;
        localStorage.setItem('user', JSON.stringify(user));

        const access = user.access;
        const role = user.tingkatan_otoritas;

        if (access === 'pending') {
          this.$router.push('/pending-access');
        } else if (access === 'inactive') {
          this.$router.push('/inactive-access');
        } else if (access === 'active') {
          this.loadingMessage = 'Akses Ditemukan. Mengalihkan...';

          setTimeout(() => {
            switch (role) {
              case 'superadmin':
              case 'admin':
              case 'user':
              case 'user_review':
              case 'anggaran':
                this.$router.push('/dashboard');
                break;
              default:
                this.$router.push('/dashboard');
                break;
            }
          }, 1500); // Hanya 1,5 detik biar tetap smooth

        } else {
          this.$router.push('/login');
        }

      } catch (error) {
        console.error("Gagal ambil status:", error.response?.data || error.message);
        this.$router.push('/login');
      } finally {
        this.isLoading = false;
      }
    }
  }
};
</script>

<style scoped>
</style>
