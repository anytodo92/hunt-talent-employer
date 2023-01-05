import { createStore } from "vuex";
import moment from 'moment';
import axios from 'axios';

export default createStore({
    state: {
        count: 0,
    },
    getters: {

    },
    mutations: {
        increment (state) {
            state.count++
        },
        init (state, payload) {
        },
    },
    actions: {
    }
})
