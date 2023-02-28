let routes= [];
let routes_list= [];

import List from '../pages/brands/List.vue'
import Form from '../pages/brands/Form.vue'
import Item from '../pages/brands/Item.vue'

routes_list = {

    path: '/brands',
    name: 'brands.index',
    component: List,
    props: true,
    children:[
        {
            path: 'form/:id?',
            name: 'brands.form',
            component: Form,
            props: true,
        },
        {
            path: 'view/:id?',
            name: 'brands.view',
            component: Item,
            props: true,
        }
    ]
};

routes.push(routes_list);

export default routes;

