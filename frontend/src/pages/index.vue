<script setup lang="ts">
import TaskList from './TaskList.vue';

definePage({
  path: '/',
  meta: {
    layout: 'blank',
    public: true,
  },
})
</script>

<template>
  <div>
    <TaskList />
  </div>
</template>
