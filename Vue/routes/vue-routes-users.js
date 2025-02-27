let routes= [];
let routes_list= [];

import List from '../pages/users/List.vue'
import Form from '../pages/users/Form.vue'
import Item from '../pages/users/Item.vue'
import Filters from "../pages/users/Filters.vue";


routes_list = {

    path: '/customers',
    name: 'users.index',
    component: List,
    props: true,
    children: [
        {
            path: 'filters',
            name: 'users.filters',
            component: Filters,
            props: true,
        },
        {
            path: 'form/:id?',
            name: 'users.form',
            component: Form,
            props: true,
        },
        {
            path: 'view/:id?',
            name: 'users.view',
            component: Item,
            props: true,
        }
    ]
};

routes.push(routes_list);

export default routes;

