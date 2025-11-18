import './bootstrap';
import Vue from 'vue';
import VueRouter from 'vue-router';
import App from './components/App.vue';
import HandbookList from './components/HandbookList.vue';
import HandbookEditor from './components/HandbookEditor.vue';

Vue.use(VueRouter);

const routes = [
    { path: '/', component: HandbookList },
    { path: '/edit/:id?', component: HandbookEditor, props: true }
];

const router = new VueRouter({ mode: 'history', routes });

new Vue({
    router,
    render: h => h(App)
}).$mount('#app');
