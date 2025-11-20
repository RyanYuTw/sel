<template>
  <div>
    <h2>學習手冊列表</h2>
    <div class="toolbar">
      <select v-model="filterYear" @change="filterHandbooks" class="filter-select">
        <option value="">全部年度</option>
        <option v-for="year in years" :key="year" :value="year">{{ year }}</option>
      </select>
      <select v-model="filterGrade" @change="filterHandbooks" class="filter-select">
        <option value="">全部年級</option>
        <option v-for="g in 6" :key="g" :value="g">{{ g }}年級</option>
      </select>
      <select v-model="filterSemester" @change="filterHandbooks" class="filter-select">
        <option value="">全部學期</option>
        <option value="上">上學期</option>
        <option value="下">下學期</option>
      </select>
      <select v-model="filterLesson" @change="filterHandbooks" class="filter-select">
        <option value="">全部課別</option>
        <option v-for="lesson in lessons" :key="lesson" :value="lesson">{{ lesson }}</option>
      </select>
      <select v-model="filterStatus" @change="filterHandbooks" class="filter-select">
        <option value="">全部狀態</option>
        <option value="1">已發布</option>
        <option value="0">未發布</option>
      </select>
      <button @click="$router.push('/edit')" class="btn-add">新增手冊</button>
    </div>
    <table class="handbook-table">
      <thead>
        <tr>
          <th>年度</th>
          <th>年級</th>
          <th>學期</th>
          <th>課別</th>
          <th>發布時間</th>
          <th>建立時間</th>
          <th>更新時間</th>
          <th>操作</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="handbook in filteredHandbooks" :key="handbook.id">
          <td>{{ handbook.year }}</td>
          <td>{{ handbook.grade }}年級</td>
          <td>{{ handbook.semester }}學期</td>
          <td>{{ handbook.lesson }}</td>
          <td>{{ formatDate(handbook.published_at) }}</td>
          <td>{{ formatDate(handbook.created_at) }}</td>
          <td>{{ formatDate(handbook.updated_at) }}</td>
          <td class="actions">
            <button @click="editHandbook(handbook.id)" class="btn-edit">編輯</button>
            <button @click="previewHandbook(handbook)" class="btn-preview">預覽</button>
            <button @click="deleteHandbook(handbook.id)" class="btn-delete">刪除</button>
          </td>
        </tr>
      </tbody>
    </table>


  </div>
</template>

<script>
export default {
  data() {
    return {
      handbooks: [],
      filteredHandbooks: [],
      filterYear: '',
      filterGrade: '',
      filterSemester: '',
      filterLesson: '',
      filterStatus: ''
    }
  },
  computed: {
    years() {
      return [...new Set(this.handbooks.map(h => h.year))].sort((a, b) => b - a)
    },
    lessons() {
      return [...new Set(this.handbooks.map(h => h.lesson))].sort()
    }
  },
  async mounted() {
    await this.loadHandbooks()
  },
  methods: {
    async loadHandbooks() {
      const response = await fetch('/api/handbooks')
      this.handbooks = await response.json()
      this.filteredHandbooks = this.handbooks
    },
    filterHandbooks() {
      this.filteredHandbooks = this.handbooks.filter(h => {
        if (this.filterYear && h.year !== parseInt(this.filterYear)) return false
        if (this.filterGrade && h.grade !== parseInt(this.filterGrade)) return false
        if (this.filterSemester && h.semester !== this.filterSemester) return false
        if (this.filterLesson && h.lesson !== this.filterLesson) return false
        if (this.filterStatus !== '' && h.status !== parseInt(this.filterStatus)) return false
        return true
      })
    },
    formatDate(timestamp) {
      if (!timestamp || timestamp === 0) return '-'
      const date = new Date(timestamp * 1000)
      return date.toLocaleString('zh-TW', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit'
      })
    },
    editHandbook(id) {
      this.$router.push(`/edit/${id}`)
    },
    previewHandbook(handbook) {
      window.open(`/preview/${handbook.id}`, '_blank')
    },
    async deleteHandbook(id) {
      const result = await Swal.fire({
        title: '確定要刪除嗎？',
        text: '此操作無法復原！',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ff7043',
        cancelButtonColor: '#9e9e9e',
        confirmButtonText: '確定刪除',
        cancelButtonText: '取消'
      })
      if (result.isConfirmed) {
        await fetch(`/api/handbooks/${id}`, { method: 'DELETE' })
        await this.loadHandbooks()
        Swal.fire({ icon: 'success', title: '已刪除', timer: 1500, showConfirmButton: false })
      }
    }
  }
}
</script>

<style scoped>
.toolbar { display: flex; gap: 1rem; margin-bottom: 1rem; align-items: center; flex-wrap: wrap; }
.filter-select { padding: 0.75rem; border: 1px solid #e1e8ed; border-radius: 6px; font-size: 1rem; min-width: 120px; }
.filter-input { padding: 0.75rem; border: 1px solid #e1e8ed; border-radius: 6px; font-size: 1rem; width: 150px; }
.btn-add { padding: 0.75rem 1.5rem; background: #8bc34a; color: white; border: none; border-radius: 6px; cursor: pointer; white-space: nowrap; transition: background 0.3s; }
.btn-add:hover { background: #7cb342; }
.handbook-table { width: 100%; border-collapse: collapse; background: white; box-shadow: 0 1px 3px rgba(0,0,0,0.1); border-radius: 8px; overflow: hidden; }
.handbook-table th { background: #8bc34a; color: white; padding: 1rem; text-align: left; font-weight: bold; }
.handbook-table td { padding: 0.75rem 1rem; border-bottom: 1px solid #f0f0f0; }
.handbook-table tbody tr:hover { background: #fffef0; }
.actions button { margin-right: 0.5rem; padding: 0.5rem 1rem; border: none; border-radius: 6px; cursor: pointer; transition: all 0.3s; }
.btn-edit { background: #8bc34a; color: white; }
.btn-edit:hover { background: #7cb342; }
.btn-preview { background: #ffd54f; color: #333; }
.btn-preview:hover { background: #ffc107; }
.btn-delete { background: #ff7043; color: white; }
.btn-delete:hover { background: #f4511e; }


@media (max-width: 768px) {
  .toolbar { flex-direction: column; }
  .search-input { width: 100%; }
  .btn-add { width: 100%; }
  .handbook-table { font-size: 0.9rem; }
  .handbook-table th, .handbook-table td { padding: 0.5rem; }
  .actions button { padding: 0.4rem 0.6rem; font-size: 0.8rem; margin-bottom: 0.25rem; }
}

@media (max-width: 480px) {
  .handbook-table { display: block; overflow-x: auto; white-space: nowrap; }
  .actions button { display: block; width: 100%; margin-right: 0; }
}
</style>
