
let routes= [];
let routes_list= [];

import List from '../pages/vendors/List.vue'
import Form from '../pages/vendors/Form.vue'
import Item from '../pages/vendors/Item.vue'
import Product from '../pages/vendors/Product.vue'

routes_list = {

    path: '/vendors',
    name: 'vendors.index',
    component: List,
    props: true,
    children:[
        {
            path: 'form/:id?',
            name: 'vendors.form',
            component: Form,
            props: true,
        },
        {
            path: 'view/:id?',
            name: 'vendors.view',
            component: Item,
            props: true,
        },
        {
            path: 'product/:id?',
            name: 'vendors.product',
            component: Product,
            props: true,
        }
    ]
};

routes.push(routes_list);

export default routes;

