const routes = [
  {
    path: '/',
    component: () => import('../layouts/MainLayout.vue'),
    children: [
      {
        path: '',
        component: () => import('../pages/IndexPage.vue'),
      },
      {
        path: 'veiculos',
        component: () => import('../pages/VehiclesPage.vue'),
      },
      {
        path: 'veiculos/:id',
        component: () => import('../pages/VehicleDetailPage.vue'),
      },
      {
        path: 'entrar',
        component: () => import('../pages/ComingSoonPage.vue'),
        props: {
          title: 'Entrar',
          description: 'A página de acesso estará disponível em breve.',
        },
      },
      {
        path: 'anunciar',
        component: () => import('../pages/ComingSoonPage.vue'),
        props: {
          title: 'Anunciar veículo',
          description: 'A página para anunciar um veículo estará disponível em breve.',
        },
      },
    ],
  },

  {
    path: '/:catchAll(.*)*',
    component: () => import('../pages/ErrorNotFound.vue'),
  },
]

export default routes
