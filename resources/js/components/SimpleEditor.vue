<template>
  <div>
    <textarea ref="editor" v-model="content"></textarea>
  </div>
</template>

<script>
export default {
  props: {
    value: {
      type: String,
      default: ''
    }
  },
  data() {
    return {
      content: this.value,
      editor: null,
      zhuyinMode: false,
      inputBuffer: ''
    }
  },
  mounted() {
    this.initEditor()
  },
  beforeDestroy() {
    if (this.editor) {
      this.editor.destroy()
    }
  },
  watch: {
    value(newVal) {
      this.content = newVal
      if (this.editor && newVal !== this.editor.getContent()) {
        this.editor.setContent(newVal || '')
      }
    }
  },
  methods: {
    async getZhuyin(word) {
      try {
        const response = await fetch(`/api/zhuyin?word=${encodeURIComponent(word)}`)
        if (!response.ok) return null
        const data = await response.json()
        return data.zhuyin && data.zhuyin.length > 0 ? data.zhuyin : null
      } catch (error) {
        console.error('注音 API 請求失敗:', error)
        return null
      }
    },
    async processLastChar(editor, char) {
      // 刪除最後一個字
      editor.execCommand('mceBackspace')
      
      // 獲取注音
      const zhuyinList = await this.getZhuyin(char)
      
      if (zhuyinList && zhuyinList.length > 0) {
        const cleanedList = zhuyinList.map(z => z.replace(/[（(]又音[）)]/g, '').replace(/[（(]語音[）)]/g, '').replace(/[（(]讀音[）)]/g, ''))
        const zhuyin = cleanedList[0]
        const html = this.formatZhuyinHTML(char, zhuyin)
        editor.insertContent(html + '<span style="font-size: inherit;">&#8203;</span>')
      } else {
        editor.insertContent(char)
      }
      
      editor.selection.collapse(false)
    },
    async processZhuyinFromEditor(editor) {
      // 獲取當前節點的文字內容
      const node = editor.selection.getNode()
      const textContent = node.textContent || ''
      
      // 從後往前找中文字
      let chineseChars = ''
      for (let i = textContent.length - 1; i >= 0; i--) {
        if (/[\u4e00-\u9fff]/.test(textContent[i])) {
          chineseChars = textContent[i] + chineseChars
        } else {
          break
        }
      }
      
      if (chineseChars.length === 0) return
      
      // 刪除這些中文字
      for (let i = 0; i < chineseChars.length; i++) {
        editor.execCommand('mceBackspace')
      }
      
      // 生成帶注音的HTML
      let result = ''
      for (let i = 0; i < chineseChars.length; i++) {
        const char = chineseChars[i]
        const zhuyinList = await this.getZhuyin(char)
        
        if (zhuyinList && zhuyinList.length > 0) {
          const cleanedList = zhuyinList.map(z => z.replace(/[（(]又音[）)]/g, '').replace(/[（(]語音[）)]/g, '').replace(/[（(]讀音[）)]/g, ''))
          const zhuyin = cleanedList[0]
          result += this.formatZhuyinHTML(char, zhuyin)
        } else {
          result += char
        }
      }
      
      editor.insertContent(result + '<span style="font-size: inherit;">&#8203;</span>')
      editor.selection.collapse(false)
    },
    showZhuyinDialog(editor, char, zhuyinList) {
      const cleanedList = zhuyinList.map(z => z.replace(/[（(]又音[）)]/g, '').replace(/[（(]語音[）)]/g, '').replace(/[（(]讀音[）)]/g, ''))
      
      const listHtml = cleanedList.map((z, i) => 
        `<div style="padding: 12px 16px; cursor: pointer; border-bottom: 1px solid #e0e0e0; font-size: 16px; transition: all 0.2s;" data-index="${i}" onmouseover="this.style.backgroundColor='#8bc34a'; this.style.color='white'" onmouseout="this.style.backgroundColor='white'; this.style.color='black'">${z}</div>`
      ).join('')
      
      editor.windowManager.open({
        title: `選擇 "${char}" 的注音`,
        body: {
          type: 'panel',
          items: [
            {
              type: 'htmlpanel',
              html: `<div id="zhuyin-list" style="max-height: 300px; overflow-y: auto; border: 1px solid #8bc34a; border-radius: 4px;">${listHtml}</div>`
            }
          ]
        },
        buttons: [
          {
            type: 'cancel',
            text: '取消'
          }
        ],
        onAction: (api, details) => {
          if (details.name === 'close') {
            api.close()
          }
        }
      })
      
      setTimeout(() => {
        const listEl = document.getElementById('zhuyin-list')
        if (listEl) {
          listEl.addEventListener('click', (e) => {
            const target = e.target.closest('[data-index]')
            if (target) {
              const index = parseInt(target.dataset.index)
              const zhuyin = cleanedList[index]
              const html = this.formatZhuyinHTML(char, zhuyin)
              editor.insertContent(html + '<span style="font-size: inherit;">&#8203;</span>')
              editor.selection.collapse(false)
              editor.windowManager.close()
            }
          })
        }
      }, 100)
    },
    formatZhuyinHTML(char, zhuyin) {
      const toneMarks = { 'ˊ': 2, 'ˇ': 3, 'ˋ': 4, '˙': 5, '·': 5, '.': 5 }
      let tone = ''
      let zhuyinBase = zhuyin
      
      for (let mark in toneMarks) {
        if (zhuyin.includes(mark)) {
          tone = mark
          zhuyinBase = zhuyin.replace(mark, '')
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
      
      return `<span class="char-with-zhuyin">${char}<span class="vertical-zhuyin">${zhuyinHTML}</span></span>`
    },
    async initEditor() {
      try {
        // 動態導入 TinyMCE
        const { default: tinymce } = await import('tinymce/tinymce')
        
        // 導入主題和插件
        await import('tinymce/themes/silver')
        await import('tinymce/models/dom')
        await import('tinymce/icons/default')
        await import('tinymce/plugins/lists')
        await import('tinymce/plugins/link')
        await import('tinymce/plugins/image')
        await import('tinymce/plugins/table')
        await import('tinymce/plugins/media')
        await import('tinymce/plugins/code')
        await import('tinymce/plugins/fullscreen')
        await import('tinymce/plugins/searchreplace')
        await import('tinymce/plugins/wordcount')
        await import('tinymce/plugins/visualblocks')
        await import('tinymce/plugins/charmap')
        await import('tinymce/plugins/anchor')
        await import('tinymce/plugins/preview')
        
        // 計算高度
        const calcHeight = () => {
          const rect = this.$refs.editor.getBoundingClientRect()
          return window.innerHeight - rect.top - 20
        }
        
        // 初始化編輯器
        tinymce.init({
          target: this.$refs.editor,
          height: calcHeight(),
          language: 'zh_TW',
          language_url: '/langs/zh_TW.js',
          menubar: 'edit insert view format table tools',
          plugins: 'lists link image table media code fullscreen searchreplace wordcount visualblocks charmap anchor preview contextmenu',
          toolbar: 'undo redo | formatselect fontsize | bold italic underline strikethrough | forecolor backcolor | alignleft aligncenter alignright | bullist numlist | outdent indent | link anchor image table media | hr charmap fontawesome zhuyin cleartableborder | removeformat | searchreplace visualblocks fullscreen preview code',
          fontsize_formats: '8pt 10pt 12pt 14pt 16pt 18pt 20pt 24pt 28pt 32pt 36pt 48pt 72pt',
          extended_valid_elements: 'i[class|style]',
          image_title: true,
          automatic_uploads: true,
          file_picker_types: 'image',
          images_upload_handler: (blobInfo, progress) => new Promise((resolve, reject) => {
            const formData = new FormData()
            formData.append('file', blobInfo.blob(), blobInfo.filename())
            
            fetch('/api/upload-image', {
              method: 'POST',
              body: formData
            })
            .then(response => response.json())
            .then(result => resolve(result.location))
            .catch(error => reject(error))
          }),
          content_css: ['https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css'],
          content_style: `
            body { font-family: Arial, sans-serif; font-size: 14px; }
            .char-with-zhuyin { display: inline-flex; align-items: center; margin-right: 0.2rem; }
            .vertical-zhuyin { display: flex; flex-direction: column; justify-content: center; margin-left: 1px; max-height: 1em; overflow: visible; position: relative; }
            .zhuyin-char { font-size: 0.28em; color: #333; line-height: 0.9; }
            .zhuyin-with-tone { display: flex; flex-direction: column; position: relative; }
            .tone-mark { position: absolute; right: -0.3em; top: 50%; transform: translateY(-50%); font-size: 0.28em; }
            .zhuyin-with-light { position: relative; display: inline-block; }
            .light-tone { position: absolute; top: -0.6em; left: 0; right: 0; text-align: center; font-size: 0.7em; color: #000; }
          `,
          skin_url: '/skins/ui/oxide',
          setup: (editor) => {
            this.editor = editor
            
            // 添加清除表格格線按鈕
            editor.ui.registry.addButton('cleartableborder', {
              text: '清除格線',
              onAction: () => {
                const table = editor.dom.getParent(editor.selection.getNode(), 'table')
                if (table) {
                  editor.dom.setAttrib(table, 'border', '0')
                  editor.dom.setStyle(table, 'border', 'none')
                  const cells = table.querySelectorAll('td, th')
                  cells.forEach(cell => {
                    editor.dom.setStyle(cell, 'border', 'none')
                  })
                  editor.notificationManager.open({
                    text: '已清除表格格線',
                    type: 'success',
                    timeout: 2000
                  })
                } else {
                  editor.notificationManager.open({
                    text: '請先選擇表格',
                    type: 'warning',
                    timeout: 2000
                  })
                }
              }
            })
            
            // 添加注音切換按鈕
            editor.ui.registry.addToggleButton('zhuyin', {
              text: '注音',
              onAction: (api) => {
                this.zhuyinMode = !this.zhuyinMode
                api.setActive(this.zhuyinMode)
                editor.notificationManager.open({
                  text: this.zhuyinMode ? '注音模式：已啟用' : '注音模式：已關閉',
                  type: 'info',
                  timeout: 2000
                })
              },
              onSetup: (api) => {
                api.setActive(this.zhuyinMode)
                return () => {}
              }
            })
            
            // 添加Font Awesome按鈕
            editor.ui.registry.addButton('fontawesome', {
              text: 'Icon',
              onAction: () => {
                editor.windowManager.open({
                  title: '插入 Font Awesome 圖標',
                  body: {
                    type: 'panel',
                    items: [
                      {
                        type: 'input',
                        name: 'iconcode',
                        label: '圖標代碼',
                        placeholder: '例如: fas fa-home'
                      }
                    ]
                  },
                  buttons: [
                    { type: 'cancel', text: '取消' },
                    { type: 'submit', text: '插入', primary: true }
                  ],
                  onSubmit: (api) => {
                    const data = api.getData()
                    const iconCode = data.iconcode.trim()
                    if (iconCode) {
                      editor.insertContent(`<i class="${iconCode}"></i>&nbsp;`)
                    }
                    api.close()
                  }
                })
              }
            })
            
            editor.on('change keyup', () => {
              const content = editor.getContent()
              this.content = content
              this.$emit('input', content)
            })
          },
          init_instance_callback: (editor) => {
            this.editor = editor
            if (this.value) {
              editor.setContent(this.value)
            }
            
            // 監聽輸入法結束
            editor.getBody().addEventListener('compositionend', async (e) => {
              if (!this.zhuyinMode) return
              const data = e.data
              if (data && /[\u4e00-\u9fff]/.test(data)) {
                const char = data
                
                // 獲取當前節點和範圍
                const node = editor.selection.getNode()
                const range = editor.selection.getRng()
                
                // 向前刪除一個字元
                if (range.startOffset > 0) {
                  range.setStart(node.firstChild || node, range.startOffset - 1)
                  range.deleteContents()
                }
                
                // 獲取注音
                const zhuyinList = await this.getZhuyin(char)
                if (zhuyinList && zhuyinList.length > 0) {
                  const cleanedList = zhuyinList.map(z => z.replace(/[（(]又音[）)]/g, '').replace(/[（(]語音[）)]/g, '').replace(/[（(]讀音[）)]/g, ''))
                  const uniqueList = [...new Set(cleanedList)]
                  if (uniqueList.length > 1) {
                    this.showZhuyinDialog(editor, char, uniqueList)
                  } else {
                    const zhuyin = uniqueList[0]
                    const html = this.formatZhuyinHTML(char, zhuyin)
                    editor.insertContent(html + '<span style="font-size: inherit;">&#8203;</span>')
                    editor.selection.collapse(false)
                  }
                } else {
                  editor.insertContent(char + '<span style="font-size: inherit;">&#8203;</span>')
                  editor.selection.collapse(false)
                }
              }
            })
          }
        })
      } catch (error) {
        console.error('TinyMCE 初始化失敗:', error)
        // 如果 TinyMCE 失敗，顯示普通 textarea
        this.$refs.editor.style.display = 'block'
        this.$refs.editor.style.height = '400px'
      }
    }
  }
}
</script>
</template>