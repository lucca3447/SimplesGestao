<script setup>
import Sidebar from './Sidebar.vue';
import TopHeader from './TopHeader.vue';
</script>

<template>
  <div class="min-h-screen bg-[#f5f6f8] flex flex-row">
    <!-- Sidebar Fixa -->
    <Sidebar />

    <!-- Área Principal de Conteúdo -->
    <div class="flex-1 flex flex-col min-w-0">
      <TopHeader />

      <main class="flex-1 p-6 lg:p-8 overflow-y-auto">
        <router-view />
      </main>

      <!-- Footer Conexa sutil -->
      <footer class="py-4 text-center text-[11px] text-gray-400 border-t border-gray-200/60 bg-white/40">
        <p>Copyright © 2026 by <strong class="text-gray-600 font-semibold">simples</strong> · Versão 1.0 · Sistema de Gestão Empresarial</p>
      </footer>
    </div>
  </div>
</template>
