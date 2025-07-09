<template>
  <div v-if="visible"
    class="fixed inset-0 z-50 flex items-center justify-center bg-[rgba(0,0,0,0.7)]">
    <div
      class="bg-white p-6 rounded-xl w-[90%] max-w-sm text-center max-h-[80vh] box-border border-[1px] border-[#08607A]">
      <img :src="ConfirmDelete" alt="Confirm Delete"
        class="w-12 h-12 mb-2 mx-auto md:w-9 md:h-9" />
      <h3 class="text-lg font-semibold text-gray-800 mb-1">
        {{ title }}
      </h3>
      <p class="text-sm text-gray-600">
        {{ message }}
      </p>
      <div class="flex flex-col gap-3 mt-4">
        <button @click="$emit('cancel')"
          class="cursor-pointer w-full py-2 px-4 text-sm rounded-full border-2 border-[#08607A] text-[#08607A] hover:bg-[#08607A] hover:text-white transition-all">
          Tidak
        </button>
        <button @click="$emit('confirm')"
          class="cursor-pointer w-full py-2 px-4 text-sm rounded-full bg-[#08607A] text-white hover:opacity-90 transition-all">
          Ya
        </button>
      </div>
    </div>
  </div>
</template>


<script>
import ConfirmDelete from '@/assets/Confirm_delete.svg';

export default {
  props: {
    visible: {
      type: Boolean,
      required: true
    },
    title: {
      type: String,
      default: 'Konfirmasi'
    },
    message: {
      type: String,
      default: 'Apakah Anda yakin?'
    }
  },
  data() {
    return {
      ConfirmDelete: ConfirmDelete
    };
  }
};
</script>
