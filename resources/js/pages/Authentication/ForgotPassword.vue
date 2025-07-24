<template>
    <div
        class="flex flex-col items-center justify-center min-h-screen bg-gray-100 p-6"
    >
        <div
            class="bg-white p-8 rounded-2xl shadow-lg max-w-md w-full text-center"
        >
            <h1 class="text-3xl font-bold mb-4 text-[#08607a]">
                Lupa Password?
            </h1>
            <p class="text-gray-600 mb-6 text-sm leading-relaxed">
                Jika Anda lupa password akun Sistem Pengadaan PLN, silakan hubungi
                kontak berikut untuk bantuan reset password:
            </p>

            <div
                class="bg-[#fef3c7] text-[#92400e] p-4 rounded-lg mb-4 text-left text-sm"
            >
                <p><strong>Kontak:</strong></p>
                <p>Ibu Linda Aqnes</p>
                <p>
                    Kunjungi di Lt.2 Building Service 
                    <!-- WhatsApp:
                    <a
                        href="https://wa.me/6281234567890"
                        target="_blank"
                        class="text-[#08607a] underline"
                        >+62 812-3456-7890</a
                    > -->
                </p>
                <p>
                    Email:
                    <a
                        href="mailto:linda.aqnes@example.com"
                        class="text-[#08607a] underline"
                        >linda.aqnes@example.com</a
                    >
                </p>
            </div>

            <p class="text-gray-500 text-xs mb-6">
                Demi keamanan, reset password hanya bisa dilakukan melalui
                verifikasi oleh Ibu Linda Aqnes.
            </p>

            <router-link
                to="/login"
                class="inline-block px-6 py-2 rounded-full bg-[#4f93af] text-white text-sm font-semibold hover:bg-[#166357]"
            >
                Kembali ke Login
            </router-link>
        </div>
    </div>
</template>

<script>
export default {
    name: "ForgotPassword",
};
</script>

<style scoped></style>
