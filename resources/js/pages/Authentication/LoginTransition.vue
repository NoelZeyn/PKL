<template>
  <div class="flex flex-col items-center justify-center min-h-screen bg-gray-100 p-6">
    <div class="bg-white p-8 rounded-2xl shadow-lg max-w-md w-full text-center">
      <h1 class="text-2xl font-bold text-[#08607a] mb-4">Proses Login...</h1>
      <p class="text-gray-500 text-sm mb-6">
        Mohon tunggu sebentar, kami sedang memeriksa status akun Anda.
      </p>
      <div class="w-12 h-12 border-4 border-blue-300 border-t-blue-600 rounded-full animate-spin mx-auto"></div>
    </div>
  </div>
</template>

<script>
import axios from "axios";

export default {
  name: "LoginTransition",
  mounted() {
    this.checkStatus();
  },
  methods: {
async checkStatus() {
  try {
    const apiUrl = import.meta.env.VITE_APP_URL || "http://localhost:8000/api";
    const token = localStorage.getItem("token");

    console.log("Token:", token);

    if (!token) {
      console.log("Token kosong, kembali ke login");
      this.$router.push('/login');
      return;
    }

    const { data: user } = await axios.post(`${apiUrl}/me`, {
      headers: {
        Authorization: `Bearer ${token}`,
      },
    });

    console.log("User data:", user);

    localStorage.setItem('user', JSON.stringify(user));

    const access = user.access;
    const role = user.tingkatan_otoritas;

    console.log("Access:", access);
    console.log("Role:", role);

    if (access === 'pending') {
      this.$router.push('/pending-access');
    } else if (access === 'inactive') {
      this.$router.push('/inactive');
    } else if (access === 'active') {
      switch (role) {
        case 'superadmin':
          this.$router.push('/dashboard');
          break;
        case 'admin':
          this.$router.push('/dashboard-admin');
          break;
        case 'user':
          this.$router.push('/dashboard-user');
          break;
        case 'user_review':
          this.$router.push('/dashboard-review');
          break;
        case 'anggaran':
          this.$router.push('/dashboard-anggaran');
          break;
        default:
          console.log("Unknown role, ke dashboard umum");
          this.$router.push('/dashboard');
      }
    } else {
      console.log("Access tidak dikenali, kembali ke login");
      this.$router.push('/login');
    }

  } catch (error) {
    console.error("Gagal ambil status:", error.response?.data || error.message);
    this.$router.push('/login');
  }
}

  },
};
</script>

<style scoped>
</style>
