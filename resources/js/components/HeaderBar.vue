<template>
  <div class="flex justify-between items-center mb-4">
    <h2 class="text-2xl font-semibold text-gray-900">{{ title }}</h2>
  </div>
</template>

<script>
export default {
  name: 'HeaderBar',
  props: {
    title: {
      type: String,
      required: true
    }
  }
}
</script>
