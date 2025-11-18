<template>
  <div>
    <h2>手冊列表</h2>
    <div class="toolbar">
      <input v-model="searchKeyword" @input="filterHandbooks" type="text" placeholder="搜尋年度、年級、學期或課次..." class="search-input">
      <button @click="$router.push('/edit')" class="btn-add">新增手冊</button>
    </div>
    <table class="handbook-table">
      <thead>
        <tr>
          <th>年度</th>
          <th>年級</th>
          <th>學期</th>
          <th>課別</th>
          <th>建立時間</th>
          <th>更新時間</th>
          <th>操作</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="handbook in filteredHandbooks" :key="handbook.id">
          <td>{{ handbook.year }}</td>
          <td>第{{ handbook.grade }}年級</td>
          <td>{{ handbook.semester }}學期</td>
          <td>{{ handbook.lesson }}</td>
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
    
    <div v-if="previewContent" class="preview-modal" @click="closePreview">
      <div class="preview-content" @click.stop>
        <h3>{{ previewTitle }}</h3>
        <div>{{ previewContent }}</div>
        <button @click="closePreview">關閉</button>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      handbooks: [],
      filteredHandbooks: [],
      searchKeyword: '',
      previewContent: null,
      previewTitle: ''
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
      const keyword = this.searchKeyword.toLowerCase()
      this.filteredHandbooks = this.handbooks.filter(h => 
        h.year.toString().includes(keyword) ||
        h.grade.toString().includes(keyword) ||
        h.semester.includes(keyword) ||
        h.lesson.toString().includes(keyword)
      )
    },
    formatDate(dateString) {
      const date = new Date(dateString)
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
      this.previewTitle = `${handbook.year}年 第${handbook.grade}年級 ${handbook.semester}學期 ${handbook.lesson}`
      // 解碼 HTML 實體顯示正確內容
      const parser = new DOMParser()
      const doc = parser.parseFromString(handbook.content, 'text/html')
      this.previewContent = doc.body.textContent || doc.body.innerText || handbook.content
    },
    closePreview() {
      this.previewContent = null
    },
    async deleteHandbook(id) {
      if (confirm('確定要刪除此手冊嗎？')) {
        await fetch(`/api/handbooks/${id}`, { method: 'DELETE' })
        await this.loadHandbooks()
      }
    }
  }
}
</script>

<style scoped>
.toolbar { display: flex; gap: 1rem; margin-bottom: 1rem; align-items: center; }
.search-input { flex: 1; padding: 0.75rem; border: 1px solid #e1e8ed; border-radius: 6px; font-size: 1rem; }
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
.preview-modal { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; z-index: 1000; }
.preview-content { background: white; padding: 2rem; border-radius: 8px; max-width: 80%; max-height: 80%; overflow: auto; }

@media (max-width: 768px) {
  .toolbar { flex-direction: column; }
  .search-input { width: 100%; }
  .btn-add { width: 100%; }
  .handbook-table { font-size: 0.9rem; }
  .handbook-table th, .handbook-table td { padding: 0.5rem; }
  .actions button { padding: 0.4rem 0.6rem; font-size: 0.8rem; margin-bottom: 0.25rem; }
  .preview-content { max-width: 95%; padding: 1rem; }
}

@media (max-width: 480px) {
  .handbook-table { display: block; overflow-x: auto; white-space: nowrap; }
  .actions button { display: block; width: 100%; margin-right: 0; }
}
</style>