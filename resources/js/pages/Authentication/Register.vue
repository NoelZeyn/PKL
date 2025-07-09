<template>
    <div class="flex min-h-screen flex-col md:flex-row">
        <!-- Left Side -->
        <div class="hidden md:flex w-full md:w-2/5 text-white rounded-r-lg overflow-hidden bg-cover bg-center opacity-80"
            :style="{ backgroundImage: `url(${mosqueBackground})` }">
            <div
                class="space-y-2 p-8 bg-black/50 h-full flex flex-col items-center justify-center text-center mb-5 w-[100%]">
                <h2 class="text-3xl font-bold">Selamat Datang</h2>
                <p class="text-base leading-relaxed text-white/90">
                    Sistem ATK PLN adalah platform yang dirancang untuk memudahkan pengelolaan dan pemantauan
                    Manajemen ATK di PT. PLN Nusantara Power UP Gresik.
                </p>
            </div>
        </div>

        <!-- Right Side -->
        <div class="w-full md:w-3/5 flex flex-col items-center pt-5 overflow-hidden">
            <div class="flex items-center gap-3 self-start ml-12 mb-10">
                <img :src="logoImage" alt="Logo Image" class="w-[75px] rounded-t-lg object-cover" />
                <span class="logo-text text-[20px] font-bold text-[#08607a] font-['Protest_Strike']">System ATK
                    PLN</span>
            </div>

            <div class="flex flex-col items-center text-center mb-5 w-[100%] max-w-md">
                <h2 class="text-3xl font-semibold mb-2 w-full">
                    Register Akun System ATK PLN
                </h2>
                <p class="text-sm text-gray-500 mb-6">
                    Monitor, Manage, and Care Better
                </p>

                <form @submit.prevent="register" class="w-full">
                    <!-- NID -->
                    <div class="mb-4">
                        <label class="block text-left font-semibold mb-1">NID</label>
                        <div class="relative">
                            <img src="@/assets/email.svg"
                                class="absolute top-1/2 left-3 transform -translate-y-1/2 w-6 h-6 opacity-50" />
                            <input type="text" v-model="form.NID" placeholder="Enter your NID" required
                                class="pl-12 pr-4 py-3 w-full rounded-xl border border-gray-300 text-gray-800 focus:outline-none" />
                            <p v-if="errors.NID" class="text-left text-sm text-red-500 mt-1">
                                {{ errors.NID[0] }}
                            </p>
                        </div>
                    </div>

                    <!-- Password -->
                    <div class="mb-4">
                        <label class="block text-left font-semibold mb-1">Password</label>
                        <div class="relative">
                            <img src="@/assets/password.svg"
                                class="absolute top-1/2 left-3 transform -translate-y-1/2 w-6 h-6 opacity-60" />
                            <input :type="showPassword ? 'text' : 'password'" v-model="form.password"
                                placeholder="Masukkan password Anda" minlength="8" required
                                class="pl-12 pr-10 py-3 w-full rounded-xl border border-gray-300 text-gray-800 focus:outline-none" />
                            <button type="button" @click="togglePassword"
                                class="absolute top-1/2 right-3 transform -translate-y-1/2 cursor-pointer">
                                <img :src="showPassword ? eyeOffIcon : eyeIcon" alt="Toggle Password" class="w-5 h-5" />
                            </button>
                            <p v-if="errors.password" class="text-left text-sm text-red-500 mt-1">
                                {{ errors.password[0] }}
                            </p>
                        </div>
                    </div>

                    <!-- Penempatan (Select dari API) -->
                    <div class="mb-4">
                        <label class="block text-left font-semibold mb-1">Penempatan</label>
                        <div class="relative">
                            <img src="@/assets/email.svg"
                                class="absolute top-1/2 left-3 transform -translate-y-1/2 w-6 h-6 opacity-50" />
                            <select v-model="form.id_penempatan_fk" required
                                class="pl-12 pr-4 py-3 w-full rounded-xl border border-gray-300 text-gray-800 focus:outline-none">
                                <option disabled value="">Pilih Penempatan</option>
                                <option v-for="item in penempatanList" :key="item.id" :value="item.id">
                                    {{ item.nama_penempatan }}
                                </option>
                            </select>
                            <p v-if="errors.id_penempatan_fk" class="text-left text-sm text-red-500 mt-1">
                                {{ errors.id_penempatan_fk[0] }}
                            </p>
                        </div>
                    </div>

                    <!-- Otoritas -->
                    <div class="mb-4">
                        <label class="block text-left font-semibold mb-1">Otoritas</label>
                        <div class="relative">
                            <img src="@/assets/email.svg"
                                class="absolute top-1/2 left-3 transform -translate-y-1/2 w-6 h-6 opacity-50" />
                            <select v-model="form.tingkatan_otoritas" required
                                class="pl-12 pr-4 py-3 w-full rounded-xl border border-gray-300 text-gray-800 focus:outline-none">
                                <option disabled value="">
                                    -- Pilih Akses --
                                </option>
                                <option value="superadmin">Superadmin</option>
                                <option value="admin">Admin</option>
                                <option value="user">User</option>
                                <option value="user_review">User Review</option>
                                <option value="anggaran">Anggaran</option>
                            </select>
                            <p v-if="errors.tingkatan_otoritas" class="text-left text-sm text-red-500 mt-1">
                                {{ errors.tingkatan_otoritas[0] }}
                            </p>
                        </div>
                    </div>

                    <!-- Button -->
                    <button type="submit"
                        class="cursor-pointer w-full mt-2 py-3 bg-[#4f93af] text-white font-semibold rounded-3xl hover:bg-[#166357]">
                        Register
                    </button>
                </form>

                <p v-if="message" :class="messageClass + ' mt-3 text-sm'">
                    {{ message }}
                </p>
                <p v-if="showSuccessAlert" class="text-green-500 mb-3">{{ successMessage }}</p>


                <div class="w-full my-3 flex items-center justify-center"></div>

                <p class="text-sm">
                    Already have an account?
                    <router-link to="/login" class="text-sm text-[#ffad54] hover:underline">Sign in</router-link>
                </p>
            </div>
        </div>
    </div>
</template>

<script>
import axios from "axios";
import mosqueBackground from "@/assets/mosque-background.png";
import eyeIcon from "@/assets/eye.svg";
import eyeOffIcon from "@/assets/eye-off.svg";
import logoImage from "@/assets/PLN.svg";
import googleLogo from "@/assets/Google.svg";
import SuccessAlert from "@/components/SuccessAlert.vue";

export default {
    components: {
        SuccessAlert,
    },
    data() {
        return {
            form: {
                NID: "",
                password: "",
                id_penempatan_fk: "",
                tingkatan_otoritas: "",
            },
            penempatanList: [],
            errors: {},
            mosqueBackground,
            message: "",
            messageClass: "",
            showPassword: false,
            logoImage,
            googleLogo,
            eyeIcon,
            eyeOffIcon,
            showSuccessAlert: false,
            successMessage: "",
        };
    },
    mounted() {
        this.fetchPenempatanList();
    },
    methods: {
        fetchPenempatanList() {
            const apiUrl =
                import.meta.env.VITE_APP_URL || "http://localhost:8000/api";
            axios
                .get(`${apiUrl}/penempatan`)
                .then((response) => {
                    this.penempatanList = response.data.data;
                })
                .catch((error) => {
                    console.error("Error fetching penempatan list:", error);
                });
        },

        togglePassword() {
            this.showPassword = !this.showPassword;
        },
        async register() {
            this.errors = {}; // Reset errors
            this.message = ""; // Reset general message
            try {
                const apiUrl =
                    import.meta.env.VITE_APP_URL || "http://localhost:8000/api";

                const response = await axios.post(`${apiUrl}/register`, {
                    NID: this.form.NID,
                    password: this.form.password,
                    id_penempatan_fk: this.form.id_penempatan_fk,
                    tingkatan_otoritas: this.form.tingkatan_otoritas,
                });


                if (response.status === 200) {
                    this.successMessage = "Registrasi berhasil! Silakan tunggu konfirmasi dari admin";
                    this.showSuccessAlert = true;
                    setTimeout(() => {
                        this.showSuccessAlert = false;
                        this.$router.push("/login");
                    }, 3000); // Redirect after 3 seconds
                }
            } catch (error) {
                console.error("Error:", error);

                if (error.response?.status === 422) {
                    this.errors = error.response.data.errors || {};
                } else if (error.response?.status === 404) {
                    this.message = error.response.data.message || "NID tidak tersedia.";
                } else {
                    this.message = "Terjadi kesalahan saat memeriksa NID.";
                }

                this.messageClass = "text-red-500";
            }
        },
    },
};
</script>
<style scoped>
.logo-text {
    font-family: "Protest Strike", sans-serif;
    font-size: 26px;
    color: #08607a;
}
</style>
