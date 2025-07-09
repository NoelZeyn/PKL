<template>
  <div class="flex flex-col items-center justify-center min-h-screen bg-yellow-50 p-6">
    <div class="bg-white p-8 rounded-2xl shadow-lg max-w-md w-full text-center">
      <h1 class="text-2xl font-bold text-yellow-600 mb-4">Akun Anda Masih Diproses</h1>
      <p class="text-gray-600 mb-4 text-sm leading-relaxed">
        Akun Anda saat ini berstatus <strong>pending</strong>. Mohon tunggu persetujuan dari admin untuk mengaktifkan akun.
      </p>
      <router-link to="/login"
        class="inline-block mt-4 px-6 py-2 rounded-full bg-yellow-500 text-white text-sm font-semibold hover:bg-yellow-600">
        Kembali ke Login
      </router-link>
    </div>
  </div>
</template>

<script>
export default {
  name: 'PendingAccess',
};
</script>
