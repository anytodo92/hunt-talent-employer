require('./bootstrap');

import { createApp, nextTick } from 'vue/dist/vue.esm-bundler.js';
window.createApp = createApp;
window.nextTick = nextTick;