import axios from 'axios'


export const tableDiningOrder = {
    namespaced: true,
    state: {
        show: {},
        orderItems: {},
        orderBranch: {},
        orderUser: {},
    },
    getters: {
        show: function (state) {
            return state.show;
        },
        orderItems: function (state) {
            return state.orderItems;
        },
        orderBranch: function (state) {
            return state.orderBranch;
        },
        orderUser: function (state) {
            return state.orderUser;
        }
    },
    actions: {
        save: function (context, payload) {
            return new Promise((resolve, reject) => {
                // Debug: Log what's being sent
                console.log('=== STORE ACTION - Sending to API ===');
                console.log('pickup_option in payload:', payload.pickup_option);
                console.log('Full payload keys:', Object.keys(payload));
                console.log('Payload pickup_option value:', payload.pickup_option);
                console.log('Full payload:', JSON.stringify(payload, null, 2));
                console.log('=== END STORE DEBUG ===');
                
                axios.post("table/dining-order", payload).then((res) => {
                    console.log('=== STORE ACTION - SUCCESS ===');
                    console.log('Response status:', res.status);
                    console.log('Response data:', res.data);
                    resolve(res);
                }).catch((err) => {
                    console.error('=== STORE ACTION - ERROR ===');
                    console.error('Error object:', err);
                    console.error('Error response:', err.response);
                    console.error('Error status:', err.response?.status);
                    console.error('Error data:', err.response?.data);
                    console.error('Error message:', err.response?.data?.message);
                    console.error('Validation errors:', err.response?.data?.errors);
                    reject(err);
                });
            });
        },
        show: function (context, payload) {
            return new Promise((resolve, reject) => {
                axios.get(`table/dining-order/show/${payload}`).then((res) => {
                    context.commit("show", res.data.data);
                    context.commit("orderItems", res.data.data.order_items);
                    context.commit("orderBranch", res.data.data.branch);
                    context.commit("orderUser", res.data.data.user);
                    resolve(res);
                }).catch((err) => {
                    reject(err);
                });
            });
        },
    },
    mutations: {
        show: function (state, payload) {
            state.show = payload;
        },
        orderItems: function (state, payload) {
            state.orderItems = payload;
        },
        orderBranch: function (state, payload) {
            state.orderBranch = payload;
        },
        orderUser: function (state, payload) {
            state.orderUser = payload;
        }
    },
}
