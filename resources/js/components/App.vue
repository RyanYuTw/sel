<template>
  <div class="app">
    <header class="header">
      <div class="logo">
        <img src="/images/logo.png" alt="教案編輯系統" class="logo-img">
        <span class="logo-text">教案編輯系統</span>
      </div>
    </header>

    <div class="main-container">
      <nav class="sidebar">
        <ul>
          <li><router-link to="/">手冊列表</router-link></li>
          <li><router-link to="/edit">新增手冊</router-link></li>
        </ul>
      </nav>

      <main class="content">
        <router-view />
      </main>
    </div>
  </div>
</template>

<style scoped>
.app { height: 100vh; display: flex; flex-direction: column; }
.header { background: linear-gradient(135deg, #f5f7f0 0%, #fdfef8 100%); color: #333; padding: 1rem; box-shadow: 0 2px 8px rgba(139, 195, 74, 0.1); border-bottom: 2px solid #e8f5e0; }
.logo { display: flex; align-items: center; gap: 1rem; }
.logo-img { height: 50px; }
.logo-text { font-size: 1.5rem; font-weight: bold; color: #5a8c2e; }
.main-container { display: flex; flex: 1; }
.sidebar { width: 200px; background: linear-gradient(180deg, #f0f8e8 0%, #e8f5e0 100%); padding: 1rem; border-right: 2px solid #d4ecc4; box-shadow: 2px 0 8px rgba(139, 195, 74, 0.08); }
.sidebar ul { list-style: none; padding: 0; }
.sidebar li { margin-bottom: 0.5rem; }
.sidebar a { color: #5a8c2e; text-decoration: none; display: block; padding: 0.75rem 1rem; border-radius: 8px; transition: all 0.3s; font-weight: 500; }
.sidebar a:hover { background: rgba(139, 195, 74, 0.15); color: #4a7a24; transform: translateX(4px); }
.sidebar a.router-link-active { background: linear-gradient(135deg, #8bc34a 0%, #7cb342 100%); color: white; box-shadow: 0 2px 6px rgba(139, 195, 74, 0.3); }
.content { flex: 1; padding: 2rem; background: linear-gradient(135deg, #fffef0 0%, #fff9e6 50%, #fffef0 100%); overflow-y: auto; }

@media (max-width: 768px) {
  .main-container { flex-direction: column; }
  .sidebar { width: 100%; padding: 0.5rem; }
  .sidebar ul { display: flex; gap: 0.5rem; }
  .sidebar li { margin-bottom: 0; }
  .content { padding: 1rem; }
  .logo { font-size: 1.2rem; }
}

@media (max-width: 480px) {
  .header { padding: 0.75rem; }
  .logo-img { height: 35px; }
  .logo-text { font-size: 1rem; }
  .content { padding: 0.5rem; }
}
</style>
