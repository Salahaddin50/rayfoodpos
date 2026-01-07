import axios from "axios";

export const token = {
    namespaced: true,
    state: {
        autoEnabled: true,
    },
    getters: {
        autoEnabled: function (state) {
            return state.autoEnabled;
        }
    },
    actions: {
        generate: function (context, payload) {
            return new Promise((resolve, reject) => {
                axios.post('/admin/token/generate', payload).then(res => {
                    resolve(res);
                }).catch((err) => {
                    reject(err);
                });
            });
        },
        reset: function (context, payload) {
            return new Promise((resolve, reject) => {
                axios.post('/admin/token/reset', payload).then(res => {
                    resolve(res);
                }).catch((err) => {
                    reject(err);
                });
            });
        },
        currentCounter: function (context, payload) {
            return new Promise((resolve, reject) => {
                axios.get('/admin/token/current-counter', { params: payload }).then(res => {
                    resolve(res);
                }).catch((err) => {
                    reject(err);
                });
            });
        },
    },
};

