<template>
    <div class="flex min-h-screen bg-gray-100">
        <Sidebar :activeMenu="activeMenu" @update:activeMenu="updateActiveMenu" />
        <div class="flex-1 p-8 pt-4 bg-white">
            <HeaderBar title="Perubahan Password" class="mt-3" />
            <div class="my-4 border-b border-gray-300"></div>

            <div class="bg-white rounded-lg shadow-lg overflow-hidden border border-gray-300 mt-6">
                <div class="p-8">
                    <h3 class="text-lg font-bold text-gray-900 pb-4 border-b border-gray-300">
                        Ubah Password
                    </h3>

                    <form @submit.prevent="submitPassword" class="space-y-4 mt-5">
                        <div class="flex gap-10 items-center">
                            <label class="w-1/3 font-semibold text-sm text-gray-700">Password Saat Ini</label>
                            <div class="relative w-1/2">
                                <input :type="showPassword ? 'text' : 'password'"
                                    v-model="passwordForm.current_password" required
                                    class="p-1 pl-2 w-full border border-gray-300 rounded-lg bg-gray-50 focus:ring-1 focus:ring-blue-500" />
                                <span class="absolute top-1/2 right-3 transform -translate-y-1/2 cursor-pointer"
                                    @click="togglePasswordVisibility">
                                    <i :class="showPassword
                                            ? 'fas fa-eye-slash'
                                            : 'fas fa-eye'
                                        "></i>
                                </span>
                            </div>
                        </div>

                        <div class="flex gap-10 items-center">
                            <label class="w-1/3 font-semibold text-sm text-gray-700">Password Baru</label>
                            <div class="relative w-1/2">
                                <input :type="showPassword ? 'text' : 'password'" v-model="passwordForm.password"
                                    required
                                    class="p-1 pl-2 w-full border border-gray-300 rounded-lg bg-gray-50 focus:ring-1 focus:ring-blue-500" />
                                <span class="absolute top-1/2 right-3 transform -translate-y-1/2 cursor-pointer"
                                    @click="togglePasswordVisibility">
                                    <i :class="showPassword
                                            ? 'fas fa-eye-slash'
                                            : 'fas fa-eye'
                                        "></i>
                                </span>
                            </div>
                        </div>

                        <div class="flex gap-10 items-center">
                            <label class="w-1/3 font-semibold text-sm text-gray-700">Konfirmasi Password Baru</label>
                            <div class="relative w-1/2">
                                <input :type="showPassword ? 'text' : 'password'"
                                    v-model="passwordForm.password_confirmation" required
                                    class="p-1 pl-2 w-full border border-gray-300 rounded-lg bg-gray-50 focus:ring-1 focus:ring-blue-500" />
                                <span class="absolute top-1/2 right-3 transform -translate-y-1/2 cursor-pointer"
                                    @click="togglePasswordVisibility">
                                    <i :class="showPassword
                                            ? 'fas fa-eye-slash'
                                            : 'fas fa-eye'
                                        "></i>
                                </span>
                            </div>
                        </div>

                        <div class="flex justify-between mt-8">
                            <button type="button"
                                class="cursor-pointer px-8 py-2 text-white bg-[#074a5d] border border-[#074a5d] rounded-full font-medium hover:bg-gray-100 hover:text-black focus:outline-none focus:ring-2 focus:ring-[#074a5d]"
                                @click="handleBack">
                                Batal
                            </button>
                            <button type="submit"
                                class="cursor-pointer px-8 py-2 text-white bg-[#074a5d] border border-[#074a5d] rounded-full font-medium hover:bg-gray-100 hover:text-black focus:outline-none focus:ring-2 focus:ring-[#074a5d]">
                                Ubah Password
                            </button>
                        </div>
                    </form>

                    <SuccessAlert :visible="showSuccessAlert" :message="successMessage" />

                    <div v-if="message" class="mt-4 text-green-600">
                        {{ message }}
                    </div>
                    <div v-if="error" class="mt-4 text-red-600">
                        {{ error }}
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
    name: "UbahPassword",
    components: { Sidebar, HeaderBar, SuccessAlert },
    data() {
        return {
            activeMenu: "profile",
            passwordForm: {
                current_password: "",
                password: "",
                password_confirmation: "",
            },
            message: "",
            error: "",
            showSuccessAlert: false,
            successMessage: "",
            showPassword: false, // Added for toggle functionality
        };
    },
    methods: {
        updateActiveMenu(newMenu) {
            this.activeMenu = newMenu;
        },
        handleBack() {
            if (confirm("Batalkan perubahan?")) {
                this.$router.go(-1);
            }
        },
        togglePasswordVisibility() {
            this.showPassword = !this.showPassword;
        },
        async submitPassword() {
            if (
                this.passwordForm.password !==
                this.passwordForm.password_confirmation
            ) {
                this.error =
                    "Password baru dan konfirmasi password tidak cocok.";
                this.showSuccessAlert = false;
                return;
            }

            try {
                const token = localStorage.getItem("token");

                const res = await axios.post(
                    "http://localhost:8000/api/editPassword",
                    this.passwordForm,
                    {
                        headers: { Authorization: `Bearer ${token}` },
                    }
                );

                if (res.data.status === "success") {
                    this.successMessage = "Password berhasil diubah.";
                    this.showSuccessAlert = true;
                    this.error = null; 

                    // Reset form
                    this.passwordForm = {
                        current_password: "",
                        password: "",
                        password_confirmation: "",
                    };

                    setTimeout(() => {
                        this.showSuccessAlert = false;
                        this.$router.push("/profile");
                    }, 3000);
                } else {
                    this.error =
                        res.data.message || "Gagal memperbarui password.";
                    this.showSuccessAlert = false;
                }
            } catch (err) {
                this.error =
                    err.response?.data?.message ||
                    "Terjadi kesalahan saat memperbarui password.";
                this.showSuccessAlert = false;
            }
        },
    },
};
</script>
