Nova.booting((Vue, router, store) => {
    router.addRoutes([
        {
            name: 'monthly-report',
            path: '/monthly-report',
            component: require('./components/Tool'),
        },
    ])
})
