let routes= [];
let routes_list= [];

import List from '../pages/warehouses/List.vue'
import Form from '../pages/warehouses/Form.vue'
import Item from '../pages/warehouses/Item.vue'
import Filters from '../pages/warehouses/Filters.vue'

routes_list = {

    path: '/warehouses',
    name: 'warehouses.index',
    component: List,
    props: true,
    children:[
        {
            path: 'form/:id?',
            name: 'warehouses.form',
            component: Form,
            props: true,
        },
        {
            path: 'view/:id?',
            name: 'warehouses.view',
            component: Item,
            props: true,
        },
        {
            path: 'filters',
            name: 'warehouses.filters',
            component: Filters,
            props: true,
        },
    ]
};

routes.push(routes_list);

export default routes;

