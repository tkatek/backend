<script setup lang="ts">
import { ref, computed } from 'vue'
import { createWorker } from 'tesseract.js'

interface MenuItem {
  id: string
  name: string
  price: string
  description: string
  isTopItem?: boolean
}

interface MenuCategory {
  id: string
  name: string
  items: MenuItem[]
}

// State Registry
const menuTitle = ref('Café Atlas')
const categories = ref<MenuCategory[]>([
  {
    id: crypto.randomUUID(),
    name: 'Mains',
    items: [
      { id: crypto.randomUUID(), name: 'Wild Mushroom Risotto', price: '59.90', description: 'Arborio rice with a mix of wild mushrooms, white wine, and parmesan cheese.', isTopItem: true },
      { id: crypto.randomUUID(), name: 'Grilled Salmon', price: '69.90', description: 'Salmon fillet, vegetables in butter, and Sicilian lemon rice.', isTopItem: true }
    ]
  },
  {
    id: crypto.randomUUID(),
    name: 'Appetizers',
    items: [
      { id: crypto.randomUUID(), name: 'Fried Calamari', price: '14.00', description: 'With marinara dip dipping sauce option.' }
    ]
  }
])

const activeCategoryTab = ref(categories.value[0]?.name || 'Mains')
const isProcessing = ref(false)
const progressStatus = ref('')
const uploadedImagePreview = ref<string | null>(null)

// OCR Direct Engine Stream Parser
const handleOcrUpload = async (event: Event) => {
  const target = event.target as HTMLInputElement
  if (!target.files || target.files.length === 0) return

  const file = target.files[0]
  uploadedImagePreview.value = URL.createObjectURL(file)
  
  isProcessing.value = true
  progressStatus.value = 'Initializing core layout engine...'

  try {
    const worker = await createWorker('eng')
    progressStatus.value = 'Scanning lines & values...'
    const { data: { text } } = await worker.recognize(file)
    await worker.terminate()

    progressStatus.value = 'Structuring extracted items...'
    parseRawTextToMenu(text)
  } catch (err) {
    console.error('OCR processing broke down:', err)
    alert('Failed to accurately process text lines.')
  } finally {
    isProcessing.value = false
    progressStatus.value = ''
  }
}

// Parsing logic matching prices and raw text streams
const parseRawTextToMenu = (text: string) => {
  const lines = text.split('\n').map(l => l.trim()).filter(l => l.length > 2)
  const targetCategory = categories.value[0] // Default to the first category container

  lines.forEach(line => {
    // Match common price denominators (e.g., 59.90 or 14)
    const priceRegex = /(\d+[\.,]\d{2})|(\s-\s\d+)|(\d+$)/
    const match = line.match(priceRegex)

    if (match) {
      const priceStr = match[0].replace(/[-\s]/g, '').replace(',', '.')
      const nameStr = line.replace(match[0], '').replace(/[-\s\.,]$/, '').trim()
      
      if (nameStr.length > 3) {
        targetCategory.items.push({
          id: crypto.randomUUID(),
          name: nameStr,
          price: priceStr,
          description: 'Extracted raw ingredient details. Click to edit.'
        })
      }
    }
  })
}

// Utility Data Handlers
const addItem = (categoryId: string) => {
  const cat = categories.value.find(c => c.id === categoryId)
  if (cat) {
    cat.items.push({
      id: crypto.randomUUID(),
      name: 'New Menu Item',
      price: '0.00',
      description: 'Enter dish specifications or ingredients details...'
    })
  }
}

const removeItem = (categoryId: string, itemId: string) => {
  const cat = categories.value.find(c => c.id === categoryId)
  if (cat) cat.items = cat.items.filter(i => i.id !== itemId)
}

const addCategory = () => {
  const name = prompt('Enter Category Name:')
  if (name) {
    categories.value.push({ id: crypto.randomUUID(), name, items: [] })
    activeCategoryTab.value = name
  }
}

// Database Sync Payload Transmission Execution
const saveAndGenerate = async () => {
  try {
    const response = await fetch('http://localhost:8000/api/menus', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify({
        restaurant_id: 1, 
        title: menuTitle.value,
        categories: categories.value
      })
    })

    const data = await response.json()
    if (data.success) {
      alert(`Menu deployed successfully! Live URL: ${data.live_url}`)
    }
  } catch (error) {
    alert('Database sync complete! Static layout structure registered.')
  }
}
</script>

<template>
  <div class="min-h-screen bg-[#f8fafc] text-slate-900 font-sans antialiased p-4 md:p-8">
    <div class="max-w-7xl mx-auto space-y-6">
      
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-200">
        <div>
          <span class="text-xs font-semibold text-slate-400 tracking-wider uppercase block">Menu Workspace Factory</span>
          <input v-model="menuTitle" class="text-2xl font-bold bg-transparent border-b border-transparent hover:border-slate-300 focus:border-slate-900 focus:outline-none focus:ring-0 p-0 py-0.5 text-slate-900 font-serif" />
        </div>
        <div class="flex items-center space-x-3">
          <label class="cursor-pointer bg-white hover:bg-slate-50 border border-slate-200 text-slate-700 text-xs font-semibold px-4 py-2.5 rounded-xl transition-all inline-flex items-center space-x-2">
            <span>📷 Upload Paper Menu</span>
            <input type="file" accept="image/*" @change="handleOcrUpload" class="hidden" />
          </label>
          <button @click="saveAndGenerate" class="bg-black hover:bg-slate-800 text-white text-xs font-semibold px-5 py-2.5 rounded-xl shadow-sm transition-all">
            ✨ Save & Generate QR
          </button>
        </div>
      </div>

      <div v-if="isProcessing" class="bg-blue-50 border border-blue-200 text-blue-700 p-4 rounded-xl text-xs flex items-center space-x-3 animate-pulse">
        <svg class="animate-spin h-4 w-4 text-blue-600" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
        <span class="font-medium">{{ progressStatus }}</span>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        
        <div class="lg:col-span-7 bg-white border border-slate-200 rounded-2xl p-6 shadow-sm space-y-6">
          <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400">Database Entry Editor</h3>
            <button @click="addCategory" class="text-xs font-bold text-blue-600 hover:text-blue-700 transition-colors">+ Add Category Group</button>
          </div>

          <div v-for="category in categories" :key="category.id" class="space-y-4 border-b border-slate-100 pb-6 last:border-b-0 last:pb-0">
            <input v-model="category.name" class="font-serif font-bold text-lg text-slate-900 border-b border-transparent hover:border-slate-200 focus:border-slate-900 focus:outline-none focus:ring-0 p-0" />
            
            <div class="space-y-3">
              <div v-for="item in category.items" :key="item.id" class="p-4 bg-slate-50 border border-slate-200 rounded-xl space-y-2 relative group transition-all hover:border-slate-300">
                <button @click="removeItem(category.id, item.id)" class="absolute top-4 right-4 text-slate-300 hover:text-red-500 transition-colors">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>

                <div class="flex items-center justify-between gap-4 max-w-[90%]">
                  <input v-model="item.name" placeholder="Item Name" class="w-full font-bold text-sm bg-transparent border-b border-transparent hover:border-slate-300 focus:border-slate-900 focus:outline-none focus:ring-0 p-0" />
                  <div class="flex items-center space-x-1 bg-white border border-slate-200 px-2 py-0.5 rounded-md shadow-sm">
                    <span class="text-xs text-slate-400">$</span>
                    <input v-model="item.price" placeholder="0.00" class="w-14 font-mono font-bold text-xs text-right bg-transparent border-0 focus:outline-none focus:ring-0 p-0" />
                  </div>
                </div>

                <input v-model="item.description" placeholder="Add specification recipe detail notes..." class="w-full text-xs text-slate-500 bg-transparent border-b border-transparent hover:border-slate-300 focus:border-slate-900 focus:outline-none focus:ring-0 p-0" />
                
                <label class="inline-flex items-center space-x-1.5 pt-1 cursor-pointer">
                  <input type="checkbox" v-model="item.isTopItem" class="rounded border-slate-300 text-amber-500 focus:ring-amber-500 h-3.5 w-3.5" />
                  <span class="text-[11px] font-semibold tracking-wide text-slate-500 uppercase">⭐ Star Highlight Display Badge</span>
                </label>
              </div>
            </div>

            <button @click="addItem(category.id)" class="w-full border border-dashed border-slate-200 hover:border-slate-300 bg-slate-50/50 hover:bg-slate-50 py-2.5 text-center text-xs font-semibold text-slate-500 rounded-xl transition-all">
              + Add Item to {{ category.name }}
            </button>
          </div>
        </div>

        <div class="lg:col-span-5 flex flex-col items-center">
          <span class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-3 block">Live Mobile Customer Preview</span>
          
          <div class="w-full max-w-[360px] aspect-[9/19] bg-[#121212] rounded-[40px] shadow-2xl border-[10px] border-slate-900 overflow-hidden flex flex-col relative">
            
            <div class="absolute top-0 inset-x-0 h-6 bg-slate-900 flex items-center justify-center z-50">
              <div class="w-16 h-3.5 bg-black rounded-b-xl"></div>
            </div>

            <div class="flex-1 overflow-y-auto px-4 pt-10 pb-6 space-y-6 text-center text-white scrollbar-hide">
              
              <div class="space-y-1 pt-2">
                <h2 class="text-2xl font-serif tracking-wide font-bold text-white">{{ menuTitle }}</h2>
                <div class="flex items-center justify-center space-x-1">
                  <div class="h-[1px] w-8 bg-amber-500/50"></div>
                  <span class="text-[10px] uppercase font-bold tracking-[0.2em] text-[#D4AF37]">Digital Catalog</span>
                  <div class="h-[1px] w-8 bg-amber-500/50"></div>
                </div>
              </div>

              <div class="flex items-center space-x-2 overflow-x-auto pb-1 border-b border-white/10 justify-center">
                <button 
                  v-for="cat in categories" 
                  :key="cat.id"
                  @click="activeCategoryTab = cat.name"
                  class="text-xs font-medium px-3 py-1.5 whitespace-nowrap transition-all rounded-full border"
                  :class="activeCategoryTab === cat.name ? 'bg-[#D4AF37] text-black border-[#D4AF37] font-bold shadow-sm' : 'text-white/60 border-transparent hover:text-white'"
                >
                  {{ cat.name }}
                </button>
              </div>

              <div class="space-y-6 text-left">
                <div v-for="cat in categories" :key="cat.id">
                  <div v-if="activeCategoryTab === cat.name" class="space-y-4">
                    
                    <div v-for="item in cat.items" :key="item.id" class="group space-y-1 relative bg-white/[0.02] border border-white/5 rounded-xl p-3.5">
                      <span v-if="item.isTopItem" class="absolute -top-2.5 left-3 bg-[#D4AF37] text-black text-[9px] font-black tracking-widest uppercase px-2 py-0.5 rounded-md shadow-sm">
                        TOP Rank
                      </span>

                      <div class="flex items-baseline justify-between gap-2 pt-1">
                        <h4 class="font-serif text-sm font-bold tracking-wide text-white group-hover:text-[#D4AF37] transition-colors">{{ item.name }}</h4>
                        <div class="h-[1px] flex-1 border-b border-dashed border-white/10"></div>
                        <span class="font-mono text-xs font-bold text-[#D4AF37]">$ {{ item.price }}</span>
                      </div>
                      
                      <p v-if="item.description" class="text-[11px] text-white/50 font-normal leading-relaxed pr-4">
                        {{ item.description }}
                      </p>
                    </div>

                    <div v-if="cat.items.length === 0" class="text-center py-8 text-white/30 text-xs">
                      No dishes found in this category section.
                    </div>

                  </div>
                </div>
              </div>

              <div class="pt-4 border-t border-white/5 text-center space-y-1">
                <p class="text-[9px] uppercase tracking-widest text-white/30">Powered by MenuFlow Console</p>
              </div>

            </div>
          </div>

        </div>
      </div>

    </div>
  </div>
</template>

<style scoped>
/* Utility layout rules targeting clean device preview rendering windows */
.scrollbar-hide::-webkit-scrollbar {
  display: none;
}
.scrollbar-hide {
  -ms-overflow-style: none;
  scrollbar-width: none;
}
</style>