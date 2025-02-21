let routes= [];
let routes_list= [];

import List from '../pages/categories/List.vue'
import Form from '../pages/categories/Form.vue'
import Item from '../pages/categories/Item.vue'

routes_list = {

    path: '/categories',
    name: 'categories.index',
    component: List,
    props: true,
    children:[
        {
            path: 'form/:id?',
            name: 'categories.form',
            component: Form,
            props: true,
        },
        {
            path: 'view/:id?',
            name: 'categories.view',
            component: Item,
            props: true,
        }
    ]
};

routes.push(routes_list);

export default routes;

