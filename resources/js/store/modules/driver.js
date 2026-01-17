import axios from "axios";
import appService from "../../services/appService";

export const driver = {
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
                let url = "admin/drivers";
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
        store: function (context, payload) {
            return new Promise((resolve, reject) => {
                axios
                    .post("admin/drivers", payload)
                    .then((res) => {
                        resolve(res);
                    })
                    .catch((err) => reject(err));
            });
        },
        update: function (context, payload) {
            return new Promise((resolve, reject) => {
                axios
                    .post(`admin/drivers/${payload.id}`, payload.data)
                    .then((res) => {
                        resolve(res);
                    })
                    .catch((err) => reject(err));
            });
        },
        destroy: function (context, payload) {
            return new Promise((resolve, reject) => {
                axios
                    .delete(`admin/drivers/${payload}`)
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


