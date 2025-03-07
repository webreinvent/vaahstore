let routes= [];
let routes_list= [];

import List from '../pages/customergroups/List.vue'
import Form from '../pages/customergroups/Form.vue'
import Item from '../pages/customergroups/Item.vue'
import Filters from '../pages/customergroups/Filters.vue'

routes_list = {

    path: '/customergroups',
    name: 'customergroups.index',
    component: List,
    props: true,
    children:[
        {
            path: 'form/:id?',
            name: 'customergroups.form',
            component: Form,
            props: true,
        },
        {
            path: 'view/:id?',
            name: 'customergroups.view',
            component: Item,
            props: true,
        },
        {
            path: 'filters',
            name: 'customergroups.filters',
            component: Filters,
            props: true,
        },
    ]
};

routes.push(routes_list);

export default routes;

