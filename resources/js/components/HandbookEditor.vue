<template>
  <div>
    <h2>{{ id ? '編輯手冊' : '新增手冊' }} <span v-if="lastSaved" class="auto-save-status">最後儲存: {{ formatTime(lastSaved) }}</span></h2>
    <form @submit.prevent="saveHandbook">
      <div class="editor-layout">
        <div class="editor-main">
          <div class="form-group">
            <label>內容：</label>
            <simple-editor v-model="handbook.content" ref="editor"></simple-editor>

        <div class="zhuyin-panel" v-if="false" style="display: none;">
          <h4>加入注音</h4>
          <div class="form-group">
            <label>輸入文字：</label>
            <input v-model="zhuyinText" type="text" class="form-control" placeholder="請輸入文字">
          </div>
          <div class="form-group" v-if="zhuyinOptions.length > 0">
            <label>選擇注音：</label>
            <div class="char-zhuyin-groups">
              <div v-for="(option, charIndex) in zhuyinOptions" :key="charIndex" class="char-group">
                <div class="char-label">{{ option.char }}</div>
                <div class="zhuyin-options">
                  <button v-for="(zhuyin, zhuyinIndex) in option.zhuyinList" :key="zhuyinIndex"
                          type="button"
                          @click="selectZhuyin(charIndex, zhuyinIndex)"
                          :class="['btn-option', { 'selected': option.selectedZhuyin === zhuyin }]">
                    {{ zhuyin }}
                  </button>
                </div>
              </div>
            </div>
            <div v-if="selectedZhuyinText" class="preview">
              <strong>預覽：</strong><span v-html="selectedZhuyinText"></span>
            </div>
          </div>
          <div class="panel-actions">
            <button type="button" @click="processZhuyin" class="btn-process">處理注音</button>
            <button type="button" @click="insertZhuyin" class="btn-insert">插入文字</button>
            <button type="button" @click="closeZhuyinPanel" class="btn-close">關閉</button>
          </div>
        </div>

        <button type="button" @click="toggleZhuyinPanel" class="btn-zhuyin-toggle" style="display: none;">
          {{ showZhuyinPanel ? '隱藏注音面板' : '顯示注音面板' }}
        </button>
          </div>
        </div>

        <div class="editor-sidebar">
          <div class="form-group">
            <label>年度：</label>
            <input v-model.number="handbook.year" type="number" required class="form-control" placeholder="例如: 114">
          </div>
          <div class="form-group">
            <label>年級：</label>
            <select v-model.number="handbook.grade" required class="form-control">
              <option value="">-- 選擇年級 --</option>
              <option v-for="g in 6" :key="g" :value="g">{{ g }}年級</option>
            </select>
          </div>
          <div class="form-group">
            <label>學期：</label>
            <select v-model="handbook.semester" required class="form-control">
              <option value="">選擇學期</option>
              <option value="上">上學期</option>
              <option value="下">下學期</option>
            </select>
          </div>
          <div class="form-group">
            <label>課別：</label>
            <input v-model="handbook.lesson" type="text" required class="form-control" placeholder="課別">
          </div>
          <div class="form-actions">
            <button type="submit" class="btn-save">儲存</button>
            <button type="button" @click="$router.push('/')" class="btn-cancel">取消</button>
          </div>
        </div>
      </div>
    </form>
  </div>
</template>

<script>
import SimpleEditor from './SimpleEditor.vue'

export default {
  components: {
    SimpleEditor
  },
  props: ['id'],
  data() {
    return {
      handbook: { year: new Date().getFullYear() - 1911, grade: '', semester: '', lesson: '', content: '' },
      editor: null,
      showZhuyinPanel: false,
      zhuyinText: '',
      zhuyinOptions: [],
      selectedZhuyinText: '',
      htmlMode: false,
      htmlTextarea: null,
      autoSaveTimer: null,
      lastSaved: null
    }
  },
  async mounted() {
    if (this.id) {
      await this.loadHandbook()
    }
    this.startAutoSave()
    this.setEditorHeight()
    window.addEventListener('resize', this.setEditorHeight)
  },
  beforeDestroy() {
    if (this.editor) {
      this.editor.destroy()
    }
    if (this.autoSaveTimer) {
      clearInterval(this.autoSaveTimer)
    }
    window.removeEventListener('resize', this.setEditorHeight)
  },

  methods: {
    async loadHandbook() {
      const response = await fetch(`/api/handbooks/${this.id}`)
      const data = await response.json()
      this.handbook = {
        ...data,
        content: this.decodeHtml(data.content || '')
      }
    },
    decodeHtml(html) {
      const txt = document.createElement('textarea')
      txt.innerHTML = html
      return txt.value
    },
    async saveHandbook() {
      const method = this.id ? 'PUT' : 'POST'
      const url = this.id ? `/api/handbooks/${this.id}` : '/api/handbooks'

      await fetch(url, {
        method,
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(this.handbook)
      })

      this.$router.push('/')
    },
    startAutoSave() {
      this.autoSaveTimer = setInterval(() => {
        if (this.id && this.handbook.year && this.handbook.grade && this.handbook.semester && this.handbook.lesson) {
          this.autoSave()
        }
      }, 30000)
    },
    async autoSave() {
      try {
        await fetch(`/api/handbooks/${this.id}`, {
          method: 'PUT',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(this.handbook)
        })
        this.lastSaved = new Date()
      } catch (error) {
        console.error('自動儲存失敗:', error)
      }
    },
    toggleZhuyinPanel() {
      this.showZhuyinPanel = !this.showZhuyinPanel
      if (this.showZhuyinPanel) {
        this.zhuyinText = ''
        this.zhuyinOptions = []
        this.selectedZhuyinText = ''
      }
    },
    closeZhuyinPanel() {
      this.showZhuyinPanel = false
    },
    async processZhuyin() {
      if (!this.zhuyinText) return

      this.zhuyinOptions = []
      this.selectedZhuyinText = ''

      for (let i = 0; i < this.zhuyinText.length; i++) {
        const char = this.zhuyinText[i]

        if (/[\u4e00-\u9fff]/.test(char)) {
          const zhuyinList = await this.getZhuyinFromAPI(char)
          const cleanedList = zhuyinList ? zhuyinList.map(z => z.replace(/[（(]又音[）)]/g, '').replace(/[（(]語音[）)]/g, '').replace(/[（(]讀音[）)]/g, '')) : ['']
          const charOptions = {
            char: char,
            position: i,
            zhuyinList: cleanedList,
            selectedZhuyin: null
          }
          this.zhuyinOptions.push(charOptions)
        }
      }
    },
    selectZhuyin(charIndex, zhuyinIndex) {
      const option = this.zhuyinOptions[charIndex]
      const selectedZhuyin = option.zhuyinList[zhuyinIndex]
      option.selectedZhuyin = selectedZhuyin
      this.updateSelectedText()
    },
    updateSelectedText() {
      this.selectedZhuyinText = ''
      for (let option of this.zhuyinOptions) {
        if (!option.selectedZhuyin) continue
        this.selectedZhuyinText += this.formatZhuyinHTML(option.char, option.selectedZhuyin)
      }
    },
    formatZhuyinHTML(char, zhuyin) {
      const cleanedZhuyin = zhuyin.replace(/[（(]又音[）)]/g, '').replace(/[（(]語音[）)]/g, '').replace(/[（(]讀音[）)]/g, '')
      const toneMarks = { 'ˊ': 2, 'ˇ': 3, 'ˋ': 4, '˙': 5, '·': 5, '.': 5 }
      let tone = ''
      let zhuyinBase = cleanedZhuyin

      for (let mark in toneMarks) {
        if (cleanedZhuyin.includes(mark)) {
          tone = mark
          zhuyinBase = cleanedZhuyin.replace(mark, '')
          break
        }
      }

      let zhuyinHTML = ''
      if (tone === '˙' || tone === '·' || tone === '.') {
        const chars = zhuyinBase.split('')
        zhuyinHTML = `<span class="zhuyin-char zhuyin-with-light"><span class="light-tone">˙</span>${chars[0]}</span>`
        for (let i = 1; i < chars.length; i++) {
          zhuyinHTML += `<span class="zhuyin-char">${chars[i]}</span>`
        }
      } else if (tone) {
        const chars = zhuyinBase.split('')
        zhuyinHTML = chars.map(char => `<span class="zhuyin-char">${char}</span>`).join('')
        zhuyinHTML = `<span class="zhuyin-with-tone">${zhuyinHTML}<span class="tone-mark">${tone}</span></span>`
      } else {
        zhuyinHTML = zhuyinBase.split('').map(char => `<span class="zhuyin-char">${char}</span>`).join('')
      }

      return `<span class="vertical-zhuyin">${zhuyinHTML}</span>`
    },
    insertZhuyin() {
      if (this.selectedZhuyinText) {
        const editor = this.$refs.editor.editor
        if (editor) {
          editor.insertContent(this.zhuyinText + this.selectedZhuyinText + '<span style="font-size: inherit;">&#8203;</span>')
          editor.selection.collapse(false)
          editor.focus()
        } else {
          this.handbook.content += this.zhuyinText + this.selectedZhuyinText
        }
      }
      this.closeZhuyinPanel()
    },
    setEditorHeight() {
      this.$nextTick(() => {
        const editorEl = this.$refs.editor?.$el
        if (editorEl) {
          const rect = editorEl.getBoundingClientRect()
          const height = window.innerHeight - rect.top - 20
          if (this.$refs.editor.editor) {
            this.$refs.editor.editor.editorContainer.style.height = height + 'px'
          }
        }
      })
    },
    toggleHtmlMode() {
      this.htmlMode = !this.htmlMode
      const editorEl = this.$refs.editor.$el

      if (this.htmlMode) {
        // 切換到 HTML 模式
        editorEl.style.display = 'none'

        if (!this.htmlTextarea) {
          this.htmlTextarea = document.createElement('textarea')
          this.htmlTextarea.style.width = '100%'
          this.htmlTextarea.style.height = '400px'
          this.htmlTextarea.style.fontFamily = 'monospace'
          this.htmlTextarea.className = 'html-editor'
          editorEl.parentNode.insertBefore(this.htmlTextarea, editorEl.nextSibling)
        }

        this.htmlTextarea.value = this.handbook.content
        this.htmlTextarea.style.display = 'block'
      } else {
        // 切換回視覺模式
        if (this.htmlTextarea) {
          this.handbook.content = this.htmlTextarea.value
          this.htmlTextarea.style.display = 'none'
        }
        editorEl.style.display = 'block'
      }
    },
    async getZhuyinFromAPI(word) {
      try {
        const response = await fetch(`/api/zhuyin?word=${encodeURIComponent(word)}`)

        if (!response.ok) {
          console.log('API 回應錯誤:', response.status)
          return null
        }

        const data = await response.json()
        console.log('API 回應:', data)

        return data.zhuyin && data.zhuyin.length > 0 ? data.zhuyin : null
      } catch (error) {
        console.error('注音 API 請求失敗:', error)
        return null
      }
    },

  },
  computed: {
    formatTime() {
      return (date) => {
        const h = date.getHours().toString().padStart(2, '0')
        const m = date.getMinutes().toString().padStart(2, '0')
        const s = date.getSeconds().toString().padStart(2, '0')
        return `${h}:${m}:${s}`
      }
    }
  }
}
</script>

<style scoped>
.editor-layout {
  display: flex;
  gap: 2rem;
}

.editor-main {
  flex: 1;
}

.editor-sidebar {
  width: 250px;
  flex-shrink: 0;
}

.auto-save-status { font-size: 0.8rem; color: #666; font-weight: normal; margin-left: 1rem; }
.form-group { margin-bottom: 1rem; }
.form-group label { display: block; margin-bottom: 0.5rem; font-weight: bold; }
.form-control { width: 100%; padding: 0.5rem; border: 1px solid #ddd; border-radius: 4px; }
.ck-editor__editable { min-height: 400px; }
.form-actions { margin-top: 2rem; display: flex; flex-direction: column; gap: 0.5rem; }
.form-actions button { width: 100%; padding: 0.75rem 1.5rem; border: none; border-radius: 4px; cursor: pointer; }
.editor-actions {
  margin-top: 0.5rem;
}

.btn-zhuyin {
  background: #8bc34a;
  color: white;
  padding: 0.5rem 1rem;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-size: 0.9rem;
  margin-right: 0.5rem;
}

.btn-html {
  background: #6c757d;
  color: white;
  padding: 0.5rem 1rem;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-size: 0.9rem;
}

.html-editor {
  border: 1px solid #ddd;
  border-radius: 4px;
  padding: 0.5rem;
}
.btn-save { background: #8bc34a; color: white; }
.btn-cancel { background: #ffd54f; color: #333; }

/* 注音面板樣式 */
.zhuyin-panel {
  background: #f8f9fa;
  border: 1px solid #ddd;
  border-radius: 8px;
  padding: 1rem;
  margin-bottom: 1rem;
}

.char-zhuyin-groups {
  display: flex;
  flex-direction: row;
  gap: 1rem;
  margin-top: 0.5rem;
  flex-wrap: wrap;
}

.char-group {
  border: 1px solid #ddd;
  border-radius: 6px;
  padding: 0.75rem;
  background: white;
}

.char-label {
  font-size: 1.2rem;
  font-weight: bold;
  margin-bottom: 0.5rem;
  color: #333;
  text-align: center;
}

.zhuyin-options {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
  justify-content: center;
}

.btn-option {
  background: #e9ecef;
  border: 1px solid #ced4da;
  border-radius: 4px;
  padding: 0.25rem 0.5rem;
  cursor: pointer;
  font-size: 0.9rem;
}

.btn-option:hover {
  background: #dee2e6;
}

.btn-option.selected {
  background: #8bc34a !important;
  color: white !important;
}

.panel-actions {
  margin-top: 1rem;
}

.panel-actions button {
  margin-right: 0.5rem;
  padding: 0.5rem 1rem;
  border: none;
  border-radius: 4px;
  cursor: pointer;
}

.btn-process { background: #8bc34a; color: white; }
.btn-insert { background: #aed581; color: white; }
.btn-close { background: #ffd54f; color: #333; }

.preview {
  margin-top: 1rem;
  padding: 0.75rem;
  background: #f8f9fa;
  border: 1px solid #dee2e6;
  border-radius: 4px;
  font-size: 1.1rem;
}

.btn-zhuyin-toggle {
  margin-top: 0.5rem;
  background: #8bc34a;
  color: white;
  padding: 0.5rem 1rem;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-size: 0.9rem;
}

/* 注音顯示樣式 */
.char-with-zhuyin {
  display: inline-flex;
  align-items: center;
  margin-right: 0.2rem;
}

.vertical-zhuyin {
  display: flex;
  flex-direction: column;
  justify-content: center;
  margin-left: 1px;
  max-height: 1em;
  overflow: visible;
  position: relative;
}

.zhuyin-char {
  font-size: 0.28em;
  color: #333;
  line-height: 0.9;
}

.zhuyin-with-tone {
  display: flex;
  flex-direction: column;
  position: relative;
}

.tone-mark {
  position: absolute;
  right: -0.3em;
  top: 50%;
  transform: translateY(-50%);
  font-size: 0.28em;
}

.zhuyin-with-light {
  position: relative;
  display: inline-block;
}

.light-tone {
  position: absolute;
  top: -0.6em;
  left: 0;
  right: 0;
  text-align: center;
  font-size: 0.7em;
  color: #000;
}

@media (max-width: 768px) {
  .editor-layout { flex-direction: column; }
  .editor-sidebar { width: 100%; }
  .char-zhuyin-groups { flex-direction: column; }
  .zhuyin-panel { padding: 0.75rem; }
  .btn-zhuyin-toggle { width: 100%; }
}

@media (max-width: 480px) {
  .panel-actions button { width: 100%; margin-bottom: 0.5rem; margin-right: 0; }
}
</style>
