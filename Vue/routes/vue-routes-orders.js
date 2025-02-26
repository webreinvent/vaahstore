let routes= [];
let routes_list= [];

import List from '../pages/orders/List.vue'
import Form from '../pages/orders/Form.vue'
import Item from '../pages/orders/Item.vue'
import Filters from "../pages/orders/Filters.vue";


routes_list = {

    path: '/orders',
    name: 'orders.index',
    component: List,
    props: true,
    children:[
        {
            path: 'filters',
            name: 'orders.filters',
            component: Filters,
            props: true,
        },
        {
            path: 'form/:id?',
            name: 'orders.form',
            component: Form,
            props: true,
        },
        {
            path: 'view/:id?',
            name: 'orders.view',
            component: Item,
            props: true,
        },

    ]
};

routes.push(routes_list);

export default routes;

