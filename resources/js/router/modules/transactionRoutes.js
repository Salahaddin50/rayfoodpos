export default [
    {
        path: '/admin/transactions',
        component: () => import("../../components/admin/transactions/TransactionListComponent"),
        name: 'admin.transactions.list',
        meta: {
            isFrontend: false,
            auth: true,
            permissionUrl: 'transactions',
            breadcrumb: 'transactions'
        }
    }
]
