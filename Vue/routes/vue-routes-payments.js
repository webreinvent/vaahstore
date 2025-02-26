let routes= [];
let routes_list= [];

import List from '../pages/payments/List.vue'
import Form from '../pages/payments/Form.vue'
import Item from '../pages/payments/Item.vue'
import Filters from "../pages/payments/Filters.vue";

routes_list = {

    path: '/payments',
    name: 'payments.index',
    component: List,
    props: true,
    children:[
        {
            path: 'filters',
            name: 'payments.filters',
            component: Filters,
            props: true,
        },
        {
            path: 'form/:id?',
            name: 'payments.form',
            component: Form,
            props: true,
        },
        {
            path: 'view/:id?',
            name: 'payments.view',
            component: Item,
            props: true,
        }
    ]
};

routes.push(routes_list);

export default routes;

