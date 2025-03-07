
let routes= [];
let routes_list= [];

import List from '../pages/shipments/List.vue'
import Form from '../pages/shipments/Form.vue'
import Item from '../pages/shipments/Item.vue'
import Filters from "../pages/shipments/Filters.vue";

routes_list = {

    path: '/shipments',
    name: 'shipments.index',
    component: List,
    props: true,
    children:[
        {
            path: 'filters',
            name: 'shipments.filters',
            component: Filters,
            props: true,
        },
        {
            path: 'form/:id?',
            name: 'shipments.form',
            component: Form,
            props: true,
        },
        {
            path: 'view/:id?',
            name: 'shipments.view',
            component: Item,
            props: true,
        }
    ]
};

routes.push(routes_list);

export default routes;

