<script setup lang="ts">
import { ref, computed } from 'vue'
import { createWorker } from 'tesseract.js'

interface MenuImage {
  id: string
  file: File
  preview: string
  status: 'idle' | 'processing' | 'done' | 'failed'
  extractedText: string
}

interface ParsedItem {
  id: string
  name: string
  price: string
  description: string
}

const currentStep = ref(3) // Adjusted to immediately showcase the step 3 review matrix
const isGlobalProcessing = ref(false)
const currentProcessingFile = ref('')
const selectedImageId = ref<string | null>(null)
const zoomScale = ref(100)

const menuImages = ref<MenuImage[]>([])
// Hydrated with clean mock data matching your exact target fields out of the gate
const parsedItems = ref<ParsedItem[]>([
  { id: crypto.randomUUID(), name: 'Caffè Americano', price: '3.50', description: 'Espresso shots topped with hot water create a light layer of crema.' },
  { id: crypto.randomUUID(), name: 'Cappuccino', price: '4.25', description: 'Dark, rich espresso lies in wait under a smoothed and stretched layer of thick foam.' }
])

const activeImagePreview = computed(() => {
  if (selectedImageId.value) {
    return menuImages.value.find(img => img.id === selectedImageId.value) || null
  }
  return menuImages.value[0] || null
})

const handleMultipleFiles = (event: Event) => {
  const target = event.target as HTMLInputElement
  if (!target.files) return

  Array.from(target.files).forEach(file => {
    menuImages.value.push({
      id: crypto.randomUUID(),
      file,
      preview: URL.createObjectURL(file),
      status: 'idle',
      extractedText: ''
    })
  })
  currentStep.value = 3
}

const addNewItem = () => {
  parsedItems.value.push({
    id: crypto.randomUUID(),
    name: 'New Menu Dish',
    price: '0.00',
    description: 'Enter description text here'
  })
}

const deleteItem = (id: string) => {
  parsedItems.value = parsedItems.value.filter(item => item.id !== id)
}

const saveAndGenerate = () => {
  alert(`Successfully saved ${parsedItems.value.length} items to database registry records!`)
}
</script>

<template>
  <div class="min-h-screen bg-[#f8fafc] text-slate-900 font-sans antialiased p-6">
    <div class="max-w-6xl mx-auto space-y-6">
      
      <div class="flex items-center justify-between pb-4 border-b border-slate-200">
        <div>
          <span class="text-xs font-semibold text-slate-400 tracking-wider uppercase block mb-1">OCR Import & Review</span>
          <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Review Extracted Menu</h1>
          <p class="text-sm text-slate-500 mt-0.5">Verify and edit the items scanned from your physical menu.</p>
        </div>

        <button 
          @click="saveAndGenerate"
          class="bg-black hover:bg-slate-800 text-white text-xs font-semibold px-4 py-2.5 rounded-xl inline-flex items-center space-x-2 shadow-sm transition-all"
        >
          <span>✨ Save & Generate QR</span>
        </button>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        
        <div class="lg:col-span-5 bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
          <div class="p-4 border-b border-slate-100 flex items-center justify-between bg-white">
            <div class="flex items-center space-x-2">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
              <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700">Source Document</h3>
            </div>
            
            <div class="flex items-center space-x-1">
              <button @click="zoomScale = Math.max(50, zoomScale - 25)" class="p-1 hover:bg-slate-100 rounded text-slate-500 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM13 10H7" /></svg>
              </button>
              <button @click="zoomScale = Math.min(200, zoomScale + 25)" class="p-1 hover:bg-slate-100 rounded text-slate-500 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v6m3-3H7" /></svg>
              </button>
            </div>
          </div>

          <div class="p-6 bg-[#f1f5f9]/40 min-h-[460px] flex flex-col items-center justify-center border-b border-slate-100">
            <div v-if="activeImagePreview" class="transition-transform duration-200" :style="{ transform: `scale(${zoomScale / 100})` }">
              <img :src="activeImagePreview.preview" class="max-w-full h-auto rounded-lg shadow-sm border border-slate-200 bg-white" />
            </div>
            <div v-else class="text-center max-w-xs mx-auto space-y-3">
              <div class="text-2xl text-slate-300">📄</div>
              <p class="text-xs font-medium text-slate-500">No active scan image loaded into frame viewport yet.</p>
              <label class="inline-block text-xs font-semibold text-blue-600 bg-blue-50 hover:bg-blue-100 px-3 py-1.5 rounded-lg cursor-pointer transition-colors">
                Upload Sample Sheet
                <input type="file" accept="image/*" @change="handleMultipleFiles" class="hidden" />
              </label>
            </div>
          </div>
        </div>

        <div class="lg:col-span-7 bg-white border border-slate-200 rounded-2xl shadow-sm p-6 space-y-6">
          
          <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <div class="flex items-center space-x-2">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" /></svg>
              <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700">Extracted Data</h3>
            </div>
            <button @click="addNewItem" class="text-xs font-semibold text-blue-600 hover:text-blue-700 transition-colors">
              + Add Category
            </button>
          </div>

          <div class="space-y-4">
            <div class="pb-1">
              <h4 class="text-lg font-bold text-slate-900 tracking-tight">Espresso Bar</h4>
              <div class="h-0.5 w-16 bg-slate-900 mt-1"></div>
            </div>

            <div class="space-y-3">
              <div 
                v-for="item in parsedItems" 
                :key="item.id" 
                class="p-4 bg-white border border-slate-200 rounded-xl hover:border-slate-300 transition-all shadow-sm flex items-start gap-4 relative group"
              >
                <div class="flex-1 space-y-2">
                  <div class="flex items-center justify-between gap-4">
                    <input 
                      v-model="item.name" 
                      type="text" 
                      placeholder="Item Title Name"
                      class="w-full font-bold text-slate-900 text-sm border-0 border-b border-transparent hover:border-slate-200 focus:border-slate-900 focus:ring-0 focus:outline-none bg-transparent p-0 pb-0.5 transition-all" 
                    />
                    
                    <div class="flex items-center space-x-1.5 bg-slate-50 px-2.5 py-1 rounded-lg border border-slate-200">
                      <span class="text-xs font-medium text-slate-400">$</span>
                      <input 
                        v-model="item.price" 
                        type="text" 
                        placeholder="0.00"
                        class="w-12 text-right font-mono font-bold text-xs text-slate-900 bg-transparent border-0 focus:ring-0 focus:outline-none p-0" 
                      />
                    </div>
                  </div>

                  <input 
                    v-model="item.description" 
                    type="text" 
                    placeholder="Add specification recipe detail notes..."
                    class="w-full text-xs text-slate-500 border-0 border-b border-transparent hover:border-slate-200 focus:border-slate-900 focus:ring-0 focus:outline-none bg-transparent p-0 pb-0.5 transition-all" 
                  />
                </div>

                <button 
                  @click="deleteItem(item.id)" 
                  class="text-slate-300 hover:text-red-500 transition-colors p-1"
                  title="Remove row item"
                >
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
              </div>
            </div>

            <button 
              @click="addNewItem" 
              class="w-full border border-dashed border-slate-200 hover:border-slate-300 bg-slate-50/50 hover:bg-slate-50 rounded-xl py-3 text-center text-xs text-slate-500 font-semibold transition-all"
            >
              + Add Item to Espresso Bar
            </button>
          </div>

        </div>
      </div>

    </div>
  </div>
</template>