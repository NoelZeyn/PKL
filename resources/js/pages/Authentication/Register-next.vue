<template>
    <div class="flex min-h-screen flex-col md:flex-row">
        <div
            class="hidden md:flex w-full md:w-2/5 text-white rounded-r-lg overflow-hidden bg-cover bg-center opacity-80"
            :style="{ backgroundImage: `url(${mosqueBackground})` }"
        >
            <div
                class="space-y-2 p-8 bg-black/50 h-full flex flex-col items-center justify-center text-center mb-5 w-[100%]"
            >
                <h2 class="text-3xl font-bold">Selamat Datang</h2>
                <p class="text-base leading-relaxed text-white/90">
                    Mengelola informasi kesehatan ibu dan anak, laporan
                    kegiatan, dan data kunjungan dalam satu platform praktis.
                </p>
            </div>
        </div>

        <div
            class="w-full md:w-3/5 flex flex-col items-center pt-5 overflow-hidden pb-8"
        >
            <div class="flex items-center gap-3 self-start ml-12 mb-10">
                <img
                    :src="logoImage"
                    alt="Logo Image"
                    class="w-[75px] rounded-t-lg object-cover"
                />
                <span
                    class="logo-text text-[20px] font-bold text-[#08607a] font-['Protest_Strike']"
                    >Sistem Pengadaan PLN</span
                >
            </div>

            <div class="w-[80%] max-w-[450px] text-center">
                <h2
                    class="text-[29px] mb-[10px] font-bold"
                    :style="{ color: '#074A5D' }"
                >
                    Lengkapi Data Diri Anda
                </h2>
                <p class="text-sm text-gray-500 mb-[25px]">
                    Silakan lengkapi data diri Anda pada formulir di bawah ini
                    untuk melanjutkan pendaftaran
                </p>

                <form @submit.prevent="register">
                    <div class="flex flex-col items-start mb-5">
                        <label class="text-base mb-1 font-bold"
                            >Nama Lengkap</label
                        >
                        <div class="relative w-full">
                            <img
                                :src="informIcon"
                                alt="Info Icon"
                                class="absolute right-[10px] top-1/2 transform -translate-y-1/2 w-5 h-5"
                            />
                            <input
                                type="text"
                                v-model="form.nama_lengkap"
                                placeholder="Masukkan nama anda"
                                required
                                class="w-full p-3 border border-gray-300 rounded-[10px] text-base placeholder:text-[#b0b3b5]"
                            />
                        </div>
                    </div>

                    <div class="flex flex-col items-start mb-5">
                        <label class="text-base mb-1 font-bold">Posisi</label>
                        <select
                            v-model="form.posisi"
                            required
                            :class="{ 'text-gray-400': form.posisi === '' }"
                            class="w-full p-3 border border-gray-300 rounded-[10px] text-base bg-white"
                        >
                            <option disabled value="">
                                -- Pilih Posisi --
                            </option>
                            <option value="Ketua">Ketua</option>
                            <option value="Wakil_Ketua">Wakil Ketua</option>
                            <option value="Sekretaris">Sekretaris</option>
                            <option value="Bendahara">Bendahara</option>
                            <option value="Pengurus">Pengurus</option>
                        </select>
                    </div>

                    <div class="flex flex-col items-start mb-5">
                        <label class="text-base mb-1 font-bold">Foto KTP</label>
                        <input
                            type="file"
                            @change="onFileChange('foto_ktp', $event)"
                            accept="image/*"
                            required
                            class="w-full p-3 border border-gray-300 rounded-[10px] text-base"
                        />
                    </div>

                    <div class="flex flex-col items-start mb-5">
                        <label class="text-base mb-1 font-bold"
                            >Foto Profil</label
                        >
                        <input
                            type="file"
                            @change="onFileChange('foto_profil', $event)"
                            accept="image/*"
                            required
                            class="w-full p-3 border border-gray-300 rounded-[10px] text-base"
                        />
                    </div>

                    <SuccessAlert
                        :visible="showSuccessAlert"
                        :message="successMessage"
                    />

                    <button
                        type="submit"
                        class="bg-[#08607a] text-white w-[100%] py-2 rounded-full hover:bg-[#065062] transition-colors duration-200"
                    >
                        Sign Up
                    </button>
                </form>

                <p v-if="message" :class="messageClass" class="mt-4">
                    {{ message }}
                </p>
            </div>
        </div>
    </div>
</template>

<script>
import mosqueBackground from "@/assets/mosque-background.png";
import axios from "axios";
import babyImage from "@/assets/baby.jpg";
import dokterImage from "@/assets/dokter.png";
import logoImage from "@/assets/logo2.png";
import informIcon from "@/assets/inform.svg";
import SuccessAlert from "@/components/SuccessAlert.vue";

export default {
    components: { SuccessAlert },
    data() {
        return {
            form: {
                nama_lengkap: "",
                posisi: "",
            },
            mosqueBackground,
            files: {
                foto_ktp: null,
                foto_profil: null,
            },
            message: "",
            messageClass: "",
            showSuccessAlert: false,
            successMessage: "",
            babyImage,
            dokterImage,
            logoImage,
            informIcon,
        };
    },
    mounted() {
        const url = window.location.href;
        const emailMatch = url.match(/email=([^&#]+)/);
        const tokenMatch = url.match(/token=([^&#]+)/);

        if (tokenMatch && emailMatch) {
            const token = decodeURIComponent(tokenMatch[1]);
            const email = decodeURIComponent(emailMatch[1]);

            localStorage.setItem("auth_token", token);
            localStorage.setItem("email", email);
            localStorage.setItem("password", "from_google_oauth");
            window.history.replaceState({}, document.title, "/register");
        }
    },
    methods: {
        onFileChange(field, event) {
            const file = event.target.files[0];
            if (file) {
                this.files[field] = file;
            }
        },
        async register() {
            const email = localStorage.getItem("email");
            const password = localStorage.getItem("password");

            if (!email || !password) {
                this.message =
                    "Email atau password tidak ditemukan. Silakan ulangi proses pendaftaran.";
                this.messageClass = "text-red-500";
                return;
            }

            const formData = new FormData();
            formData.append("nama_lengkap", this.form.nama_lengkap);
            formData.append("posisi", this.form.posisi);
            formData.append("email", email);
            formData.append("password", password);
            formData.append("foto_ktp", this.files.foto_ktp);
            formData.append("foto_profil", this.files.foto_profil);

            try {
                const apiUrl =
                    import.meta.env.VITE_APP_URL || "http://localhost:8000/api";
                const response = await axios.post(
                    `${apiUrl}/register`,
                    formData,
                    {
                        headers: {
                            "Content-Type": "multipart/form-data",
                        },
                    }
                );

                if (response.status === 201) {
                    this.successMessage = "Pendaftaran berhasil!";
                    this.showSuccessAlert = true;
                    this.message = "";
                    this.$router.push("/login");
                }
            } catch (error) {
                console.error("Error saat register:", error);
                this.message =
                    error.response?.data?.message ||
                    "Terjadi kesalahan saat mendaftar.";
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
