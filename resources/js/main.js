import { createApp } from 'vue';
import App from './app.vue';
import router from './router';
import '../css/app.css';

const app = createApp(App);
app.use(router).mount('#app');