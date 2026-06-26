<script setup lang="ts">
import { ref } from 'vue'

const selectedFile = ref<File | null>(null)
const imagePreview = ref<string | null>(null)
const isProcessing = ref(false)
const rawTextOutput = ref<string>('')
const menuTitle = ref<string>('')

// Handle image selection file changes
const handleFileChange = (event: Event) => {
  const target = event.target as HTMLInputElement
  if (target.files && target.files[0]) {
    const file = target.files[0]
    selectedFile.value = file
    imagePreview.value = URL.createObjectURL(file)
  }
}

// Mimic initial processing state step before backend integration
const handleOcrSubmit = async () => {
  if (!selectedFile.value) return
  isProcessing.value = true
  rawTextOutput.value = ''
  
  // Simulated frontend response delay for UX feel
  setTimeout(() => {
    isProcessing.value = false
    rawTextOutput.value = `APPETIZERS\nMargherita Pizza 12.00\nGarlic Bread 5.50\n\nMAIN COURSES\nGrilled Salmon 24.00\nTruffle Pasta 18.50`
  }, 2500)
}
</script>

<template>
  <div class="max-w-4xl mx-auto p-8">
    <header class="mb-8 pb-6 border-b border-neutral-200">
      <h1 class="text-3xl font-bold tracking-tight text-neutral-900">OCR Menu Import</h1>
      <p class="text-neutral-500 mt-1">Convert physical paper menu snapshots into digital data structures instantly.</p>
    </header>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
      <section class="space-y-6">
        <div>
          <label class="block text-sm font-medium text-neutral-700 mb-2">Menu System Title</label>
          <input 
            v-model="menuTitle" 
            type="text" 
            placeholder="e.g., Summer Dinner Menu" 
            class="w-full px-4 py-2.5 bg-white border border-neutral-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-black focus:border-transparent transition"
          />
        </div>

        <div>
          <label class="block text-sm font-medium text-neutral-700 mb-2">Upload Menu Snapshot Image</label>
          <div class="border-2 border-dashed border-neutral-300 rounded-xl p-6 bg-white hover:border-neutral-400 transition text-center cursor-pointer relative">
            <input 
              type="file" 
              accept="image/*" 
              @change="handleFileChange" 
              class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
            />
            <div v-if="!imagePreview" class="space-y-2">
              <span class="text-3xl">📷</span>
              <p class="text-sm font-medium text-neutral-600">Click to upload or drag an image file here</p>
              <p class="text-xs text-neutral-400">Supports PNG, JPG, or WEBP up to 5MB</p>
            </div>
            <img v-else :src="imagePreview" class="max-h-64 mx-auto rounded-lg shadow-sm" alt="Menu Preview" />
          </div>
        </div>

        <button 
          @click="handleOcrSubmit"
          :disabled="!selectedFile || isProcessing"
          class="w-full bg-black text-white py-3 px-4 rounded-lg font-medium hover:bg-neutral-800 disabled:bg-neutral-200 disabled:text-neutral-400 transition"
        >
          <span v-if="isProcessing">Extracting Layout Strings...</span>
          <span v-else>Run Optical Character Analysis</span>
        </button>
      </section>

      <section class="flex flex-col h-full bg-neutral-900 text-neutral-100 rounded-xl p-6 shadow-inner min-h-[350px]">
        <div class="flex items-center justify-between mb-4 border-b border-neutral-800 pb-3">
          <h2 class="text-sm font-medium uppercase tracking-wider text-neutral-400">Layout Engine Output</h2>
          <span v-if="isProcessing" class="inline-flex h-2 w-2 rounded-full bg-amber-400 animate-pulse"></span>
        </div>
        
        <div class="flex-1 font-mono text-sm leading-relaxed whitespace-pre-wrap overflow-y-auto max-h-[400px]">
          <span v-if="!rawTextOutput && !isProcessing" class="text-neutral-500 italic">No layout processing results computed yet. Choose a file and hit compile.</span>
          <span v-else-if="isProcessing" class="text-neutral-400 animate-pulse">Reading pixels and mapping strings...</span>
          <span v-else>{{ rawTextOutput }}</span>
        </div>
      </section>
    </div>
  </div>
</template>