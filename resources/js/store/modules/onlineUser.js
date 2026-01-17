import axios from "axios";
import appService from "../../services/appService";

export const onlineUser = {
    namespaced: true,
    state: {
        lists: [],
    },
    getters: {
        lists: function (state) {
            return state.lists;
        },
    },
    actions: {
        lists: function (context, payload) {
            return new Promise((resolve, reject) => {
                let url = "admin/online-users";
                if (payload) {
                    url = url + appService.requestHandler(payload);
                }
                axios
                    .get(url)
                    .then((res) => {
                        if (typeof payload?.vuex === "undefined" || payload?.vuex === true) {
                            context.commit("lists", res.data.data);
                        }
                        resolve(res);
                    })
                    .catch((err) => reject(err));
            });
        },
        export: function (context, payload) {
            return new Promise((resolve, reject) => {
                let url = 'admin/online-users/export';
                if (payload) {
                    url = url + appService.requestHandler(payload);
                }
                axios.get(url, { responseType: 'blob' }).then((res) => {
                    resolve(res);
                }).catch((err) => reject(err));
            });
        },
        downloadSample: function () {
            return new Promise((resolve, reject) => {
                axios.get('admin/online-users/download-sample', { responseType: 'blob' }).then((res) => {
                    resolve(res);
                }).catch((err) => reject(err));
            });
        },
        import: function (context, payload) {
            return new Promise((resolve, reject) => {
                axios.post('admin/online-users/import/file', payload.form).then((res) => {
                    resolve(res);
                }).catch((err) => reject(err));
            });
        },
        store: function (context, payload) {
            return new Promise((resolve, reject) => {
                axios
                    .post("admin/online-users", payload)
                    .then((res) => resolve(res))
                    .catch((err) => reject(err));
            });
        },
        update: function (context, payload) {
            return new Promise((resolve, reject) => {
                axios
                    .post(`admin/online-users/${payload.id}`, payload.data)
                    .then((res) => resolve(res))
                    .catch((err) => reject(err));
            });
        },
        destroy: function (context, payload) {
            return new Promise((resolve, reject) => {
                axios
                    .delete(`admin/online-users/${payload}`)
                    .then((res) => resolve(res))
                    .catch((err) => reject(err));
            });
        },
    },
    mutations: {
        lists: function (state, payload) {
            state.lists = payload;
        },
    },
};


