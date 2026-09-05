<script setup>
import { ref } from 'vue';
import Sidebar from './Sidebar.vue';
import TopHeader from './TopHeader.vue';

const isSidebarCollapsed = ref(false);

function toggleSidebar() {
  isSidebarCollapsed.value = !isSidebarCollapsed.value;
}
</script>

<template>
  <div class="min-h-screen bg-[#f5f6f8] flex flex-row">
    <!-- Sidebar Retrátil -->
    <Sidebar :is-collapsed="isSidebarCollapsed" />

    <!-- Área Principal de Conteúdo -->
    <div class="flex-1 flex flex-col min-w-0">
      <TopHeader @toggle-sidebar="toggleSidebar" />

      <main class="flex-1 p-6 lg:p-8 overflow-y-auto">
        <router-view />
      </main>

      <!-- Footer Corporativo -->
      <footer class="py-4 text-center text-[11px] text-gray-400 border-t border-gray-200/60 bg-white/40">
        <p>Copyright © 2026 by <strong class="text-gray-600 font-semibold">simples</strong> · Versão 1.0 · Sistema de Gestão Empresarial</p>
      </footer>
    </div>
  </div>
</template>
