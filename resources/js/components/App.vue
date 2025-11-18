<template>
  <div class="app">
    <header class="header">
      <div class="logo">
        <img src="/images/logo.png" alt="教材編輯系統" class="logo-img">
        <span class="logo-text">教材編輯系統</span>
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
.header { background: #fffef0; color: #333; padding: 1rem; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
.logo { display: flex; align-items: center; gap: 1rem; }
.logo-img { height: 50px; }
.logo-text { font-size: 1.5rem; font-weight: bold; }
.main-container { display: flex; flex: 1; }
.sidebar { width: 200px; background: #8bc34a; padding: 1rem; border-right: 1px solid #7cb342; }
.sidebar ul { list-style: none; padding: 0; }
.sidebar li { margin-bottom: 0.5rem; }
.sidebar a { color: white; text-decoration: none; display: block; padding: 0.5rem; border-radius: 4px; transition: all 0.3s; }
.sidebar a:hover, .sidebar a.router-link-active { background: #7cb342; color: white; }
.content { flex: 1; padding: 2rem; background: #ffffff; overflow-y: auto; }

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